<?php
// functions.php — helpers (port of src/lib/utils.ts, src/lib/image.ts, src/lib/ref.ts, src/lib/store.ts)

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!defined('APP_RAW_DIR')) {
    $rawCandidates = [
        dirname(__DIR__, 2) . '/data/raw',  // repo root (dev)
        __DIR__ . '/../data/raw',           // bundled with the PHP app (deploy)
    ];
    foreach ($rawCandidates as $c) {
        if (is_dir($c)) {
            define('APP_RAW_DIR', $c);
            break;
        }
    }
    if (!defined('APP_RAW_DIR')) define('APP_RAW_DIR', __DIR__ . '/../data/raw');
}
if (!defined('APP_ROOT')) define('APP_ROOT', dirname(__DIR__));

/* ---------------------------------- utils.ts ---------------------------------- */

function esc(?string $s): string
{
    if ($s === null) return '';
    return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function site_base_url(): string
{
    $host = $_SERVER['HTTP_HOST'] ?? 'blowdryonwheels-com.wp-arenastaging.com';
    return 'https://' . $host;
}

function to_slug(string $s): string
{
    $s = strtolower($s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? $s;
    return trim($s, '-');
}

function fmt_price(float|int $value, string $currency = 'AED', float $rate = 1.0, int $digits = 0): string
{
    $v = $value * $rate;
    $n = number_format($v, $digits, '.', ',');
    $sym = match ($currency) {
        'USD' => '$', 'EUR' => '€', 'GBP' => '£', 'INR' => '₹', default => 'AED ',
    };
    return $sym . $n;
}

function format_area(int|float $sqm, string $unit = 'Sqft'): string
{
    $v = $unit === 'Sqft' ? $sqm * 10.764 : $sqm;
    return number_format(round($v)) . ' ' . $unit;
}

function wa_link(string $phone, string $text): string
{
    $clean = preg_replace('/[^0-9]/', '', $phone);
    return 'https://wa.me/' . $clean . '?text=' . rawurlencode($text);
}

function mailto_link(string $email, string $subject, string $body = ''): string
{
    $q = http_build_query(['subject' => $subject] + ($body !== '' ? ['body' => $body] : []));
    return 'mailto:' . $email . '?' . $q;
}

function parse_id_from_slug(string $slug): int
{
    if (preg_match('/(\d+)-?$/', $slug, $m)) return (int) $m[1];
    return 0;
}

/** FAQPage schema.org JSON-LD */
function faq_schema(array $items): array
{
    return [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($i) => [
            '@type' => 'Question',
            'name' => $i['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $i['a']],
        ], $items),
    ];
}

/* ---------------------------------- image.ts ---------------------------------- */

function local_image(?string $url): ?string
{
    global $LOCAL_OVERRIDES;
    if (!$url) return null;
    if (!preg_match('/([^\/]+)\.(?:webp|jpe?g|png|avif)$/i', $url, $m)) return null;
    return $LOCAL_OVERRIDES[$m[1]]['base'] ?? null;
}

function local_image_for(?string $url, string $size): ?string
{
    global $LOCAL_OVERRIDES;
    if (!$url) return null;
    if (!preg_match('/([^\/]+)\.(?:webp|jpe?g|png|avif)$/i', $url, $m)) return null;
    $o = $LOCAL_OVERRIDES[$m[1]] ?? null;
    if (!$o) return null;
    return $o['sizes'][$size] ?? $o['base'];
}

function cf_path(string $rest, string $size): string
{
    $file = preg_replace('/\.(svg|png|jpe?g|avif)$/i', '.webp', $rest);
    $i = strrpos($file, '/');
    if ($i !== false) return CDN_BASE . '/x/' . substr($file, 0, $i) . '/' . $size . '/' . substr($file, $i + 1);
    return CDN_BASE . '/x/' . $size . '/' . $file;
}

/** ggfx S3 -> cloudfront transform URL (cft port). */
function cft(?string $url, int $w = 340, int $h = 252): string
{
    if (!$url) return '';
    $local = local_image_for($url, "{$w}x{$h}");
    if ($local) return $local;
    if (str_contains($url, CDN_BASE)) return $url;
    if (!preg_match('#/i/(.+)$#', $url, $m)) return $url;
    if (strpos($m[1], '/') === false) return CDN_BASE . '/i/' . $m[1];
    return cf_path($m[1], "{$w}x{$h}");
}

/** Width-only cloudfront transform (cfw port). */
function cfw(?string $url, int $w = 744): string
{
    if (!$url) return '';
    $local = local_image_for($url, "{$w}x");
    if ($local) return $local;
    if (str_contains($url, CDN_BASE)) return $url;
    if (!preg_match('#/i/(.+)$#', $url, $m)) return $url;
    if (strpos($m[1], '/') === false) return CDN_BASE . '/i/' . $m[1];
    return cf_path($m[1], "{$w}x");
}

/** Raw cloudfront /i/ URL (no transform) — root-level keys 403 on /x/ transforms. */
function cf_original(?string $url): string
{
    if (!$url) return '';
    if (str_contains($url, CDN_BASE)) return $url;
    if (preg_match('#https?://ggfx-providentestate\.s3\.eu-west-2\.amazonaws\.com(/.*)#', $url, $m)) {
        return CDN_BASE . $m[1];
    }
    return $url;
}

/** Legacy cf() helper from ref.ts (passthrough). */
function cf(?string $url, int $width = 340): ?string
{
    return $url ?: null;
}

/* ---------------------------------- ref.ts ---------------------------------- */

function load_json(string $rel): ?array
{
    $file = APP_RAW_DIR . '/' . ltrim($rel, '/');
    if (!is_file($file)) return null;
    $raw = file_get_contents($file);
    if ($raw === false) return null;
    $j = json_decode($raw, true, 2048);
    return is_array($j) ? $j : null;
}

/** Load page-data for a route (getPageData port). Returns raw JSON or null. */
function get_page_data(string $route): ?array
{
    $rel = $route === '/' ? 'index' : ltrim($route, '/');
    $rel = preg_replace('#/$#', '', $rel);
    $candidates = [
        'pages/' . $rel . '.json',
        'listings/' . $rel . '.json',
        'listings/' . $rel,
        'projects/' . $rel . '.json',
        'projects/' . $rel,
        'properties/' . $rel . '.json',
        'properties/' . $rel,
    ];
    // /new-projects/* are project pages; the properties/ snapshots are stale
    // scrapes (property-details responses) and must not shadow them.
    if (str_starts_with($route, '/new-projects')) {
        $candidates = array_values(array_filter($candidates, fn ($c) => !str_starts_with($c, 'properties/')));
    }
    foreach ($candidates as $c) {
        $j = load_json($c);
        if ($j) return $j;
    }
    return null;
}

/** Classify a page-data payload by template (classify port). */
function classify(?array $j, string $route): ?array
{
    if (!$j || !isset($j['result'])) return null;
    $res = $j['result'];
    $data = $res['data'] ?? [];
    $keys = array_keys($data);
    if (isset($res['serverData']) && is_array($res['serverData'])) {
        $sd = $res['serverData'];
        $inner = $sd['data'] ?? $sd;
        if (is_array($inner) && ($inner['status'] ?? null) === true && isset($inner['hits'])) {
            $h0 = $inner['hits'][0] ?? [];
            $kind = isset($h0['slug'])
                ? (str_starts_with($route, '/new-projects') ? 'project' : 'listing')
                : ((isset($h0['building_type']) || isset($h0['search_type'])) ? 'project' : 'listing');
            return ['kind' => $kind, 'data' => $inner, 'route' => $route];
        }
        if (is_array($inner) && ($inner['status'] ?? null) === true && isset($inner['data']) && isset($inner['message'])) {
            $d = $inner['data'];
            $isProject = (is_array($d) && ($d['department'] ?? '') === 'new_developments')
                || (is_array($d) && is_string($d['building_type'] ?? null) && isset($d['completion_year']));
            if ($isProject) return ['kind' => 'project', 'data' => ['hits' => [$d]], 'route' => $route];
            return ['kind' => 'property', 'data' => $d, 'route' => $route];
        }
    }
    foreach (['strapiPage', 'strapiBlog', 'strapiTeam', 'strapiAreaGuide', 'strapiCareer', 'strapiEvent', 'strapiDeveloper', 'strapiOffice'] as $key) {
        if (in_array($key, $keys, true)) return ['kind' => 'page', 'data' => $data[$key], 'route' => $route];
    }
    return null;
}

/* ---------------------------------- store.ts ---------------------------------- */

function load_rel(string $rel): ?array
{
    return load_json($rel . '.json');
}

function get_listing(string $rel): ?array
{
    $j = load_rel('listings/' . $rel);
    if (!$j || !isset($j['result'])) return null;
    $res = $j['result'];
    $sd = $res['serverData'] ?? null;
    if (!is_array($sd)) return null;
    $inner = $sd['data'] ?? $sd;
    return (is_array($inner) && isset($inner['hits'])) ? $inner : null;
}

function get_property(string $rel): ?array
{
    $j = load_rel('properties/' . $rel);
    if (!$j || !isset($j['result'])) return null;
    $res = $j['result'];
    $sd = $res['serverData'] ?? null;
    if (!is_array($sd)) return null;
    $inner = $sd['data'] ?? $sd;
    if (!is_array($inner)) return null;
    if (isset($inner['data']) && is_array($inner['data']) && ($inner['status'] ?? null) === true) return $inner['data'];
    return $inner;
}

/* ---------------------------------- misc ---------------------------------- */

/** Generic JSON response helper for api/*.php endpoints. */
function json_response(mixed $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function json_body(): array
{
    if (isset($_POST['json']) && is_string($_POST['json']) && $_POST['json'] !== '') {
        $j = json_decode($_POST['json'], true);
        if (is_array($j)) return $j;
    }
    $raw = file_get_contents('php://input');
    if ($raw === false || $raw === '') return [];
    if (preg_match('/(?:^|&)json=/', $raw)) {
        parse_str($raw, $fields);
        if (isset($fields['json']) && is_string($fields['json']) && $fields['json'] !== '') {
            $j = json_decode($fields['json'], true);
            if (is_array($j)) return $j;
        }
    }
    $j = json_decode($raw, true);
    return is_array($j) ? $j : [];
}

/** Alt text convention: "{label} - Zoya Ventures Real Estate" */
function alt_text(string $label): string
{
    return trim($label) . ' - ' . APP_NAME;
}

function now_iso(): string
{
    return gmdate('Y-m-d\TH:i:s.v\Z');
}

/**
 * Apply the Zoya Ventures logo as a top-left watermark to an image.
 * Works with JPEG, PNG, WebP. Preserves original format and aspect ratio.
 * Returns true on success, false on failure. Errors are logged but not exposed.
 */
function apply_logo_watermark(string $imagePath): bool
{
    $logoPath = __DIR__ . '/../images/logo.png';
    if (!file_exists($logoPath)) { error_log('Watermark: logo.png not found'); return false; }
    if (!file_exists($imagePath)) { error_log('Watermark: image not found: ' . $imagePath); return false; }

    $logoInfo = @getimagesize($logoPath);
    if ($logoInfo === false) { error_log('Watermark: cannot read logo.png'); return false; }

    $imageInfo = @getimagesize($imagePath);
    if ($imageInfo === false) { error_log('Watermark: cannot read image'); return false; }

    $mime = $imageInfo['mime'] ?? '';
    $ext = match($mime) {
        'image/jpeg' => 'jpeg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        default => null,
    };
    if ($ext === null) { error_log('Watermark: unsupported mime ' . $mime); return false; }

    $srcFunc = match($ext) {
        'jpeg' => 'imagecreatefromjpeg',
        'png' => 'imagecreatefrompng',
        'webp' => 'imagecreatefromwebp',
    };
    $dstFunc = match($ext) {
        'jpeg' => 'imagejpeg',
        'png' => 'imagepng',
        'webp' => 'imagewebp',
    };

    $src = @$srcFunc($imagePath);
    if (!$src) { error_log('Watermark: cannot create image resource'); return false; }
    $sw = imagesx($src); $sh = imagesy($src);
    if ($sw < 50 || $sh < 50) { imagedestroy($src); return false; }

    $logoW = $logoInfo[0]; $logoH = $logoInfo[1];
    $maxLogoW = (int)($sw * 0.25);
    $maxLogoH = (int)($sh * 0.12);
    $scale = min($maxLogoW / max($logoW, 1), $maxLogoH / max($logoH, 1), 1.0);
    $finalW = (int)($logoW * $scale);
    $finalH = (int)($logoH * $scale);
    if ($finalW < 1 || $finalH < 1) { imagedestroy($src); return false; }

    $logo = @imagecreatefrompng($logoPath);
    if (!$logo) { imagedestroy($src); return false; }
    $scaledLogo = imagecreatetruecolor($finalW, $finalH);
    imagecopyresampled($scaledLogo, $logo, 0, 0, 0, 0, $finalW, $finalH, $logoW, $logoH);
    imagedestroy($logo);

    $margin = (int)min(30, $sw * 0.03, $sh * 0.03);
    imagecopy($src, $scaledLogo, $margin, $margin, 0, 0, $finalW, $finalH);
    imagedestroy($scaledLogo);

    $quality = ($ext === 'jpeg') ? 85 : 9;
    $result = @$dstFunc($src, $imagePath, $quality);
    imagedestroy($src);
    if (!$result) { error_log('Watermark: failed to save image'); }
    return $result;
}
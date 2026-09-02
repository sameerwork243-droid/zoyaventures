<?php
// rich.php — Rich component port (src/components/rich.tsx)

require_once __DIR__ . '/../functions.php';

function rich(?string $html): string
{
    if ($html === null || $html === '') return '';
    return '<div>' . localize_links($html) . '</div>';
}

/** Rewrite https://providentestate.com/<path> links to local <path> so nothing sends visitors to the original site. */
function localize_links(?string $html): string
{
    if ($html === null || $html === '') return '';
    $html = preg_replace('#https?://(?:www\.)?providentestate\.com(?=/|#|\?)#i', '', $html) ?? $html;
    $html = preg_replace('#https?://(?:www\.)?providentestate\.com\b#i', '/', $html) ?? $html;
    return $html;
}

function cta_href(?array $cta, string $fallback = '#'): string
{
    if (!$cta) return $fallback;
    if (!empty($cta['custom_link'])) {
        $href = $cta['custom_link'];
        if (is_string($href) && str_contains($href, 'popup=download-report')) {
            $q = parse_url($href, PHP_URL_QUERY);
            $params = [];
            if (is_string($q)) parse_str($q, $params);
            if (!empty($params['file_url'])) return $params['file_url'];
        }
        return localize_links(is_string($href) ? $href : null);
    }
    if (!empty($cta['menu']['slug'])) {
        $parent = $cta['menu']['strapi_parent'] ?? null;
        if (is_array($parent) && !empty($parent['slug'])) return '/' . $parent['slug'] . '/' . $cta['menu']['slug'] . '/';
        return '/' . $cta['menu']['slug'] . '/';
    }
    return $fallback;
}

function strip_html(?string $s): string
{
    if ($s === null) return '';
    $s = preg_replace('/<[^>]*>/', '', $s) ?? $s;
    $s = str_replace(['&amp;', '&#x27;', '&quot;'], ['&', "'", '"'], $s);
    return trim($s);
}

/** CountryFlag port (src/components/phone-flag.tsx) — default AE. */
function country_flag(string $code = 'AE'): string
{
    $flags = [
        'AE' => '🇦🇪', 'GB' => '🇬🇧', 'US' => '🇺🇸', 'IN' => '🇮🇳', 'PK' => '🇵🇰', 'SA' => '🇸🇦',
        'EG' => '🇪🇬', 'PH' => '🇵🇭', 'BD' => '🇧🇩', 'LK' => '🇱🇰', 'JO' => '🇯🇴', 'LB' => '🇱🇧',
        'IQ' => '🇮🇶', 'IR' => '🇮🇷', 'OM' => '🇴🇲', 'QA' => '🇶🇦', 'KW' => '🇰🇼', 'BH' => '🇧🇭',
    ];
    $flag = $flags[$code] ?? $flags['AE'];
    return '<span role="img" aria-label="' . esc($code) . '" style="font-family:Apple Color Emoji, Segoe UI Emoji, Noto Color Emoji, Twemoji Mozilla, EmojiOne Color, Segoe UI Symbol, sans-serif;-webkit-font-smoothing:antialiased;text-transform:none;line-height:1">' . $flag . '</span>';
}
<?php
// store.php — data corpus helpers (port of src/lib/store.ts + src/lib/props.tsx)

require_once __DIR__ . '/functions.php';

/* ------------------------------ props.tsx port ------------------------------ */

function price_fmt(?int $n): string
{
    if ($n === null) return '';
    return number_format($n, 0, '.', ',');
}

function price_fmt_html(?int $value, ?string $qualifier): string
{
    if ($value === null) return $qualifier ?? '';
    return ($qualifier ?: 'AED ') . number_format($value, 0, '.', ',');
}

function address_of(array $hit): string
{
    if (!empty($hit['display_address'])) return $hit['display_address'];
    $a = $hit['address_full'] ?? [];
    if (!is_array($a)) $a = [];
    return implode(', ', array_filter([$a['address2'] ?? '', $a['address3'] ?? '', $a['address4'] ?? ''])) ?: ($a['area'] ?? '');
}

function prop_link(array $hit): string
{
    $t = strtolower((string) ($hit['search_type'] ?? ''));
    $base = (str_contains($t, 'rent') || str_contains($t, 'letting')) ? '/let/' : '/buy/';
    return $base . ($hit['slug'] ?? '') . ($hit['id'] ?? '') . '/';
}

function neg_of(array $hit): array
{
    $neg = $hit['crm_negotiator_id'] ?? [];
    if (is_array($neg) && isset($neg[0]) && is_array($neg[0])) $neg = $neg[0];
    return is_array($neg) ? $neg : [];
}

function wa_link_property(array $hit): string
{
    $neg = neg_of($hit);
    $phone = $neg['phone'] ?? '+971 568 308 221';
    $ref = (string) ($hit['crm_id'] ?? '');
    $type = $hit['building'][0] ?? $hit['building_type'] ?? '';
    $price = !empty($hit['price']) ? 'AED ' . number_format((int) $hit['price'], 0, '.', ',') : '';
    $loc = address_of($hit);
    $link = prop_link($hit);
    $text = "Hello Zoya Ventures,\n\nI would like to know more about this property:\n\n• Reference: $ref\n• Type: $type\n• Price: $price\n• Location: $loc\n• Link: https://providentestate.com$link\n\nModifying this message will prevent it from being sent to the agent.";
    $searchType = strtolower((string) ($hit['search_type'] ?? ''));
    $kind = (str_contains($searchType, 'rent') || str_contains($searchType, 'letting')) ? 'secondaryrent' : 'secondarysale';
    $params = [
        'phone' => '971568308221',
        'text' => $text,
        'resp_name' => $neg['name'] ?? '',
        'utm_source' => 'Browser Direct',
        'gclid' => '"',
        'type' => $kind,
        'referrer_url' => 'https://providentestate.com' . $link,
        'event_type' => 'Whatsapp Click',
        'utm_platform' => '"',
    ];
    if (!empty($neg['email'])) $params['email'] = $neg['email'];
    $respPhone = preg_replace('/\D/', '', (string) ($neg['phone'] ?? ''));
    if ($respPhone !== '') $params['resp_phone'] = $respPhone;
    return 'https://wa.provident.ae/inquire?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
}

function long_desc(array $hit): string
{
    $d = trim(preg_replace('/\s+/', ' ', (string) ($hit['long_description'] ?? '')));
    return mb_strlen($d) > 100 ? mb_substr($d, 0, 100) . '...' : $d;
}

/* ------------------------------ store.ts port ------------------------------ */

/** Convert a property-detail object to a listing-hit shape (toHit port). */
function to_hit(array $p): array
{
    $images = [];
    foreach ($p['images'] ?? [] as $im) {
        $src = $im['url'] ?? $im['srcUrl'] ?? null;
        $images[] = ['340x252' => cft($src, 340, 252), '464x312' => cft($src, 464, 312), '696x520' => cft($src, 696, 520)];
    }
    $first = $p['thumbnail']['url'] ?? $p['images'][0]['url'] ?? null;
    $building = $p['building'] ?? [];
    if (!is_array($building)) $building = [$building];
    return array_merge($p, [
        'images' => $images,
        'imageCount' => count($images),
        'display_address' => $p['display_address'] ?? $p['address'] ?? '',
        'crm_negotiator_id' => $p['crm_negotiator_id'] ?? null,
        'building' => $building,
        'description' => $p['introtext'] ?? $p['description'] ?? '',
        'floorarea_min' => $p['floorarea_min'] ?? $p['floorarea_max'],
        'floorarea_max' => $p['floorarea_max'] ?? null,
    ]);
}

/** Parse "/buy/...slug12345" -> [id, file, kind] (propRouteParts port). */
function prop_route_parts(string $link): array
{
    $clean = trim($link, '/');
    preg_match('/^(.*?)(\d+)$/', $clean, $m);
    $id = $m[2] ?? '';
    $slug = $m[1] ?? $clean;
    $kind = str_starts_with($clean, 'let') ? 'let' : 'buy';
    $slugBase = str_starts_with($slug, $kind . '/') ? substr($slug, strlen($kind) + 1) : $slug;
    return ['id' => $id, 'file' => "{$kind}/{$slugBase}{$id}.json", 'kind' => $kind];
}

function by_link(string $link): ?array
{
    $parts = prop_route_parts($link);
    $j = load_json('properties/' . $parts['file']);
    $inner = $j['result']['serverData']['data'] ?? null;
    return (is_array($inner) && ($inner['status'] ?? null) === true && !empty($inner['data']['id'])) ? to_hit($inner['data']) : null;
}

function corpus(string $kind): array
{
    static $cache = [];
    if (isset($cache[$kind])) return $cache[$kind];
    $dir = APP_RAW_DIR . '/properties/' . $kind;
    $out = [];
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.json') ?: [] as $file) {
            $j = load_json('properties/' . $kind . '/' . basename($file));
            $inner = $j['result']['serverData']['data'] ?? null;
            if (is_array($inner) && ($inner['status'] ?? null) === true && !empty($inner['data']['id'])) {
                $out[] = to_hit($inner['data']);
            }
        }
    }
    $cache[$kind] = $out;
    return $out;
}

/** Route constraints from a listing route (routeFilters port). */
function route_filters(string $route): array
{
    $segs = array_values(array_filter(explode('/', $route)));
    $f = [
        'rent' => ($segs[0] ?? '') === 'let',
        'type' => null, 'area' => null, 'priceMin' => null, 'priceMax' => null,
        'bedsMin' => null, 'bedsMax' => null, 'sizeMin' => null, 'sizeMax' => null,
        'completion' => null, 'furnished' => false, 'amenities' => [],
    ];
    foreach ($segs as $s) {
        if (str_starts_with($s, 'in-')) $f['area'] = substr($s, 3);
        elseif (preg_match('/^above-(\d+)$/', $s, $m)) $f['priceMin'] = (int) $m[1];
        elseif (preg_match('/^under-(\d+)$/', $s, $m)) $f['priceMax'] = (int) $m[1];
        elseif (preg_match('/^under-(\d+)-bedrooms$/', $s, $m)) $f['bedsMax'] = (int) $m[1];
        elseif (preg_match('/^with-(\d+)-to-(\d+)-bedrooms$/', $s, $m)) { $f['bedsMin'] = (int) $m[1]; $f['bedsMax'] = (int) $m[2]; }
        elseif (preg_match('/^with-size-under-(\d+)$/', $s, $m)) $f['sizeMax'] = (int) $m[1];
        elseif (preg_match('/^with-size-(\d+)-to-(\d+)$/', $s, $m)) { $f['sizeMin'] = (int) $m[1]; $f['sizeMax'] = (int) $m[2]; }
        elseif (preg_match('/^with-size-above-(\d+)$/', $s, $m)) $f['sizeMin'] = (int) $m[1];
        elseif ($s === 'furnished') $f['furnished'] = true;
        elseif (str_starts_with($s, 'completion-')) $f['completion'] = substr($s, 11);
        elseif (str_starts_with($s, 'with-amenities-')) $f['amenities'][] = substr($s, 15);
        elseif (str_ends_with($s, '-for-sale')) $f['type'] = substr($s, 0, -9);
        elseif (str_ends_with($s, '-for-rent')) $f['type'] = substr($s, 0, -9);
    }
    return $f;
}

function match_hit(array $h, array $f): bool
{
    if ($f['type'] && $f['type'] !== 'properties') {
        $ft = strtolower($f['type']);
        $dept = strtolower((string) ($h['department'] ?? ''));
        $bt = strtolower((string) ($h['building_type'] ?? ''));
        $bld = array_map(fn ($b) => strtolower((string) $b), $h['building'] ?? []);
        if ($dept !== $ft && !str_contains($bt, $ft) && !array_any($bld, fn ($b) => str_contains($b, $ft))) {
            if (!in_array($ft, ['commercial-properties', 'whole-building', 'plots', 'short-term'], true)) return false;
        }
    }
    if ($f['area']) {
        $a = $h['address_full'] ?? [];
        if (!is_array($a)) $a = [];
        $hay = strtolower(implode(' ', array_filter([$a['area'] ?? '', $a['address3'] ?? '', $a['address4'] ?? '', $h['display_address'] ?? ''])));
        $hay = preg_replace('/[^a-z0-9]+/', ' ', $hay) ?? '';
        $want = strtolower(preg_replace('/[^a-z0-9]+/', ' ', $f['area']) ?? '');
        if (!str_contains($hay, $want)) return false;
    }
    if ($f['priceMin'] !== null && (int) ($h['price'] ?? 0) < $f['priceMin']) return false;
    if ($f['priceMax'] !== null && (int) ($h['price'] ?? 0) > $f['priceMax']) return false;
    if ($f['bedsMin'] !== null && (int) ($h['bedroom'] ?? 0) < $f['bedsMin']) return false;
    if ($f['bedsMax'] !== null && (int) ($h['bedroom'] ?? 0) > $f['bedsMax']) return false;
    if ($f['sizeMin'] !== null || $f['sizeMax'] !== null) {
        $sz = (int) ($h['floorarea_min'] ?? $h['floorarea_max'] ?? 0);
        if ($f['sizeMin'] !== null && $sz < $f['sizeMin']) return false;
        if ($f['sizeMax'] !== null && $sz > $f['sizeMax']) return false;
    }
    if ($f['amenities']) {
        $amens = array_merge($h['accommodation_summary'] ?? [], $h['amenities'] ?? []);
        $amens = array_map(fn ($x) => strtolower((string) $x), $amens);
        foreach ($f['amenities'] as $a) {
            $want = str_replace('-', ' ', $a);
            if (!array_any($amens, fn ($x) => str_contains($x, $want))) return false;
        }
    }
    return true;
}

/** Nearest existing listing page-data file for a route, walking up filter/area segments (baseListingRel port). */
function base_listing_rel(string $route): ?string
{
    $rel = preg_replace('#^/#', '', $route);
    $rel = preg_replace('#/page/\d+$#', '', $rel);
    $rel = rtrim($rel, '/');
    $parts = array_values(array_filter(explode('/', $rel)));
    while (count($parts)) {
        $candidate = implode('/', $parts);
        if ($candidate === 'buy') return 'buy/properties-for-sale';
        if ($candidate === 'let') return 'let/properties-for-rent';
        if (load_json('listings/' . $candidate . '.json')) return $candidate;
        array_pop($parts);
    }
    return null;
}

/** Synthesize a page of hits for routes we did not scrape (page > 1) (synthHits port). */
function synth_hits(string $route, int $page, int $perPage = 20): array
{
    $f = route_filters($route);
    $src = corpus($f['rent'] ? 'let' : 'buy');
    $filtered = array_values(array_filter($src, fn ($h) => match_hit($h, $f)));
    usort($filtered, fn ($a, $b) => (int) ($b['id'] ?? 0) - (int) ($a['id'] ?? 0));
    return array_slice($filtered, ($page - 1) * $perPage, $perPage);
}

function area_label(string $slug): string
{
    return ucwords(str_replace('-', ' ', $slug));
}

/* ------------------------------ project corpus ------------------------------ */

function project_corpus(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $out = [];
    $dir = APP_RAW_DIR . '/projects/new-projects';
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.json') ?: [] as $file) {
            $j = load_json('projects/new-projects/' . basename($file));
            $d = $j['result']['serverData']['data'] ?? null;
            if (is_array($d) && !empty($d['hits'])) {
                foreach ($d['hits'] as $h) {
                    if (($h['publish'] ?? true) !== false) $out[] = $h;
                }
            }
        }
    }
    $cache = $out;
    return $out;
}

function project_by_slug(string $slug): ?array
{
    $j = load_json('projects/new-projects/' . $slug . '.json');
    $d = $j['result']['serverData']['data'] ?? null;
    if (is_array($d) && isset($d['hits'][0])) return $d['hits'][0];
    // Files are named in-{area}.json; the hit slug is independent of the
    // filename, so fall back to a corpus scan.
    foreach (project_corpus() as $h) {
        if (rtrim((string) ($h['slug'] ?? ''), '.') === $slug) return $h;
    }
    return null;
}

function project_detail_by_slug(string $slug): ?array
{
    if (!db_enabled()) return null;
    $row = db_row('SELECT data FROM project_details WHERE slug = ? LIMIT 1', [$slug]);
    if (!$row) return null;
    $j = json_decode((string) $row['data'], true);
    return is_array($j) ? $j : null;
}

function projects_by_developer(string $dev): array
{
    $key = preg_replace('/[^a-z0-9-]+/', '-', strtolower($dev)) ?? '';
    return array_values(array_filter(project_corpus(), fn ($h) => (preg_replace('/[^a-z0-9-]+/', '-', strtolower((string) ($h['developer'] ?? ''))) ?? '') === $key));
}

function projects_by_type(string $t): array
{
    $key = preg_replace('/[^a-z0-9]+/', '', strtolower($t)) ?? '';
    return array_values(array_filter(project_corpus(), function ($h) use ($key) {
        $bt = $h['building_type'] ?? [];
        if (!is_array($bt)) $bt = [$bt];
        foreach ($bt as $b) {
            $k = preg_replace('/[^a-z0-9]+/', '', strtolower((string) $b)) ?? '';
            if ($k === $key) return true;
        }
        return false;
    }));
}

function developer_hub_data(string $dev): array
{
    $hits = projects_by_developer($dev);
    return [
        'hits' => $hits,
        'nbHits' => count($hits),
        'page' => 0,
        'nbPages' => 1,
        'hitsPerPage' => count($hits) ?: 1,
        'content' => ['title' => 'Projects by ' . str_replace('-', ' ', $dev)],
    ];
}

function type_hub_data(string $t): array
{
    $hits = projects_by_type($t);
    return [
        'hits' => $hits,
        'nbHits' => count($hits),
        'page' => 0,
        'nbPages' => 1,
        'hitsPerPage' => count($hits) ?: 1,
        'content' => ['title' => 'Off-Plan ' . strtoupper(substr($t, 0, 1)) . substr($t, 1) . ' Projects in Dubai'],
    ];
}

/* ------------------------------ project DB bridge (content-bridge.ts port) ------------------------------ */

function db_json_arr(mixed $v): array
{
    if ($v === null || $v === '') return [];
    $p = json_decode((string) $v, true);
    if (is_array($p)) return array_map('strval', $p);
    return array_values(array_filter(array_map('trim', explode(',', (string) $v))));
}

function db_dev_slug_key(mixed $s): string
{
    return preg_replace('/[^a-z0-9-]+/', '-', strtolower((string) $s)) ?? '';
}

function db_type_slug_key(mixed $s): string
{
    return preg_replace('/[^a-z0-9]+/', '', strtolower((string) $s)) ?? '';
}

/** Published projects from the database, shaped like scraped new-projects hits (dbProjects port). */
function db_projects(): array
{
    if (!db_enabled()) return [];
    $items = db_rows("SELECT * FROM projects WHERE published = 1 ORDER BY id DESC");
    $out = [];
    foreach ($items as $p) {
        $images = [];
        foreach (db_json_arr($p['images'] ?? '') as $u) {
            $images[] = ['340x252' => $u, '464x312' => $u, '696x520' => $u];
        }
        $banner = (string) ($p['banner_image'] ?? '');
        $out[] = [
            'id' => (int) ($p['id'] ?? 0),
            'slug' => (string) ($p['slug'] ?? ''),
            'title' => (string) ($p['title'] ?? ''),
            'status' => (string) ($p['status'] ?? 'ready'),
            'price' => (int) ($p['price'] ?? 0),
            'currency' => (string) ($p['currency'] ?? 'AED'),
            'community' => (string) ($p['community'] ?? ''),
            'developer' => (string) ($p['developer'] ?? ''),
            'building_type' => db_json_arr($p['building_type'] ?? ''),
            'department' => (string) ($p['department'] ?? ''),
            'min_bedrooms' => (int) ($p['bedrooms_min'] ?? 0),
            'max_bedrooms' => (int) ($p['bedrooms_max'] ?? 0),
            'display_address' => (string) ($p['display_address'] ?? ''),
            'about' => (string) ($p['about'] ?? ''),
            'images' => $images,
            'amenities' => db_json_arr($p['amenities'] ?? ''),
            'banner_image' => $banner !== '' ? [['376x' => $banner, '744x' => $banner, '1650x' => $banner]] : [],
            'completion_year' => isset($p['completion_year']) && $p['completion_year'] !== null && $p['completion_year'] !== '' ? (int) $p['completion_year'] : null,
            'publish' => (int) ($p['published'] ?? 1) === 1,
        ];
    }
    return $out;
}

/* ------------------------------ misc corpus helpers ------------------------------ */

function team_members(int $limit = 1000): array
{
    static $cache = null;
    if ($cache !== null) return array_slice($cache, 0, $limit);
    $out = [];
    $dir = APP_RAW_DIR . '/pages/team';
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.json') ?: [] as $file) {
            $j = load_json('pages/team/' . basename($file));
            $t = $j['result']['data']['strapiTeam'] ?? null;
            if (!is_array($t)) continue;
            $out[] = [
                'slug' => $t['slug'] ?? '',
                'name' => $t['name'] ?? '',
                'designation' => $t['designation'] ?? '',
                'image' => $t['extra']['profile_image'] ?? $t['image']['url'] ?? null,
                'phone' => $t['phone'] ?? $t['office_phone'] ?? '',
                'email' => $t['email'] ?? '',
                'category' => $t['category']['strapi_json_value'] ?? [],
                'languages' => $t['languages']['strapi_json_value'] ?? [],
                'rank' => isset($t['rank']) && is_numeric($t['rank']) ? (int) $t['rank'] : null,
            ];
        }
    }
    usort($out, function ($a, $b) {
        $ra = $a['rank'] ?? PHP_INT_MAX;
        $rb = $b['rank'] ?? PHP_INT_MAX;
        if ($ra !== $rb) return $ra <=> $rb;
        return strcmp((string) ($a['name'] ?? ''), (string) ($b['name'] ?? ''));
    });
    $cache = $out;
    return array_slice($out, 0, $limit);
}

function project_hits(int $limit = 6): array
{
    $out = [];
    $dir = APP_RAW_DIR . '/projects';
    if (is_dir($dir)) {
        foreach (glob($dir . '/*') ?: [] as $sub) {
            if (!is_dir($sub)) continue;
            foreach (glob($sub . '/*.json') ?: [] as $file) {
                $j = load_json('projects/' . basename($sub) . '/' . basename($file));
                $d = $j['result']['serverData']['data'] ?? null;
                if (is_array($d) && !empty($d['hits'])) {
                    foreach ($d['hits'] as $h) {
                        if (($h['publish'] ?? true) !== false) $out[] = $h;
                        if (count($out) >= $limit) return array_slice($out, 0, $limit);
                    }
                }
            }
        }
    }
    return array_slice($out, 0, $limit);
}

/** Spotlight image: string url or sized-map, prefer 340x252 then 464x312 then 696x520 (spotImg port). */
function img_of(mixed $im): string
{
    if (is_string($im)) return $im;
    if (is_array($im)) return (string) ($im['340x252'] ?? $im['464x312'] ?? $im['696x520'] ?? '');
    return '';
}

/** Similar properties from the local corpus (dbSimilarProperties port, corpus fallback):
 *  same type OR community, price-closest, excluding the property itself. */
function similar_properties(array $prop, string $kind, int $limit = 6): array
{
    $txn = $kind === 'let' ? 'rent' : 'buy';
    $type = strtolower((string) ($prop['building_type'] ?? $prop['building'][0] ?? $prop['property_type'] ?? ''));
    $community = strtolower((string) ($prop['address_full']['area'] ?? $prop['address']['area'] ?? $prop['area'] ?? $prop['display_address'] ?? $prop['community'] ?? ''));
    $price = (int) ($prop['price'] ?? 0);
    if ($type === '' && $community === '') return [];

    $self = [];
    foreach (corpus($kind) as $c) {
        if ((string) ($c['transaction_type'] ?? $kind) === $txn
            && strtolower((string) ($c['property_type'] ?? '')) === $type
            && (int) ($c['price'] ?? 0) === $price) {
            $self[(string) ($c['crm_id'] ?? $c['id'] ?? '')] = true;
        }
    }
    $selfId = (string) ($prop['crm_id'] ?? $prop['id'] ?? '');
    if ($selfId !== '') $self[$selfId] = true;

    $items = [];
    foreach (corpus($kind) as $c) {
        $cid = (string) ($c['crm_id'] ?? $c['id'] ?? '');
        if ($cid !== '' && isset($self[$cid])) continue;
        $t = strtolower((string) ($c['building_type'] ?? $c['building'][0] ?? $c['property_type'] ?? ''));
        $com = strtolower((string) ($c['address_full']['area'] ?? $c['address']['area'] ?? $c['area'] ?? $c['display_address'] ?? $c['community'] ?? ''));
        if ($t === $type || ($com !== '' && $com === $community)) {
            $items[] = ['hit' => $c, 'typeMatch' => $t === $type ? 0 : 1, 'dist' => abs((int) ($c['price'] ?? 0) - $price)];
        }
    }
    usort($items, fn ($a, $b) => [$a['typeMatch'], $a['dist']] <=> [$b['typeMatch'], $b['dist']]);
    return array_map(fn ($x) => $x['hit'], array_slice($items, 0, $limit));
}

function get_listing_data(string $route): ?array{
    $rel = trim($route, '/');
    $j = load_json('listings/' . $rel . '.json') ?? load_json('listings/' . $rel);
    $d = $j['result']['serverData']['data'] ?? null;
    if (!is_array($d)) return null;
    return [
        'hits' => $d['hits'] ?? [],
        'nbHits' => $d['nbHits'] ?? 0,
        'nbPages' => $d['nbPages'] ?? 1,
        'page' => $d['page'] ?? 0,
        'hitsPerPage' => $d['hitsPerPage'] ?? 20,
        'content' => $d['content'] ?? null,
        'projects' => $d['projects'] ?? null,
    ];
}

function get_property_data(string $route): ?array
{
    $rel = trim($route, '/');
    $j = load_json('properties/' . $rel . '.json') ?? load_json('properties/' . $rel);
    $inner = $j['result']['serverData']['data'] ?? null;
    return (is_array($inner) && ($inner['status'] ?? null) === true && !empty($inner['data']['id'])) ? $inner['data'] : null;
}

/* ------------------------------ DB property bridge (property-bridge.ts port) ------------------------------ */

function db_department(string $type): string
{
    $map = [
        'apartment' => 'apartments',
        'apartments' => 'apartments',
        'villa' => 'villas',
        'villas' => 'villas',
        'townhouse' => 'townhouses',
        'townhouses' => 'townhouses',
        'penthouse' => 'penthouses',
        'penthouses' => 'penthouses',
        'studio' => 'studios',
        'studios' => 'studios',
        'duplex' => 'duplexes',
        'duplexes' => 'duplexes',
        'mansion' => 'mansions',
        'mansions' => 'mansions',
        'commercial-property' => 'commercial-properties',
        'office' => 'commercial-properties',
        'retail' => 'commercial-properties',
        'plot' => 'plots',
        'land' => 'plots',
    ];
    return $map[strtolower($type)] ?? $type;
}

function db_negotiator(array $p): array
{
    $name = trim((string) ($p['agent_name'] ?? ''));
    if ($name === '') {
        return ['name' => 'Zoya Ventures Real Estate', 'phone' => '+971 568 308 221', 'email' => 'zoyaventure15@gmail.com'];
    }
    return [
        'name' => $name,
        'url' => (string) ($p['agent_img'] ?? ''),
        'designation' => (string) ($p['agent_role'] ?? 'Sales Associate'),
        'brn_number' => (string) ($p['agent_brn'] ?? ''),
        'phone' => (string) ($p['agent_phone'] ?? '') ?: '+971 568 308 221',
        'email' => (string) ($p['agent_email'] ?? '') ?: 'zoyaventure15@gmail.com',
    ];
}

/** A properties row shaped like a listing hit (dbHit port). */
function db_hit(array $p): array
{
    $thumb = (string) ($p['thumb'] ?? '');
    $id = (int) ($p['id'] ?? 0);
    $sqft = (int) ($p['area_sqft'] ?? 0);
    $img = fn ($u) => ['340x252' => $u, '464x312' => $u, '696x520' => $u];
    $placeholder = '/images/property-placeholder.svg';
    $type = (string) ($p['property_type'] ?? '');
    return [
        'id' => $id,
        'slug' => (string) ($p['slug'] ?? 'property-' . $id),
        'crm_id' => 'PE-' . $id,
        'title' => (string) ($p['title'] ?? ''),
        'price' => (int) ($p['price'] ?? 0),
        'price_qualifier' => (string) ($p['price_qualifier'] ?? 'AED'),
        'bedroom' => (int) ($p['bedroom'] ?? 0),
        'bathroom' => (int) ($p['bathroom'] ?? 0),
        'floorarea_min' => $sqft ?: null,
        'floorarea_max' => $sqft ?: null,
        'display_address' => (string) ($p['display_address'] ?? $p['location'] ?? ''),
        'address_full' => !empty($p['community']) ? ['area' => (string) $p['community']] : null,
        'department' => db_department($type),
        'building_type' => $type,
        'building' => array_values(array_filter([$type])),
        'description' => (string) ($p['introtext'] ?? ''),
        'long_description' => (string) ($p['long_description'] ?? ''),
        'introtext' => (string) ($p['introtext'] ?? ''),
        'images' => $thumb !== '' ? [$img($thumb)] : [$img($placeholder)],
        'imageCount' => 1,
        'search_type' => (string) ($p['transaction_type'] ?? '') === 'rent' ? 'rental' : 'sale',
        'crm_negotiator_id' => db_negotiator($p),
        'status' => (string) ($p['status'] ?? 'ready'),
        'completion_year' => isset($p['year_built']) && $p['year_built'] !== null && $p['year_built'] !== '' ? (int) $p['year_built'] : null,
        'furnished' => (string) ($p['furnished'] ?? ''),
    ];
}

/** Detail-page shape (images + amenities) for a property row (detailFromRow port). */
function db_property_detail(array $p): array
{
    $media = db_rows('SELECT * FROM property_media WHERE property_id = ? ORDER BY sort_order, id', [(int) $p['id']]);
    $images = [];
    foreach ($media as $m2) {
        if (($m2['kind'] ?? '') === 'image') {
            $url = (string) ($m2['url'] ?? '');
            $images[] = ['url' => $url, 'srcUrl' => $url];
        }
    }
    if (!$images) $images = [['url' => '/images/property-placeholder.svg', 'srcUrl' => '/images/property-placeholder.svg']];
    $amenityNames = array_map(fn ($a) => (string) $a['name'], db_rows(
        'SELECT a.name FROM property_amenities pa JOIN amenities a ON a.id = pa.amenity_id WHERE pa.property_id = ? ORDER BY a.name',
        [(int) $p['id']]
    ));

    $hit = db_hit($p);
    $hit['images'] = $images;
    $hit['amenities'] = $amenityNames;
    $hit['status'] = (string) ($p['completion_status'] ?? '') ?: 'Ready';
    $hit['furnishing'] = (string) ($p['furnished'] ?? '') ?: 'Unfurnished';
    return $hit;
}

/** Resolve a route like /buy/my-apartment42/ to a DB property detail (dbPropertyByRoute port). */
function db_property_by_route(string $route): ?array
{
    if (!db_enabled()) return null;
    if (!preg_match('#^/(buy|let)/([^/]+?)/?$#', $route, $m)) return null;
    $kind = $m[1];
    $part = $m[2];
    $txn = $kind === 'let' ? 'rent' : 'buy';

    $sel = "SELECT p.*, ag.name AS agent_name, ag.img AS agent_img, ag.role AS agent_role,
            ag.brn_number AS agent_brn, ag.phone AS agent_phone, ag.email AS agent_email
            FROM properties p
            LEFT JOIN agents ag ON ag.id = p.agent_id
            WHERE p.published = 1 AND p.transaction_type = ?";

    // The card link is `{slug}{id}` concatenated, so a slug ending in digits is
    // ambiguous (e.g. slug "dsav-2" + id 27 -> "dsav-227"). Try progressive
    // splits, longest id first, until a row matches.
    $p = null;
    if (preg_match('/(\d+)$/', $part, $dm)) {
        $digits = $dm[1];
        $len = strlen($digits);
        for ($idLen = $len; $idLen >= 0; $idLen--) {
            $id = $idLen > 0 ? (int) substr($digits, $len - $idLen) : null;
            $slug = $idLen < $len ? substr($part, 0, strlen($part) - $idLen) : $part;
            if ($id !== null && $id > 0 && $id <= 2147483647) {
                $p = db_row($sel . ' AND p.id = ?', [$txn, $id]);
                if ($p) break;
            }
            if ($slug !== '') {
                $p = db_row($sel . ' AND p.slug = ?', [$txn, $slug]);
                if ($p) break;
            }
        }
    } else {
        $p = db_row($sel . ' AND p.slug = ?', [$txn, $part]);
    }
    if (!$p) return null;

    return ['kind' => $kind, 'data' => db_property_detail($p)];
}

/** Dedupe hits by crm_id/id/slug (dedupeBySlug semantics + key-based merge dedupe). */
function dedupe_hits(array $list): array
{
    $seen = [];
    $out = [];
    foreach ($list as $h) {
        $key = $h['crm_id'] ?? $h['id'] ?? $h['slug'] ?? null;
        if ($key !== null) {
            $k = (string) $key;
            if (isset($seen[$k])) continue;
            $seen[$k] = true;
        }
        $out[] = $h;
    }
    return $out;
}

if (!function_exists('array_any')) {
    function array_any(array $items, callable $fn): bool
    {
        foreach ($items as $i) {
            if ($fn($i)) return true;
        }
        return false;
    }
}

/* ------------------------------ content-page store helpers (store.ts port) ------------------------------ */

function home_json(): ?array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = load_json('home.json');
    return $cache;
}

/** Unique community list from the extracted homepage (label + slug). */
function communities(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $out = [];
    $seen = [];
    $hj = home_json();
    foreach (($hj['communities'] ?? []) as $c) {
        if (!is_array($c) || !preg_match('#in-([^/]+)/$#', (string) ($c['href'] ?? ''), $m)) continue;
        $slug = $m[1];
        if (isset($seen[$slug])) continue;
        $seen[$slug] = true;
        $out[] = ['label' => (string) ($c['label'] ?? ''), 'slug' => $slug];
    }
    $cache = $out;
    return $out;
}

function areas(): array
{
    return array_map(fn ($c) => $c['label'], communities());
}

/** Featured slider ids from the extracted homepage. */
function featured_ids(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $hj = home_json();
    $links = $hj['featuredSliders'][0]['links'] ?? [];
    $cache = array_values(array_filter(is_array($links) ? $links : [], fn ($l) => !str_ends_with((string) $l, '/properties-for-sale')));
    return $cache;
}

function signature_ids(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $hj = home_json();
    $links = $hj['featuredSliders'][1]['links'] ?? [];
    $cache = array_values(array_filter(is_array($links) ? $links : [], fn ($l) => !str_ends_with((string) $l, '/properties-for-sale')));
    return $cache;
}

/** Unique developer names from the homepage (excluding the "icon" filler). */
function developers(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $hj = home_json();
    $devs = [];
    foreach (($hj['developers'] ?? []) as $d) {
        if ($d === 'icon') continue;
        $devs[$d] = true;
    }
    $cache = array_keys($devs);
    return $cache;
}

/** Homepage developer slider logos (exact order + CDN files from the reference). */
function dev_logos(): array
{
    return [
        ['slug' => 'damac-properties', 'name' => 'Damac Properties', 'file' => 'Damac_c63829f7d0.webp'],
        ['slug' => 'emaar-properties', 'name' => 'Emaar Properties', 'file' => 'Emaar_f229e25788.webp'],
        ['slug' => 'meraas', 'name' => 'Meraas', 'file' => 'Meraas_logo_58aa6236ab.webp'],
        ['slug' => 'sobha-realty', 'name' => 'Sobha Realty', 'file' => 'logo_01_4fd8dc607d.webp'],
        ['slug' => 'nakheel', 'name' => 'Nakheel', 'file' => 'logo_02_1_666ef04015.webp'],
        ['slug' => 'binghatti', 'name' => 'Binghatti', 'file' => 'binghatti_7c9b5b6084.webp'],
        ['slug' => 'select-group', 'name' => 'Select Group', 'file' => 'Select_Group_be8d857695.webp'],
        ['slug' => 'city-view-developments', 'name' => 'City View Developments', 'file' => 'city_view_logo_cd13ea3726.webp'],
        ['slug' => 'ellington-properties', 'name' => 'Ellington Properties', 'file' => 'Ellington_58133c54d4.webp'],
        ['slug' => 'majid-al-futtaim', 'name' => 'Majid Al Futtaim', 'file' => 'Majid_Al_Futtaim_b3d70262eb.webp'],
    ];
}

/** Latest blog posts (title, slug, date, image, category) from the saved blog corpus. */
function blog_posts(int $limit = 4): array
{
    static $cache = null;
    if ($cache !== null) return array_slice($cache, 0, $limit);
    $posts = [];
    $dir = APP_RAW_DIR . '/pages/blog';
    if (is_dir($dir)) {
        foreach (glob($dir . '/*.json') ?: [] as $file) {
            $j = load_json('pages/blog/' . basename($file));
            $b = $j['result']['data']['strapiBlog'] ?? null;
            if (!is_array($b)) continue;
            $cat = $b['category']['strapi_json_value'] ?? null;
            $posts[] = [
                'slug' => (string) ($b['slug'] ?? ''),
                'title' => (string) ($b['title'] ?? ''),
                'date' => (string) ($b['date'] ?? ''),
                'category' => is_array($cat) ? implode(', ', $cat) : (string) ($b['category'] ?? ''),
                'image' => $b['tile_image']['url'] ?? $b['banner_image']['url'] ?? null,
                'description' => (string) ($b['short_description'] ?? ''),
            ];
        }
    }
    usort($posts, fn ($a, $b) => strcmp((string) ($b['date'] ?? ''), (string) ($a['date'] ?? '')));
    $cache = $posts;
    return array_slice($posts, 0, $limit);
}

function rentals(): array
{
    $l = get_listing('let/properties-for-rent');
    return is_array($l) ? ($l['hits'] ?? []) : [];
}

function sales_hits(): array
{
    $l = get_listing('buy/properties-for-sale');
    return is_array($l) ? ($l['hits'] ?? []) : [];
}

/** First-page area-guide slugs in the exact reference order. */
const AREA_GUIDE_PAGE1 = [
    'downtown-dubai',
    'palm-jumeirah',
    'dubai-marina',
    'business-bay',
    'emaar-beachfront',
    'bluewater-island-dubai',
    'jumeirah-lake-towers',
    'dubai-creek-harbour',
    'sobha-hartland',
    'dubai-hills-estate',
    'jumeirah-beach-residence',
    'jumeirah-village-circle',
    'dubai-south',
    'dubai-sports-city',
    'difc',
    'emaar-south',
    'jumeirah-bay-island',
    'damac-hills',
    'sobha-siniya-island',
    'al-marjan-island',
    'palm-jebel-ali',
    'dubai-islands',
    'jumeirah-golf-estates',
    'mina-al-arab',
];

/** All area guides as listing cards (page-1 areas first, rest alphabetical after). */
function area_guides_data(): array
{
    static $cache = null;
    if ($cache !== null) return $cache;
    $rank = array_flip(AREA_GUIDE_PAGE1);
    $out = [];
    $dir = APP_RAW_DIR . '/pages/area-guides';
    if (is_dir($dir)) {
        $files = glob($dir . '/*.json') ?: [];
        sort($files);
        foreach ($files as $file) {
            $slug = preg_replace('/\.json$/', '', basename($file)) ?? '';
            $j = load_json('pages/area-guides/' . basename($file));
            $a = $j['result']['data']['strapiAreaGuide'] ?? null;
            if (!is_array($a) || empty($a['title'])) continue;
            $img = $a['tile_image']['url'] ?? $a['banner_image']['url'] ?? null;
            $amenities = $a['amenities']['strapi_json_value'] ?? null;
            $out[] = [
                'slug' => (string) ($a['slug'] ?? $slug),
                'title' => (string) $a['title'],
                'image' => cft($img, 340, 212),
                'image304' => cft($img, 304, 300),
                'desc' => (string) ($a['description']['data']['description'] ?? ''),
                'amenities' => is_array($amenities) ? $amenities : [],
                'page1' => array_key_exists($slug, $rank) ? $rank[$slug] : -1,
            ];
        }
    }
    usort($out, function ($a, $b) {
        $ra = $a['page1'] === -1 ? 1000 : $a['page1'];
        $rb = $b['page1'] === -1 ? 1000 : $b['page1'];
        if ($ra !== $rb) return $ra <=> $rb;
        return strcmp((string) $a['title'], (string) $b['title']);
    });
    $cache = $out;
    return $out;
}

/** Developer corpus (slug, name, logo, background, description) from developers.json. */
function developers_list(): array
{
    $j = load_json('developers.json');
    $list = is_array($j) ? $j : [];
    $out = [];
    foreach ($list as $d) {
        if (!is_array($d)) continue;
        $logo = !empty($d['logo']) ? 'https://d3h330vgpwpjr8.cloudfront.net/x/296x/' . $d['logo'] : 'https://d3h330vgpwpjr8.cloudfront.net/x/296x/placeholder.jpg';
        $out[] = [
            'slug' => (string) ($d['slug'] ?? ''),
            'name' => (string) ($d['name'] ?? ''),
            'logo' => $logo,
            'background' => 'https://d3h330vgpwpjr8.cloudfront.net/x/600x400/developer-bg-placeholder.jpg',
            'description' => (string) ($d['description'] ?? ''),
        ];
    }
    return $out;
}

/** Developer names present in the project corpus (developers listing counts). */
function developer_hits(int $limit = 40): array
{
    $seen = [];
    $dir = APP_RAW_DIR . '/projects';
    if (is_dir($dir)) {
        foreach (glob($dir . '/*') ?: [] as $sub) {
            if (!is_dir($sub)) continue;
            foreach (glob($sub . '/*.json') ?: [] as $file) {
                $j = load_json('projects/' . basename($sub) . '/' . basename($file));
                $d = $j['result']['serverData']['data'] ?? null;
                foreach (($d['hits'] ?? []) as $h) {
                    $dev = (string) ($h['developer'] ?? '');
                    if ($dev !== '') $seen[$dev] = ($seen[$dev] ?? 0) + 1;
                }
                if (count($seen) >= $limit) break 2;
            }
        }
    }
    $out = [];
    foreach ($seen as $dev => $count) $out[] = ['developer' => $dev, 'count' => $count];
    return array_slice($out, 0, $limit);
}

/** Project hits whose community / address matches an area (label or slug). */
function projects_by_area(string $area): array
{
    $key = strtolower(preg_replace('/[^a-z0-9]+/', '-', $area) ?? '');
    $needle = strtolower($area);
    return array_values(array_filter(project_corpus(), function ($h) use ($key, $needle) {
        $hay = strtolower(implode(' ', array_filter([$h['community'] ?? '', $h['display_address'] ?? '', ...($h['search_areas'] ?? [])], fn ($x) => $x !== '' && $x !== null)));
        return str_contains($hay, $key) || str_contains($hay, $needle);
    }));
}
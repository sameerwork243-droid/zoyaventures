<?php
// api/admin/blogs.php — read-only list of blog posts from the content corpus
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();

$dir = APP_RAW_DIR . '/pages/blog';
$items = [];
foreach (glob($dir . '/*.json') ?: [] as $f) {
    $raw = @file_get_contents($f);
    $j = $raw !== false ? json_decode($raw, true) : null;
    $b = is_array($j) ? ($j['result']['data']['strapiBlog'] ?? null) : null;
    if (!is_array($b)) continue;
    $cat = $b['category'] ?? '';
    if (is_array($cat)) {
        $cat = is_array($cat['strapi_json_value'] ?? null) ? implode(', ', $cat['strapi_json_value']) : '';
    } else {
        $cat = (string) $cat;
    }
    $items[] = [
        'title' => (string) ($b['title'] ?? basename($f, '.json')),
        'slug' => (string) ($b['slug'] ?? basename($f, '.json')),
        'category' => $cat,
        'author' => (string) ($b['author'] ?? ''),
        'date' => (string) ($b['date'] ?? ''),
        'published' => empty($b['publish']) ? 0 : 1,
    ];
}

usort($items, function ($a, $b) {
    $ta = strtotime($a['date']);
    $tb = strtotime($b['date']);
    if ($ta === false && $tb === false) return 0;
    if ($ta === false) return 1;
    if ($tb === false) return -1;
    return $tb <=> $ta;
});

$q = trim((string) ($_GET['q'] ?? ''));
if ($q !== '') {
    $items = array_values(array_filter($items, function ($it) use ($q) {
        return stripos($it['title'], $q) !== false
            || stripos($it['slug'], $q) !== false
            || stripos($it['category'], $q) !== false;
    }));
}

json_response(['items' => $items, 'total' => count($items)]);
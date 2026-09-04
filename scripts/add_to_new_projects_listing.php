<?php
error_reporting(E_ALL);
$root = dirname(__DIR__);
$f = $root . '/data/raw/pages/new-projects.json';
$raw = file_get_contents($f);
$j = json_decode($raw, true);
$data = &$j['result']['serverData']['data'];
$hits = &$data['hits'];

$new = ['riviera-azure','hayyan','riviera-reve','beach-oasis','opus','davinci-tower','seslia-tower'];
$add = [];
foreach ($new as $slug) {
    $cj = json_decode(file_get_contents($root . "/data/raw/projects/new-projects/$slug.json"), true);
    $h = $cj['result']['serverData']['data']['hits'][0];
    // ensure required keys present & consistent
    $h['rank'] = null;
    $h['publish'] = true;
    $h['search_type'] = 'sales';
    $h['department'] = 'new_developments';
    $add[] = $h;
}

// prepend (newest/featured first), avoid dupes
$existing = array_map(fn($h) => $h['slug'] ?? '', $hits);
$add = array_values(array_filter($add, fn($h) => !in_array($h['slug'] ?? '', $existing, true)));
$hits = array_merge($add, $hits);

$data['nbHits'] = (int) $data['nbHits'] + count($add);
$data['nbPages'] = max(1, (int) ceil($data['nbHits'] / (int) $data['hitsPerPage']));

$out = json_encode($j, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
file_put_contents($f, $out);
echo "prepended " . count($add) . " hits. new nbHits=" . $data['nbHits'] . " nbPages=" . $data['nbPages'] . "\n";
echo "first 10 slugs now:\n";
foreach (array_slice($hits, 0, 10) as $h) echo "  " . $h['slug'] . "\n";

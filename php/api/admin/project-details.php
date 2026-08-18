<?php
// api/admin/project-details.php — list/get-by-slug/PUT (port of project-details admin route)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $slug = trim((string) ($_GET['slug'] ?? ''));
    if ($slug !== '') {
        $row = db_row("SELECT slug, data, updated_at FROM project_details WHERE slug = ?", [$slug]);
        if (!$row) json_response(['error' => 'Not found'], 404);
        $data = json_decode($row['data'], true);
        json_response(['item' => ['slug' => $row['slug'], 'data' => is_array($data) ? $data : [], 'updated_at' => $row['updated_at']]]);
    }
    $rows = db_rows("SELECT slug, data, updated_at FROM project_details ORDER BY updated_at DESC");
    $out = [];
    foreach ($rows ?: [] as $r) {
        $d = json_decode((string) $r['data'], true);
        $d = is_array($d) ? $d : [];
        $out[] = [
            'slug' => $r['slug'],
            'title' => (string) ($d['title'] ?? $r['slug']),
            'developer' => (string) ($d['developer'] ?? ''),
            'display_address' => (string) ($d['display_address'] ?? ''),
            'completion_year' => $d['completion_year'] ?? null,
            'updated_at' => $r['updated_at'],
        ];
    }
    json_response(['items' => $out]);
}
if ($method === 'PUT') {
    $body = json_body();
    $slug = trim((string) ($body['slug'] ?? ''));
    $data = $body['data'] ?? null;
    if ($slug === '' || !is_array($data)) json_response(['error' => 'slug and data are required'], 400);
    $encoded = json_encode($data);
    if ($encoded === false) json_response(['error' => 'Invalid data'], 400);
    $exists = db_row("SELECT slug FROM project_details WHERE slug = ?", [$slug]);
    if ($exists) {
        db_run("UPDATE project_details SET data = ?, updated_at = ? WHERE slug = ?", [$encoded, now_iso(), $slug]);
    } else {
        db_run("INSERT INTO project_details (slug, data, updated_at) VALUES (?, ?, ?)", [$slug, $encoded, now_iso()]);
    }
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
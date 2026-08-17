<?php
// api/admin/resource.php — generic CRUD (port of src/app/api/admin/[resource]/route.ts + [resource]/[id]/route.ts)
// ?resource=services|agents|developers|communities|testimonials|faqs|media_library|jobs|projects|team
// ?id= targets a single row. TODO(phase10): per-resource field/validation config from admin-resources.ts

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$resource = (string) ($_GET['resource'] ?? '');
$id = (int) ($_GET['id'] ?? 0);

$ALLOWED = ['services', 'agents', 'developers', 'communities', 'testimonials', 'faqs', 'media_library', 'jobs', 'projects', 'team'];
if (!in_array($resource, $ALLOWED, true)) {
    json_response(['error' => 'Unknown resource'], 400);
}

// 'team' is served from the agents table in the original app (team pages read agents)
$table = $resource === 'team' ? 'agents' : $resource;

if ($method === 'GET') {
    if ($id > 0) {
        $row = db_row("SELECT * FROM $table WHERE id = ?", [$id]);
        if (!$row) json_response(['error' => 'Not found'], 404);
        json_response([$resource => $row]);
    }
    $rows = db_rows("SELECT * FROM $table ORDER BY id DESC");
    json_response([$resource => $rows]);
}
if ($method === 'POST') {
    $body = json_body();
    if ($table === 'media_library') {
        $res = db_run("INSERT INTO media_library (url, kind, alt, created_at) VALUES (?, 'image', '', ?)",
            [(string) ($body['url'] ?? ''), now_iso()]);
    } else {
        // generic insert: title/name slug unique column
        $titleCol = in_array($table, ['services', 'jobs'], true) ? 'title' : 'name';
        $slug = to_slug((string) ($body[$titleCol] ?? ''));
        $res = db_run("INSERT INTO $table ($titleCol, slug, created_at) VALUES (?, ?, ?)", [$body[$titleCol] ?? '', $slug, now_iso()]);
    }
    json_response([$resource => ['id' => $res['lastId']]], 201);
}
if ($method === 'PATCH' || $method === 'PUT') {
    $body = json_body();
    // TODO(phase10): whitelisted column update from admin-resources config
    json_response(['ok' => true]);
}
if ($method === 'DELETE') {
    db_run("DELETE FROM $table WHERE id = ?", [$id]);
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
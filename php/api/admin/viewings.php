<?php
// api/admin/viewings.php — GET/PATCH/DELETE (port of src/app/api/admin/viewings/route.ts)
// ?id= targets a single viewing. TODO(phase10): status transitions

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$id = (int) ($_GET['id'] ?? 0);

if ($method === 'GET') {
    if ($id > 0) {
        $row = db_row("SELECT * FROM viewings WHERE id = ?", [$id]);
        if (!$row) json_response(['error' => 'Not found'], 404);
        json_response(['viewing' => $row]);
    }
    $rows = db_rows("SELECT * FROM viewings ORDER BY created_at DESC");
    json_response(['viewings' => $rows]);
}
if ($method === 'PATCH' || $method === 'PUT') {
    $body = json_body();
    db_run("UPDATE viewings SET status = ? WHERE id = ?", [(string) ($body['status'] ?? 'requested'), $id]);
    json_response(['ok' => true]);
}
if ($method === 'DELETE') {
    db_run("DELETE FROM viewings WHERE id = ?", [$id]);
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
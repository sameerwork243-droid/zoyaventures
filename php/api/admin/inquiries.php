<?php
// api/admin/inquiries.php — GET/PATCH/DELETE (port of src/app/api/admin/inquiries/route.ts)
// ?id= targets a single inquiry. TODO(phase10): status transitions + filtering

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$id = (int) ($_GET['id'] ?? 0);

if ($method === 'GET') {
    if ($id > 0) {
        $row = db_row("SELECT * FROM inquiries WHERE id = ?", [$id]);
        if (!$row) json_response(['error' => 'Not found'], 404);
        json_response(['inquiry' => $row]);
    }
    $rows = db_rows("SELECT * FROM inquiries ORDER BY created_at DESC");
    json_response(['inquiries' => $rows]);
}
if ($method === 'PATCH' || $method === 'PUT') {
    $body = json_body();
    db_run("UPDATE inquiries SET status = ? WHERE id = ?", [(string) ($body['status'] ?? 'new'), $id]);
    json_response(['ok' => true]);
}
if ($method === 'DELETE') {
    db_run("DELETE FROM inquiries WHERE id = ?", [$id]);
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
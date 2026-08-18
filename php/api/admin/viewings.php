<?php
// api/admin/viewings.php — GET/PATCH/DELETE with user join
// (port of src/app/api/admin/viewings/route.ts)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$id = (int) ($_GET['id'] ?? 0);
$body = $method !== 'GET' ? json_body() : [];
if (!$id && isset($body['id'])) $id = (int) $body['id'];

if ($method === 'GET') {
    $rows = db_rows(
        "SELECT v.*, u.name AS user_name, u.email AS user_email, u.phone AS user_phone
         FROM viewings v LEFT JOIN users u ON u.id = v.user_id
         ORDER BY v.created_at DESC, v.id DESC"
    );
    json_response(['items' => $rows ?: []]);
}
if ($method === 'PATCH') {
    $status = trim((string) ($body['status'] ?? ''));
    if (!$id || $status === '') json_response(['error' => 'id and status are required'], 400);
    db_run("UPDATE viewings SET status = ? WHERE id = ?", [$status, $id]);
    json_response(['ok' => true]);
}
if ($method === 'DELETE') {
    if (!$id) json_response(['error' => 'Missing id'], 400);
    db_run("DELETE FROM viewings WHERE id = ?", [$id]);
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
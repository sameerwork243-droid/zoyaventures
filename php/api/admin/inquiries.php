<?php
// api/admin/inquiries.php — GET/PATCH/DELETE with kind filter + user join
// (port of src/app/api/admin/inquiries/route.ts)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$id = (int) ($_GET['id'] ?? 0);
$body = $method !== 'GET' ? json_body() : [];
if (!$id && isset($body['id'])) $id = (int) $body['id'];

if ($method === 'GET') {
    $kind = trim((string) ($_GET['kind'] ?? ''));
    if ($kind !== '') {
        $rows = db_rows(
            "SELECT i.*, u.name AS user_name, u.email AS user_email
             FROM inquiries i LEFT JOIN users u ON u.id = i.user_id
             WHERE i.kind = ? ORDER BY i.created_at DESC, i.id DESC",
            [$kind]
        );
    } else {
        $rows = db_rows(
            "SELECT i.*, u.name AS user_name, u.email AS user_email
             FROM inquiries i LEFT JOIN users u ON u.id = i.user_id
             ORDER BY i.created_at DESC, i.id DESC"
        );
    }
    json_response(['items' => $rows ?: []]);
}
if ($method === 'PATCH') {
    $status = trim((string) ($body['status'] ?? ''));
    if (!$id || $status === '') json_response(['error' => 'id and status are required'], 400);
    db_run("UPDATE inquiries SET status = ? WHERE id = ?", [$status, $id]);
    json_response(['ok' => true]);
}
if ($method === 'DELETE') {
    if (!$id) json_response(['error' => 'Missing id'], 400);
    db_run("DELETE FROM inquiries WHERE id = ?", [$id]);
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
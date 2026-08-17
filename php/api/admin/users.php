<?php
// api/admin/users.php — GET/POST/PATCH/DELETE (port of src/app/api/admin/users/route.ts + users/[id]/route.ts)
// ?id= selects a single user. TODO(phase10): full field validation

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$id = (int) ($_GET['id'] ?? 0);

if ($method === 'GET') {
    if ($id > 0) {
        $u = db_row("SELECT u.id, u.email, u.name, u.phone, u.avatar, r.name AS role, u.is_active, u.created_at FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?", [$id]);
        if (!$u) json_response(['error' => 'Not found'], 404);
        json_response(['user' => $u]);
    }
    $rows = db_rows("SELECT u.id, u.email, u.name, u.phone, u.avatar, r.name AS role, u.is_active, u.created_at FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.created_at DESC");
    json_response(['users' => $rows]);
}
if ($method === 'PATCH' || $method === 'PUT') {
    $body = json_body();
    db_run("UPDATE users SET name = ?, phone = ?, is_active = ? WHERE id = ?",
        [(string) ($body['name'] ?? ''), (string) ($body['phone'] ?? ''), (int) ($body['is_active'] ?? 1), $id]);
    json_response(['ok' => true]);
}
if ($method === 'DELETE') {
    db_run("DELETE FROM users WHERE id = ?", [$id]);
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
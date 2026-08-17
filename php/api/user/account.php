<?php
// api/user/account.php — GET/PATCH (port of src/app/api/user/account/route.ts)
// TODO(phase8): full port — GET returns user profile, PATCH updates profile fields

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$user = require_user();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    json_response(['user' => $user]);
}
if ($method === 'PATCH') {
    $body = json_body();
    // TODO(phase8): validate + persist editable fields (name, phone, avatar, address...)
    $name = trim((string) ($body['name'] ?? $user['name']));
    $phone = trim((string) ($body['phone'] ?? $user['phone']));
    db_run("UPDATE users SET name = ?, phone = ?, updated_at = ? WHERE id = ?", [$name, $phone, now_iso(), $user['id']]);
    json_response(['user' => get_auth_user()]);
}
json_response(['error' => 'Method not allowed'], 405);
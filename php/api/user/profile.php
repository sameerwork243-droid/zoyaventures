<?php
// api/user/profile.php — GET/PATCH (port of src/app/api/user/profile/route.ts)
// TODO(phase8): full port (name, phone, avatar)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$user = require_user();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    json_response(['profile' => $user]);
}
if ($method === 'PATCH') {
    $body = json_body();
    $name = trim((string) ($body['name'] ?? $user['name']));
    $phone = trim((string) ($body['phone'] ?? $user['phone']));
    $avatar = trim((string) ($body['avatar'] ?? $user['avatar']));
    db_run("UPDATE users SET name = ?, phone = ?, avatar = ?, updated_at = ? WHERE id = ?", [$name, $phone, $avatar, now_iso(), $user['id']]);
    json_response(['profile' => get_auth_user()]);
}
json_response(['error' => 'Method not allowed'], 405);
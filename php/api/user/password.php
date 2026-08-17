<?php
// api/user/password.php — POST (port of src/app/api/user/password/route.ts)
// TODO(phase8): full port (mirrors auth/change-password.php behavior)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$user = require_user();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$body = json_body();
$current = (string) ($body['current_password'] ?? '');
$next = (string) ($body['new_password'] ?? '');

if (strlen($next) < 8) json_response(['error' => 'New password must be at least 8 characters'], 400);
if (!preg_match('/[A-Za-z]/', $next) || !preg_match('/\d/', $next)) {
    json_response(['error' => 'New password must contain letters and numbers'], 400);
}

$record = find_user_by_email($user['email']);
if (!$record || !verify_password($current, (string) $record['password_hash'])) {
    json_response(['error' => 'Current password is incorrect'], 400);
}

db_run("UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?", [hash_password($next), now_iso(), $user['id']]);
json_response(['ok' => true]);
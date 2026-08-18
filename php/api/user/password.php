<?php
// api/user/password.php — POST (port of src/app/api/user/password/route.ts)
// The portal client sends only {new_password, confirm_password}.

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$user = require_user();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$body = json_body();
$next = (string) ($body['new_password'] ?? '');
$confirm = (string) ($body['confirm_password'] ?? '');

if ($next === '' || $confirm === '') json_response(['error' => 'Enter and confirm your new password'], 400);
if ($next !== $confirm) json_response(['error' => 'New passwords do not match'], 400);
if (strlen($next) < 8) json_response(['error' => 'Password must be at least 8 characters'], 400);
if (!preg_match('/[A-Za-z]/', $next) || !preg_match('/\d/', $next)) {
    json_response(['error' => 'Password must contain letters and numbers'], 400);
}

db_run("UPDATE users SET password_hash = ?, updated_at = ? WHERE id = ?", [hash_password($next), now_iso(), $user['id']]);
delete_all_sessions($user['id']);
json_response(['ok' => true]);
<?php
// api/user/account.php — GET/DELETE (port of src/app/api/user/account/route.ts)
// DELETE removes the account permanently (cascade wipes sessions/saved/viewings/prefs).

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$user = require_user();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    json_response(['user' => $user]);
}
if ($method === 'DELETE') {
    $body = json_body();
    $reason = trim((string) ($body['reason'] ?? ''));
    db_run("INSERT INTO account_deletion_logs (user_id, reason, created_at) VALUES (?, ?, ?)", [$user['id'], $reason, now_iso()]);
    db_run("DELETE FROM users WHERE id = ?", [$user['id']]);
    setcookie(SESSION_COOKIE_NAME, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => session_secure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
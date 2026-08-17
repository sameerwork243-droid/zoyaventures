<?php
// api/user/notifications.php — GET/PATCH (port of src/app/api/user/notifications/route.ts)
// TODO(phase8): full port — mark read, delete

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$user = require_user();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = db_rows("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC", [$user['id']]);
    json_response(['notifications' => $rows]);
}
if ($method === 'PATCH') {
    $body = json_body();
    if (isset($body['read_all']) && $body['read_all']) {
        db_run("UPDATE notifications SET `read` = 1 WHERE user_id = ?", [$user['id']]);
    }
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
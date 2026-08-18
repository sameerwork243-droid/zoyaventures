<?php
// api/user/notifications.php — GET/PATCH/PUT (port of src/app/api/user/notifications/route.ts)
// GET → items; PATCH (any body) → mark all read; PUT → save notification preferences.

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$user = require_user();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = db_rows("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC", [$user['id']]);
    json_response(['items' => $rows]);
}
if ($method === 'PATCH') {
    db_run("UPDATE notifications SET `read` = 1 WHERE user_id = ?", [$user['id']]);
    json_response(['ok' => true]);
}
if ($method === 'PUT') {
    $body = json_body();
    $p = $body['preferences'] ?? [];
    $prefs = db_row("SELECT id FROM notification_preferences WHERE user_id = ?", [$user['id']]);
    $sub = !empty($p['subscribe_news']) ? 1 : 0;
    $eml = !empty($p['email_notifications']) ? 1 : 0;
    $alerts = !empty($p['property_alerts']) ? 1 : 0;
    if ($prefs) {
        db_run("UPDATE notification_preferences SET subscribe_news = ?, email_notifications = ?, property_alerts = ?, updated_at = ? WHERE id = ?", [$sub, $eml, $alerts, now_iso(), $prefs['id']]);
    } else {
        db_run("INSERT INTO notification_preferences (user_id, subscribe_news, email_notifications, property_alerts, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)", [$user['id'], $sub, $eml, $alerts, now_iso(), now_iso()]);
    }
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
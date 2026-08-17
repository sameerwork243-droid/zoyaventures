<?php
// api/user/viewings.php — GET/POST (port of src/app/api/user/viewings/route.ts)
// TODO(phase8): full port with validation

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$user = require_user();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = db_rows("SELECT * FROM viewings WHERE user_id = ? ORDER BY created_at DESC", [$user['id']]);
    json_response(['viewings' => $rows]);
}
if ($method === 'POST') {
    $body = json_body();
    $res = db_run(
        "INSERT INTO viewings (user_id, property_ref, property_slug, preferred_date, time_slot, notes, status, created_at) VALUES (?, ?, ?, ?, ?, ?, 'requested', ?)",
        [$user['id'], (string) ($body['property_ref'] ?? ''), (string) ($body['property_slug'] ?? ''),
         (string) ($body['preferred_date'] ?? ''), (string) ($body['time_slot'] ?? ''), (string) ($body['notes'] ?? ''), now_iso()]
    );
    json_response(['ok' => true, 'id' => $res['lastId']], 201);
}
json_response(['error' => 'Method not allowed'], 405);
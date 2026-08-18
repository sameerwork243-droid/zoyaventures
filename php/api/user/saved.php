<?php
// api/user/saved.php — GET/POST/DELETE (port of src/app/api/user/saved/route.ts)
// TODO(phase8): full port with property snapshot fields

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$user = require_user();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = db_rows("SELECT * FROM saved_properties WHERE user_id = ? ORDER BY created_at DESC", [$user['id']]);
    json_response(['items' => $rows]);
}
if ($method === 'POST') {
    $body = json_body();
    $ref = (string) ($body['property_ref'] ?? '');
    if ($ref === '') json_response(['error' => 'Missing property_ref'], 400);
    db_run(
        "INSERT INTO saved_properties (user_id, property_ref, property_slug, title, price, thumb, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
        [$user['id'], $ref, (string) ($body['property_slug'] ?? ''), (string) ($body['title'] ?? ''),
         (int) ($body['price'] ?? 0), (string) ($body['thumb'] ?? ''), now_iso()]
    );
    json_response(['ok' => true], 201);
}
if ($method === 'DELETE') {
    $body = json_body();
    $ref = (string) ($body['property_ref'] ?? '');
    db_run("DELETE FROM saved_properties WHERE user_id = ? AND property_ref = ?", [$user['id'], $ref]);
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
<?php
// api/inquiries.php — POST (port of src/app/api/inquiries/route.ts)

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$KINDS = ['property', 'contact', 'viewing', 'quiz', 'careers', 'general', 'listing'];

$body = json_body();
$name = trim((string) ($body['name'] ?? ''));
$email = strtolower(trim((string) ($body['email'] ?? '')));
if (mb_strlen($name) < 2 || !preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
    json_response(['error' => 'Please provide your name and a valid email address'], 400);
}
$kind = in_array((string) ($body['kind'] ?? ''), $KINDS, true) ? (string) $body['kind'] : 'general';
$user = get_auth_user();

$res = db_run(
    "INSERT INTO inquiries (user_id, name, email, phone, kind, property_ref, property_slug, message, status, created_at)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'new', ?)",
    [$user['id'] ?? null, $name, $email, substr((string) ($body['phone'] ?? ''), 0, 60), $kind,
     (string) ($body['property_ref'] ?? ''), (string) ($body['property_slug'] ?? ''), substr((string) ($body['message'] ?? ''), 0, 8000), now_iso()]
);

json_response(['ok' => true, 'id' => $res['lastId']], 201);
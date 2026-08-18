<?php
// api/user/profile.php — GET/PUT (port of src/app/api/user/profile/route.ts)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$user = require_user();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $addr = db_row("SELECT address_line1, address_line2, town_city, postcode, country FROM user_addresses WHERE user_id = ? ORDER BY is_primary DESC, id ASC LIMIT 1", [$user['id']]);
    $prefs = db_row("SELECT subscribe_news, email_notifications, property_alerts FROM notification_preferences WHERE user_id = ?", [$user['id']]);
    $full = db_row("SELECT first_name, surname, email, phone, name FROM users WHERE id = ?", [$user['id']]) ?? [];
    json_response([
        'user' => [
            'id' => $user['id'],
            'first_name' => (string) ($full['first_name'] ?? ''),
            'surname' => (string) ($full['surname'] ?? ''),
            'email' => (string) ($full['email'] ?? $user['email']),
            'phone' => (string) ($full['phone'] ?? $user['phone']),
            'name' => (string) ($full['name'] ?? $user['name']),
        ],
        'address' => $addr ? array_map(fn ($v) => (string) $v, $addr) : null,
        'preferences' => $prefs
            ? [
                'subscribe_news' => (bool) $prefs['subscribe_news'],
                'email_notifications' => (bool) $prefs['email_notifications'],
                'property_alerts' => (bool) $prefs['property_alerts'],
            ]
            : ['subscribe_news' => true, 'email_notifications' => true, 'property_alerts' => true],
    ]);
}
if ($method === 'PUT') {
    $body = json_body();
    $first = trim((string) ($body['first_name'] ?? ''));
    $surname = trim((string) ($body['surname'] ?? ''));
    $email = trim((string) ($body['email'] ?? ''));
    $phone = trim((string) ($body['phone'] ?? ''));
    if ($first === '' || $surname === '') json_response(['error' => 'First name and surname are required'], 400);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['error' => 'Enter a valid email address'], 400);
    if ($email !== $user['email']) {
        $dup = db_row("SELECT id FROM users WHERE LOWER(email) = ? AND id != ?", [strtolower($email), $user['id']]);
        if ($dup) json_response(['error' => 'That email address is already in use'], 400);
    }
    $name = trim($first . ' ' . $surname);
    db_run("UPDATE users SET first_name = ?, surname = ?, name = ?, email = ?, phone = ?, updated_at = ? WHERE id = ?", [$first, $surname, $name, $email, $phone, now_iso(), $user['id']]);

    $address = $body['address'] ?? [];
    $existing = db_row("SELECT id FROM user_addresses WHERE user_id = ?", [$user['id']]);
    if ($existing) {
        db_run("UPDATE user_addresses SET address_line1 = ?, address_line2 = ?, town_city = ?, postcode = ?, country = ?, updated_at = ? WHERE id = ?", [
            (string) ($address['address_line1'] ?? ''), (string) ($address['address_line2'] ?? ''),
            (string) ($address['town_city'] ?? ''), (string) ($address['postcode'] ?? ''),
            (string) ($address['country'] ?? ''), now_iso(), $existing['id'],
        ]);
    } else {
        db_run("INSERT INTO user_addresses (user_id, address_line1, address_line2, town_city, postcode, country, is_primary, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, 1, ?, ?)", [
            $user['id'], (string) ($address['address_line1'] ?? ''), (string) ($address['address_line2'] ?? ''),
            (string) ($address['town_city'] ?? ''), (string) ($address['postcode'] ?? ''),
            (string) ($address['country'] ?? ''), now_iso(), now_iso(),
        ]);
    }

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
<?php
// api/auth/register.php — POST (port of src/app/api/auth/register/route.ts)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$body = json_body();
$email = strtolower(trim((string) ($body['email'] ?? '')));
$password = (string) ($body['password'] ?? '');
$name = trim((string) ($body['name'] ?? ''));
$phone = trim((string) ($body['phone'] ?? ''));

if (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email)) {
    json_response(['error' => 'Please enter a valid email address'], 400);
}
if (mb_strlen($name) < 2) {
    json_response(['error' => 'Please enter your full name'], 400);
}
if (strlen($password) < 8) {
    json_response(['error' => 'Password must be at least 8 characters'], 400);
}
if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
    json_response(['error' => 'Password must contain letters and numbers'], 400);
}

if (find_user_by_email($email)) {
    json_response(['error' => 'An account with this email already exists'], 409);
}

$userRole = db_row("SELECT id FROM roles WHERE name = 'user'");
$res = db_run(
    "INSERT INTO users (email, password_hash, name, phone, role_id, is_active, created_at) VALUES (?, ?, ?, ?, ?, 1, ?)",
    [$email, hash_password($password), $name, $phone, $userRole['id'] ?? 2, now_iso()]
);

login_user((int) $res['lastId'], true);

$user = get_auth_user();
json_response(['user' => $user]);
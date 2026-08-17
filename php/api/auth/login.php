<?php
// api/auth/login.php — POST (port of src/app/api/auth/login/route.ts)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

$body = json_body();
$email = strtolower(trim((string) ($body['email'] ?? '')));
$password = (string) ($body['password'] ?? '');
$remember = (bool) ($body['remember'] ?? false);

if (!preg_match('/^[^\s@]+@[^\s@]+\.[^\s@]+$/', $email) || $password === '') {
    json_response(['error' => 'Please enter your email and password'], 400);
}

$user = find_user_by_email($email);
if (!$user || !verify_password($password, (string) $user['password_hash'])) {
    json_response(['error' => 'Incorrect email or password'], 401);
}
if (!(int) $user['is_active']) {
    json_response(['error' => 'This account has been deactivated'], 403);
}

// legacy scrypt hashes are rehashed to password_hash on successful login
if (needs_password_rehash((string) $user['password_hash'])) {
    db_run("UPDATE users SET password_hash = ? WHERE id = ?", [hash_password($password), $user['id']]);
}

login_user((int) $user['id'], $remember);

json_response(['user' => [
    'id' => (int) $user['id'],
    'email' => (string) $user['email'],
    'name' => (string) $user['name'],
    'phone' => (string) ($user['phone'] ?? ''),
    'avatar' => (string) ($user['avatar'] ?? ''),
    'role' => (string) $user['role'],
]]);
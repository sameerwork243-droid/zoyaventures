<?php
// api/admin/users.php — GET/POST/PUT/DELETE (port of src/app/api/admin/users/route.ts)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$id = (int) ($_GET['id'] ?? 0);

if ($method === 'GET') {
    if ($id) {
        $u = db_row(
            "SELECT u.id, u.email, u.name, u.phone, u.avatar, u.is_active, u.last_login_at, u.created_at, r.name AS role
             FROM users u JOIN roles r ON r.id = u.role_id WHERE u.id = ?",
            [$id]
        );
        if (!$u) json_response(['error' => 'Not found'], 404);
        json_response(['item' => $u]);
    }
    $rows = db_rows(
        "SELECT u.id, u.email, u.name, u.phone, u.avatar, u.is_active, u.last_login_at, u.created_at, r.name AS role
         FROM users u JOIN roles r ON r.id = u.role_id ORDER BY u.id DESC"
    );
    json_response(['items' => $rows ?: []]);
}

$body = json_body();

if ($method === 'POST' || $method === 'PUT') {
    $name = trim((string) ($body['name'] ?? ''));
    $email = trim((string) ($body['email'] ?? ''));
    $phone = trim((string) ($body['phone'] ?? ''));
    $role = trim((string) ($body['role'] ?? 'user'));
    $active = !empty($body['is_active']) ? 1 : 0;
    $password = (string) ($body['password'] ?? '');

    if ($name === '' || $email === '') json_response(['error' => 'Name and email are required'], 400);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(['error' => 'Enter a valid email address'], 400);
    $roleRow = db_row("SELECT id FROM roles WHERE name = ?", [$role]) ?? db_row("SELECT id FROM roles WHERE name = 'user'");
    if (!$roleRow) json_response(['error' => 'Unknown role'], 400);
    if ($password !== '' && (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password))) {
        json_response(['error' => 'Password must be at least 8 characters with letters and numbers'], 400);
    }

    if ($method === 'POST') {
        if ($password === '') json_response(['error' => 'A password is required for new users'], 400);
        if (find_user_by_email($email)) json_response(['error' => 'An account with this email already exists'], 409);
        $res = db_run(
            "INSERT INTO users (email, password_hash, name, phone, role_id, is_active, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$email, hash_password($password), $name, $phone, $roleRow['id'], $active, now_iso()]
        );
        json_response(['ok' => true, 'id' => $res['lastId']], 201);
    }

    if (!$id) json_response(['error' => 'Missing id'], 400);
    $dup = db_row("SELECT id FROM users WHERE LOWER(email) = ? AND id != ?", [strtolower($email), $id]);
    if ($dup) json_response(['error' => 'An account with this email already exists'], 409);
    if ($password !== '') {
        db_run(
            "UPDATE users SET email = ?, name = ?, phone = ?, role_id = ?, is_active = ?, password_hash = ?, updated_at = ? WHERE id = ?",
            [$email, $name, $phone, $roleRow['id'], $active, hash_password($password), now_iso(), $id]
        );
    } else {
        db_run(
            "UPDATE users SET email = ?, name = ?, phone = ?, role_id = ?, is_active = ?, updated_at = ? WHERE id = ?",
            [$email, $name, $phone, $roleRow['id'], $active, now_iso(), $id]
        );
    }
    json_response(['ok' => true]);
}

if ($method === 'DELETE') {
    if (!$id) json_response(['error' => 'Missing id'], 400);
    db_run("DELETE FROM users WHERE id = ?", [$id]);
    json_response(['ok' => true]);
}

json_response(['error' => 'Method not allowed'], 405);
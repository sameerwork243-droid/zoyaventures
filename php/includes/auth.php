<?php
// auth.php — auth core + session handling (port of src/server/auth-core.ts + session.ts)
// Password policy per migration spec:
//   - New passwords: password_hash() (bcrypt/argon2)
//   - Legacy scrypt "salt:hash" (hex) hashes verified, then rehashed to password_hash on login
// Sessions: random token in cookie `provident_session`, sha256(token) stored in `sessions` table.

require_once __DIR__ . '/functions.php';

/* ------------------------------ password core ------------------------------ */

function hash_password(string $password): string
{
    return password_hash($password, PASSWORD_DEFAULT);
}

/** Verify a stored password hash. Supports password_hash() formats and legacy scrypt salt:hash. */
function verify_password(string $password, string $stored): bool
{
    if ($stored === '') return false;
    if (str_starts_with($stored, '$')) {
        return password_verify($password, $stored);
    }
    // legacy scrypt format: hex(salt16):hex(hash64)
    if (str_contains($stored, ':')) {
        [$salt, $hash] = explode(':', $stored, 2);
        if (strlen($salt) !== 32 || strlen($hash) !== 128) return false;
        if (function_exists('scrypt_verify')) {
            return scrypt_verify($password, hex2bin($salt), hex2bin($hash));
        }
        return false; // PHP < 8.4 has no native scrypt; user must reset via admin
    }
    return false;
}

/** True when the stored hash is the legacy scrypt format (needs rehash). */
function needs_password_rehash(string $stored): bool
{
    return $stored !== '' && !str_starts_with($stored, '$');
}

function hash_token(string $token): string
{
    return hash('sha256', $token);
}

/* -------------------------------- sessions -------------------------------- */

const SESSION_COOKIE_NAME = 'provident_session';

function session_secure(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
}

function create_session_token(int $userId, bool $remember): array
{
    $token = bin2hex(random_bytes(32));
    $expiresAt = time() * 1000 + ($remember ? REMEMBER_TTL : SESSION_TTL);
    db_run(
        "INSERT INTO sessions (user_id, token_hash, expires_at, user_agent, ip, created_at) VALUES (?, ?, ?, ?, ?, ?)",
        [$userId, hash_token($token), $expiresAt, $_SERVER['HTTP_USER_AGENT'] ?? '', $_SERVER['REMOTE_ADDR'] ?? '', now_iso()]
    );
    return ['token' => $token, 'expiresAt' => $expiresAt];
}

function set_session_cookie(string $token, int $expiresAt): void
{
    $maxAge = (int) floor(($expiresAt - time() * 1000) / 1000);
    setcookie(SESSION_COOKIE_NAME, $token, [
        'expires' => time() + $maxAge,
        'path' => '/',
        'secure' => session_secure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

/** Current auth user or null (getAuthUser port). */
function get_auth_user(): ?array
{
    $token = $_COOKIE[SESSION_COOKIE_NAME] ?? null;
    if (!$token) return null;
    $s = db_row("SELECT user_id, expires_at FROM sessions WHERE token_hash = ?", [hash_token($token)]);
    if (!$s) return null;
    if ((int) $s['expires_at'] < (int) (microtime(true) * 1000)) {
        db_run("DELETE FROM sessions WHERE token_hash = ?", [hash_token($token)]);
        return null;
    }
    $u = db_row(
        "SELECT u.id, u.email, u.name, u.phone, u.avatar, r.name AS role, u.last_login_at, u.created_at
         FROM users u JOIN roles r ON r.id = u.role_id
         WHERE u.id = ? AND u.is_active = 1",
        [$s['user_id']]
    );
    if (!$u) return null;
    return [
        'id' => (int) $u['id'],
        'email' => (string) $u['email'],
        'name' => (string) $u['name'],
        'phone' => (string) ($u['phone'] ?? ''),
        'avatar' => (string) ($u['avatar'] ?? ''),
        'role' => (string) $u['role'],
        'last_login_at' => $u['last_login_at'] ? (string) $u['last_login_at'] : null,
        'created_at' => (string) $u['created_at'],
    ];
}

function login_user(int $userId, bool $remember = false): void
{
    $t = create_session_token($userId, $remember);
    set_session_cookie($t['token'], $t['expiresAt']);
    db_run("UPDATE users SET last_login_at = ? WHERE id = ?", [now_iso(), $userId]);
}

function logout_user(): void
{
    $token = $_COOKIE[SESSION_COOKIE_NAME] ?? null;
    if ($token) {
        db_run("DELETE FROM sessions WHERE token_hash = ?", [hash_token($token)]);
    }
    setcookie(SESSION_COOKIE_NAME, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => session_secure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    unset($_COOKIE[SESSION_COOKIE_NAME]);
}

function delete_all_sessions(int $userId): void
{
    db_run("DELETE FROM sessions WHERE user_id = ?", [$userId]);
}

function find_user_by_email(string $email): ?array
{
    return db_row(
        "SELECT u.id, u.email, u.password_hash, u.name, u.phone, u.avatar, u.role_id, u.is_active, r.name AS role
         FROM users u JOIN roles r ON r.id = u.role_id WHERE LOWER(u.email) = ?",
        [strtolower(trim($email))]
    );
}

/* ------------------------------ route guards ------------------------------ */

/** requireUser port: redirect to /login when unauthenticated. */
function require_user(): array
{
    $user = get_auth_user();
    if (!$user) {
        header('Location: /login');
        exit;
    }
    return $user;
}

/** requireAdmin port: redirect to /dashboard when not admin/agent. */
function require_admin(): array
{
    $user = require_user();
    if ($user['role'] !== 'admin') {
        header('Location: /dashboard');
        exit;
    }
    return $user;
}

/** requireGuest port: redirect authed users to their dashboard/admin. */
function require_guest(): ?array
{
    $user = get_auth_user();
    if ($user) {
        header('Location: ' . (in_array($user['role'], ['admin', 'agent'], true) ? '/admin' : '/dashboard'));
        exit;
    }
    return null;
}
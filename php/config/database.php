<?php
// database.php — PDO MySQL connection (port of src/server/db.ts)
// Supports the same PROVIDENT_DATABASE_URL env contract: mysql://user:pass@host:port/dbname
// If no MySQL URL is configured, db_enabled() returns false and callers fall back to JSON.

require_once __DIR__ . '/env.php';

function db_url(): string
{
    return (string) (env('PROVIDENT_DATABASE_URL') ?? env('DATABASE_URL') ?? '');
}

function db_enabled(): bool
{
    return str_starts_with(db_url(), 'mysql://');
}

function db_connect(): ?PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;
    if (!db_enabled()) return null;

    $url = db_url();
    $u = parse_url($url);
    if ($u === false || !isset($u['host'])) return null;

    $scheme = $u['scheme'] ?? 'mysql';
    $host = $u['host'] ?? '127.0.0.1';
    $port = $u['port'] ?? 3306;
    $user = rawurldecode($u['user'] ?? 'root');
    $pass = rawurldecode($u['pass'] ?? '');
    $dbname = isset($u['path']) ? ltrim(rawurldecode($u['path']), '/') : '';
    $query = $u['query'] ?? '';

    $dsn = sprintf('%s:host=%s;port=%d;charset=utf8mb4', $scheme, $host, $port);
    if ($dbname !== '') $dsn .= ";dbname=$dbname";

    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    // PDO::MYSQL_ATTR_FOUND_ROWS is deprecated since PHP 8.5
    if (PHP_VERSION_ID >= 80500) {
        $opts[\Pdo\Mysql::ATTR_FOUND_ROWS] = true;
    } else {
        $opts[PDO::MYSQL_ATTR_FOUND_ROWS] = true;
    }

    // sslmode=require / PROVIDENT_DB_SSL=1 → enable SSL (Neon-style URLs)
    if (env('PROVIDENT_DB_SSL') === '1' || str_contains(strtolower($query), 'sslmode=require') || str_contains(strtolower($query), 'sslmode=verify-full')) {
        $opts[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    }

    try {
        $pdo = new PDO($dsn, $user, $pass, $opts);
    } catch (PDOException $e) {
        // mirror dbEnabled() fallback semantics: API stays functional via JSON data
        error_log('[db] connection failed: ' . $e->getMessage());
        return null;
    }
    return $pdo;
}

/** Rowset helper with the same `run` return shape as the Node layer. */
function db_rows(string $sql, array $params = []): array
{
    $pdo = db_connect();
    if (!$pdo) return [];
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return $st->fetchAll();
}

function db_row(string $sql, array $params = []): ?array
{
    $rows = db_rows($sql, $params);
    return $rows[0] ?? null;
}

function db_run(string $sql, array $params = []): array
{
    $pdo = db_connect();
    if (!$pdo) return ['changes' => 0, 'lastId' => 0];
    $st = $pdo->prepare($sql);
    $st->execute($params);
    return ['changes' => $st->rowCount(), 'lastId' => (int) $pdo->lastInsertId()];
}

function db_now(): string
{
    return gmdate('Y-m-d\TH:i:s.v\Z');
}

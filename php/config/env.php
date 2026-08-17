<?php
// env.php — minimal .env loader (no dependencies)

function env_load(string $path): void
{
    if (!is_file($path)) return;
    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v);
        // strip optional surrounding quotes
        if ((str_starts_with($v, '"') && str_ends_with($v, '"')) || (str_starts_with($v, "'") && str_ends_with($v, "'"))) {
            $v = substr($v, 1, -1);
        }
        if (!getenv($k)) putenv("$k=$v");
        if (!array_key_exists($k, $_ENV)) $_ENV[$k] = $v;
    }
}

function env(string $key, ?string $default = null): ?string
{
    $v = getenv($key);
    if ($v === false) $v = $_ENV[$key] ?? null;
    return $v !== null ? (string) $v : $default;
}

<?php
// api/auth/me.php — GET (port of src/app/api/auth/me/route.ts)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

$user = get_auth_user();
json_response(['user' => $user]);
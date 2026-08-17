<?php
// api/auth/logout.php — POST (port of src/app/api/auth/logout/route.ts)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

logout_user();
json_response(['success' => true]);
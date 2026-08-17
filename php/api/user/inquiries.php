<?php
// api/user/inquiries.php — GET (port of src/app/api/user/inquiries/route.ts)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$user = require_user();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}
$rows = db_rows("SELECT * FROM inquiries WHERE user_id = ? ORDER BY created_at DESC", [$user['id']]);
json_response(['inquiries' => $rows]);
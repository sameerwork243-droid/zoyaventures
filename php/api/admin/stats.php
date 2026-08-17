<?php
// api/admin/stats.php — GET (port of src/app/api/admin/stats/route.ts)
// TODO(phase10): full port of each stat (counts + trends)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

json_response([
    'stats' => [
        'properties' => (int) (db_row('SELECT COUNT(*) AS n FROM properties')['n'] ?? 0),
        'inquiries' => (int) (db_row('SELECT COUNT(*) AS n FROM inquiries')['n'] ?? 0),
        'users' => (int) (db_row('SELECT COUNT(*) AS n FROM users')['n'] ?? 0),
        'viewings' => (int) (db_row('SELECT COUNT(*) AS n FROM viewings')['n'] ?? 0),
    ],
]);
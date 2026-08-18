<?php
// api/admin/stats.php — dashboard stats (port of src/app/api/admin/stats/route.ts)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    json_response(['error' => 'Method not allowed'], 405);
}

$count = fn (string $table, string $where = '1=1', array $params = []) =>
    (int) (db_row("SELECT COUNT(*) AS n FROM `$table` WHERE $where", $params)['n'] ?? 0);

$stats = [
    'properties' => $count('properties'),
    'publishedProperties' => $count('properties', 'published = 1'),
    'users' => $count('users'),
    'inquiries' => $count('inquiries'),
    'newInquiries' => $count('inquiries', "status = 'new'"),
    'viewings' => $count('viewings'),
    'pendingViewings' => $count('viewings', "status = 'requested'"),
    'services' => $count('services'),
    'agents' => $count('agents'),
    'developers' => $count('developers'),
    'communities' => $count('communities'),
    'testimonials' => $count('testimonials'),
    'faqs' => $count('faqs'),
    'media' => $count('media_library'),
    'savedProperties' => $count('saved_properties'),
];

$recent = db_rows(
    "SELECT i.id, i.name, i.email, i.kind, i.message, i.status, i.created_at
     FROM inquiries i ORDER BY i.created_at DESC, i.id DESC LIMIT 5"
);

json_response(['stats' => $stats, 'recentInquiries' => $recent ?: []]);
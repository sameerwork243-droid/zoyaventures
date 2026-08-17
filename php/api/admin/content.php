<?php
// api/admin/content.php — key/value content editors (port of src/app/api/admin/{about,contact,homepage,amenities,categories,project-details}/route.ts)
// ?key=about|contact|homepage|amenities|categories|project-details ; value = JSON string

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$key = (string) ($_GET['key'] ?? '');

$TABLES = [
    'about' => 'page_content', 'contact' => 'contact_info', 'homepage' => 'homepage_content',
    'amenities' => 'amenities', 'categories' => 'categories', 'project-details' => 'project_details',
];
if (!isset($TABLES[$key])) {
    json_response(['error' => 'Unknown content key'], 400);
}
$table = $TABLES[$key];

if ($method === 'GET') {
    if ($key === 'amenities' || $key === 'categories') {
        $rows = db_rows("SELECT * FROM $table ORDER BY sort, id");
        json_response(['items' => $rows]);
    }
    if ($key === 'project-details') {
        $rows = db_rows("SELECT slug, data, updated_at FROM $table");
        json_response(['items' => $rows]);
    }
    $row = db_row("SELECT `value` FROM $table WHERE `key` = ?", [$key]);
    json_response(['value' => $row ? json_decode($row['value'], true) : null]);
}
if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
    $body = json_body();
    if ($key === 'amenities' || $key === 'categories' || $key === 'project-details') {
        // TODO(phase10): item-level CRUD for these resources
        json_response(['error' => 'Item-level CRUD not yet implemented'], 501);
    }
    $value = json_encode($body['value'] ?? $body, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    db_run("INSERT INTO $table (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)", [$key, $value]);
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
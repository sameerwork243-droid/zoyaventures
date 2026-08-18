<?php
// api/admin/amenities.php — GET list / POST create / DELETE by name

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = db_rows("SELECT * FROM amenities ORDER BY name");
    json_response(['items' => $rows ?: []]);
}
if ($method === 'POST') {
    $name = trim((string) (json_body()['name'] ?? ''));
    if ($name === '') json_response(['error' => 'Name is required'], 400);
    $exists = db_row("SELECT id FROM amenities WHERE name = ?", [$name]);
    if ($exists) json_response(['ok' => true, 'id' => $exists['id']], 200);
    $res = db_run("INSERT INTO amenities (name) VALUES (?)", [$name]);
    json_response(['ok' => true, 'id' => $res['lastId']], 201);
}
if ($method === 'DELETE') {
    $body = json_body();
    $name = trim((string) ($_GET['name'] ?? ($body['name'] ?? '')));
    if ($name === '') json_response(['error' => 'Name is required'], 400);
    $a = db_row("SELECT id FROM amenities WHERE name = ?", [$name]);
    if ($a) {
        db_run("DELETE FROM property_amenities WHERE amenity_id = ?", [$a['id']]);
        db_run("DELETE FROM amenities WHERE id = ?", [$a['id']]);
    }
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
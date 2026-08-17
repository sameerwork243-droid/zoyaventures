<?php
// api/admin/properties.php — GET/POST/PATCH/DELETE (port of src/app/api/admin/properties/route.ts + properties/[id]/route.ts)
// ?id= selects a single property. TODO(phase10): full field validation + media/amenities handling

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$id = (int) ($_GET['id'] ?? 0);

if ($method === 'GET') {
    if ($id > 0) {
        $p = db_row("SELECT * FROM properties WHERE id = ?", [$id]);
        if (!$p) json_response(['error' => 'Not found'], 404);
        json_response(['property' => $p]);
    }
    $rows = db_rows("SELECT * FROM properties ORDER BY created_at DESC");
    json_response(['properties' => $rows]);
}
if ($method === 'POST') {
    $body = json_body();
    $slug = to_slug((string) ($body['title'] ?? ''));
    $res = db_run(
        "INSERT INTO properties (slug, title, category, property_type, transaction_type, status, price, price_qualifier, community, developer, location, display_address, bedroom, bathroom, area_sqft, introtext, long_description, featured, published, created_at)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)",
        [$slug, (string) ($body['title'] ?? ''), (string) ($body['category'] ?? ''), (string) ($body['property_type'] ?? 'apartment'),
         (string) ($body['transaction_type'] ?? 'buy'), (string) ($body['status'] ?? 'ready'), (int) ($body['price'] ?? 0),
         (string) ($body['price_qualifier'] ?? 'AED'), (string) ($body['community'] ?? ''), (string) ($body['developer'] ?? ''),
         (string) ($body['location'] ?? ''), (string) ($body['display_address'] ?? ''), (int) ($body['bedroom'] ?? 0),
         (int) ($body['bathroom'] ?? 0), (int) ($body['area_sqft'] ?? 0), (string) ($body['introtext'] ?? ''),
         (string) ($body['long_description'] ?? ''), (int) ($body['featured'] ?? 0), now_iso()]
    );
    json_response(['property' => ['id' => $res['lastId']]], 201);
}
if ($method === 'PATCH' || $method === 'PUT') {
    $body = json_body();
    db_run("UPDATE properties SET title = ?, updated_at = ? WHERE id = ?", [(string) ($body['title'] ?? ''), now_iso(), $id]);
    json_response(['ok' => true]);
}
if ($method === 'DELETE') {
    db_run("DELETE FROM properties WHERE id = ?", [$id]);
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
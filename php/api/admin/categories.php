<?php
// api/admin/categories.php — item CRUD for categories (name/slug/type/sort)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$id = (int) ($_GET['id'] ?? 0);

if ($method === 'GET') {
    if ($id) {
        $row = db_row("SELECT * FROM categories WHERE id = ?", [$id]);
        if (!$row) json_response(['error' => 'Not found'], 404);
        json_response(['item' => $row]);
    }
    $rows = db_rows("SELECT * FROM categories ORDER BY sort, id");
    json_response(['items' => $rows ?: []]);
}

$body = json_body();

if ($method === 'POST' || $method === 'PUT') {
    $name = trim((string) ($body['name'] ?? ''));
    $slug = trim((string) ($body['slug'] ?? ''));
    $type = trim((string) ($body['type'] ?? ''));
    $sort = (int) ($body['sort'] ?? 0);
    if ($name === '') json_response(['error' => 'Name is required'], 400);
    if ($method === 'POST') {
        $res = db_run(
            "INSERT INTO categories (name, slug, type, sort) VALUES (?, ?, ?, ?)",
            [$name, $slug, $type, $sort]
        );
        json_response(['ok' => true, 'id' => $res['lastId']], 201);
    }
    if (!$id) json_response(['error' => 'Missing id'], 400);
    db_run("UPDATE categories SET name = ?, slug = ?, type = ?, sort = ? WHERE id = ?", [$name, $slug, $type, $sort, $id]);
    json_response(['ok' => true]);
}
if ($method === 'DELETE') {
    if (!$id) json_response(['error' => 'Missing id'], 400);
    db_run("DELETE FROM categories WHERE id = ?", [$id]);
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
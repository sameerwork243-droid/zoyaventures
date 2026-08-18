<?php
// api/admin/kv.php — shared GET/PUT for key/value content tables
// GET → {items:[{key,value}]}; PUT {items:[{key,value}]} → upsert.

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$table = (string) ($_GET['table'] ?? '');
if (!in_array($table, ['page_content', 'homepage_content', 'contact_info'], true)) {
    json_response(['error' => 'Unknown table'], 400);
}
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    $rows = db_rows("SELECT `key`, value FROM `$table` ORDER BY id");
    json_response(['items' => $rows ?: []]);
}
if ($method === 'PUT') {
    $body = json_body();
    $items = $body['items'] ?? [];
    if (!is_array($items)) json_response(['error' => 'items must be an array'], 400);
    foreach ($items as $it) {
        $key = (string) ($it['key'] ?? '');
        $value = (string) ($it['value'] ?? '');
        if ($key === '') continue;
        $exists = db_row("SELECT id FROM `$table` WHERE `key` = ?", [$key]);
        if ($exists) {
            db_run("UPDATE `$table` SET value = ? WHERE `key` = ?", [$value, $key]);
        } else {
            db_run("INSERT INTO `$table` (`key`, value) VALUES (?, ?)", [$key, $value]);
        }
    }
    json_response(['ok' => true]);
}
json_response(['error' => 'Method not allowed'], 405);
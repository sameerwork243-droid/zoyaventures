<?php
// api/admin/_crud.php — shared generic CRUD for admin endpoints.
// Each endpoint file declares its table + column allowlist and dispatches here.

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

/** JSON list keys accepted in the request body. */
function admin_crud_dispatch(array $config): never
{
    require_admin();

    $table = $config['table'];
    $cols = $config['cols'];          // column => type ('text'|'int'|'json'|'float')
    $search = $config['search'] ?? []; // columns searched by ?q=
    $labelCol = $config['label'] ?? 'title';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $id = (int) ($_GET['id'] ?? 0);

    if ($method === 'GET') {
        $q = trim((string) ($_GET['q'] ?? ''));
        if ($q !== '' && $search) {
            $where = [];
            $params = [];
            foreach ($search as $c) {
                $where[] = "$c LIKE ?";
                $params[] = "%$q%";
            }
            $rows = db_rows("SELECT * FROM `$table` WHERE " . implode(' OR ', $where) . " ORDER BY id DESC", $params);
        } else {
            $rows = db_rows("SELECT * FROM `$table` ORDER BY id DESC");
        }
        if ($id) {
            $row = null;
            foreach ($rows as $r) { if ((int) $r['id'] === $id) { $row = $r; break; } }
            if (!$row) json_response(['error' => 'Not found'], 404);
            json_response(['item' => $row]);
        }
        json_response(['items' => $rows ?: []]);
    }

    $body = json_body();

    if ($method === 'POST' || $method === 'PUT') {
        $set = [];
        $params = [];
        foreach ($cols as $col => $type) {
            if (!array_key_exists($col, $body)) {
                if ($method === 'POST') continue;
                continue;
            }
            $v = $body[$col];
            switch ($type) {
                case 'int':
                    $v = $v === '' || $v === null ? 0 : (int) $v;
                    break;
                case 'float':
                    $v = $v === '' || $v === null ? 0.0 : (float) $v;
                    break;
                case 'json':
                    $v = is_array($v) ? $v : array_values(array_filter(array_map('trim', explode(',', (string) $v))));
                    $v = json_encode($v);
                    break;
                default:
                    $v = (string) $v;
            }
            $set[] = "`$col` = ?";
            $params[] = $v;
        }
        if ($method === 'POST') {
            $set[] = 'created_at = ?';
            $params[] = now_iso();
            $res = db_run("INSERT INTO `$table` SET " . implode(', ', $set), $params);
            json_response(['ok' => true, 'id' => $res['lastId']], 201);
        }
        if (!$id) json_response(['error' => 'Missing id'], 400);
        $set[] = 'updated_at = ?';
        $params[] = now_iso();
        $params[] = $id;
        db_run("UPDATE `$table` SET " . implode(', ', $set) . " WHERE id = ?", $params);
        json_response(['ok' => true]);
    }

    if ($method === 'DELETE') {
        if (!$id) json_response(['error' => 'Missing id'], 400);
        db_run("DELETE FROM `$table` WHERE id = ?", [$id]);
        json_response(['ok' => true]);
    }

    json_response(['error' => 'Method not allowed'], 405);
}
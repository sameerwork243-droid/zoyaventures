<?php
// api/admin/properties.php — full property CRUD with media + amenities
// (port of src/app/api/admin/properties/route.ts behavior)

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$id = (int) ($_GET['id'] ?? 0);

if ($method === 'GET') {
    if ($id) {
        $p = db_row("SELECT * FROM properties WHERE id = ?", [$id]);
        if (!$p) json_response(['error' => 'Not found'], 404);
        $media = db_rows("SELECT id, kind, url, is_featured FROM property_media WHERE property_id = ? ORDER BY sort_order, id", [$id]);
        $amenities = db_rows(
            "SELECT a.name FROM property_amenities pa JOIN amenities a ON a.id = pa.amenity_id WHERE pa.property_id = ?",
            [$id]
        );
        $p['media'] = $media ?: [];
        $p['amenities'] = array_map(fn ($r) => $r['name'], $amenities ?: []);
        json_response(['item' => $p]);
    }
    $q = trim((string) ($_GET['q'] ?? ''));
    if ($q !== '') {
        $rows = db_rows(
            "SELECT p.*, (SELECT COUNT(*) FROM property_media m WHERE m.property_id = p.id) AS image_count,
                    (SELECT COUNT(*) FROM property_amenities pa WHERE pa.property_id = p.id) AS amenity_count
             FROM properties p WHERE p.title LIKE ? OR p.slug LIKE ? OR p.developer LIKE ? OR p.community LIKE ?
             ORDER BY p.id DESC",
            ["%$q%", "%$q%", "%$q%", "%$q%"]
        );
    } else {
        $rows = db_rows(
            "SELECT p.*, (SELECT COUNT(*) FROM property_media m WHERE m.property_id = p.id) AS image_count,
                    (SELECT COUNT(*) FROM property_amenities pa WHERE pa.property_id = p.id) AS amenity_count
             FROM properties p ORDER BY p.id DESC"
        );
    }
    json_response(['items' => $rows ?: []]);
}

$body = json_body();

if ($method === 'POST' || $method === 'PUT') {
    $cols = [
        'title', 'slug', 'transaction_type', 'property_type', 'category', 'status',
        'price', 'price_qualifier', 'community', 'developer', 'agent_id', 'location',
        'display_address', 'latitude', 'longitude', 'bedroom', 'bathroom', 'area_sqft',
        'plot_size', 'parking', 'furnished', 'completion_status', 'year_built',
        'featured', 'published', 'introtext', 'long_description',
    ];
    $set = [];
    $params = [];
    foreach ($cols as $col) {
        if (!array_key_exists($col, $body)) continue;
        $v = $body[$col];
        switch ($col) {
            case 'price':
            case 'bedroom':
            case 'bathroom':
            case 'area_sqft':
            case 'plot_size':
            case 'parking':
            case 'year_built':
            case 'featured':
            case 'published':
                $v = $v === '' || $v === null ? 0 : (int) $v;
                break;
            case 'latitude':
            case 'longitude':
                $v = $v === '' || $v === null ? null : (float) $v;
                break;
            case 'agent_id':
                $v = $v === '' || $v === null ? null : (int) $v;
                break;
            default:
                $v = (string) $v;
        }
        $set[] = "`$col` = ?";
        $params[] = $v;
    }

    if ($method === 'POST') {
        $title = (string) ($body['title'] ?? '');
        if (trim($title) === '') json_response(['error' => 'Title is required'], 400);
        $slug = trim((string) ($body['slug'] ?? ''));
        if ($slug === '') {
            $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($title));
            $slug = trim($slug, '-');
        }
        $existing = db_row("SELECT id FROM properties WHERE slug = ?", [$slug]);
        $slug = $existing ? $slug . '-' . random_int(100, 999) : $slug;
        $set[] = 'slug = ?';
        $params[] = $slug;
        $set[] = 'created_at = ?';
        $params[] = now_iso();
        $res = db_run("INSERT INTO properties SET " . implode(', ', $set), $params);
        $id = (int) $res['lastId'];
    } else {
        if (!$id) json_response(['error' => 'Missing id'], 400);
        $set[] = 'updated_at = ?';
        $params[] = now_iso();
        $params[] = $id;
        db_run("UPDATE properties SET " . implode(', ', $set) . " WHERE id = ?", $params);
    }

    if ($id) {
        db_run("DELETE FROM property_media WHERE property_id = ?", [$id]);
        $media = $body['media'] ?? [];
        $sort = 0;
        foreach ($media as $m) {
            $url = trim((string) ($m['url'] ?? ''));
            if ($url === '') continue;
            db_run(
                "INSERT INTO property_media (property_id, kind, url, is_featured, sort_order) VALUES (?, ?, ?, ?, ?)",
                [$id, (string) ($m['kind'] ?? 'image'), $url, $sort === 0 ? 1 : 0, $sort]
            );
            $sort++;
        }
        db_run("DELETE FROM property_amenities WHERE property_id = ?", [$id]);
        $amenities = $body['amenities'] ?? [];
        foreach ($amenities as $name) {
            $name = trim((string) $name);
            if ($name === '') continue;
            $a = db_row("SELECT id FROM amenities WHERE name = ?", [$name]);
            if (!$a) {
                $r = db_run("INSERT INTO amenities (name) VALUES (?)", [$name]);
                $a = ['id' => $r['lastId']];
            }
            db_run("INSERT INTO property_amenities (property_id, amenity_id) VALUES (?, ?)", [$id, $a['id']]);
        }
    }

    json_response(['ok' => true, 'id' => $id], $method === 'POST' ? 201 : 200);
}

if ($method === 'DELETE') {
    if (!$id) json_response(['error' => 'Missing id'], 400);
    db_run("DELETE FROM properties WHERE id = ?", [$id]);
    json_response(['ok' => true]);
}

json_response(['error' => 'Method not allowed'], 405);
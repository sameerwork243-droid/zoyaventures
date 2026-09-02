<?php
// api/admin/projects.php — CRUD for the projects table + rich-detail bridge.
// After every save the row is mirrored into project_details so admin-created
// projects become public (curated) and their floor plans render on the site.
require_once __DIR__ . '/_crud.php';

/** Mirror a saved projects row into project_details (merge over existing detail). */
function projects_sync_detail(int $id): void
{
    $row = db_row('SELECT * FROM projects WHERE id = ?', [$id]);
    if (!$row) return;
    $slug = trim((string) ($row['slug'] ?? ''));
    if ($slug === '') return;

    $existing = db_row('SELECT data FROM project_details WHERE slug = ? LIMIT 1', [$slug]);
    $detail = $existing ? json_decode((string) $existing['data'], true) : null;
    if (!is_array($detail)) $detail = [];

    $imgs = db_json_arr($row['images'] ?? '');
    $mediaImages = array_values(array_map(fn ($u) => ['url' => $u], array_filter($imgs)));
    $banner = (string) ($row['banner_image'] ?? '');

    $floorPlans = [];
    $fpRaw = json_decode((string) ($row['floor_plans'] ?? ''), true);
    if (is_array($fpRaw)) {
        foreach ($fpRaw as $p) {
            if (!is_array($p)) continue;
            $media = $p['media'] ?? '';
            if (is_array($media)) $media = (string) ($media['url'] ?? '');
            $title = trim((string) ($p['title'] ?? ''));
            if ($title === '' && (string) $media === '') continue;
            $floorPlans[] = [
                'title' => $title !== '' ? $title : 'Floor Plan',
                'size' => (string) ($p['size'] ?? ''),
                'media' => ['url' => (string) $media],
            ];
        }
    }

    $amenityTexts = db_json_arr($row['amenities'] ?? '');
    $amenities = array_values(array_filter(array_map(fn ($t) => ['text' => (string) $t, 'image' => null], $amenityTexts)));

    $detail = array_merge($detail, [
        'slug' => $slug,
        'title' => (string) ($row['title'] ?? ''),
        'developer' => (string) ($row['developer'] ?? ''),
        'status' => (string) ($row['status'] ?? '') ?: ($detail['status'] ?? ''),
        'price' => (int) ($row['price'] ?? 0),
        'display_address' => (string) ($row['display_address'] ?? ''),
        'completion_year' => $row['completion_year'] !== null ? (int) $row['completion_year'] : ($detail['completion_year'] ?? null),
        'about' => (string) ($row['about'] ?? ''),
        'department' => (string) ($row['department'] ?? '') ?: ($detail['department'] ?? 'new_developments'),
        'building_type' => db_json_arr($row['building_type'] ?? '') ?: ($detail['building_type'] ?? []),
        'media_images' => $mediaImages ?: ($detail['media_images'] ?? []),
        'banner_image' => $banner !== '' ? ['url' => $banner] : ($detail['banner_image'] ?? null),
        'floor_plans' => $floorPlans ?: ($detail['floor_plans'] ?? []),
        'amenities' => $amenities ?: ($detail['amenities'] ?? []),
    ]);

    db_run(
        'INSERT INTO project_details (slug, data, updated_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE data = VALUES(data), updated_at = VALUES(updated_at)',
        [$slug, json_encode($detail), now_iso()]
    );
}

admin_crud_dispatch([
    'table' => 'projects',
    'label' => 'title',
    'search' => ['title', 'slug', 'developer'],
    'after' => function (string $method, int $id): void {
        if ($method === 'POST' || $method === 'PUT') projects_sync_detail($id);
        if ($method === 'DELETE') {
            // Row removed: nothing else to mirror; detail record intentionally kept.
        }
    },
    'cols' => [
        'title' => 'text', 'slug' => 'text', 'status' => 'text', 'price' => 'int',
        'currency' => 'text', 'bedrooms_min' => 'int', 'bedrooms_max' => 'int',
        'completion_year' => 'int', 'community' => 'text', 'developer' => 'text',
        'department' => 'text', 'display_address' => 'text', 'building_type' => 'json',
        'about' => 'text', 'images' => 'json', 'amenities' => 'json',
        'floor_plans' => 'json',
        'banner_image' => 'text', 'published' => 'int',
    ],
]);

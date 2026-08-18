<?php
// api/admin/services.php — CRUD for the services table
require_once __DIR__ . '/_crud.php';
admin_crud_dispatch([
    'table' => 'services',
    'label' => 'title',
    'search' => ['title', 'slug'],
    'cols' => [
        'title' => 'text', 'slug' => 'text', 'icon' => 'text', 'banner_image' => 'text',
        'description' => 'text', 'rich_content' => 'text', 'gallery' => 'json',
        'seo_title' => 'text', 'seo_description' => 'text', 'published' => 'int',
    ],
]);
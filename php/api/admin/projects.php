<?php
// api/admin/projects.php — CRUD for the projects table
require_once __DIR__ . '/_crud.php';
admin_crud_dispatch([
    'table' => 'projects',
    'label' => 'title',
    'search' => ['title', 'slug', 'developer'],
    'cols' => [
        'title' => 'text', 'slug' => 'text', 'status' => 'text', 'price' => 'int',
        'currency' => 'text', 'bedrooms_min' => 'int', 'bedrooms_max' => 'int',
        'completion_year' => 'int', 'community' => 'text', 'developer' => 'text',
        'department' => 'text', 'display_address' => 'text', 'building_type' => 'json',
        'about' => 'text', 'images' => 'json', 'amenities' => 'json',
        'banner_image' => 'text', 'published' => 'int',
    ],
]);
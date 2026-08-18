<?php
// api/admin/developers.php — CRUD for the developers table
require_once __DIR__ . '/_crud.php';
admin_crud_dispatch([
    'table' => 'developers',
    'label' => 'name',
    'search' => ['name', 'slug'],
    'cols' => [
        'name' => 'text', 'slug' => 'text', 'region' => 'text', 'founded' => 'int',
        'deliveries' => 'int', 'img' => 'text', 'description' => 'text', 'published' => 'int',
    ],
]);
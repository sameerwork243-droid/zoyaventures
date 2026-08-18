<?php
// api/admin/communities.php — CRUD for the communities table
require_once __DIR__ . '/_crud.php';
admin_crud_dispatch([
    'table' => 'communities',
    'label' => 'name',
    'search' => ['name', 'slug'],
    'cols' => [
        'name' => 'text', 'slug' => 'text', 'region' => 'text', 'published' => 'int',
    ],
]);
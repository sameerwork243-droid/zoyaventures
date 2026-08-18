<?php
// api/admin/agents.php — CRUD for the agents table
require_once __DIR__ . '/_crud.php';
admin_crud_dispatch([
    'table' => 'agents',
    'label' => 'name',
    'search' => ['name', 'slug', 'email'],
    'cols' => [
        'name' => 'text', 'slug' => 'text', 'role' => 'text', 'phone' => 'text',
        'email' => 'text', 'brn_number' => 'text', 'img' => 'text',
        'languages' => 'json', 'specialties' => 'json', 'bio' => 'text', 'published' => 'int',
    ],
]);
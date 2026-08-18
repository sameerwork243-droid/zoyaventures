<?php
// api/admin/testimonials.php — CRUD for the testimonials table
require_once __DIR__ . '/_crud.php';
admin_crud_dispatch([
    'table' => 'testimonials',
    'label' => 'author',
    'search' => ['author'],
    'cols' => [
        'author' => 'text', 'role' => 'text', 'content' => 'text', 'rating' => 'int',
        'img' => 'text', 'published' => 'int',
    ],
]);
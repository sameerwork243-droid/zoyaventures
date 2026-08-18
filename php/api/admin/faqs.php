<?php
// api/admin/faqs.php — CRUD for the faqs table
require_once __DIR__ . '/_crud.php';
admin_crud_dispatch([
    'table' => 'faqs',
    'label' => 'question',
    'search' => ['question', 'category'],
    'cols' => [
        'question' => 'text', 'answer' => 'text', 'category' => 'text',
        'sort' => 'int', 'published' => 'int',
    ],
]);
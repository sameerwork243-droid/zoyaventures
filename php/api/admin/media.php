<?php
// api/admin/media.php — CRUD for the media_library table
require_once __DIR__ . '/_crud.php';
admin_crud_dispatch([
    'table' => 'media_library',
    'label' => 'url',
    'search' => ['url', 'alt'],
    'cols' => [
        'url' => 'text', 'kind' => 'text', 'alt' => 'text',
    ],
]);
<?php
// api/admin/jobs.php — CRUD for the jobs table
require_once __DIR__ . '/_crud.php';
admin_crud_dispatch([
    'table' => 'jobs',
    'label' => 'title',
    'search' => ['title', 'slug', 'location'],
    'cols' => [
        'title' => 'text', 'slug' => 'text', 'location' => 'text', 'summary' => 'text',
        'job_details' => 'text', 'published' => 'int',
    ],
]);
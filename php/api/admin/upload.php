<?php
// api/admin/upload.php — POST multipart (port of src/app/api/admin/upload/route.ts)
// Replaces @vercel/blob with local storage under uploads/.
// TODO(phase10): full port — validation (mime, size), unique filename, media_library row

require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin();
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_response(['error' => 'Method not allowed'], 405);
}

if (empty($_FILES['file'])) {
    json_response(['error' => 'No file uploaded'], 400);
}
$f = $_FILES['file'];
if ($f['error'] !== UPLOAD_ERR_OK) {
    json_response(['error' => 'Upload failed'], 400);
}
if ($f['size'] > 10 * 1024 * 1024) {
    json_response(['error' => 'File too large'], 400);
}

$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/svg+xml' => 'svg', 'image/gif' => 'gif'];
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($f['tmp_name']);
if (!isset($allowed[$mime])) {
    json_response(['error' => 'Unsupported file type'], 400);
}

$name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
$dir = __DIR__ . '/../../uploads/images';
if (!is_dir($dir)) mkdir($dir, 0755, true);
if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) {
    json_response(['error' => 'Could not store file'], 500);
}

$url = '/uploads/images/' . $name;
db_run("INSERT INTO media_library (url, kind, alt, created_at) VALUES (?, 'image', '', ?)", [$url, now_iso()]);

json_response(['url' => $url], 201);
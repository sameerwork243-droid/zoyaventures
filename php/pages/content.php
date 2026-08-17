<?php
// pages/content.php — generic content page (blog, team, area-guides, about, careers, contact, legal...).
// Receives $model from index.php.

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/render/content-pages.php';
require_once __DIR__ . '/../includes/head.php';

$content = $model['data'] ?? [];
$route = $model['route'] ?? '/';
$banner = is_array($content['banner'] ?? null) ? $content['banner'] : [];
$page_title = $banner['title'] ?? $content['page_name'] ?? $content['title'] ?? 'Zoya Ventures Real Estate';
$body = content_pages_dispatch($content, $route);
?><!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<?php require __DIR__ . '/../includes/header.php'; ?>
<main>
<?php echo $body; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php render_site_footer_scripts(); ?>
</body>
</html>
<?php
// projects.php — project hub page (/new-projects/{slug}/). Receives $model from index.php.
// TODO(phase7): project detail from projects-detail data, gallery, amenities, enquiry

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';

$project = $model['data'] ?? [];
$page_title = $project['title'] ?? '';
?><!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<?php require __DIR__ . '/../includes/header.php'; ?>
<main>
  <!-- TODO(phase7): project hub content from $project -->
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php render_site_footer_scripts(); ?>
</body>
</html>
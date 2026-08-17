<?php
// dashboard.php — user portal shell
// TODO(phase8): portal shell markup (portal-shell.tsx port) + dashboard-app.js

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';
$user = require_user();

$page_title = 'Dashboard';
?><!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<?php require __DIR__ . '/../includes/header.php'; ?>
<main>
  <!-- TODO(phase8): portal shell + dashboard content -->
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php render_site_footer_scripts(); ?>
</body>
</html>
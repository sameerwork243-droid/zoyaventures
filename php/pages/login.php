<?php
// login.php — login page
// TODO(phase9): exact port of the login form markup + auth.js wiring

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';
require_guest();

$page_title = 'Login';
?><!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<?php require __DIR__ . '/../includes/header.php'; ?>
<main>
  <!-- TODO(phase9): login form -->
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php render_site_footer_scripts(); ?>
</body>
</html>
<?php
// admin/index.php — admin SPA shell (port of src/app/admin/page.tsx + admin-app.tsx mount)
// TODO(phase10): render the admin shell markup + admin-app.js

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';
require_admin();

$page_title = 'Admin';
?><!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body class="admin-body">
<main id="admin-root">
  <!-- TODO(phase10): admin-app.js renders here (admin-app.tsx port) -->
</main>
<script src="/admin/assets/js/admin-app.js" defer></script>
</body>
</html>
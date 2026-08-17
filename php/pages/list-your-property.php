<?php
// list-your-property.php — List Your Property page (critical form)
// TODO(phase11): exact port of list-property-form.tsx markup (form.custom-form,
//                form-grid, input-box, input-field, phone-field-row, form-bottom)
//                + forms.js validation + POST /api/inquiries.php

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';

$page_title = 'List Your Property';
?><!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<?php require __DIR__ . '/../includes/header.php'; ?>
<main>
  <!-- TODO(phase11): List Your Property form -->
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php render_site_footer_scripts(); ?>
</body>
</html>
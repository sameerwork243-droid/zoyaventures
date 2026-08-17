<?php
// 404.php — not found page
// TODO(phase6+): match the original app's 404 markup exactly (from catch-all page)

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';

http_response_code(404);
$page_title = 'Page Not Found';
?><!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<?php require __DIR__ . '/../includes/header.php'; ?>
<main>
  <section class="section-p">
    <div class="container">
      <h1>Page Not Found</h1>
      <p>The page you are looking for does not exist.</p>
      <a class="button button-orange" href="/"><span>Back to Home</span></a>
    </div>
  </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php render_site_footer_scripts(); ?>
</body>
</html>
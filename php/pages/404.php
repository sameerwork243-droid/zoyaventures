<?php
// 404.php — not found page (port of the Gatsby 404 page markup)

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
  <div class="notfound-page container">
    <h1>Oops! Page Not Found.</h1>
    <p class="description">The page you are looking for cannot be found. Please check the URL or try using our search bar to find what you&rsquo;re looking for.</p>
    <div class="cta-section">
      <a class="button button-orange" href="/"><span>Go to Home</span></a>
      <a class="button button-gray" href="/contact/"><span>Contact Us</span></a>
    </div>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php render_site_footer_scripts(); ?>
</body>
</html>
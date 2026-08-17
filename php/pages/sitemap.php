<?php
// pages/sitemap.php — sitemap (renders on /sitemap)
// TODO(phase11): generate full sitemap.xml-equivalent listing of all routes from data/raw.

require_once __DIR__ . '/../includes/functions.php';
$page_title = 'Sitemap';
$page_description = 'Sitemap of Zoya Ventures Real Estate';
render_head();
render_header();
?>

<section class="section-p">
  <div class="container">
    <h1>Sitemap</h1>
    <p>Sitemap generation is completed in a later phase.</p>
  </div>
</section>

<?php
render_footer();
render_site_footer_scripts();
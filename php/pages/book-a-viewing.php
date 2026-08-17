<?php
// pages/book-a-viewing.php — viewing booking page (renders on /book-a-viewing)
// TODO(phase9): port the full book-a-viewing form + enquiry flow.

require_once __DIR__ . '/../includes/functions.php';
$page_title = 'Book a Viewing';
$page_description = 'Book a property viewing with Zoya Ventures Real Estate';
render_head();
render_header();
?>

<section class="section-p">
  <div class="container">
    <h1>Book a Viewing</h1>
    <p>The viewing booking form is completed in a later phase.</p>
  </div>
</section>

<?php
render_footer();
render_site_footer_scripts();
<?php
// forgot-password.php — password reset page
// Port of src/app/forgot-password/page.tsx (static notice — no self-service reset)

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';
require_guest();

$page_title = 'Reset Password';
?>
<!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<div class="portal-root">
  <header class="portal-appbar">
    <a class="portal-brand" href="/" aria-label="Zoya Ventures Real Estate"><img draggable="false" src="/lloo.png" alt="Zoya Ventures Real Estate" /></a>
    <div class="portal-title">My Account</div>
    <a class="portal-back" href="/">Back to Website</a>
  </header>
  <div class="portal-appbar-spacer"></div>
  <div class="portal-auth-bg" style="background-image:url('/sign_up_bg_0e123241d1.jpg')"></div>
  <div class="portal-auth">
    <div class="portal-auth-inner">
      <div class="portal-auth-card">
        <h1>Reset your password</h1>
        <p class="auth-subtitle">For security reasons, self-service password reset is not available on this demo. Please contact support at support@providentestate.com.</p>
      </div>
    </div>
  </div>
  <footer class="portal-footer">
    <div class="portal-footer-inner">
      <div class="portal-copy">© 2024, Zoya Ventures Real Estate <a href="/privacy-policy/">Privacy Policy</a></div>
      <div class="portal-siteby">Site by <span>Starberry</span></div>
    </div>
  </footer>
</div>
<?php render_site_footer_scripts(); ?>
</body>
</html>

<?php
// register.php — registration page
// Port of src/app/register/page.tsx + components/auth/register-form.tsx + portal/auth-layout.tsx

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';
require_guest();

$page_title = 'Create Account';
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
        <img class="portal-auth-logo" draggable="false" src="/lloo.png" alt="Zoya Ventures Real Estate" />
        <h1>Create your account</h1>
        <p class="auth-subtitle">Join Zoya Ventures Real Estate to save properties, book viewings and track your inquiries in one place.</p>

        <div class="auth-error" id="auth-error" hidden></div>

        <form data-auth-form="register" novalidate>
          <div class="auth-field">
            <label for="reg-name">Full name</label>
            <input id="reg-name" type="text" autocomplete="name" placeholder="John Smith" required />
          </div>
          <div class="auth-field">
            <label for="reg-email">Email address</label>
            <input id="reg-email" type="email" autocomplete="email" placeholder="you@example.com" required />
          </div>
          <div class="auth-field">
            <label for="reg-phone">Phone (optional)</label>
            <input id="reg-phone" type="tel" autocomplete="tel" placeholder="+971 50 000 0000" />
          </div>
          <div class="auth-field">
            <label for="reg-password">Password</label>
            <input id="reg-password" type="password" autocomplete="new-password" placeholder="At least 8 characters with letters and numbers" required />
          </div>
          <div class="auth-field">
            <label for="reg-confirm">Confirm password</label>
            <input id="reg-confirm" type="password" autocomplete="new-password" placeholder="Repeat your password" required />
          </div>
          <button class="portal-btn block" type="submit">Create Account</button>
        </form>

        <p class="auth-terms">By creating an account you agree to Zoya Ventures Real Estate&apos;s terms of use and privacy policy.</p>
        <p class="auth-alt">Already have an account? <a href="/login/">Sign in</a></p>
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
<script src="/assets/js/auth.js" defer></script>
</body>
</html>

<?php
// login.php — login page
// Port of src/app/login/page.tsx + components/auth/login-form.tsx + portal/auth-layout.tsx (footerBare)

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';
require_guest();

$page_title = 'Sign In';
?>
<!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<div class="portal-root portal-auth-nofoot">
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
        <h1>Login to your account</h1>
        <p class="auth-subtitle">Welcome back. Enter your details to access your account.</p>

        <div class="auth-error" id="auth-error" hidden></div>

        <form data-auth-form="login" novalidate>
          <div class="auth-field">
            <label for="auth-email">Email Address *</label>
            <input id="auth-email" type="email" autocomplete="email" placeholder="you@example.com" required />
          </div>
          <div class="auth-field">
            <label for="auth-password">Password *</label>
            <input id="auth-password" type="password" autocomplete="current-password" placeholder="••••••••" required />
          </div>
          <div class="auth-row">
            <span></span>
            <a href="/forgot-password/">Forgot your password?</a>
          </div>
          <p class="auth-terms">By clicking &quot;Continue&quot; you agree to our <a href="/privacy-policy/">Privacy Policy</a>.</p>
          <button class="portal-btn block" type="submit">Continue</button>
        </form>

        <p class="auth-alt">Don&apos;t have an account yet? <a href="/register/">Sign Up</a></p>
      </div>
    </div>
  </div>
</div>
<?php render_site_footer_scripts(); ?>
<script src="/assets/js/auth.js" defer></script>
</body>
</html>

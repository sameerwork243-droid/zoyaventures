<?php
// dashboard.php — user portal (port of portal-shell.tsx + dashboard-app.tsx)
// Interactivity wired by assets/js/dashboard-app.js.

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';
$user = require_user();
if (in_array($user['role'] ?? '', ['admin', 'agent'], true)) {
    header('Location: /admin/', true, 302);
    exit;
}

$page_title = 'Dashboard';

$firstName = trim((string) ($user['first_name'] ?? ''));
$surname = trim((string) ($user['surname'] ?? ''));
$fullName = $firstName !== '' && $surname !== '' ? $firstName . ' ' . $surname : (string) ($user['name'] ?? $user['email'] ?? '');
$welcome = $firstName !== '' ? $firstName : explode(' ', (string) ($user['name'] ?? $user['email'] ?? ''))[0];

$initials = implode('', array_slice(array_filter(array_map(fn ($p) => mb_substr($p, 0, 1), explode(' ', $fullName))), 0, 2));

$COUNTRIES = [
    ['AE', 'United Arab Emirates', '+971', "\u{1F1E6}\u{1F1EA}"],
    ['GB', 'United Kingdom', '+44', "\u{1F1EC}\u{1F1E7}"],
    ['US', 'United States', '+1', "\u{1F1FA}\u{1F1F8}"],
    ['IN', 'India', '+91', "\u{1F1EE}\u{1F1F3}"],
    ['PK', 'Pakistan', '+92', "\u{1F1F5}\u{1F1F0}"],
    ['SA', 'Saudi Arabia', '+966', "\u{1F1F8}\u{1F1E6}"],
    ['EG', 'Egypt', '+20', "\u{1F1EA}\u{1F1EC}"],
    ['PH', 'Philippines', '+63', "\u{1F1F5}\u{1F1ED}"],
    ['BD', 'Bangladesh', '+880', "\u{1F1E7}\u{1F1E9}"],
    ['LK', 'Sri Lanka', '+94', "\u{1F1F1}\u{1F1F0}"],
    ['JO', 'Jordan', '+962', "\u{1F1EF}\u{1F1F4}"],
    ['LB', 'Lebanon', '+961', "\u{1F1F1}\u{1F1E7}"],
    ['IQ', 'Iraq', '+964', "\u{1F1EE}\u{1F1F6}"],
    ['IR', 'Iran', '+98', "\u{1F1EE}\u{1F1F7}"],
    ['OM', 'Oman', '+968', "\u{1F1F4}\u{1F1F2}"],
    ['QA', 'Qatar', '+974', "\u{1F1F6}\u{1F1E6}"],
    ['KW', 'Kuwait', '+965', "\u{1F1F0}\u{1F1FC}"],
    ['BH', 'Bahrain', '+973', "\u{1F1E7}\u{1F1ED}"],
];
$countryOptions = '';
foreach ($COUNTRIES as $c) {
    $countryOptions .= '<option value="' . esc($c[0]) . '">' . $c[3] . ' ' . esc($c[2]) . '</option>';
}

$icon = fn (string $path) => '<svg viewBox="0 0 24 24" fill="currentColor"><path d="' . $path . '"/></svg>';
$ic = [
    'launch' => $icon('M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z'),
    'home' => $icon('M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z'),
    'person' => $icon('M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z'),
    'logout' => $icon('M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9'),
    'menu' => $icon('M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z'),
    'expand-more' => $icon('M16.59 8.59 12 13.17 7.41 8.59 6 10l6 6 6-6z'),
    'search' => $icon('M15.5 14h-.79l-.28-.27a6.47 6.47 0 0 0 1.52-4.23C15.95 6.57 13.38 4 10.24 4 7.11 4 4.55 6.57 4.55 9.5S7.11 15 10.24 15c1.61 0 3.1-.58 4.23-1.57l.27.28v.79l5 5L20.49 19l-4.99-5zm-5.26 0C8.07 14 6.11 12.03 6.11 9.5S8.07 5 10.24 5s4.13 1.97 4.13 4.5-1.96 4.5-4.13 4.5z'),
    'bookmark' => $icon('M17 3H7a2 2 0 0 0-2 2v16l7-3 7 3V5a2 2 0 0 0-2-2z'),
    'eye' => $icon('M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z'),
    'eye-off' => $icon('M17.94 17.94A9.99 9.99 0 0 1 12 20c-5.52 0-10-4.48-10-10 0-1.1.22-2.15.6-3.12l1.46 1.46C2.67 10.25 2 11.15 2 12c0 5.52 4.48 10 10 10 1.1 0 2.15-.22 3.12-.6l1.46 1.46c-.97.53-2.03.9-3.12.9zM13.06 8.06L15.12 10.12C15.32 10.32 15.5 10.56 15.5 10.83c0 1.66-1.34 3-3 3-.27 0-.53-.04-.78-.12L10.94 12.94C11.14 12.74 11.32 12.5 11.32 12.17c0-1.66 1.34-3 3-3 .27 0 .53.04.78.12zM1.1 10.06l1.46-1.46C2.67 7.75 2 8.65 2 9.5c0 1.1.22 2.15.6 3.12L1.1 10.06zM12 7c-2.76 0-5 2.24-5 5 0 1.1.22 2.15.6 3.12l1.46-1.46C8.67 12.25 8 11.35 8 10.5c0-1.1-.22-2.15-.6-3.12L7.06 7.06C7.4 6.7 7.73 6.5 8 6.5c1.66 0 3 1.34 3 3 0 .27.04.53.12.78l1.46-1.46C12.33 8.75 12 8.15 12 7.5c0-.27-.04-.53-.12-.78L13.06 8.06z'),
];
?>
<!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<div class="shell-root">
  <aside class="shell-sidebar" data-portal-sidebar>
    <div class="shell-brand">
      <a href="/" aria-label="Zoya Ventures Real Estate">
        <img draggable="false" src="/lloo.png" alt="Zoya Ventures Real Estate" />
      </a>
    </div>
    <nav class="shell-nav">
      <div class="shell-nav-group">
        <a href="/" class="shell-nav-item">
          <span class="shell-nav-icon"><?php echo $ic['launch']; ?></span>
          <span class="shell-nav-label">Back to Website</span>
        </a>
      </div>
      <div class="shell-nav-group">
        <div class="shell-nav-section">Dashboard</div>
        <button type="button" class="shell-nav-item active" data-portal-tab="overview">
          <span class="shell-nav-icon"><?php echo $ic['home']; ?></span>
          <span class="shell-nav-label">Dashboard</span>
        </button>
      </div>
      <div class="shell-nav-group">
        <div class="shell-nav-section">Account</div>
        <button type="button" class="shell-nav-item" data-portal-tab="settings">
          <span class="shell-nav-icon"><?php echo $ic['person']; ?></span>
          <span class="shell-nav-label">Profile</span>
        </button>
      </div>
    </nav>
    <div class="shell-sidebar-foot">
      <button type="button" class="shell-nav-item logout" data-logout>
        <span class="shell-nav-icon"><?php echo $ic['logout']; ?></span>
        <span class="shell-nav-label">Log out</span>
      </button>
    </div>
  </aside>
  <div class="shell-backdrop" data-portal-backdrop></div>

  <div class="shell-body">
    <header class="shell-topbar">
      <button type="button" class="shell-burger" aria-label="Open menu" data-portal-burger>
        <?php echo $ic['menu']; ?>
      </button>
      <div class="shell-topbar-title">Dashboard</div>
      <div class="shell-user-menu">
        <button type="button" class="shell-user" aria-expanded="false" data-user-menu>
          <span class="shell-user-avatar"><?php echo esc($initials); ?></span>
          <span class="shell-user-name"><?php echo esc($fullName); ?></span>
          <span class="shell-user-caret"><?php echo $ic['expand-more']; ?></span>
        </button>
        <div class="shell-user-dropdown" data-user-dropdown hidden>
          <button type="button" class="shell-user-option" data-portal-tab="settings">Profile</button>
          <button type="button" class="shell-user-option danger" data-logout>Logout</button>
        </div>
      </div>
    </header>
    <main class="shell-main">
      <div class="shell-container">

        <div data-portal-panel="overview">
          <div class="myprop-head">
            <div class="myprop-title">My Property</div>
            <div class="myprop-sub">View and manage the properties you have shortlisted.</div>
          </div>
          <div class="myprop-tabs">
            <button type="button" class="myprop-tab active" data-myprop-view="wishlist">Wishlist</button>
            <button type="button" class="myprop-tab" data-myprop-view="searches">Saved Searches</button>
          </div>
          <div data-myprop-panel="wishlist">
            <div data-saved-render>
              <div class="app-card"><p class="app-empty">Loading&hellip;</p></div>
            </div>
          </div>
          <div data-myprop-panel="searches" hidden>
            <div class="myprop-card">
              <span class="myprop-card-icon"><?php echo $ic['bookmark']; ?></span>
              <div class="myprop-card-body">
                <h3>No saved searches yet</h3>
                <p>Save a search to be notified as soon as matching properties are listed.</p>
              </div>
              <a class="myprop-cta" href="/">Search &amp; Save</a>
            </div>
          </div>
          <div class="app-toast" data-toast hidden></div>
        </div>

        <div data-portal-panel="settings" hidden>
          <div class="profile-page">
            <div class="profile-header">
              <h1 class="profile-title">My Account</h1>
              <p class="profile-subtitle">Manage your personal details, preferences, and account security.</p>
            </div>
            <div class="profile-tabs">
              <button type="button" class="profile-tab active" data-profile-tab="personal">Personal Details</button>
              <button type="button" class="profile-tab" data-profile-tab="notifications">Notification Preferences</button>
              <button type="button" class="profile-tab" data-profile-tab="password">Change Password</button>
              <button type="button" class="profile-tab" data-profile-tab="delete">Delete Account</button>
            </div>
            <div class="app-toast" data-profile-toast hidden></div>

            <div data-profile-panel="personal" class="profile-card">
              <div class="profile-card-head">
                <div>
                  <h2>Personal Details</h2>
                  <p class="app-card-sub">Update your personal information.</p>
                </div>
              </div>
              <form class="app-form-grid" data-profile-form novalidate>
                <div class="app-field full">
                  <label>Welcome <?php echo esc($welcome); ?></label>
                </div>
                <div class="profile-section-title">Personal Information</div>
                <div class="app-field">
                  <label>First Name <span class="required-indicator">*</span></label>
                  <input name="first_name" required />
                </div>
                <div class="app-field">
                  <label>Surname <span class="required-indicator">*</span></label>
                  <input name="surname" required />
                </div>
                <div class="app-field">
                  <label>Email <span class="required-indicator">*</span></label>
                  <input name="email" type="email" required />
                </div>
                <div class="app-field full">
                  <label>Mobile Number <span class="required-indicator">*</span></label>
                  <div class="phone-input">
                    <select class="country-select" data-country-select><?php echo $countryOptions; ?></select>
                    <input type="tel" name="phone" placeholder="Phone number" />
                  </div>
                </div>
                <div class="profile-section-title">Address</div>
                <div class="app-field full">
                  <label>Address Line 1</label>
                  <input name="address_line1" />
                </div>
                <div class="app-field full">
                  <label>Address Line 2</label>
                  <input name="address_line2" />
                </div>
                <div class="app-field">
                  <label>Town / City</label>
                  <input name="town_city" />
                </div>
                <div class="app-field">
                  <label>Postcode</label>
                  <input name="postcode" />
                </div>
                <div class="app-field">
                  <label>Country</label>
                  <input name="address_country" />
                </div>
                <div class="full">
                  <button type="submit" class="app-btn">Submit</button>
                </div>
              </form>
            </div>

            <div data-profile-panel="notifications" class="profile-card" hidden>
              <div class="profile-card-head">
                <div>
                  <h2>Notification Preferences</h2>
                  <p class="app-card-sub">Manage how we communicate with you.</p>
                </div>
              </div>
              <form class="app-form-grid" data-prefs-form novalidate>
                <div class="app-field full">
                  <div class="toggle-field">
                    <label>Subscribe to news and updates</label>
                    <button type="button" class="toggle-switch" role="switch" aria-checked="true" data-toggle="subscribe_news"><span class="toggle-slider"></span></button>
                  </div>
                </div>
                <div class="app-field full">
                  <div class="toggle-field">
                    <label>Receive email notifications</label>
                    <button type="button" class="toggle-switch" role="switch" aria-checked="true" data-toggle="email_notifications"><span class="toggle-slider"></span></button>
                  </div>
                </div>
                <div class="app-field full">
                  <div class="toggle-field">
                    <label>Receive property alerts</label>
                    <button type="button" class="toggle-switch" role="switch" aria-checked="true" data-toggle="property_alerts"><span class="toggle-slider"></span></button>
                  </div>
                </div>
                <div class="full">
                  <button type="submit" class="app-btn">Save Preferences</button>
                </div>
              </form>
            </div>

            <div data-profile-panel="password" class="profile-card" hidden>
              <div class="profile-card-head">
                <div>
                  <h2>Change Password</h2>
                  <p class="app-card-sub">Update your password for security.</p>
                </div>
              </div>
              <form class="app-form-grid" data-password-form novalidate>
                <div class="app-field full">
                  <label>New Password</label>
                  <div class="password-input">
                    <input type="password" name="new_password" data-pw-new required />
                    <button type="button" class="password-toggle" data-pw-toggle><?php echo $ic['eye']; ?></button>
                  </div>
                  <div class="password-strength">
                    <div class="strength-meter">
                      <?php for ($i = 1; $i <= 5; $i++) echo '<div class="strength-bar" data-bar="' . $i . '"></div>'; ?>
                    </div>
                    <span class="strength-text" data-strength-text>Weak</span>
                  </div>
                </div>
                <div class="app-field full">
                  <label>Confirm Password</label>
                  <div class="password-input">
                    <input type="password" name="confirm_password" data-pw-confirm required />
                    <button type="button" class="password-toggle" data-pw-toggle><?php echo $ic['eye']; ?></button>
                  </div>
                </div>
                <div class="full">
                  <button type="submit" class="app-btn">Change Password</button>
                </div>
              </form>
            </div>

            <div data-profile-panel="delete" class="profile-card" hidden>
              <div class="profile-card-head">
                <div>
                  <h2>Delete Account</h2>
                  <p class="app-card-sub">Permanently remove your account and data.</p>
                </div>
              </div>
              <form class="app-form-grid" data-delete-form novalidate>
                <div class="app-field full">
                  <h3 style="color:#b3261e;font-size:16px;margin-bottom:8px">Deleting Account</h3>
                  <p style="color:#666;font-size:13px;line-height:1.5">Deleting your account will permanently remove all your information from our database. This action cannot be undone.</p>
                </div>
                <div class="app-field full">
                  <label>Why are you deleting your account?</label>
                  <textarea name="reason" rows="4" placeholder="We value your feedback. Please let us know why you&rsquo;re leaving."></textarea>
                </div>
                <div class="full">
                  <button type="submit" class="app-btn danger">Delete Account</button>
                </div>
              </form>
            </div>
          </div>
        </div>

      </div>
    </main>
    <footer class="portal-footer">
      <div class="portal-footer-inner">
        <div class="portal-copy">
          &copy; 2024, Zoya Ventures Real Estate
          <a href="/privacy-policy/">Privacy Policy</a>
        </div>
        <div class="portal-siteby">Site by <span>Starberry</span></div>
      </div>
    </footer>
  </div>
</div>
<?php render_site_footer_scripts(); ?>
<script src="/assets/js/dashboard-app.js" defer></script>
</body>
</html>
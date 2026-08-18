<?php
// admin/index.php — admin panel (port of src/app/admin/page.tsx + admin-app.tsx + portal-shell.tsx)
// Static shell + embedded field/column schemas; behavior in /admin/assets/js/admin-app.js

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';
$user = require_admin();

$page_title = 'Admin Panel';

$fullName = trim((string) ($user['name'] ?? $user['email'] ?? ''));
$initials = implode('', array_slice(array_filter(array_map(fn ($p) => mb_substr($p, 0, 1), explode(' ', $fullName))), 0, 2));

$ic = [
    'launch' => 'M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z',
    'home' => 'M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z',
    'building' => 'M3 21h18v-2H3v2zM7 5h2v2H7V5zm0 4h2v2H7V9zm0 4h2v2H7v-2zm0 4h2v2H7v-2zM13 5h2v2h-2V5zm0 4h2v2h-2V9zm0 4h2v2h-2v-2zm0 4h2v2h-2v-2z',
    'briefcase' => 'M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-2 .89-2 2v11c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z',
    'chat' => 'M20 2H4c-1.1 0-1.99.9-1.99 2L2 22l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zM6 9h12v2H6V9zm8 5H6v-2h8v2zm4-6H6V6h12v2z',
    'calendar' => 'M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2h-1V1h-2zm3 18H5V8h14v11z',
    'users' => 'M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z',
    'person' => 'M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z',
    'map' => 'M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z',
    'tag' => 'M21.41 11.58l-9-9C12.05 2.22 11.55 2 11 2H4c-1.1 0-2 .9-2 2v7c0 .55.22 1.05.59 1.42l9 9c.36.36.86.58 1.41.58.55 0 1.05-.22 1.41-.59l7-7c.37-.36.59-.86.59-1.41 0-.55-.23-1.06-.59-1.42zM5.5 7C4.67 7 4 6.33 4 5.5S4.67 4 5.5 4 7 4.67 7 5.5 6.33 7 5.5 7z',
    'star' => 'M12 17.27 18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z',
    'question' => 'M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z',
    'image' => 'M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z',
    'grid' => 'M3 5h6v11H3V5zm14 0h4v11h-4V5zm-7 0h4v5h-4V5zm0 6h4v5h-4v-5z',
    'menu' => 'M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z',
    'expand-more' => 'M16.59 8.59 12 13.17 7.41 8.59 6 10l6 6 6-6z',
    'logout' => 'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9',
];

/* ------------------------------ field schemas ------------------------------ */

$F = [
    'projects' => [
        ['key' => 'title', 'label' => 'Project name', 'required' => true, 'full' => true],
        ['key' => 'slug', 'label' => 'Slug', 'required' => true, 'full' => true],
        ['key' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['ready', 'pending', 'under_construction', 'future_launch', 'off-plan']],
        ['key' => 'price', 'label' => 'Price (AED)', 'type' => 'number'],
        ['key' => 'currency', 'label' => 'Currency'],
        ['key' => 'bedrooms_min', 'label' => 'Bedrooms (min)', 'type' => 'number'],
        ['key' => 'bedrooms_max', 'label' => 'Bedrooms (max)', 'type' => 'number'],
        ['key' => 'completion_year', 'label' => 'Completion year', 'type' => 'number'],
        ['key' => 'community', 'label' => 'Community', 'full' => true],
        ['key' => 'developer', 'label' => 'Developer', 'full' => true],
        ['key' => 'department', 'label' => 'Department'],
        ['key' => 'display_address', 'label' => 'Display address', 'full' => true],
        ['key' => 'building_type', 'label' => 'Building types (comma-separated)', 'type' => 'json', 'full' => true],
        ['key' => 'about', 'label' => 'About (HTML)', 'type' => 'textarea', 'full' => true],
        ['key' => 'images', 'label' => 'Image URLs (comma-separated)', 'type' => 'json', 'full' => true],
        ['key' => 'amenities', 'label' => 'Amenities (comma-separated)', 'type' => 'json', 'full' => true],
        ['key' => 'banner_image', 'label' => 'Banner image URL', 'full' => true],
        ['key' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'hint' => 'Visible on the public site'],
    ],
    'services' => [
        ['key' => 'title', 'label' => 'Title', 'required' => true, 'full' => true],
        ['key' => 'slug', 'label' => 'Slug', 'required' => true, 'full' => true],
        ['key' => 'icon', 'label' => 'Icon URL', 'full' => true],
        ['key' => 'banner_image', 'label' => 'Banner image URL', 'full' => true],
        ['key' => 'description', 'label' => 'Description', 'type' => 'textarea', 'full' => true],
        ['key' => 'rich_content', 'label' => 'Rich content (HTML)', 'type' => 'textarea', 'full' => true],
        ['key' => 'gallery', 'label' => 'Gallery URLs', 'type' => 'json', 'full' => true],
        ['key' => 'seo_title', 'label' => 'SEO title', 'full' => true],
        ['key' => 'seo_description', 'label' => 'SEO description', 'full' => true],
        ['key' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'hint' => 'Visible on the public site'],
    ],
    'agents' => [
        ['key' => 'name', 'label' => 'Name', 'required' => true, 'full' => true],
        ['key' => 'slug', 'label' => 'Slug', 'required' => true, 'full' => true],
        ['key' => 'role', 'label' => 'Role'],
        ['key' => 'phone', 'label' => 'Phone'],
        ['key' => 'email', 'label' => 'Email', 'full' => true],
        ['key' => 'brn_number', 'label' => 'BRN number'],
        ['key' => 'img', 'label' => 'Profile image URL', 'full' => true],
        ['key' => 'languages', 'label' => 'Languages', 'type' => 'json', 'full' => true],
        ['key' => 'specialties', 'label' => 'Specialties', 'type' => 'json', 'full' => true],
        ['key' => 'bio', 'label' => 'Bio', 'type' => 'textarea', 'full' => true],
        ['key' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'hint' => 'Visible on the team page'],
    ],
    'developers' => [
        ['key' => 'name', 'label' => 'Name', 'required' => true, 'full' => true],
        ['key' => 'slug', 'label' => 'Slug', 'required' => true, 'full' => true],
        ['key' => 'region', 'label' => 'Region'],
        ['key' => 'founded', 'label' => 'Founded', 'type' => 'number'],
        ['key' => 'deliveries', 'label' => 'Deliveries', 'type' => 'number'],
        ['key' => 'img', 'label' => 'Logo URL', 'full' => true],
        ['key' => 'description', 'label' => 'Description', 'type' => 'textarea', 'full' => true],
        ['key' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'hint' => 'Visible on the public site'],
    ],
    'communities' => [
        ['key' => 'name', 'label' => 'Name', 'required' => true, 'full' => true],
        ['key' => 'slug', 'label' => 'Slug', 'required' => true, 'full' => true],
        ['key' => 'region', 'label' => 'Region', 'full' => true],
        ['key' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'hint' => 'Visible on the public site'],
    ],
    'testimonials' => [
        ['key' => 'author', 'label' => 'Author', 'required' => true, 'full' => true],
        ['key' => 'role', 'label' => 'Role', 'full' => true],
        ['key' => 'content', 'label' => 'Content', 'type' => 'textarea', 'full' => true],
        ['key' => 'rating', 'label' => 'Rating (1-5)', 'type' => 'number'],
        ['key' => 'img', 'label' => 'Photo URL', 'full' => true],
        ['key' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'hint' => 'Visible on the public site'],
    ],
    'faqs' => [
        ['key' => 'question', 'label' => 'Question', 'required' => true, 'full' => true],
        ['key' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'full' => true],
        ['key' => 'category', 'label' => 'Category'],
        ['key' => 'sort', 'label' => 'Sort order', 'type' => 'number'],
        ['key' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'hint' => 'Visible on the public site'],
    ],
    'media' => [
        ['key' => 'url', 'label' => 'URL', 'required' => true, 'full' => true],
        ['key' => 'kind', 'label' => 'Kind', 'type' => 'select', 'options' => ['image', 'video', 'floorplan', 'brochure']],
        ['key' => 'alt', 'label' => 'Alt text', 'full' => true],
    ],
    'jobs' => [
        ['key' => 'title', 'label' => 'Job title', 'required' => true, 'full' => true],
        ['key' => 'slug', 'label' => 'Slug', 'required' => true, 'full' => true],
        ['key' => 'location', 'label' => 'Location'],
        ['key' => 'summary', 'label' => 'Summary', 'type' => 'textarea', 'full' => true],
        ['key' => 'job_details', 'label' => 'Full details (HTML)', 'type' => 'textarea', 'full' => true],
        ['key' => 'published', 'label' => 'Published', 'type' => 'checkbox', 'hint' => 'Visible on the careers page'],
    ],
];

$C = [
    'projects' => [
        ['label' => 'Project', 'kind' => 'strong', 'key' => 'title'],
        ['label' => 'Developer', 'kind' => 'dash', 'key' => 'developer'],
        ['label' => 'Price', 'kind' => 'price', 'key' => 'price'],
        ['label' => 'Status', 'kind' => 'badge', 'key' => 'status'],
        ['label' => 'Published', 'kind' => 'bool', 'key' => 'published'],
    ],
    'services' => [
        ['label' => 'Service', 'kind' => 'strong', 'key' => 'title'],
        ['label' => 'Slug', 'kind' => 'muted', 'key' => 'slug'],
        ['label' => 'Published', 'kind' => 'bool', 'key' => 'published'],
    ],
    'agents' => [
        ['label' => 'Agent', 'kind' => 'strong', 'key' => 'name'],
        ['label' => 'Role', 'kind' => 'text', 'key' => 'role'],
        ['label' => 'Email', 'kind' => 'small', 'key' => 'email'],
        ['label' => 'Published', 'kind' => 'bool', 'key' => 'published'],
    ],
    'developers' => [
        ['label' => 'Developer', 'kind' => 'strong', 'key' => 'name'],
        ['label' => 'Region', 'kind' => 'text', 'key' => 'region'],
        ['label' => 'Founded', 'kind' => 'dash', 'key' => 'founded'],
        ['label' => 'Published', 'kind' => 'bool', 'key' => 'published'],
    ],
    'communities' => [
        ['label' => 'Community', 'kind' => 'strong', 'key' => 'name'],
        ['label' => 'Region', 'kind' => 'text', 'key' => 'region'],
        ['label' => 'Published', 'kind' => 'bool', 'key' => 'published'],
    ],
    'testimonials' => [
        ['label' => 'Author', 'kind' => 'strong', 'key' => 'author'],
        ['label' => 'Role', 'kind' => 'text', 'key' => 'role'],
        ['label' => 'Rating', 'kind' => 'stars', 'key' => 'rating'],
        ['label' => 'Published', 'kind' => 'bool', 'key' => 'published'],
    ],
    'faqs' => [
        ['label' => 'Question', 'kind' => 'strong', 'key' => 'question'],
        ['label' => 'Category', 'kind' => 'text', 'key' => 'category'],
        ['label' => 'Sort', 'kind' => 'text', 'key' => 'sort'],
        ['label' => 'Published', 'kind' => 'bool', 'key' => 'published'],
    ],
    'media' => [
        ['label' => 'URL', 'kind' => 'breakall', 'key' => 'url'],
        ['label' => 'Kind', 'kind' => 'badge', 'key' => 'kind'],
        ['label' => 'Alt', 'kind' => 'dash', 'key' => 'alt'],
    ],
    'jobs' => [
        ['label' => 'Job', 'kind' => 'strong', 'key' => 'title'],
        ['label' => 'Location', 'kind' => 'dash', 'key' => 'location'],
        ['label' => 'Published', 'kind' => 'bool', 'key' => 'published'],
    ],
];

$boot = [
    'user' => [
        'id' => $user['id'], 'email' => $user['email'], 'name' => $fullName,
        'phone' => $user['phone'] ?? '', 'avatar' => $user['avatar'] ?? '', 'role' => $user['role'],
    ],
    'schemas' => ['fields' => $F, 'columns' => $C],
];

$nav = [
    ['label' => null, 'items' => [['key' => 'back', 'label' => 'Back to Website', 'icon' => 'launch', 'href' => '/']]],
    ['label' => 'Main', 'items' => [
        ['key' => 'overview', 'label' => 'Dashboard', 'icon' => 'home'],
        ['key' => 'properties', 'label' => 'Properties', 'icon' => 'building'],
        ['key' => 'projects', 'label' => 'Projects', 'icon' => 'building'],
        ['key' => 'services', 'label' => 'Services', 'icon' => 'briefcase'],
    ]],
    ['label' => 'CRM', 'items' => [
        ['key' => 'inquiries', 'label' => 'Inquiries', 'icon' => 'chat'],
        ['key' => 'viewings', 'label' => 'Viewings', 'icon' => 'calendar'],
        ['key' => 'listings', 'label' => 'Listings', 'icon' => 'building'],
        ['key' => 'users', 'label' => 'Users', 'icon' => 'users'],
        ['key' => 'agents', 'label' => 'Agents', 'icon' => 'person'],
    ]],
    ['label' => 'Directory', 'items' => [
        ['key' => 'developers', 'label' => 'Developers', 'icon' => 'building'],
        ['key' => 'communities', 'label' => 'Areas', 'icon' => 'map'],
        ['key' => 'categories', 'label' => 'Categories', 'icon' => 'tag'],
    ]],
    ['label' => 'Content', 'items' => [
        ['key' => 'testimonials', 'label' => 'Testimonials', 'icon' => 'star'],
        ['key' => 'faqs', 'label' => 'FAQs', 'icon' => 'question'],
        ['key' => 'media', 'label' => 'Blogs', 'icon' => 'image'],
        ['key' => 'homepage', 'label' => 'Homepage Content', 'icon' => 'grid'],
        ['key' => 'more', 'label' => 'More', 'icon' => 'menu'],
    ]],
];

$navHtml = '';
foreach ($nav as $group) {
    $navHtml .= '<div class="shell-nav-group" data-nav-group="' . ($group['label'] ? esc($group['label']) : 'nav') . '">';
    if ($group['label']) $navHtml .= '<div class="shell-nav-section">' . esc($group['label']) . '</div>';
    foreach ($group['items'] as $it) {
        $attrs = 'data-admin-tab="' . esc($it['key']) . '"';
        if (isset($it['href'])) {
            $navHtml .= '<a href="' . esc($it['href']) . '" class="shell-nav-item">'
                . '<span class="shell-nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="' . $ic[$it['icon']] . '"/></svg></span>'
                . '<span class="shell-nav-label">' . esc($it['label']) . '</span></a>';
        } else {
            $navHtml .= '<button type="button" class="shell-nav-item' . ($it['key'] === 'overview' ? ' active' : '') . '" ' . $attrs . '>'
                . '<span class="shell-nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="' . $ic[$it['icon']] . '"/></svg></span>'
                . '<span class="shell-nav-label">' . esc($it['label']) . '</span></button>';
        }
    }
    $navHtml .= '</div>';
}

$panel = function (string $key, string $inner, bool $hidden = false): string {
    return '<div data-admin-panel="' . esc($key) . '"' . ($hidden ? ' hidden' : '') . '>' . $inner . '</div>';
};
$loadingCard = '<div class="app-card"><p class="app-empty">Loading&hellip;</p></div>';
?>
<!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body class="admin-body">
<div class="shell-root">
  <aside class="shell-sidebar" data-portal-sidebar>
    <div class="shell-brand">
      <a href="/" aria-label="Zoya Ventures Real Estate">
        <img draggable="false" src="/lloo.png" alt="Zoya Ventures Real Estate" />
      </a>
    </div>
    <nav class="shell-nav"><?php echo $navHtml; ?></nav>
    <div class="shell-sidebar-foot">
      <button type="button" class="shell-nav-item logout" data-logout>
        <span class="shell-nav-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="<?php echo $ic['logout']; ?>"/></svg></span>
        <span class="shell-nav-label">Log out</span>
      </button>
    </div>
  </aside>
  <div class="shell-backdrop" data-portal-backdrop></div>

  <div class="shell-body">
    <header class="shell-topbar">
      <button type="button" class="shell-burger" aria-label="Open menu" data-portal-burger>
        <svg viewBox="0 0 24 24" fill="currentColor"><path d="<?php echo $ic['menu']; ?>"/></svg>
      </button>
      <div class="shell-topbar-title">Admin Panel</div>
      <div class="shell-user-menu">
        <button type="button" class="shell-user" aria-expanded="false" data-user-menu>
          <span class="shell-user-avatar"><?php echo esc($initials); ?></span>
          <span class="shell-user-name"><?php echo esc($fullName); ?></span>
          <span class="shell-user-caret"><svg viewBox="0 0 24 24" fill="currentColor"><path d="<?php echo $ic['expand-more']; ?>"/></svg></span>
        </button>
        <div class="shell-user-dropdown" data-user-dropdown hidden>
          <button type="button" class="shell-user-option" data-profile-link>Profile</button>
          <button type="button" class="shell-user-option danger" data-logout>Logout</button>
        </div>
      </div>
    </header>
    <main class="shell-main">
      <div class="shell-container">

        <?php echo $panel('overview', $loadingCard); ?>
        <?php echo $panel('properties', $loadingCard); ?>
        <?php echo $panel('projects', $loadingCard); ?>
        <?php echo $panel('services', $loadingCard); ?>
        <?php echo $panel('inquiries', $loadingCard); ?>
        <?php echo $panel('viewings', $loadingCard); ?>
        <?php echo $panel('listings', $loadingCard); ?>
        <?php echo $panel('users', $loadingCard); ?>
        <?php echo $panel('agents', $loadingCard); ?>
        <?php echo $panel('developers', $loadingCard); ?>
        <?php echo $panel('communities', $loadingCard); ?>
        <?php echo $panel('categories', $loadingCard); ?>
        <?php echo $panel('testimonials', $loadingCard); ?>
        <?php echo $panel('faqs', $loadingCard); ?>
        <?php echo $panel('media', $loadingCard); ?>
        <?php echo $panel('homepage', $loadingCard); ?>
        <?php echo $panel('more', $loadingCard); ?>

        <div class="app-toast" data-toast hidden></div>
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
<div data-modal-root></div>
<script>window.ADMIN_BOOT = <?php echo json_encode($boot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;</script>
<script src="/admin/assets/js/admin-app.js" defer></script>
</body>
</html>
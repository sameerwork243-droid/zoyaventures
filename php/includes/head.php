<?php
// head.php — <head> block (port of src/app/layout.tsx metadata + GTM snippet)
// Expects: $page_title (string), $page_description (string), $canonical (string, optional),
//          $og_image (string, optional), $body_class (string, optional)
// Global variables used by pages. Call render_head() inside <head>.

require_once __DIR__ . '/functions.php';

$page_title = $page_title ?? '';
$page_description = $page_description ?? 'Your one-stop for all real estate services, including selling, renting, snagging, conveyancing, mortgages, property management, & expert property consultants.';
$canonical = $canonical ?? '';
$og_image = $og_image ?? 'https://www.providentestate.com/icons/icon-512x512.png';

function render_head(): void
{
    global $page_title, $page_description, $canonical, $og_image;

    $title = $page_title !== ''
        ? $page_title . ' | Zoya Ventures Real Estate'
        : 'Leading Real Estate Agency in Dubai, UAE | Zoya Ventures Real Estate';

    $canonical = $canonical !== '' ? $canonical : (($_SERVER['HTTP_HOST'] ?? '') . ($_SERVER['REQUEST_URI'] ?? '/'));
    ?>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= esc($title) ?></title>
<meta name="description" content="<?= esc($page_description) ?>">
<meta name="robots" content="index, follow">
<link rel="icon" href="/favicon-32x32.png">
<link rel="canonical" href="<?= esc($canonical) ?>">
<meta property="og:title" content="Leading Real Estate Agency in Dubai, UAE">
<meta property="og:site_name" content="Zoya Ventures Real Estate">
<meta property="og:image" content="<?= esc($og_image) ?>">
<meta property="og:type" content="website">
<link rel="stylesheet" href="/assets/css/provident.css">
<link rel="stylesheet" href="/assets/css/header-styles.css">
<link rel="stylesheet" href="/assets/css/developer-styles.css">
<link rel="stylesheet" href="/assets/css/app-styles.css">
<link rel="stylesheet" href="/assets/css/portal.css">
<link rel="stylesheet" href="/assets/css/app-shell.css">
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','GTM-PGNHTGZ5');</script>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PGNHTGZ5" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <?php
}

function render_site_footer_scripts(): void
{
    ?>
<script src="/assets/js/main.js" defer></script>
<script src="/assets/js/listing-ui.js" defer></script>
<script src="/assets/js/property.js" defer></script>
<script src="/assets/js/content-ui.js" defer></script>
    <?php
}
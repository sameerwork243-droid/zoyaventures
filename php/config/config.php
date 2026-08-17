<?php
// config.php — site-wide constants (port of src/lib/site.ts + src/lib/image.ts + src/lib/utils.ts)

define('APP_NAME', 'Zoya Ventures Real Estate');
define('APP_DOMAIN', 'provident.ae');
define('APP_PHONE', '+971 568 308 221');
define('APP_PHONE_LINK', '+971568308221');
define('APP_WHATSAPP', '971568308221');
define('APP_EMAIL', 'zoyaventure15@gmail.com');
define('APP_MAP_QUERY', 'Dubai Marina');
define('APP_SOCIAL', ['instagram', 'facebook', 'linkedin', 'x', 'youtube']);

define('APP_BRAND_PRIMARY', '#EE7133');
define('APP_BRAND_NAVY', '#07234B');

define('APP_GTM_ID', 'GTM-PGNHTGZ5');
define('APP_COPYRIGHT_YEAR', '2024');

define('SESSION_COOKIE', 'provident_session');
define('SESSION_TTL', 7 * 24 * 3600);      // 7 days
define('REMEMBER_TTL', 30 * 24 * 3600);    // 30 days

define('LISTING_PER_PAGE', 9);

define('CDN_BASE', 'https://d3h330vgpwpjr8.cloudfront.net');

// settings (src/lib/site.ts SETTINGS)
$SETTINGS_CURRENCIES = ['AED', 'USD', 'EUR', 'GBP', 'INR'];
$SETTINGS_UNITS = ['Sqft', 'Sqm'];
$SETTINGS_RATES = ['AED' => 1, 'USD' => 0.273, 'EUR' => 0.252, 'GBP' => 0.216, 'INR' => 22.6];

// local CDN asset overrides (src/lib/image.ts LOCAL_OVERRIDES)
$LOCAL_OVERRIDES = [
    '2_15234debdc' => [
        'base' => '/images/banners/careers-banner.webp',
        'sizes' => ['376x' => '/images/banners/careers-banner-376.webp', '744x' => '/images/banners/careers-banner-744.webp'],
    ],
    'Group_image_016b5fb1e3' => [
        'base' => '/images/banners/about-video.webp',
        'sizes' => ['336x' => '/images/banners/about-video-336.webp', '696x' => '/images/banners/about-video-696.webp'],
    ],
];

// nav links (src/lib/site.ts NAV_LINKS)
$NAV_LINKS = [
    ['label' => 'Buy', 'href' => '/buy', 'children' => [
        ['label' => 'Apartments for sale', 'href' => '/buy/apartments-for-sale'],
        ['label' => 'Villas for sale', 'href' => '/buy/villas-for-sale'],
        ['label' => 'Townhouses for sale', 'href' => '/buy/townhouses-for-sale'],
        ['label' => 'Penthouse for sale', 'href' => '/buy/penthouses-for-sale'],
        ['label' => 'All properties for sale', 'href' => '/buy'],
    ]],
    ['label' => 'Rent', 'href' => '/let', 'children' => [
        ['label' => 'Apartments for rent', 'href' => '/let/apartments-for-rent'],
        ['label' => 'Villas for rent', 'href' => '/let/villas-for-rent'],
        ['label' => 'All properties to rent', 'href' => '/let'],
    ]],
    ['label' => 'New Projects', 'href' => '/new-projects', 'children' => [
        ['label' => 'All new projects', 'href' => '/new-projects'],
        ['label' => 'Under construction', 'href' => '/new-projects/type-under-construction'],
        ['label' => 'Ready to move in', 'href' => '/new-projects/type-ready'],
        ['label' => 'Across all developers', 'href' => '/developers'],
    ]],
    ['label' => 'Area Guides', 'href' => '/area-guides'],
    ['label' => 'Blog', 'href' => '/blog'],
    ['label' => 'Team', 'href' => '/team'],
    ['label' => 'Company', 'href' => '/about', 'children' => [
        ['label' => 'About us', 'href' => '/about'],
        ['label' => 'Our team', 'href' => '/team'],
        ['label' => 'Careers', 'href' => '/careers'],
        ['label' => 'Services', 'href' => '/services'],
        ['label' => 'Roadshow & events', 'href' => '/roadshow'],
        ['label' => 'Contact', 'href' => '/contact'],
    ]],
];

<?php
// content-pages.php — ContentPages port (src/components/content-pages.tsx)
// + AreaGuidesListing + BlogListing + DeveloperListing, static server-side.
// Interactive parts (search/filter/pagination/selects) are wired by
// assets/js/content-ui.js via the data-* hooks emitted here.

require_once __DIR__ . '/modules.php';

const QUICK_LINKS = [
    ['label' => 'Buy', 'href' => '/buy/properties-for-sale/'],
    ['label' => 'Rent', 'href' => '/let/properties-for-rent/'],
    ['label' => 'Projects', 'href' => '/new-projects/'],
    ['label' => 'Developers ', 'href' => '/developers/'],
    ['label' => 'Areas', 'href' => '/area-guides/'],
    ['label' => 'Services', 'href' => '/property-services/'],
    ['label' => 'Blogs', 'href' => '/blog/'],
];

const PARENT_LABELS = [
    'sell' => 'Sell',
    'contact' => 'Contact',
    'property-services' => 'Services',
    'roadshow' => 'Roadshow',
    'careers' => 'Careers',
    'blog' => 'News & Insights',
    'team' => 'Meet the Team',
    'area-guides' => 'Communities',
    'about' => 'About',
    'off-plan' => 'Off-Plan',
    'new-projects' => 'Off-Plan Projects',
    'developers' => 'Developers',
    'list-your-property' => 'List Your Property',
];

/** MobileBannerMenu port. */
function mobile_banner_menu(bool $black = false, ?string $current = null): string
{
    $out = '<div class="mobile-banner-menu' . ($black ? ' black' : ' undefined') . '">';
    $out .= '<div class="scroll-i d-flex d-md-none">';
    foreach (QUICK_LINKS as $l) {
        $out .= '<a aria-current="' . ($current === $l['href'] ? 'page' : 'false') . '" class="main-menu" href="' . esc($l['href']) . '">'
            . '<span>' . esc($l['label']) . '</span></a>';
    }
    $out .= '</div></div>';
    return $out;
}

/** Breadcrumbs port. */
function breadcrumbs_html(array $crumbs): string
{
    $out = '<div class="breadcrumbs-wrap"><div class="breadcrumbs-container container"><nav class="breadcrumbs"><ol class="breadcrumb">';
    foreach ($crumbs as $i => $c) {
        $active = !empty($c['active']);
        $cls = 'breadcrumb-item' . ($i === 0 ? ' enable-link-home' : '') . ($active ? ' active' : '');
        $linkCls = 'breadcrumb-link ' . ($active ? 'disable-link' : 'enable-link');
        $out .= '<li class="' . $cls . '"><a aria-current="' . ($active ? 'page' : 'false') . '" class="' . $linkCls . '" href="' . esc($c['href']) . '">'
            . esc($c['label']) . '</a></li>';
    }
    $out .= '</ol></nav></div></div>';
    return $out;
}

/** routeCrumbs port (white variant unused in source). */
function route_crumbs(string $route, string $leafLabel, ?string $leafHref = null): array
{
    $segs = array_values(array_filter(explode('/', $route)));
    $crumbs = [['label' => 'Home', 'href' => '/']];
    $n = count($segs);
    for ($i = 0; $i < $n - 1; $i++) {
        $href = '/' . implode('/', array_slice($segs, 0, $i + 1)) . '/';
        $label = PARENT_LABELS[$segs[$i]] ?? str_replace('-', ' ', $segs[$i]);
        $crumbs[] = ['label' => $label, 'href' => $href];
    }
    $crumbs[] = ['label' => $leafLabel, 'href' => $leafHref ?? rtrim($route, '/') . '/', 'active' => true];
    return $crumbs;
}

/** ctaSection port (StrapiPage banner CTAs). */
function cta_section_html(array $ctas): string
{
    if (!count($ctas)) return '';
    $out = '<div class="cta-section">';
    foreach ($ctas as $c) {
        $custom = is_string($c['custom_link'] ?? null) ? $c['custom_link'] : null;
        $magic = $custom !== null && (str_starts_with($custom, '#') || str_starts_with($custom, '$'));
        $label = !empty($c['cta_label']) ? $c['cta_label'] : 'Learn More';
        $gray = ($c['icon'] ?? '') === 'phone-blue' || ($c['icon'] ?? '') === 'right-arrow-white';
        $cls = 'button ' . ($gray ? 'button-gray' : 'button-orange');
        $href = $magic ? ($custom ?? '#') : cta_href($c, '/contact/');
        $btn = '<span>' . esc($label) . '</span>';
        if (($c['icon'] ?? '') === 'up-right-arrow-white') {
            $btn .= '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-up-right-icon"><path d="M2.25 9.75L9.75 2.25M9.75 2.25L4.125 2.25M9.75 2.25V7.875" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" /></svg>';
        }
        if (($c['icon'] ?? '') === 'phone-blue') {
            $btn .= '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="mobile-icon"><path d="M10.5 1.5H8.25C7.00736 1.5 6 2.50736 6 3.75V20.25C6 21.4926 7.00736 22.5 8.25 22.5H15.75C16.9926 22.5 18 21.4926 18 20.25V3.75C18 2.50736 16.9926 1.5 15.75 1.5H13.5M10.5 1.5V3H13.5V1.5M10.5 1.5H13.5M10.5 20.25H13.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>';
        }
        if (($c['icon'] ?? '') === 'right-arrow-white') {
            $btn .= '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" class="right-arrow-icon"><path d="M2.25 6H9.75M9.75 6L5.625 1.875M9.75 6L5.625 10.125" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>';
        }
        $out .= '<a class="' . $cls . '" href="' . esc($href) . '">' . $btn . '</a>';
    }
    $out .= '</div>';
    return $out;
}

/** AreaGuidesListing port (static initial state: "All", first 24 guides). */
function area_guides_listing_html(array $areas): string
{
    $amenities = ['All', 'Luxury living', 'Big city life', 'Beachfront properties', 'Waterfront properties', 'Near metro', 'Green nature living', 'Family community', 'Near golf course', 'Villa community', 'Outdoor spaces', "Children's play area"];
    $visible = array_slice($areas, 0, 24);
    $out = '<div class="community-listing-wrap listing-wrap" data-area-guides>'
        . '<script type="application/json" data-area-json>' . json_encode(array_map(function ($a) {
            return [
                'slug' => $a['slug'] ?? '',
                'title' => $a['title'] ?? '',
                'image' => $a['image'] ?? '',
                'image304' => $a['image304'] ?? '',
                'desc' => $a['desc'] ?? '',
                'amenities' => array_values((array) ($a['amenities'] ?? [])),
            ];
        }, $areas), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>'
        . '<div class="amenities-section-wrap category-section" style="top:-146px">'
        . '<div class="amenities-section container"><div class="max-filter">'
        . '<div class="search-box-comm">'
        . '<input class="form-control search" data-area-search placeholder="Search Communities" />'
        . '<span><i class="icon grey-search-icon"></i></span>'
        . '</div>'
        . '<div class="react-select-wrap"><div class="react-select"><div class="react-select__control" data-area-amenity-toggle>'
        . '<div class="react-select__value-container react-select__value-container--has-value">'
        . '<div class="react-select__single-value" data-area-amenity-value>All</div></div>'
        . '<div class="react-select__indicators"><span class="react-select__indicator-separator"></span>'
        . '<div class="dropdown-indicator react-select__indicator react-select__dropdown-indicator" aria-hidden="true">'
        . '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-down-icon"><path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        . '</div></div></div>'
        . '<div class="react-select__menu"><div class="react-select__menu-list">';
    foreach ($amenities as $i => $o) {
        $sel = $i === 0 ? ' react-select__option--is-focused react-select__option--is-selected' : '';
        $out .= '<div class="react-select__option' . $sel . '" data-area-amenity="' . esc($o) . '">' . esc($o) . '</div>';
    }
    $out .= '</div></div></div></div></div></div></div>'
        . '<div class="community-listing-container container">'
        . '<div class="community-amenities-select-section"></div>'
        . '<div class="community-listing-section" data-area-list>';
    foreach ($visible as $a) {
        $href = '/area-guides/' . rawurlencode((string) $a['slug']) . '/';
        $out .= '<div class="areaguide-card" data-area-card="' . esc($a['slug']) . '">'
            . '<div class="img-section img-zoom"><a class="tt-fi" href="' . esc($href) . '">';
        if (!empty($a['image'])) {
            $out .= '<img loading="lazy" draggable="false" src="' . esc($a['image']) . '" srcSet="' . esc($a['image']) . ' 340w, ' . esc($a['image304']) . ' 304w" sizes="(min-width: 100px) 340px" alt="' . esc(alt_text($a['title'])) . '" />';
        }
        $out .= '</a></div>'
            . '<a class="title" href="' . esc($href) . '">' . esc($a['title']) . '</a>';
        if (!empty($a['desc'])) {
            $out .= '<a class="description" href="' . esc($href) . '">' . $a['desc'] . '</a>';
        }
        $out .= '</div>';
    }
    $out .= '</div></div></div>';
    return $out;
}

/** DeveloperListing port (static initial state: no query → all developers). */
function developer_listing_html(array $devs): string
{
    $out = '<div class="developer-listing-wrap listing-wrap" data-developer-listing>'
        . '<div class="developer-listing-container container">'
        . '<div class="category-section search-section-wrap container"><div class="search-section">'
        . '<div class="search-input-wrap"><input type="text" data-developer-search placeholder="Search Developers" /></div>'
        . '<button class="button button-orange" type="button">'
        . '<span>Search</span>'
        . '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none" class="search-icon"><path d="M14.5 14L11.0355 10.5355M11.0355 10.5355C11.9404 9.63071 12.5 8.38071 12.5 7C12.5 4.23858 10.2614 2 7.5 2C4.73858 2 2.5 4.23858 2.5 7C2.5 9.76142 4.73858 12 7.5 12C8.88071 12 10.1307 11.4404 11.0355 10.5355Z" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        . '</button></div></div>'
        . '<div class="developer-listing-section" data-developer-list>';
    foreach ($devs as $d) {
        $link = '/new-projects/developed-by-' . rawurlencode((string) $d['slug']) . '/';
        $out .= '<div class="developer-card" data-developer-card="' . esc($d['slug']) . '">'
            . '<a class="img-section-wrap img-zoom" href="' . esc($link) . '">'
            . '<div class="img-section" style="background-image:url(' . esc($d['background']) . ')">'
            . '<div class="logo-section"><img loading="lazy" draggable="false" src="' . esc($d['logo']) . '" alt="' . esc(alt_text($d['name'])) . '" /></div>'
            . '</div></a>'
            . '<a class="name" href="' . esc($link) . '"><span>' . esc($d['name']) . '</span>'
            . '<svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-up-right-icon"><path d="M2.25 9.75L9.75 2.25M9.75 2.25L4.125 2.25M9.75 2.25V7.875" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
            . '</a>'
            . '<p class="description">' . esc($d['description']) . '</p>'
            . '</div>';
    }
    $out .= '</div></div></div>';
    return $out;
}

/** BlogListing port (static initial state: no query, All Categories, page 1). */
function blog_listing_html(array $posts): string
{
    $perPage = 12;
    $categories = ['All Categories'];
    $seen = [];
    foreach ($posts as $p) {
        if (!empty($p['category']) && !in_array($p['category'], $seen, true)) $seen[] = $p['category'];
    }
    $categories = array_merge($categories, $seen);
    $totalPages = max(1, (int) ceil(count($posts) / $perPage));
    $pageItems = array_slice($posts, 0, $perPage);

    $out = '<div class="blog-listing-wrap listing-wrap" data-blog-listing>'
        . '<script type="application/json" data-blog-json>' . json_encode(array_map(function ($p) {
            return [
                'slug' => $p['slug'] ?? '',
                'title' => $p['title'] ?? '',
                'date' => $p['date'] ?? '',
                'category' => $p['category'] ?? '',
                'image' => $p['image'] ?? '',
            ];
        }, $posts), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>'
        . '<div class="category-section-wrap category-section"><div class="category-section container"><div class="max-filter">'
        . '<div class="search-box-comm">'
        . '<input class="form-control search" data-blog-search placeholder="Search by keyword" />'
        . '<span><i class="icon grey-search-icon"></i></span>'
        . '</div>'
        . '<div class="react-select-wrap"><div class="react-select css-b62m3t-container">'
        . '<div class="react-select__control css-14qho42-control" data-blog-category-toggle>'
        . '<div class="react-select__value-container react-select__value-container--has-value css-hlgwow">'
        . '<div class="react-select__single-value css-1ubv46r-singleValue" data-blog-category-value>All Categories</div></div>'
        . '<div class="react-select__indicators css-1wy0on6"><span class="react-select__indicator-separator css-1uei4ir-indicatorSeparator"></span>'
        . '<div class="dropdown-indicator react-select__indicator react-select__dropdown-indicator css-15ctyzv-indicatorContainer" aria-hidden="true">'
        . '<svg class="arrow-down-icon" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        . '</div></div></div>'
        . '<div class="react-select__menu"><div class="react-select__menu-list">';
    foreach ($categories as $c) {
        $out .= '<div class="react-select__option' . ($c === 'All Categories' ? ' react-select__option--is-selected' : '') . '" data-blog-category="' . esc($c) . '">' . esc($c) . '</div>';
    }
    $out .= '</div></div></div></div></div></div></div>'
        . '<div class="blog-listing-container container">'
        . '<div class="blog-category-select-section"></div>'
        . '<div class="blog-listing-section" data-blog-list>';
    foreach ($pageItems as $b) {
        $href = '/blog/' . rawurlencode((string) $b['slug']) . '/';
        $out .= '<div class="news-card-wrapper"><div class="news-card">'
            . '<div class="img-section-wrap img-zoom"><a class="img-section" href="' . esc($href) . '">';
        if (!empty($b['image'])) $out .= '<img loading="lazy" src="' . esc($b['image']) . '" alt="' . esc($b['title']) . '" />';
        if (!empty($b['category'])) $out .= '<p class="img-tag">' . esc($b['category']) . '</p>';
        $out .= '</a></div>'
            . '<a class="title" href="' . esc($href) . '">' . esc($b['title']) . '</a>'
            . '<p class="date">' . esc($b['date']) . '</p>'
            . '</div></div>';
    }
    $out .= '</div>';
    if ($totalPages > 1) {
        $out .= '<nav class="pagination-wrapper"><div><div class="pagination-container">'
            . '<button class="button button-white pagination-button button-back button-disabled" data-blog-back disabled type="button">'
            . '<svg class="arrow-left-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
            . '<span>Back</span></button>'
            . '<div class="pagination-select-wrap"><span class="page-text">Page:</span>'
            . '<div class="pagination-select"><div class="react-select css-b62m3t-container">'
            . '<div class="pagination-select__control css-13cymwt-control" data-blog-page-toggle>'
            . '<div class="pagination-select__value-container pagination-select__value-container--has-value css-hlgwow">'
            . '<div class="pagination-select__single-value css-1dimb5e-singleValue" data-blog-page-value>1</div></div>'
            . '<div class="pagination-select__indicators css-1wy0on6"><span class="pagination-select__indicator-separator css-1uei4ir-indicatorSeparator"></span>'
            . '<div class="dropdown-indicator react-select__indicator react-select__dropdown-indicator css-15ctyzv-indicatorContainer" aria-hidden="true">'
            . '<svg class="arrow-down-icon" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13 5.5L8 10.5L3 5.5" stroke="#9399A4" stroke-linecap="round" stroke-linejoin="round" /></svg>'
            . '</div></div></div>'
            . '<div class="pagination-select__menu"><div class="pagination-select__menu-list">';
        for ($n = 1; $n <= $totalPages; $n++) {
            $out .= '<div class="pagination-select__option' . ($n === 1 ? ' pagination-select__option--is-selected' : '') . '" data-blog-page="' . $n . '">' . $n . '</div>';
        }
        $out .= '</div></div></div></div>'
            . '<span class="page-text">of ' . $totalPages . '</span></div>'
            . '<button class="button button-white pagination-button button-next' . ($totalPages <= 1 ? ' button-disabled' : '') . '" data-blog-next type="button">'
            . '<span>Next</span>'
            . '<svg class="arrow-right-icon" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M8.25 4.5L15.75 12L8.25 19.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
            . '</button></div></div></nav>';
    }
    $out .= '</div></div>';
    return $out;
}

/* ------------------------------ StrapiPage ------------------------------ */

/** brandBx port (default banner block). */
function brand_bx_html(array $banner, string $title): string
{
    $out = '<div class="brand-bx"><h1 class="title">' . esc($title) . '</h1>';
    if (!empty($banner['heading'])) $out .= '<h2 class="heading">' . esc($banner['heading']) . '</h2>';
    $descHtml = $banner['description']['data']['description'] ?? null;
    if ($descHtml) $out .= '<div class="description">' . rich((string) $descHtml) . '</div>';
    $out .= cta_section_html(is_array($banner['ctas'] ?? null) ? $banner['ctas'] : []);
    $out .= '</div>';
    return $out;
}

/** StrapiPage port. */
function content_strapi_page(array $page, string $route): string
{
    $banner = is_array($page['banner'] ?? null) ? $page['banner'] : [];
    $layout = (string) ($page['layout'] ?? 'landing_page');
    $isForm = $layout === 'form_page';
    $isAreasListing = ($page['page_class'] ?? '') === 'communities_listing_page';
    $bg = $banner['banner_image']['url'] ?? null;
    $title = $banner['title'] ?? $page['page_name'] ?? '';
    $mods = is_array($page['modules'] ?? null) ? $page['modules'] : [];
    $crumbLeaf = $page['page_name'] ?? $banner['title'] ?? strip_html((string) $title) ?? 'Page';
    if (!$crumbLeaf) $crumbLeaf = 'Page';
    $descHtml = $banner['description']['data']['description'] ?? null;
    $videoThumb = $banner['banner_video']['thumbnail']['url'] ?? null;
    $crumbs = route_crumbs($route, $crumbLeaf);

    if (($page['page_class'] ?? '') === 'developers_listing_page') return content_developers_listing_page($page, $route);
    if (($page['page_class'] ?? '') === 'news_landing_page') return content_news_listing_page($page, $route);

    $crumbNav = breadcrumbs_html($crumbs);

    if ($isAreasListing) {
        $out = '';
        if (!$isForm) $out .= mobile_banner_menu(true);
        $out .= $crumbNav;
        $out .= '<div class="banner-listing-wrap" style="padding-bottom:186px">'
            . '<div class="banner-listing-container container">'
            . '<h1 class="title">' . esc($title) . '</h1>';
        if ($descHtml) $out .= '<div class="description">' . rich((string) $descHtml) . '</div>';
        $out .= '</div></div>';
        $out .= area_guides_listing_html(area_guides_data());
        return $out;
    }

    if ($layout === 'landing_page_2') {
        $out = '<div><div class="banner-wrap banner-home-wrap"><div class="bg-section-gradient"></div>';
        $out .= mobile_banner_menu(true);
        $out .= $crumbNav;
        $out .= '<div class="center-content"><div class="banner-container container">';
        $out .= brand_bx_html($banner, $title);
        if ($videoThumb) {
            $out .= '<div class="banner-video">'
                . '<img loading="lazy" draggable="false" src="' . esc(cfw((string) $videoThumb, 1968)) . '" '
                . 'srcSet="' . esc(cfw((string) $videoThumb, 336)) . ' 336w, ' . esc(cfw((string) $videoThumb, 696)) . ' 696w, ' . esc(cfw((string) $videoThumb, 1968)) . ' 1968w" '
                . 'sizes="(max-width: 480px) 336px, (max-width: 1100px) 696px, (min-width: 1100px) 1968px" '
                . 'alt="banner-video - Zoya Ventures Real Estate" class="video-thumbnail" />'
                . '<button class="play-button" aria-label="play button"></button></div>';
        }
        $out .= '</div></div></div>';
        $out .= content_modules_loop($mods);
        $out .= '</div>';
        return $out;
    }

    if ($layout === 'listing_page') {
        $out = '<div><div class="listing-page-wrap"><div class="listing-page-top"><div class="bg-section-gradient"></div>';
        $out .= mobile_banner_menu(true);
        $out .= $crumbNav;
        $out .= '<div class="banner-listing-wrap"><div class="banner-listing-container container">'
            . '<h1 class="title">' . esc($title) . '</h1>';
        if ($descHtml) $out .= '<div class="description">' . rich((string) $descHtml) . '</div>';
        $out .= cta_section_html(is_array($banner['ctas'] ?? null) ? $banner['ctas'] : []);
        $out .= '</div></div></div></div>';
        $out .= content_modules_loop($mods);
        $out .= '</div>';
        return $out;
    }

    $out = '<div><div class="banner-wrap banner-landing-wrap">';
    if (!$isForm) $out .= mobile_banner_menu();
    $out .= '<div class="bg-section">';
    if ($bg) {
        $out .= '<img loading="eager" draggable="false" src="' . esc(cfw((string) $bg, 1773)) . '" '
            . 'srcSet="' . esc(cfw((string) $bg, 376)) . ' 376w, ' . esc(cfw((string) $bg, 744)) . ' 744w, ' . esc(cfw((string) $bg, 1773)) . ' 1773w" '
            . 'sizes="(max-width: 480px) 376px, (max-width: 1100px) 744px, (min-width: 1100px) 1773px" '
            . 'alt="banner-bg - Zoya Ventures Real Estate" />';
    }
    $out .= '<div class="overlay"></div></div>';
    $out .= '<div class="breadcrumbs-wrap white-color">' . $crumbNav . '</div>';
    $out .= '<div><div class="banner-container container">';
    $out .= brand_bx_html($banner, $title);
    if (!empty($banner['show_reviews'])) {
        $out .= '<div class="reviews-section">'
            . '<div class="review-item"><img draggable="false" src="/assets/google-stars.svg" alt="Google Stars" /><p class="review">4.8</p></div>'
            . '<div class="divider"></div>'
            . '<div class="review-item"><img draggable="false" src="/assets/trustpilot.svg" alt="Trust Pilot" /><p class="review">4.9/5</p></div>'
            . '</div>';
    }
    $out .= '</div></div></div>';
    $out .= content_modules_loop($mods);
    $out .= '</div>';
    return $out;
}

/** Shared module loop: skip communities_listing inside content pages. */
function content_modules_loop(array $mods): string
{
    $out = '';
    foreach ($mods as $m) {
        if (!is_array($m)) continue;
        if (($m['strapi_component'] ?? '') === 'modules.listing-module' && ($m['module'] ?? '') === 'communities_listing') continue;
        $out .= module_wrap($m);
    }
    return $out;
}

/* ------------------------------ listing pages ------------------------------ */

/** DevelopersListingPage port. */
function content_developers_listing_page(array $page, string $route): string
{
    $banner = is_array($page['banner'] ?? null) ? $page['banner'] : [];
    $title = $banner['title'] ?? $page['page_name'] ?? 'Developers';
    $descHtml = $banner['description']['data']['description'] ?? null;
    $mods = is_array($page['modules'] ?? null) ? $page['modules'] : [];
    $out = '<div class="listing-page-wrap"><div class="listing-page-top"><div class="bg-section-gradient"></div>';
    $out .= mobile_banner_menu(true, '/developers/');
    $out .= breadcrumbs_html(route_crumbs($route, 'Developers'));
    $out .= '<div class="banner-listing-wrap"><div class="banner-listing-container container">'
        . '<h1 class="title">' . esc($title) . '</h1>'
        . '<div class="description">' . ($descHtml ? rich((string) $descHtml) : '') . '</div>'
        . '</div></div></div>';
    $out .= developer_listing_html(developers_list());
    foreach ($mods as $m) {
        if (!is_array($m)) continue;
        if (($m['strapi_component'] ?? '') === 'modules.listing-module' && ($m['module'] ?? '') === 'developer_listing') continue;
        if (($m['strapi_component'] ?? '') === 'modules.global-module' && ($m['choose_module'] ?? '') === 'contact_module') continue;
        $out .= module_wrap($m);
    }
    $out .= '</div>';
    return $out;
}

/** NewsListingPage port. */
function content_news_listing_page(array $page, string $route): string
{
    $banner = is_array($page['banner'] ?? null) ? $page['banner'] : [];
    $title = $banner['title'] ?? $page['page_name'] ?? 'News, Media Gallery & Insights';
    $descHtml = $banner['description']['data']['description'] ?? null;
    $out = '<div class="listing-page-wrap"><div class="listing-page-top"><div class="bg-section-gradient"></div>';
    $out .= mobile_banner_menu(true, '/blog/');
    $out .= breadcrumbs_html(route_crumbs($route, 'News & Insight'));
    $out .= '<div class="banner-listing-wrap"><div class="banner-listing-container container">'
        . '<h1 class="title">' . esc($title) . '</h1>'
        . '<div class="description">' . ($descHtml ? rich((string) $descHtml) : '') . '</div>'
        . '</div></div></div>';
    $out .= blog_listing_html(blog_posts(10000));
    $out .= '</div>';
    return $out;
}

/* ------------------------------ detail pages ------------------------------ */

/** BlogDetail port. */
function content_blog_detail(array $b, string $route): string
{
    $bg = $b['banner_image']['url'] ?? null;
    $mods = is_array($b['modules'] ?? null) ? $b['modules'] : [];
    $cat = $b['category']['strapi_json_value'] ?? null;
    $category = is_array($cat) ? implode(', ', $cat) : (string) ($b['category'] ?? '');
    $posts = [];
    foreach (blog_posts(12) as $p) {
        if ($p['slug'] === ($b['slug'] ?? '')) continue;
        $posts[] = $p;
    }
    $posts = array_slice($posts, 0, 10);

    $out = '<div>';
    $out .= mobile_banner_menu(true);
    $out .= breadcrumbs_html(route_crumbs($route, (string) ($b['title'] ?? $b['slug'] ?? 'Page')));
    $out .= '<div class="news-detail-container container"><div class="news-info-section">';
    $out .= '<div><div class="new-banner-wrap">'
        . '<h1 class="title">' . esc((string) ($b['title'] ?? '')) . '</h1>'
        . '<div class="info-section">';
    if (!empty($b['date'])) $out .= '<p class="date">' . esc($b['date']) . '</p>';
    if (!empty($b['date']) && $category) $out .= '<span class="slash-divider">/</span>';
    if ($category) $out .= '<p class="category">' . esc($category) . '</p>';
    $out .= '</div></div></div>';
    if ($bg) {
        $out .= '<div><div class="news-banner-img">'
            . '<img loading="lazy" src="' . esc(cfw((string) $bg, 1000)) . '" '
            . 'srcSet="' . esc(cfw((string) $bg, 339)) . ' 339w, ' . esc(cfw((string) $bg, 696)) . ' 696w, ' . esc(cfw((string) $bg, 1000)) . ' 1000w" '
            . 'sizes="(max-width: 480px) 339px, (max-width: 1100px) 696px, (min-width: 1100px) 1000px" '
            . 'alt="' . esc(alt_text((string) ($b['title'] ?? 'News'))) . '" />'
            . '</div></div>';
    }
    $out .= '<div><div class="news-content">';
    foreach ($mods as $m) {
        if (is_array($m)) $out .= module_wrap($m);
    }
    $out .= '</div></div></div></div>';
    if (count($posts)) {
        $slides = [];
        foreach ($posts as $p) {
            $href = '/blog/' . rawurlencode((string) $p['slug']) . '/';
            $card = '<div class="news-card"><div class="img-section-wrap img-zoom">'
                . '<a class="img-section" href="' . esc($href) . '">';
            if (!empty($p['image'])) $card .= '<img loading="lazy" src="' . esc(cfw((string) $p['image'], 696)) . '" alt="' . esc($p['title']) . '" />';
            $card .= '</a></div><div class="content-section">';
            if (!empty($p['category'])) $card .= '<p class="img-tag">' . esc($p['category']) . '</p>';
            $card .= '<a class="title" href="' . esc($href) . '">' . esc($p['title']) . '</a>';
            if (!empty($p['date'])) $card .= '<p class="date">' . esc($p['date']) . '</p>';
            $card .= '</div></div>';
            $slides[] = $card;
        }
        $out .= '<div class="slider-module-wrap more-nwes-wrap section-p">'
            . '<div class="slider-module-container container">'
            . '<h2 class="heading">More News</h2>'
            . slick_shell('', $slides, 4)
            . '</div></div>';
    }
    $out .= '</div>';
    return $out;
}

/** TeamDetail port. */
function content_team_detail(array $t, string $route): string
{
    $img = $t['extra']['profile_image'] ?? $t['image']['url'] ?? null;
    $about = $t['about']['data']['about'] ?? $t['bio'] ?? '';
    $langs = $t['languages']['strapi_json_value'] ?? null;
    $langs = is_array($langs) ? $langs : [];
    $cats = $t['category']['strapi_json_value'] ?? null;
    $cats = is_array($cats) ? $cats : [];
    $phone = preg_replace('/[^+\d]/', '', (string) ($t['phone'] ?? $t['office_phone'] ?? ''));
    $waPhone = (string) preg_replace('/^\+/', '', (string) $phone);

    $out = '<div>';
    $out .= mobile_banner_menu(true);
    $out .= breadcrumbs_html(route_crumbs($route, (string) ($t['name'] ?? $t['slug'] ?? 'Page')));
    $out .= '<div class="team-detail-container container"><div class="team-info-section "><div class="left-section">';
    $out .= '<h1 class="name">' . esc((string) ($t['name'] ?? '')) . '</h1>';
    if (!empty($t['designation'])) $out .= '<p class="designation">' . esc($t['designation']) . '</p>';
    if ($about) {
        $out .= '<div class="about-section-wrap" id="about-section">'
            . '<p class="heading">About ' . esc((string) ($t['name'] ?? '')) . '</p>'
            . read_more(rich((string) $about), 4, 'about-section')
            . '</div>';
    }
    if (count($langs) || !empty($t['license'])) {
        $out .= '<div class="agent-info-wrap">';
        if (count($langs)) $out .= '<p class="agent-info"><span class="label">Languages:</span> ' . esc(implode(', ', $langs)) . '</p>';
        if (!empty($t['license'])) $out .= '<p class="agent-info"><span class="label">License:</span> ' . esc($t['license']) . '</p>';
        if (count($cats)) $out .= '<p class="agent-info"><span class="label">Category:</span> ' . esc(implode(', ', $cats)) . '</p>';
        $out .= '</div>';
    }
    $out .= '<div class="cta-section agent-cta-section">';
    if ($phone) {
        $out .= '<a class="property-cta" href="tel:' . esc($phone) . '">' . country_flag('AE') . '<span>Call</span></a>';
    }
    if ($waPhone) {
        $out .= '<a class="property-cta whats" target="_blank" rel="noreferrer" href="https://wa.provident.ae/inquire?phone=' . rawurlencode($waPhone) . '&text=Hello%20Zoya%20Ventures%2C%0A%0AI%20would%20like%20to%20know%20more%20about%20this%20page%3A%0A%0A%E2%80%A2%20Page%20Name%3A%20%0A%E2%80%A2%20Link%3A%20%0A%0AModifying%20this%20message%20will%20prevent%20it%20from%20being%20sent%20to%20the%20agent.">'
            . '<span>WhatsApp</span></a>';
    }
    if (!empty($t['email'])) {
        $out .= '<a class="property-cta email" href="mailto:' . esc($t['email']) . '"><span>Email</span></a>';
    }
    $out .= '</div></div>';
    if ($img) {
        $out .= '<div class="right-section"><div class="image-wrap">'
            . '<img loading="lazy" src="' . esc($img) . '" alt="' . esc((string) ($t['name'] ?? '')) . '" />'
            . '</div></div>';
    }
    $out .= '</div></div></div>';
    return $out;
}

/** AreaGuideDetail port. */
function content_area_guide_detail(array $a, string $route): string
{
    $bg = $a['banner_image']['url'] ?? null;
    $content = $a['content']['data']['content'] ?? '';
    $desc = $a['description']['data']['description'] ?? '';
    $label = trim(strip_html((string) ($a['title'] ?? '')));
    $moreInfo = [];
    if (is_array($a['more_info'] ?? null)) {
        foreach ($a['more_info'] as $m) {
            if (!is_array($m)) continue;
            $moreInfo[] = ['question' => (string) ($m['question'] ?? ''), 'answer' => (string) ($m['answer']['data']['answer'] ?? '')];
        }
    }
    $sponsored = [];
    if (is_array($a['sponsored_projects'] ?? null)) {
        foreach ($a['sponsored_projects'] as $s) {
            $slug = is_string($s) ? $s : ($s['slug'] ?? null);
            if (!$slug) continue;
            $p = project_by_slug((string) $slug);
            if ($p) $sponsored[] = $p;
        }
    }
    $projects = array_slice(projects_by_area((string) ($a['title'] ?? $a['slug'] ?? '')), 0, 10);
    $crumbs = route_crumbs($route, $label);

    $out = '<div><div class="banner-wrap banner-landing-wrap">';
    $out .= mobile_banner_menu();
    $out .= '<div class="bg-section">';
    if ($bg) {
        $out .= '<img loading="eager" src="' . esc(cfw((string) $bg, 1773)) . '" '
            . 'srcSet="' . esc(cfw((string) $bg, 376)) . ' 376w, ' . esc(cfw((string) $bg, 744)) . ' 744w, ' . esc(cfw((string) $bg, 1773)) . ' 1773w" '
            . 'sizes="(max-width: 480px) 376px, (max-width: 1100px) 744px, (min-width: 1100px) 1773px" '
            . 'alt="banner-bg - Zoya Ventures Real Estate" />';
    }
    $out .= '<div class="overlay"></div></div>';
    $out .= '<div class="breadcrumbs-wrap white-color">' . breadcrumbs_html($crumbs) . '</div>';
    $out .= '<div><div class="banner-container container">'
        . '<div class="brand-bx"><h1 class="title">' . esc($label) . ' Guide</h1>';
    if ($desc) $out .= '<div class="description">' . rich((string) $desc) . '</div>';
    $out .= '</div>'
        . '<div class="search-box-wrap"><div class="search-box-container"><div class="search-filter">'
        . '<div class="mutil-select-wrap"><div class="multi-select-input" id="multi-select-input"><div class="filter search-box">'
        . '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none" class="search-icon"><path d="M14.5 14L11.0355 10.5355M11.0355 10.5355C11.9404 9.63071 12.5 8.38071 12.5 7C12.5 4.23858 10.2614 2 7.5 2C4.73858 2 2.5 4.23858 2.5 7C2.5 9.76142 4.73858 12 7.5 12C8.88071 12 10.1307 11.4404 11.0355 10.5355Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        . '<div class="autosuggest__container"><input id="search-input-field" type="text" placeholder="Area, project or community" class="autosuggest__input" autocomplete="off" value="" /></div>'
        . '</div></div></div>'
        . '<div class="filter-dropdown bedroom-filter-dropdown ishide-mod dropdown">'
        . '<button class="custom-dropdown-toggle filter-dropdown-toggle dropdown-toggle" aria-expanded="false"><span><span>Beds</span></span>'
        . '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="arrow-down-icon"><path d="M13 5.5L8 10.5L3 5.5" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        . '</button></div>'
        . '<div class="vertical-divider ishide-mod"></div>'
        . '<div class="filter-dropdown price-filter-dropdown ishide-mod dropdown">'
        . '<button class="custom-dropdown-toggle filter-dropdown-toggle dropdown-toggle" aria-expanded="false"><span><span>Price Range</span></span>'
        . '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="arrow-down-icon"><path d="M13 5.5L8 10.5L3 5.5" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        . '</button></div>'
        . '</div>'
        . '<div class="search-cta-section"><a class="button button-orange" href="/buy/properties-for-sale/in-' . rawurlencode((string) ($a['slug'] ?? '')) . '/"><span>Search</span></a></div>'
        . '</div></div>'
        . '</div></div></div>';

    if ($content) {
        $out .= '<div class="area-info-wrap section-m"><div class="area-info-container container">'
            . '<div class="content-section"><p class="heading">about ' . esc($label) . '</p>'
            . '<div class="content">' . rich((string) $content) . '</div>'
            . '</div></div></div>';
    }

    if (count($sponsored)) {
        $out .= '<div class="container"><div class="offplan-card-wrap sponsor list-view">';
        foreach ($sponsored as $p) {
            $link = '/new-projects/' . rawurlencode((string) ($p['slug'] ?? '')) . '/';
            $bt = is_array($p['building_type'] ?? null) ? implode(', ', $p['building_type']) : (string) ($p['building_type'] ?? '');
            $img = $p['images']['464x312'] ?? $p['images']['340x252'] ?? null;
            $out .= '<div class="sponsor-card"><div class="img-section"><div class="flag-section">'
                . '<p class="img-tag"><span>Sponsored Project</span></p>';
            if ($bt) $out .= '<p class="img-tag tag-new"><span>' . esc($bt) . '</span></p>';
            $out .= '</div><a class="img-section listview-img-section" href="' . esc($link) . '">';
            if ($img) $out .= '<img loading="lazy" src="' . esc($img) . '" alt="' . esc((string) ($p['title'] ?? '')) . '" />';
            $out .= '</a></div><div class="content-section">'
                . '<a class="title" href="' . esc($link) . '">' . esc((string) ($p['title'] ?? '')) . '</a>'
                . '<div class="price"><span>Starting Price </span>' . (!empty($p['display_price']) ? 'AED ' . esc($p['display_price']) : '') . '</div>';
            if (!empty($p['display_address'])) $out .= '<p class="location">' . esc($p['display_address']) . '</p>';
            $out .= '</div></div>';
        }
        $out .= '</div></div>';
    }

    if (count($projects)) {
        $slides = [];
        foreach ($projects as $p) {
            $link = '/new-projects/' . rawurlencode((string) ($p['slug'] ?? '')) . '/';
            $bt = is_array($p['building_type'] ?? null) ? implode(', ', $p['building_type']) : (string) ($p['building_type'] ?? '');
            if (!$bt) $bt = 'Project';
            $img = $p['images']['464x312'] ?? $p['images']['340x252'] ?? null;
            $card = '<div class="offplan-card-wrap"><div class="img-section"><div class="flag-section">'
                . '<p class="img-tag"><span>' . esc($bt) . '</span></p></div>';
            if (!empty($p['completion_year'])) {
                $card .= '<div class="flag-section ready-flag"><p class="img-tag"><span>' . esc($p['completion_year']) . '</span></p></div>';
            }
            $card .= '<a href="' . esc($link) . '"><div class="img-section">';
            if ($img) $card .= '<img loading="lazy" src="' . esc($img) . '" alt="' . esc((string) ($p['title'] ?? '')) . '" />';
            $card .= '</div></a></div><div class="content-section">'
                . '<a class="title" href="' . esc($link) . '">' . esc((string) ($p['title'] ?? '')) . '</a>';
            if (!empty($p['developer'])) {
                $card .= '<a class="developer" href="/new-projects/developed-by-' . rawurlencode((string) $p['developer']) . '/">by <span>' . esc($p['developer']) . '</span></a>';
            }
            $card .= '<div class="price"><span>Starting Price </span>' . (!empty($p['display_price']) ? 'AED ' . esc($p['display_price']) : '') . '</div>'
                . '<div class="more-info">';
            if (!empty($p['display_address'])) $card .= '<p class="location">' . esc($p['display_address']) . '</p>';
            $card .= '</div></div></div>';
            $slides[] = $card;
        }
        $out .= '<div class="area-guide-featured-slider-tab-section"><div class="tab-body">'
            . slick_shell('', $slides, 4)
            . '</div></div>';
    }

    if (count($moreInfo)) {
        $out .= '<div class="areaguide-moreinfo-wrap section-p">' . faq_list($moreInfo, 'More About ' . $label) . '</div>';
    }
    $out .= '</div>';
    return $out;
}

/** CareerDetail port. */
function content_career_detail(array $c, string $route): string
{
    $details = $c['job_details']['data']['job_details'] ?? '';
    $out = '<div>';
    $out .= mobile_banner_menu(true);
    $out .= breadcrumbs_html(route_crumbs($route, (string) ($c['title'] ?? $c['slug'] ?? 'Page')));
    $out .= '<div class="career-info-wrap"><div class="career-info-container container"><div class="left-section">'
        . '<div class="banner-section"><h1 class="title"><span>' . esc((string) ($c['title'] ?? '')) . '</span></h1>';
    if (!empty($c['location'])) {
        $out .= '<p class="location">'
            . '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="location-icon"><path d="M10 7C10 8.10457 9.10457 9 8 9C6.89543 9 6 8.10457 6 7C6 5.89543 6.89543 5 8 5C9.10457 5 10 5.89543 10 7Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /><path d="M13 7C13 11.7614 8 14.5 8 14.5C8 14.5 3 11.7614 3 7C3 4.23858 5.23858 2 8 2C10.7614 2 13 4.23858 13 7Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
            . '<span>' . esc($c['location']) . '</span></p>';
    }
    $out .= '</div><div class="career-content">' . rich((string) $details) . '</div></div>'
        . '<div class="right-section"><div class="contact-section"><div class="cta-section">'
        . '<a class="button button-orange bottom-fix-career" href="/send-us-your-cv/"><span>Apply for this job</span></a>'
        . '</div></div></div></div></div></div>';
    return $out;
}

/** EventDetail port. */
function content_event_detail(array $e, string $route): string
{
    $desc = $e['description']['data']['description'] ?? '';
    $imgs = [];
    if (is_array($e['images'] ?? null)) {
        foreach ($e['images'] as $im) {
            $url = $im['image']['url'] ?? null;
            if ($url) $imgs[] = $url;
        }
    }
    $tb = is_array($e['tile_block'] ?? null) ? $e['tile_block'] : [];
    $tbDesc = $tb['description']['data']['description'] ?? '';
    $tbImg = $tb['image']['url'] ?? null;

    $out = '<div>';
    $out .= mobile_banner_menu(true);
    $out .= breadcrumbs_html(route_crumbs($route, (string) ($e['title'] ?? 'Event')));
    $out .= '<div class="event-reg page-layout"><div class="event-info"><div class="event-content-bk"><div class="content-section">'
        . '<h1 class="title">' . esc((string) ($e['title'] ?? '')) . '</h1>';
    if (!empty($e['date'])) $out .= '<p class="event-date">' . esc($e['date']) . '</p>';
    if (!empty($e['location'])) {
        $out .= '<p class="event-loc">'
            . '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="location-icon"><path d="M10 7C10 8.10457 9.10457 9 8 9C6.89543 9 6 8.10457 6 7C6 5.89543 6.89543 5 8 5C9.10457 5 10 5.89543 10 7Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /><path d="M13 7C13 11.7614 8 14.5 8 14.5C8 14.5 3 11.7614 3 7C3 4.23858 5.23858 2 8 2C10.7614 2 13 4.23858 13 7Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
            . '<span>' . esc($e['location']) . '</span></p>';
    }
    if ($desc) $out .= '<div class="description">' . rich((string) $desc) . '</div>';
    $out .= '</div></div></div>';
    if (count($imgs)) {
        $out .= '<div class="event-gallery-wrap"><div class="event-gallery-container container"><div class="row">';
        foreach ($imgs as $i => $src) {
            $out .= '<div class="col-xl-3 col-md-4 col-sm-6"><div class="img-zoom">'
                . '<img loading="lazy" src="' . esc(cft((string) $src, 360, 430)) . '" alt="' . esc((string) ($e['title'] ?? 'Event') . ' - ' . ($i + 1)) . '" />'
                . '</div></div>';
        }
        $out .= '</div></div></div>';
    }
    if ($tbDesc || $tbImg) {
        $out .= '<div class="tile-block-wrapper section-p"><div class="tile-block-container container"><div class="row align-items-center">';
        if ($tbImg) {
            $out .= '<div class="col-xl-6 col-lg-12"><div class="img-section">'
                . '<img loading="lazy" src="' . esc(cfw((string) $tbImg, 744)) . '" alt="' . esc((string) ($tb['title'] ?? $e['title'] ?? 'Event')) . '" />'
                . '</div></div>';
        }
        $out .= '<div class="col-xl-6 col-lg-12"><div class="content-section">';
        if (!empty($tb['title'])) $out .= '<h2 class="title">' . esc($tb['title']) . '</h2>';
        if ($tbDesc) $out .= '<div class="description">' . rich((string) $tbDesc) . '</div>';
        if (!empty($tb['cta']['cta_label'])) {
            $out .= '<a class="button button-orange" href="' . esc(cta_href($tb['cta'], '#register')) . '"><span>' . esc($tb['cta']['cta_label']) . '</span></a>';
        }
        $out .= '</div></div></div></div></div>';
    }
    $out .= '<div class="register-interest-section section-p" id="Event_Form">'
        . '<div class="register-interest-wrapper container"><div class="contact-form-wrapper  section-p">'
        . '<div class="contact-form-container  container">'
        . '<h2 class="title">Register Your Interest</h2>'
        . '<form class="custom-form team-contact-form Event_Form" action="#" method="post"><div class="form-grid"><div class="form-section">'
        . '<div class="input-box input-box-name"><label class="input-label" for="name">Full Name</label>'
        . '<input class="input-field" type="text" name="name" id="name" placeholder="Full Name" /></div>'
        . '<div class="input-box input-box-telephone"><label class="input-label" for="phone">Phone Number</label>'
        . '<input class="input-field" type="tel" name="phone" id="phone" placeholder="Phone Number" /></div>'
        . '<div class="input-box input-box-email"><label class="input-label" for="email">Email Address</label>'
        . '<input class="input-field" type="email" name="email" id="email" placeholder="Email Address" /></div>'
        . '<div class="input-box input-box-message"><label class="input-label" for="message">Message</label>'
        . '<textarea class="input-field input-textarea" name="message" id="message" placeholder="Message"></textarea></div>'
        . '</div></div><div class="form-bottom">'
        . '<button class="reg-btn button button-orange" type="submit"><span>Register Interest</span></button>'
        . '</div></form>'
        . '</div></div></div></div></div></div>';
    return $out;
}

/* ------------------------------ dispatch ------------------------------ */

/** ContentPages port. */
function content_pages_dispatch(array $d, string $route): string
{
    if (!empty($d['job_details']) || (!empty($d['title']) && !empty($d['location']))) {
        $isCareer = !empty($d['job_details']['data']['job_details']);
        if ($isCareer) return content_career_detail($d, $route);
    }
    if (!empty($d['designation'])) return content_team_detail($d, $route);
    if (!empty($d['more_info']) && (!empty($d['content']) || !empty($d['banner_image']))) return content_area_guide_detail($d, $route);
    if (!empty($d['images']) && !empty($d['tile_block'])) return content_event_detail($d, $route);
    if (!empty($d['tile_image']) && !empty($d['short_description'])) return content_blog_detail($d, $route);
    return content_strapi_page($d, $route);
}


<?php
// projects.php — project hub + project detail pages
// Port of src/components/projects.tsx + src/components/project-detail-ui.tsx

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';
require_once __DIR__ . '/../includes/render/listing-ui.php';
require_once __DIR__ . '/../includes/render/read-more.php';
require_once __DIR__ . '/../includes/render/modules.php';

$hits = [];
foreach (($model['data']['hits'] ?? []) as $h) {
    if (is_array($h) && !empty($h['slug'])) $hits[] = $h;
}
$content = $model['data']['content'] ?? null;
$routeBase = (string) ($model['route'] ?? '/new-projects');
$last = '';
foreach (array_values(array_filter(explode('/', $routeBase))) as $seg) $last = (string) $seg;
$isDetail = count($hits) <= 1 && isset($hits[0]) && (str_starts_with($last, 'in-') || $last === (string) ($hits[0]['slug'] ?? ''));

function project_dev_slug(?string $developer): string
{
    return strtolower(preg_replace('/[^a-z0-9]+/', '-', (string) $developer) ?? '');
}

function project_beds(array $h): string
{
    if (!empty($h['display_bedrooms'])) return (string) $h['display_bedrooms'];
    if (isset($h['min_bedrooms']) && $h['min_bedrooms'] !== null && isset($h['max_bedrooms']) && $h['max_bedrooms'] !== null) {
        return (string) $h['min_bedrooms'] . ' - ' . (string) $h['max_bedrooms'];
    }
    return '';
}

function project_img(array $h, string $key, array $pref): ?string
{
    $g = $h[$key] ?? null;
    if (!is_array($g)) return null;
    foreach ($g as $im) {
        if (!is_array($im)) continue;
        foreach ($pref as $k) {
            if (!empty($im[$k])) return (string) $im[$k];
        }
    }
    return null;
}

function project_gallery_urls(array $h): array
{
    $out = [];
    $groups = [$h['images'] ?? null, $h['images1'] ?? [], $h['images2'] ?? []];
    foreach ($groups as $g) {
        if (!is_array($g)) continue;
        foreach ($g as $im) {
            if (!is_array($im)) continue;
            $src = $im['696x520'] ?? $im['464x312'] ?? $im['340x252'] ?? null;
            if ($src) $out[] = (string) $src;
        }
    }
    return $out;
}

function register_interest_form(string $title): string
{
    return '<form class="contact-form" data-enquiry-form="project" data-project-title="' . esc($title) . '" novalidate>'
        . '<div class="input-section">'
        . '<input type="text" name="name" placeholder="Full Name" required />'
        . '<select name="dial" class="country-select" aria-label="Country code">' . countries_options('+971') . '</select>'
        . '<input type="tel" name="phone" placeholder="Phone Number" />'
        . '<input type="email" name="email" placeholder="Email Address" required />'
        . '</div>'
        . '<p class="success-msg" style="display:none">Thank you &#8212; one of our consultants will get back to you shortly.</p>'
        . '<p class="error-msg" style="display:none"></p>'
        . '<button class="button button-orange" type="submit"><span>Submit</span></button>'
        . '</form>';
}

function render_offplan_card(array $h): string
{
    $link = '/new-projects/' . esc($h['slug']) . '/';
    $allImages = [];
    foreach ([$h['images'] ?? null, $h['images2'] ?? [], $h['images1'] ?? []] as $g) {
        if (!is_array($g)) continue;
        foreach ($g as $im) {
            if (!is_array($im)) continue;
            $src = $im['340x252'] ?? $im['464x312'] ?? null;
            if ($src) $allImages[] = (string) $src;
        }
    }
    $bt = $h['building_type'] ?? 'Project';
    $btLabel = is_array($bt) ? implode(', ', $bt) : (string) $bt;
    $devSlug = project_dev_slug($h['developer'] ?? null);
    $beds = project_beds($h);
    $price = '';
    if (!empty($h['display_price'])) $price = 'AED ' . (string) $h['display_price'];
    elseif (!empty($h['price'])) $price = 'AED ' . number_format((float) $h['price']);
    $slides = '';
    foreach (array_slice($allImages, 0, 3) as $i => $src) {
        $slides .= '<div class="swiper-slide"><img loading="' . ($i === 0 ? 'eager' : 'lazy') . '" src="' . esc($src) . '" alt="' . esc($btLabel) . '" /></div>';
    }
    $out = '<div class="offplan-card-wrap">';
    $out .= '<div class="img-section ttf">';
    $out .= '<div class="flag-section"><p class="img-tag"><span>' . esc($btLabel) . '</span></p></div>';
    if (!empty($h['completion_year'])) {
        $out .= '<div class="flag-section ready-flag"><p class="img-tag"><span>' . esc((string) $h['completion_year']) . '</span></p></div>';
    }
    $out .= '<a href="' . $link . '">';
    $out .= '<div class="img-section"><div class="swiper"><div class="swiper-wrapper">' . $slides . '</div><div class="swiper-pagination"></div></div></div>';
    $out .= '</a></div>';
    $out .= '<div class="content-section">';
    $out .= '<a class="title" href="' . $link . '">' . esc((string) $h['title']) . '</a>';
    if (!empty($h['developer'])) {
        $out .= '<a class="developer" href="/new-projects/developed-by-' . esc($devSlug) . '/">by <span>' . esc((string) $h['developer']) . '</span></a>';
    }
    $out .= '<div class="price">';
    if (!empty($h['display_price'])) $out .= '<span>Starting Price </span>';
    $out .= esc($price) . '</div>';
    $out .= '<div class="more-info">';
    if (!empty($h['display_address'])) {
        $out .= '<p class="location"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 7C10 8.10457 9.10457 9 8 9C6.89543 9 6 8.10457 6 7C6 5.89543 6.89543 5 8 5C9.10457 5 10 5.89543 10 7Z" stroke="#9399A4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M13 7C13 11.7614 8 14.5 8 14.5C8 14.5 3 11.7614 3 7C3 4.23858 5.23858 2 8 2C10.7614 2 13 4.23858 13 7Z" stroke="#9399A4" stroke-linecap="round" stroke-linejoin="round"></path></svg><span>' . esc((string) $h['display_address']) . '</span></p>';
    }
    if ($beds !== '') {
        $out .= '<p class="beds"><svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M1.714 10.857c.631 0 1.143-.767 1.143-1.714s-.512-1.714-1.143-1.714S.57 7.196.57 8.143s.513 1.714 1.143 1.714ZM5.143 5.714c.631 0 1.143-.767 1.143-1.714S5.774 2.286 5.143 2.286 4 3.053 4 4s.512 1.714 1.143 1.714ZM10.857 5.714C11.488 5.714 12 4.947 12 4s-.512-1.714-1.143-1.714S9.714 3.053 9.714 4s.512 1.714 1.143 1.714ZM14.286 10.857c.63 0 1.143-.767 1.143-1.714s-.512-1.714-1.143-1.714-1.143.767-1.143 1.714.512 1.714 1.143 1.714ZM11.429 11.429c0 1.577-1.852 2.285-3.429 2.285-1.577 0-3.429-.708-3.429-2.285 0-1.578 1.143-4 3.429-4 2.286 0 3.429 2.422 3.429 4Z" fill="#07234B"></path></svg><span>' . esc($beds) . '</span></p>';
    }
    $out .= '</div></div></div>';
    return $out;
}

function project_banner_desktop(array $h): ?string
{
    $bi = $h['banner_image'] ?? null;
    if (is_array($bi) && isset($bi[0]) && is_array($bi[0])) {
        foreach (['1650x', '744x'] as $k) {
            if (!empty($bi[0][$k])) return (string) $bi[0][$k];
        }
    }
    return project_img($h, 'images', ['696x520', '464x312']);
}

function project_banner_mobile(array $h, ?string $fallback): ?string
{
    $bi = $h['banner_image'] ?? null;
    if (is_array($bi) && isset($bi[0]) && is_array($bi[0])) {
        foreach (['376x512', '1650x'] as $k) {
            if (!empty($bi[0][$k])) return (string) $bi[0][$k];
        }
    }
    $mobile = $h['banner_image_mobile'] ?? null;
    if (is_array($mobile) && isset($mobile[0]) && is_array($mobile[0]) && !empty($mobile[0]['376x512'])) {
        return (string) $mobile[0]['376x512'];
    }
    return project_img($h, 'images', ['464x312']);
}

function project_nav_bar(array $ids): string
{
    $out = '<div class="offplan-nav-bar-wrap offplan-nav-bar-wrap--top" data-project-nav-bar>'
        . '<div class="offplan-nav-bar-container container"><div class="nav-bar-list">';
    foreach ($ids as $s) {
        $out .= '<a href="#' . esc($s['id']) . '" class="nav-bar-item" data-project-nav-id="' . esc($s['id']) . '">' . esc($s['label']) . '</a>';
    }
    $out .= '</div></div></div>';
    return $out;
}

function project_gallery_block(array $images, string $title): string
{
    if (!count($images)) return '';
    $list = array_slice($images, 0, 6);
    $grid = '';
    foreach ($list as $i => $src) {
        $grid .= '<div class="image-item img-zoom" data-proj-open="' . $i . '" role="button" tabindex="0">'
            . '<img loading="lazy" src="' . esc($src) . '" alt="' . esc($title) . ' - image ' . ($i + 1) . '" /></div>';
    }
    $actions = '';
    if (count($list) > 0) {
        $actions = '<div class="gallery-actions">'
            . '<a class="button button-gray brochure-button" href="' . esc($list[0]) . '" target="_blank" rel="noopener noreferrer">Download 4K Images</a>'
            . '<button type="button" class="button button-white all-image-button" data-proj-open="0">All Images</button></div>';
    }
    $slides = '';
    foreach ($list as $i => $src) {
        $slides .= '<img class="proj-lightbox-slide' . ($i === 0 ? ' active' : '') . '" src="' . esc($src) . '" alt="' . esc($title) . ' - image ' . ($i + 1) . '" />';
    }
    return '<div class="offplan-images-wrap section-l-m" id="offplan-gallery">'
        . '<div class="offplan-images-container container"><div class="images-grid-wrap">'
        . '<div class="images-grid">' . $grid . '</div>' . $actions
        . '</div></div>'
        . '<div class="gallery-lightbox" data-proj-lightbox role="dialog" aria-modal="true" hidden>'
        . '<button type="button" class="lightbox-close" data-proj-close aria-label="Close">&times;</button>'
        . '<button type="button" class="lightbox-nav lightbox-prev" data-proj-prev aria-label="Previous">&#8249;</button>'
        . '<div class="proj-lightbox-slides">' . $slides . '</div>'
        . '<button type="button" class="lightbox-nav lightbox-next" data-proj-next aria-label="Next">&#8250;</button>'
        . '</div></div>';
}

function project_simple_detail(array $hit, string $route): string
{
    $title = (string) ($hit['title'] ?? '');
    $devSlug = project_dev_slug($hit['developer'] ?? null);
    $beds = project_beds($hit);
    $bannerMobile = project_banner_mobile($hit, null);
    $bannerDesktop = project_banner_desktop($hit);
    $features = is_array($hit['features'] ?? null) ? $hit['features'] : [];
    $amenities = is_array($hit['amenities'] ?? null) ? $hit['amenities'] : [];
    $allProps = array_merge($features, $amenities);
    $gallery = project_gallery_urls($hit);

    $out = '<div>';
    $out .= '<div class="offplan-banner-wrap">';
    if ($bannerMobile) {
        $out .= '<div class="bg-section d-block d-lg-none"><div class="overlay"></div><img loading="eager" src="' . esc($bannerMobile) . '" alt="' . esc($title) . '" /></div>';
    }
    if ($bannerDesktop) {
        $out .= '<div class="bg-section d-none d-lg-block"><div class="overlay"></div><img loading="eager" src="' . esc($bannerDesktop) . '" alt="' . esc($title) . '" /></div>';
    }
    $out .= '<div class="mobile-banner-menu undefined"><div class="scroll-i d-flex d-md-none">';
    foreach ([['About', 'about'], ['Images', 'images'], ['Features', 'features'], ['Location', 'location'], ['Payment Plan', 'payment'], ['FAQ', 'faq']] as $i => $pair) {
        $out .= '<a class="main-menu" href="#' . esc($pair[1]) . '"><span>' . esc($pair[0]) . '</span></a>';
    }
    $out .= '</div></div>';
    $out .= '<div class="offplan-banner-container container"><div class="offplan-banner-section">';
    $out .= '<div class="content-section">';
    $out .= '<h1 class="title">' . esc($title) . '</h1>';
    if (!empty($hit['developer'])) {
        $out .= '<a class="developer" href="/new-projects/developed-by-' . esc($devSlug) . '/">by <span>' . esc((string) $hit['developer']) . '</span></a>';
    }
    if (!empty($hit['display_address'])) $out .= '<p class="location">' . esc((string) $hit['display_address']) . '</p>';
    $out .= '</div>';
    $out .= '<div class="cta-section">';
    if (!empty($hit['display_price'])) $out .= '<p class="price">AED ' . esc((string) $hit['display_price']) . '</p>';
    $out .= '<a class="button button-orange" href="#register"><span>Register Interest</span></a>';
    $out .= '<a class="button button-white" href="tel:+971568308221"><span>&#127482;&#127480; Call Us</span></a>';
    $out .= '</div></div></div>';
    $out .= '<div class="breadcrumbs-wrap white-color"><div class="breadcrumbs-container container"><nav class="breadcrumbs"><ol class="breadcrumb">';
    $out .= '<li class="enable-link-home breadcrumb-item"><a class="breadcrumb-link enable-link" href="/">Home</a></li>';
    $out .= '<li class="breadcrumb-item"><a class="breadcrumb-link enable-link" href="/new-projects/">Off-Plan Projects</a></li>';
    $out .= '<li class="breadcrumb-item active"><a aria-current="page" class="breadcrumb-link disable-link" href="' . esc($route . '/') . '">' . esc($title) . '</a></li>';
    $out .= '</ol></nav></div></div></div>';

    $out .= '<div class="about-offplan-wrap new section-l-m" id="about"><div class="about-offplan-container container"><div class="row">';
    $out .= '<div class="col-xl-8 col-lg-12"><div class="left-section">';
    $out .= '<h2 class="title">About ' . esc($title) . '</h2>';
    $out .= '<div class="description">' . ($hit['about'] ?? '') . '</div>';
    $out .= '</div></div>';
    $out .= '<div class="col-xl-4 col-lg-12"><div class="right-section">';
    $price = !empty($hit['display_price']) ? 'AED ' . (string) $hit['display_price'] : (!empty($hit['price']) ? 'AED ' . number_format((float) $hit['price']) : '&#8212;');
    $out .= '<div class="item-wrap"><p class="label">Starting Price</p><p class="value">' . $price . '</p></div>';
    if ($beds !== '') $out .= '<div class="item-wrap"><p class="label">Bedrooms</p><p class="value">' . esc($beds) . '</p></div>';
    if (!empty($hit['completion_year'])) $out .= '<div class="item-wrap"><p class="label">Completion</p><p class="value">' . esc((string) $hit['completion_year']) . '</p></div>';
    if (!empty($hit['status'])) $out .= '<div class="item-wrap"><p class="label">Status</p><p class="value">' . esc(str_replace('-', ' ', (string) $hit['status'])) . '</p></div>';
    $out .= '</div></div></div></div></div>';

    if (count($gallery) > 1) $out .= project_gallery_block($gallery, $title);

    if (count($allProps) > 0) {
        $items = '';
        foreach ($allProps as $f) {
            $name = is_string($f) ? $f : (string) ($f['name'] ?? $f['title'] ?? '');
            $items .= '<div class="feature-item"><span>' . esc($name) . '</span></div>';
        }
        $out .= '<div class="tile-block-wrapper tile-blue-bg section-l-p characteristics-module blue" id="features">'
            . '<div class="tile-block-container container">'
            . '<div class="img-section"><div><h3 class="title">Features &amp; Amenities</h3></div></div>'
            . '<div class="content-section"><div class="description"><div class="features-grid">' . $items . '</div></div></div>'
            . '</div></div>';
    }

    $out .= '<div class="register-interest-module-wrap section-l-p" id="register"><div class="register-interest-module-container container"><div class="row">';
    $out .= '<div class="col-xl-6 col-lg-12"><div class="content-section">';
    $out .= '<h2 class="title">Register Your Interest</h2>';
    $out .= '<p class="description">Be the first to know about payment plans, availability and launch offers for ' . esc($title) . '.</p>';
    $out .= '</div></div>';
    $out .= '<div class="col-xl-6 col-lg-12">' . register_interest_form($title) . '</div>';
    $out .= '</div></div></div>';
    $out .= '</div>';
    return $out;
}

function project_feature_list(array $items): string
{
    $out = '<ul class="place-list">';
    foreach ($items as $p) {
        if (!is_array($p)) continue;
        $out .= '<li><span class="place-name">' . esc((string) ($p['place_name'] ?? '')) . '</span><span class="place-time">' . esc((string) ($p['time_distance'] ?? '')) . '</span></li>';
    }
    return $out . '</ul>';
}

function project_tile(array $tile, string $title, string $id = ''): string
{
    $out = '<div class="tile-block-wrapper tile-blue-bg section-l-p characteristics-module blue"' . ($id ? ' id="' . esc($id) . '"' : '') . '>';
    $out .= '<div class="tile-block-container container">';
    if (!empty($tile['image']['url'])) {
        $out .= '<div class="img-section"><img loading="lazy" src="' . esc($tile['image']['url']) . '" alt="' . esc($tile['title'] ?? $title) . '" /></div>';
    }
    $out .= '<div class="content-section">';
    if (!empty($tile['heading'])) $out .= '<p class="heading">' . esc($tile['heading']) . '</p>';
    if (!empty($tile['title'])) $out .= '<h3 class="title">' . esc($tile['title']) . '</h3>';
    if (!empty($tile['description'])) $out .= '<div class="description">' . $tile['description'] . '</div>';
    if (!empty($tile['add_place']) && is_array($tile['add_place']) && count($tile['add_place'])) $out .= project_feature_list($tile['add_place']);
    if (!empty($tile['cta']['cta_label'])) {
        $out .= '<a class="button button-white-outline" href="' . esc((string) ($tile['cta']['custom_link'] ?? '#register-interest')) . '"><span>' . esc($tile['cta']['cta_label']) . '</span>'
            . '<svg class="arrow-right-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg></a>';
    }
    $out .= '</div></div></div>';
    return $out;
}

function project_amenity_slider(array $items): string
{
    $cards = '';
    foreach ($items as $a) {
        if (!is_array($a)) continue;
        $text = (string) ($a['text'] ?? $a['name'] ?? '');
        $img = (string) ($a['image']['url'] ?? '');
        $cards .= '<div class="amenity-card"><div class="img-section">';
        if ($img) $cards .= '<img loading="lazy" src="' . esc($img) . '" alt="' . esc($text) . '" />';
        $cards .= '</div><p class="name">' . esc($text) . '</p></div>';
    }
    return '<div class="slider-module-wrap amenities-slider-wrap section-p">'
        . '<div class="slider-module-container container" id="offplan-amenities-slider">'
        . '<div class="top-section"><h2 class="title">Amenities</h2>'
        . '<div class="slider-arrow-btn-section">'
        . '<button type="button" class="arrow-btn button button-white prev disabled" data-amenity-prev aria-label="Previous"><svg class="arrow-left-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>'
        . '<button type="button" class="arrow-btn button button-white next" data-amenity-next aria-label="Next"><svg class="arrow-right-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg></button>'
        . '</div></div>'
        . '<div class="slider-section"><div class="amenity-track" data-amenity-track>' . $cards . '</div></div>'
        . '</div></div>';
}

function project_floorplans(array $plans): string
{
    $buttons = '';
    $active = $plans[0] ?? null;
    foreach ($plans as $i => $p) {
        $buttons .= '<button type="button" class="floorplan-item-wrap' . ($i === 0 ? ' selected' : '') . '" data-floorplan-sel="' . $i . '" data-floorplan-media="' . esc((string) ($p['media'] ?? '')) . '">'
            . '<div class="floorplan-item"><div class="content">'
            . '<p class="title">' . esc((string) ($p['title'] ?? 'Floor Plan')) . '</p>'
            . (!empty($p['size']) ? '<p class="size">' . esc((string) $p['size']) . '</p>' : '')
            . '</div><svg class="arrow-right-icon" width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="m9 6 6 6-6 6" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"></path></svg></div></button>';
    }
    $media = (string) ($active['media'] ?? '');
    $out = '<div class="floorplans-wrap old section-m"><div class="floorplans-container container" id="floor-plans">';
    $out .= '<h2 class="title">Floorplans</h2><div class="floorplan-grid">';
    $out .= '<div class="left-section"><div class="floorplan-section">' . $buttons . '</div>';
    if ($media) $out .= '<a class="button button-gray" href="' . esc($media) . '" target="_blank" rel="noopener noreferrer">Download Floorplans</a>';
    $out .= '</div>';
    $out .= '<div class="img-section img-zoom">';
    if ($media) $out .= '<img loading="lazy" src="' . esc($media) . '" alt="' . esc((string) ($active['title'] ?? 'Floor plan')) . '" data-floorplan-img />';
    $out .= '</div></div></div></div>';
    return $out;
}

function project_faq_accordion(array $faqs, string $title): string
{
    if (!count($faqs)) return '';
    $items = '';
    foreach ($faqs as $i => $f) {
        $items .= '<div class="accordion-item' . ($i === 0 ? ' open' : '') . '">'
            . '<div class="accordion-header">'
            . '<button type="button" class="accordion-button' . ($i === 0 ? '' : ' collapsed') . '" aria-expanded="' . ($i === 0 ? 'true' : 'false') . '">' . esc((string) ($f['question'] ?? '')) . '</button>'
            . '</div>'
            . '<div class="accordion-collapse collapse' . ($i === 0 ? ' show' : '') . '"><div class="accordion-body">' . ($f['answer'] ?? '') . '</div></div>'
            . '</div>';
    }
    return '<div class="areaguide-moreinfo-wrap section-p offplan">'
        . '<div class="faq-section areaguide-accordian-section container faq-list">'
        . '<h2 class="title">Useful Information about ' . esc($title) . '</h2>'
        . '<div class="accordion">' . $items . '</div></div></div>';
}

function project_live_detail(array $hit, array $detail, string $route): string
{
    $title = (string) ($detail['title'] ?? $hit['title'] ?? '');
    $developer = (string) ($detail['developer'] ?? $hit['developer'] ?? '');
    $devSlug = project_dev_slug($developer);
    $bannerDesktop = (string) ($detail['banner_image']['url'] ?? project_banner_desktop($hit) ?? '');
    $bannerMobile = (string) ($detail['banner_image_mobile']['url'] ?? $detail['banner_image']['url'] ?? project_banner_mobile($hit, $bannerDesktop) ?? $bannerDesktop);
    if (!empty($detail['display_price'])) $displayPrice = 'AED ' . (string) $detail['display_price'];
    elseif (!empty($hit['display_price'])) $displayPrice = 'AED ' . (string) $hit['display_price'];
    elseif (!empty($detail['price'])) $displayPrice = 'AED ' . number_format((float) $detail['price']);
    else $displayPrice = '';

    $gallery = [];
    foreach (($detail['media_images'] ?? []) as $im) {
        if (is_array($im) && !empty($im['url'])) $gallery[] = (string) $im['url'];
    }
    if (!count($gallery)) $gallery = project_gallery_urls($hit);

    $plans = [];
    foreach (($detail['floor_plans'] ?? []) as $p) {
        if (!is_array($p)) continue;
        $plans[] = [
            'title' => (string) ($p['title'] ?? ''),
            'size' => (string) ($p['size'] ?? ''),
            'media' => (string) ($p['media']['url'] ?? $p['url'] ?? ''),
        ];
    }
    $amenities = [];
    foreach (($detail['amenities'] ?? []) as $a) {
        if (!is_array($a)) continue;
        $amenities[] = ['text' => (string) ($a['text'] ?? ''), 'image' => (string) ($a['image']['url'] ?? '')];
    }
    $faqs = [];
    foreach (($detail['more_info'] ?? []) as $f) {
        if (!is_array($f)) continue;
        $faqs[] = ['question' => (string) ($f['question'] ?? ''), 'answer' => (string) ($f['answer'] ?? '')];
    }
    $usp = is_array($detail['characteristics_module'] ?? null) ? $detail['characteristics_module'] : null;
    $loc = is_array($detail['location_tile'] ?? null) ? $detail['location_tile'] : null;
    $brochure = is_array($detail['brochure'] ?? null) ? $detail['brochure'] : null;
    $paymentPlans = [];
    foreach (($detail['add_plan'] ?? []) as $g) {
        if (!is_array($g) || !is_array($g['add_single_plan'] ?? null)) continue;
        foreach ($g['add_single_plan'] as $p) {
            if (!is_array($p)) continue;
            $paymentPlans[] = ['title' => (string) ($p['title'] ?? ''), 'description' => (string) ($p['description'] ?? '')];
        }
    }
    $videoUrl = (string) ($detail['video_module']['video_url'] ?? '');
    $whatsapp = 'https://wa.provident.ae/inquire?phone=971505390249';
    $tel = 'tel:+971505390249';
    $navIds = [
        ['label' => 'Details', 'id' => 'offplan-details'],
        ['label' => 'Gallery', 'id' => 'offplan-gallery'],
        ['label' => 'Floor Plans', 'id' => 'floor-plans'],
        ['label' => 'Amenities', 'id' => 'offplan-amenities-slider'],
        ['label' => 'Location', 'id' => 'offplan-location'],
        ['label' => 'Brochure', 'id' => 'offplan-brochure'],
    ];

    $out = '<div class="offplan-detail-page">';
    $out .= '<div class="offplan-banner-wrap">';
    if ($bannerMobile) $out .= '<div class="bg-section d-block d-lg-none"><div class="overlay"></div><img loading="eager" src="' . esc($bannerMobile) . '" alt="' . esc($title) . '" /></div>';
    if ($bannerDesktop) $out .= '<div class="bg-section d-none d-lg-block"><div class="overlay"></div><img loading="eager" src="' . esc($bannerDesktop) . '" alt="' . esc($title) . '" /></div>';
    $out .= '<div class="offplan-banner-container container"><div class="offplan-banner-section">';
    $out .= '<div class="content-section"><h1>' . esc($title) . '</h1>';
    if ($developer !== '') $out .= '<a class="developer" href="/new-projects/developed-by-' . esc($devSlug) . '/">by <span>' . esc($developer) . '</span></a>';
    $out .= '</div><div class="cta-section">';
    if (!empty($brochure['file']['url'])) {
        $out .= '<a class="button button-orange trigger-button" href="' . esc($brochure['file']['url']) . '" target="_blank" rel="noopener noreferrer"><span>Download Brochure</span></a>';
    }
    $out .= '<a class="button button-gray trigger-button" href="#register-interest"><span>Register Interest</span></a>';
    $out .= '</div></div></div>';
    $out .= '<div class="breadcrumbs-wrap white-color"><div class="breadcrumbs-container container"><nav class="breadcrumbs"><ol class="breadcrumb">';
    $out .= '<li class="breadcrumb-item"><a class="breadcrumb-link enable-link" href="/">Home</a></li>';
    $out .= '<li class="breadcrumb-item"><a class="breadcrumb-link enable-link" href="/new-projects/">All Projects in Dubai</a></li>';
    $out .= '<li class="breadcrumb-item active"><a aria-current="page" class="breadcrumb-link disable-link" href="' . esc($route . '/') . '">' . esc($title) . '</a></li>';
    $out .= '</ol></nav></div></div></div>';

    $out .= project_nav_bar($navIds);

    $out .= '<div class="about-offplan-wrap old section-l-m" id="offplan-details"><div class="about-offplan-container container">';
    $out .= '<div class="left-section"><p class="heading">About the project</p><div class="content">' . ($detail['about'] ?? $hit['about'] ?? '') . '</div></div>';
    $out .= '<div class="right-section">';
    if ($displayPrice !== '') $out .= '<div class="item-wrap"><p>Starting Price</p><p class="value">' . esc($displayPrice) . '</p></div>';
    $cy = $detail['completion_year'] ?? $hit['completion_year'] ?? null;
    if ($cy) $out .= '<div class="item-wrap"><p>Handover</p><p class="value">' . esc((string) $cy) . '</p></div>';
    if (!empty($detail['payment_plan_text'])) $out .= '<div class="item-wrap"><p>Payment Plan</p><p class="value">' . esc((string) $detail['payment_plan_text']) . '</p></div>';
    $out .= '</div></div></div>';

    $out .= project_gallery_block($gallery, $title);

    if ($usp) $out .= project_tile($usp, $title);

    if (count($amenities)) $out .= project_amenity_slider($amenities);

    if (count($plans)) $out .= project_floorplans($plans);

    if ($loc) $out .= project_tile($loc, $title, 'offplan-location');

    if (count($paymentPlans)) {
        $items = '';
        foreach ($paymentPlans as $p) {
            $items .= '<div class="plan-item"><p class="plan-title">' . esc($p['title']) . '</p><p class="plan-description">' . esc($p['description']) . '</p></div>';
        }
        $out .= '<div class="payment-plans-wrap old section-l-m" id="payment-plans"><div class="payment-plans-container container">'
            . '<div class="left-section"><h2 class="title">Payment Plan</h2><div class="payment-plans-section">' . $items . '</div></div>'
            . '</div></div>';
    }

    if ($brochure) {
        $out .= '<div class="offplan-brochure-wrap section-l-m" id="offplan-brochure"><div class="offplan-brochure-container container">';
        $out .= '<div class="left-section"><h2 class="title">Project Brochure</h2><p class="description">All you need to know about ' . esc($title) . '</p>';
        if (!empty($brochure['file']['url'])) {
            $out .= '<a class="button button-orange trigger-button" href="' . esc($brochure['file']['url']) . '" target="_blank" rel="noopener noreferrer"><span>Download Brochure</span></a>';
        }
        $out .= '<p class="text">Get the brochure in less than 10 seconds.</p></div>';
        if (!empty($brochure['image']['url'])) $out .= '<div class="right-section"><img loading="lazy" src="' . esc($brochure['image']['url']) . '" alt="' . esc($title) . ' brochure" /></div>';
        $out .= '</div></div>';
    }

    if ($videoUrl) {
        $poster = (string) ($detail['video_module']['thumbnail']['url'] ?? '');
        $out .= '<div id="offplan-video" class="video-banner-container section-l-m container"><video controls preload="metadata"' . ($poster ? ' poster="' . esc($poster) . '"' : '') . '><source src="' . esc($videoUrl) . '" /></video></div>';
    }

    $out .= '<div class="register-interest-module-wrap old section-l-p" id="register-interest"><div class="bg-section"><div class="overlay"></div>';
    $ads = (string) ($detail['ads_image']['url'] ?? $detail['banner_image']['url'] ?? '');
    if ($ads) $out .= '<img loading="lazy" src="' . esc($ads) . '" alt="" />';
    $out .= '</div><div class="register-interest-module-container container"><div class="row">';
    $out .= '<div class="col-xl-6 col-lg-12"><div class="left-section">';
    $out .= '<h2 class="title">Begin Your Property Journey with Us</h2>';
    $out .= '<p class="description">Discover more about ' . esc($title) . ' and how it fits your lifestyle and investment goals. Our property specialists are ready to help.</p>';
    $out .= '<ul><li>Personalised guidance from our expert team</li><li>Latest availability, prices and payment plans</li><li>Site visits and private viewings</li></ul>';
    $out .= '<a class="property-cta" href="' . $tel . '">&#127482;&#127480; Request a Call Back Now</a>';
    $out .= '<a class="button whatsapp-icon-btn button-white-outline" href="' . $whatsapp . '" target="_blank" rel="noopener noreferrer"><span>Chat with us now</span></a>';
    $out .= '</div></div>';
    $out .= '<div class="col-xl-6 col-lg-12">' . register_interest_form($title) . '</div>';
    $out .= '</div></div></div>';

    $out .= project_faq_accordion($faqs, $title);
    $out .= '</div>';
    return $out;
}

if ($isDetail && isset($hits[0])) {
    $detail = project_detail_by_slug((string) $hits[0]['slug']);
    $page_title = (string) ($hits[0]['title'] ?? '');
    $body = $detail ? project_live_detail($hits[0], $detail, $routeBase) : project_simple_detail($hits[0], $routeBase);
} else {
    $page_title = is_array($content) && !empty($content['title']) ? (string) $content['title'] : 'Off-Plan Projects in Dubai';
    $count = $model['data']['nbHits'] ?? count($hits);

    $typeLinks = [
        ['label' => 'All Types', 'href' => '/new-projects/'],
        ['label' => 'Apartment', 'href' => '/new-projects/type-apartment/'],
        ['label' => 'Villa', 'href' => '/new-projects/type-villa/'],
        ['label' => 'Townhouse', 'href' => '/new-projects/type-townhouse/'],
        ['label' => 'Penthouse', 'href' => '/new-projects/type-penthouse/'],
    ];
    $areaLinks = [['label' => 'All Areas', 'href' => '/new-projects/']];
    foreach (array_slice(communities(), 0, 20) as $c) {
        $areaLinks[] = ['label' => $c['label'], 'href' => '/new-projects/in-' . $c['slug'] . '/'];
    }
    $completionLinks = [
        ['label' => 'All', 'href' => '/new-projects/'],
        ['label' => 'Ready', 'href' => '/new-projects/completion-ready/'],
        ['label' => 'Under Construction', 'href' => '/new-projects/completion-under-construction/'],
    ];

    $cards = '';
    if (count($hits)) {
        foreach ($hits as $h) $cards .= render_offplan_card($h);
    } else {
        $cards = '<p class="no-results">No projects found for this search.</p>';
    }

    $body = '<div class="offplan-results-wrap min-vh-100">'
        . '<div class="bg-section-gradient"><div class="search-filters-section"><div class="search-filters-container container">'
        . '<div class="mutil-select-wrap"><div class="multi-select-input" id="multi-select-input"><div class="filter search-box">'
        . '<svg class="search-icon" width="17" height="16" viewBox="0 0 17 16" fill="none"><circle cx="7" cy="7" r="5.25" stroke="#07234B" stroke-width="1.2"></circle><path d="m11 11 4.5 4.5" stroke="#07234B" stroke-width="1.2" stroke-linecap="round"></path></svg>'
        . '<div class="autosuggest__container"><input id="search-input-field" type="text" placeholder="Area, project or community" class="autosuggest__input" autocomplete="off" /></div>'
        . '</div></div></div>'
        . '<div class="filters-section d-none d-xl-flex">'
        . type_select($typeLinks, 'All Types')
        . filter_dropdown('Area', $areaLinks)
        . filter_dropdown('Type', $typeLinks)
        . filter_dropdown('Completion', $completionLinks)
        . '</div>'
        . '</div></div></div>'
        . '<div class="property-breadcrumb-wrap"><div class="breadcrumbs-wrap"><div class="breadcrumbs-container container"><nav class="breadcrumbs"><ol class="breadcrumb">'
        . '<li class="enable-link-home breadcrumb-item"><a class="breadcrumb-link enable-link" href="/">Home</a></li>'
        . '<li class="breadcrumb-item active"><a aria-current="page" class="breadcrumb-link disable-link" href="/new-projects/">Off-Plan Projects</a></li>'
        . '</ol></nav></div></div></div>'
        . '<div class="search-results-section offplan-results-section">'
        . '<div class="info-map-sort-wrap container"><div class="info-map-sort-section"><div class="bottom-section">'
        . '<div class="fit-bk-search"><div class="h1-section"><h1>' . esc($page_title) . '</h1></div>'
        . '<p class="info d-none d-xl-block"><span>' . number_format((int) $count) . '</span> projects</p></div>'
        . '<div class="map-sort-section"><div class="d-block d-xl-none info"><span>' . number_format((int) $count) . '</span> projects</div>'
        . '<div class="sort-divider"></div><div class="d-flex align-items-center"><div class="sort-dropdown dropdown"><div class="sort-field">'
        . '<svg width="16" height="16" class="sort-icon" viewBox="0 0 16 16" fill="none"><path d="M3 4h10M6 8h4M8.5 12h-1" stroke="#07234B" stroke-linecap="round"></path></svg><span>Sort By</span>'
        . '</div></div></div></div></div></div></div>'
        . '<div class="property-list-container container"><div class="property-list-section isoffplan">' . $cards . '</div></div>'
        . (is_array($content) && !empty($content['description'])
            ? '<div class="text-copy-wrap section-p"><div class="text-copy-container container">' . read_more($content['description'], 4, 'description') . '</div></div>'
            : '')
        . '<div class="qes-bk com"><div class="container">' . module_questionnaire(['title' => 'Confused About Where to Buy or Invest in Dubai?']) . '</div></div>'
        . '</div></div>';
}

$page_description = 'Off-plan projects and new developments in Dubai by Zoya Ventures Real Estate.';
?>
<!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<?php require __DIR__ . '/../includes/header.php'; ?>
<main>
<?= $body ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php render_site_footer_scripts(); ?>
<script src="/assets/js/project-ui.js" defer></script>
</body>
</html>
<?php
// home.php — HomePage port (src/components/home.tsx + search-hero.tsx), static SSR.
// Interactive search (tabs/autosuggest/filters) is wired by assets/js/content-ui.js.

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/render/modules.php';
require_once __DIR__ . '/../includes/head.php';

$transparent = true;
$page_title = '';
$hj = load_json('pages/index.json')['result']['data']['strapiPage'] ?? [];
$banner = is_array($hj['banner'] ?? null) ? $hj['banner'] : [];
$title = $banner['title'] ?? 'Find your home in Dubai.';

$desc = $banner['description']['data']['description'] ?? null;
if ($desc) {
    $review = str_replace(['<p>', '</p>'], '', $desc);
    $review = str_replace('&nbsp;', "\xC2\xA0", $review);
    $review = str_replace("\xC3\x82\xC2\xB7", "\xC2\xB7", $review);
    $review = str_replace("\xEF\xBF\xBD", "\xC2\xB7", $review);
} else {
    $review = '4,000 listings &nbsp;&middot;&nbsp;400+ agents &nbsp;&middot;&nbsp;Serving 80+ communities';
}

$homeLinks = [
    ['Buy', '/buy/properties-for-sale/'],
    ['Rent', '/let/properties-for-rent/'],
    ['Projects', '/new-projects/'],
    ['Developers ', '/developers/'],
    ['Areas', '/area-guides/'],
    ['Services', '/property-services/'],
    ['Blogs', '/blog/'],
];

$mods = [];
foreach (($hj['modules'] ?? []) as $m) {
    if (!is_array($m)) continue;
    if (($m['strapi_component'] ?? '') === 'modules.ads-banner') continue;
    if (($m['strapi_component'] ?? '') === 'modules.global-module' && ($m['choose_module'] ?? '') === 'contact_module') continue;
    $mods[] = $m;
}

$body = '<div>'
    . '<div class="home-banner"><div class="banner-wrap banner-landing-wrap">'
    . '<div class="mobile-banner-menu"><div class="scroll-i d-flex d-md-none">';
foreach ($homeLinks as $l) {
    $body .= '<a class="main-menu" href="' . esc($l[1]) . '"><span>' . esc($l[0]) . '</span></a>';
}
$body .= '</div></div>'
    . '<div class="container"><div class="bg-section">'
    . '<video poster="/images/video-thumbnail.webp" class="home-banner-video active" src="/media/hero.mp4" preload="auto" playsinline loop muted autoplay></video>'
    . '<div><div class="d-block d-md-none"><div class="gatsby-image-wrapper home-banner-video">'
    . '<div aria-hidden="true" style="width:100%;padding-bottom:138.29787234042556%"></div>'
    . '<img aria-hidden="true" alt="" src="/images/video-thumbnail.webp" style="position:absolute;inset:0;box-sizing:border-box;padding:0;border:none;margin:auto;display:block;width:0;height:0;min-width:100%;max-width:100%;min-height:100%;max-height:100%" />'
    . '</div></div>'
    . '<div class="d-none d-md-block"><div class="gatsby-image-wrapper home-banner-video">'
    . '<div aria-hidden="true" style="width:100%;padding-bottom:56.25%"></div>'
    . '<img aria-hidden="true" alt="" src="/images/video-thumbnail.webp" style="position:absolute;inset:0;box-sizing:border-box;padding:0;border:none;margin:auto;display:block;width:0;height:0;min-width:100%;max-width:100%;min-height:100%;max-height:100%" />'
    . '</div></div></div></div>'
    . '<div class=""><div class="banner-container container">'
    . '<div class="brand-bx"><h1 class="title">' . esc($title) . '</h1></div>'
    . '<div class="modal-filter-item buy-rent-tab" data-hero-search>'
    . '<script type="application/json" data-hero-areas>' . json_encode(array_values(array_map(fn ($c) => $c['label'] ?? '', communities())), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>'
    . '<div class="filter-tabs tab-header">'
    . '<button class="tab-button selected-tab" type="button" data-hero-tab="0">Buy</button>'
    . '<button class="tab-button" type="button" data-hero-tab="1">Rent</button>'
    . '<button class="tab-button" type="button" data-hero-tab="2">Off Plan</button>'
    . '</div>'
    . '<div class="search-box-wrap"><div class="search-box-container"><div class="search-filter">'
    . '<div class="mutil-select-wrap"><div class="multi-select-input" id="multi-select-input"><div class="filter search-box">'
    . '<svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none" class="search-icon"><path d="M14.5 14L11.0355 10.5355M11.0355 10.5355C11.9404 9.63071 12.5 8.38071 12.5 7C12.5 4.23858 10.2614 2 7.5 2C4.73858 2 2.5 4.23858 2.5 7C2.5 9.76142 4.73858 12 7.5 12C8.88071 12 10.1307 11.4404 11.0355 10.5355Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
    . '<div class="autosuggest__container">'
    . '<input id="search-input-field" type="text" placeholder="Area, project or community" class="autosuggest__input" autocomplete="off" value="" data-hero-input />'
    . '<div class="autosuggest__suggestions-container" data-hero-suggestions style="display:none"><div class="autosuggest__suggestions-list"></div></div>'
    . '</div></div></div></div>'
    . '<div class="filter-dropdown bedroom-filter-dropdown ishide-mod dropdown">'
    . '<button type="button" class="custom-dropdown-toggle filter-dropdown-toggle dropdown-toggle" aria-expanded="false" data-hero-dropdown="beds"><span><span>Beds</span></span>'
    . '<svg class="arrow-down-icon" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13 5.5L8 10.5L3 5.5" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" /></svg>'
    . '</button>'
    . '<div class="dropdown-menu filter-dropdown-menu show" data-hero-beds-menu style="display:none"><div class="custom-dropdown-menu">'
    . '<div class="menu-item-wrap"><p class="label" style="color:#35373C;font-size:12px;font-weight:400;letter-spacing:0.12px;line-height:19.2px;white-space:nowrap;margin:0">Min Bedrooms</p>'
    . '<div class="react-select-wrap filter-select min-bedroom-select" data-hero-select="minBed"><div class="react-select" style="position:relative">'
    . '<button type="button" class="react-select__control" aria-haspopup="listbox" aria-expanded="false" aria-label="Min Bedrooms" style="display:flex;align-items:center;justify-content:space-between;width:100%;height:21px;min-height:0;border:none;background:transparent;cursor:pointer;padding:0;padding-left:0">'
    . '<div class="react-select__value-container react-select__value-container--has-value" style="flex:1 1 0%;display:grid;align-items:center;overflow:hidden"><div class="react-select__single-value" style="display:flex;color:#35373C;font-family:\'Plus Jakarta Sans\',sans-serif;font-size:14px;font-weight:400;line-height:19.6px;letter-spacing:normal;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;width:max-content">No Min</div></div>'
    . '<span class="react-select__indicators" style="flex:0 0 auto;display:flex;align-items:center"><span class="dropdown-indicator react-select__indicator react-select__dropdown-indicator" aria-hidden="true" style="display:flex;align-items:center;margin-left:10px">'
    . '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="arrow-down-icon"><path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg></span></span></button>'
    . '<div class="react-select__menu" role="listbox" style="display:none;position:absolute;top:21px;left:0;width:100%;min-width:100%;z-index:10;background:#fff;border-radius:4px;box-shadow:0 0 0 1px rgba(0,0,0,.1),0 4px 11px rgba(0,0,0,.1)"><div class="react-select__menu-list" data-hero-options="minBed" style="max-height:300px;overflow-y:auto;padding:4px 0"></div></div>'
    . '</div></div></div>'
    . '<div class="menu-item-wrap"><p class="label" style="color:#35373C;font-size:12px;font-weight:400;letter-spacing:0.12px;line-height:19.2px;white-space:nowrap;margin:0">Max Bedrooms</p>'
    . '<div class="react-select-wrap filter-select max-bedroom-select" data-hero-select="maxBed"><div class="react-select" style="position:relative">'
    . '<button type="button" class="react-select__control" aria-haspopup="listbox" aria-expanded="false" aria-label="Max Bedrooms" style="display:flex;align-items:center;justify-content:space-between;width:100%;height:21px;min-height:0;border:none;background:transparent;cursor:pointer;padding:0;padding-left:0">'
    . '<div class="react-select__value-container react-select__value-container--has-value" style="flex:1 1 0%;display:grid;align-items:center;overflow:hidden"><div class="react-select__single-value" style="display:flex;color:#35373C;font-family:\'Plus Jakarta Sans\',sans-serif;font-size:14px;font-weight:400;line-height:19.6px;letter-spacing:normal;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;width:max-content">No Max</div></div>'
    . '<span class="react-select__indicators" style="flex:0 0 auto;display:flex;align-items:center"><span class="dropdown-indicator react-select__indicator react-select__dropdown-indicator" aria-hidden="true" style="display:flex;align-items:center;margin-left:10px">'
    . '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="arrow-down-icon"><path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg></span></span></button>'
    . '<div class="react-select__menu" role="listbox" style="display:none;position:absolute;top:21px;left:0;width:100%;min-width:100%;z-index:10;background:#fff;border-radius:4px;box-shadow:0 0 0 1px rgba(0,0,0,.1),0 4px 11px rgba(0,0,0,.1)"><div class="react-select__menu-list" data-hero-options="maxBed" style="max-height:300px;overflow-y:auto;padding:4px 0"></div></div>'
    . '</div></div></div>'
    . '</div></div></div>'
    . '<div class="vertical-divider ishide-mod"></div>'
    . '<div class="filter-dropdown price-filter-dropdown ishide-mod dropdown">'
    . '<button type="button" class="custom-dropdown-toggle filter-dropdown-toggle dropdown-toggle" aria-expanded="false" data-hero-dropdown="price"><span><span>Price Range</span></span>'
    . '<svg class="arrow-down-icon" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13 5.5L8 10.5L3 5.5" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" /></svg>'
    . '</button>'
    . '<div class="dropdown-menu filter-dropdown-menu show" data-hero-price-menu style="display:none"><div class="custom-dropdown-menu">'
    . '<div class="menu-item-wrap"><p class="label" style="color:#35373C;font-size:12px;font-weight:400;letter-spacing:0.12px;line-height:19.2px;white-space:nowrap;margin:0">Min Price</p>'
    . '<div class="react-select-wrap filter-select min-price-select" data-hero-select="minPrice"><div class="react-select" style="position:relative">'
    . '<button type="button" class="react-select__control" aria-haspopup="listbox" aria-expanded="false" aria-label="Min Price" style="display:flex;align-items:center;justify-content:space-between;width:100%;height:21px;min-height:0;border:none;background:transparent;cursor:pointer;padding:0;padding-left:0">'
    . '<div class="react-select__value-container react-select__value-container--has-value" style="flex:1 1 0%;display:grid;align-items:center;overflow:hidden"><div class="react-select__single-value" style="display:flex;color:#35373C;font-family:\'Plus Jakarta Sans\',sans-serif;font-size:14px;font-weight:400;line-height:19.6px;letter-spacing:normal;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;width:max-content">No Min</div></div>'
    . '<span class="react-select__indicators" style="flex:0 0 auto;display:flex;align-items:center"><span class="dropdown-indicator react-select__indicator react-select__dropdown-indicator" aria-hidden="true" style="display:flex;align-items:center;margin-left:10px">'
    . '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="arrow-down-icon"><path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg></span></span></button>'
    . '<div class="react-select__menu" role="listbox" style="display:none;position:absolute;top:21px;left:0;width:100%;min-width:100%;z-index:10;background:#fff;border-radius:4px;box-shadow:0 0 0 1px rgba(0,0,0,.1),0 4px 11px rgba(0,0,0,.1)"><div class="react-select__menu-list" data-hero-options="minPrice" style="max-height:300px;overflow-y:auto;padding:4px 0"></div></div>'
    . '</div></div></div>'
    . '<div class="menu-item-wrap"><p class="label" style="color:#35373C;font-size:12px;font-weight:400;letter-spacing:0.12px;line-height:19.2px;white-space:nowrap;margin:0">Max Price</p>'
    . '<div class="react-select-wrap filter-select max-price-select" data-hero-select="maxPrice"><div class="react-select" style="position:relative">'
    . '<button type="button" class="react-select__control" aria-haspopup="listbox" aria-expanded="false" aria-label="Max Price" style="display:flex;align-items:center;justify-content:space-between;width:100%;height:21px;min-height:0;border:none;background:transparent;cursor:pointer;padding:0;padding-left:0">'
    . '<div class="react-select__value-container react-select__value-container--has-value" style="flex:1 1 0%;display:grid;align-items:center;overflow:hidden"><div class="react-select__single-value" style="display:flex;color:#35373C;font-family:\'Plus Jakarta Sans\',sans-serif;font-size:14px;font-weight:400;line-height:19.6px;letter-spacing:normal;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;width:max-content">No Max</div></div>'
    . '<span class="react-select__indicators" style="flex:0 0 auto;display:flex;align-items:center"><span class="dropdown-indicator react-select__indicator react-select__dropdown-indicator" aria-hidden="true" style="display:flex;align-items:center;margin-left:10px">'
    . '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" class="arrow-down-icon"><path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg></span></span></button>'
    . '<div class="react-select__menu" role="listbox" style="display:none;position:absolute;top:21px;left:0;width:100%;min-width:100%;z-index:10;background:#fff;border-radius:4px;box-shadow:0 0 0 1px rgba(0,0,0,.1),0 4px 11px rgba(0,0,0,.1)"><div class="react-select__menu-list" data-hero-options="maxPrice" style="max-height:300px;overflow-y:auto;padding:4px 0"></div></div>'
    . '</div></div></div>'
    . '</div></div></div>'
    . '</div></div>'
    . '<div class="search-cta-section"><button class="button button-orange" type="button" data-hero-go><span>Search</span></button></div>'
    . '</div>'
    . '<div class="review-txt"><p>' . $review . '</p></div>'
    . '</div></div></div></div></div>';

foreach ($mods as $m) {
    $body .= module_renderer($m);
}
$body .= '</div>';
?><!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<?php require __DIR__ . '/../includes/header.php'; ?>
<main>
<?php echo $body; ?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php render_site_footer_scripts(); ?>
</body>
</html>
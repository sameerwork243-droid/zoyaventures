<?php
// pages/listing.php — ListingPage port (src/components/listing.tsx)
// Expects: $route (canonical, may include /page/n/), $routeBase, $pageNum, $model['data'] as $data.

require_once __DIR__ . '/../includes/render/listing-ui.php';
require_once __DIR__ . '/../includes/render/property-card.php';
require_once __DIR__ . '/../includes/render/questionnaire.php';
require_once __DIR__ . '/../includes/render/read-more.php';
require_once __DIR__ . '/../includes/render/rich.php';
require_once __DIR__ . '/../includes/head.php';

const LISTING_TYPES = ['apartment', 'villa', 'townhouse', 'penthouse', 'duplex', 'commercial-properties', 'whole-building', 'plots', 'short-term', 'office-space'];
const LISTING_PRICES = [
    ['label' => 'Under AED 1M', 'suffix' => 'under-1000000'],
    ['label' => 'Above AED 20M', 'suffix' => 'above-20000000'],
    ['label' => 'Above AED 10M', 'suffix' => 'above-10000000'],
    ['label' => 'Above AED 5M', 'suffix' => 'above-5000000'],
    ['label' => 'Above AED 3M', 'suffix' => 'above-3000000'],
    ['label' => 'Above AED 2M', 'suffix' => 'above-2000000'],
];
const LISTING_BEDS = [
    ['label' => 'Studio', 'suffix' => 'under-0-bedrooms'],
    ['label' => '1 Bed', 'suffix' => 'with-1-to-1-bedrooms'],
    ['label' => '2 Beds', 'suffix' => 'with-2-to-2-bedrooms'],
    ['label' => '3 Beds', 'suffix' => 'with-3-to-3-bedrooms'],
    ['label' => '4 Beds', 'suffix' => 'with-4-to-4-bedrooms'],
    ['label' => '5+ Beds', 'suffix' => 'with-5-to-6-bedrooms'],
];
const LISTING_SIZES = [
    ['label' => 'Under 1000 sqft', 'suffix' => 'with-size-under-1000'],
    ['label' => '1000 - 2000 sqft', 'suffix' => 'with-size-1000-to-2000'],
    ['label' => '2000 - 4000 sqft', 'suffix' => 'with-size-2000-to-4000'],
    ['label' => 'Above 4000 sqft', 'suffix' => 'with-size-above-4000'],
];
const LISTING_AMENITIES = [
    ['label' => 'Swimming Pool', 'suffix' => 'with-amenities-swimming-pool'],
    ['label' => 'Shared Gym', 'suffix' => 'with-amenities-shared-gym'],
    ['label' => 'Near Metro', 'suffix' => 'with-amenities-near-metro'],
    ['label' => 'Covered Parking', 'suffix' => 'with-amenities-covered-parking'],
    ['label' => 'Security', 'suffix' => 'with-amenities-security'],
];

function listing_type_options(string $route): array
{
    $rent = str_starts_with($route, '/let');
    $out = [];
    foreach (LISTING_TYPES as $t) {
        $suffix = $rent ? $t . '-for-rent' : $t . '-for-sale';
        $out[] = ['label' => str_replace('-', ' ', $t), 'href' => '/' . ($rent ? 'let' : 'buy') . '/' . $suffix . '/'];
    }
    return $out;
}

function listing_price_options(string $route): array
{
    $rent = str_starts_with($route, '/let');
    $base = '/' . ($rent ? 'let' : 'buy') . '/properties-' . ($rent ? 'for-rent' : 'for-sale');
    $out = [];
    foreach (LISTING_PRICES as $p) $out[] = ['label' => $p['label'], 'href' => $base . '/' . $p['suffix'] . '/'];
    return $out;
}

function listing_bed_options(string $route): array
{
    $rent = str_starts_with($route, '/let');
    $type = route_filters($route)['type'] ?? 'properties';
    $base = '/' . ($rent ? 'let' : 'buy') . '/' . $type . '-' . ($rent ? 'for-rent' : 'for-sale');
    $out = [];
    foreach (LISTING_BEDS as $b) $out[] = ['label' => $b['label'], 'href' => $base . '/' . $b['suffix'] . '/'];
    return $out;
}

function listing_size_options(string $route): array
{
    $rent = str_starts_with($route, '/let');
    $base = '/' . ($rent ? 'let' : 'buy') . '/properties-' . ($rent ? 'for-rent' : 'for-sale');
    $out = [];
    foreach (LISTING_SIZES as $s) $out[] = ['label' => $s['label'], 'href' => $base . '/' . $s['suffix'] . '/'];
    return $out;
}

function listing_amenity_options(string $route): array
{
    $rent = str_starts_with($route, '/let');
    $base = '/' . ($rent ? 'let' : 'buy') . '/properties-' . ($rent ? 'for-rent' : 'for-sale');
    $out = [];
    foreach (LISTING_AMENITIES as $a) $out[] = ['label' => $a['label'], 'href' => $base . '/' . $a['suffix'] . '/'];
    return $out;
}

const LISTING_POPULAR_RENT = [
    ['label' => 'Apartments to rent in Dubai', 'href' => '/let/apartment-for-rent/'],
    ['label' => 'Villas to rent in Dubai', 'href' => '/let/villa-for-rent/'],
    ['label' => 'Townhouses to rent in Dubai', 'href' => '/let/townhouse-for-rent/'],
    ['label' => 'Penthouses to rent in Dubai', 'href' => '/let/penthouse-for-rent/'],
    ['label' => 'Short terms to rent in Dubai', 'href' => '/let/short-term-for-rent/'],
    ['label' => 'Duplexes to rent in Dubai', 'href' => '/let/duplex-for-rent/'],
];
const LISTING_USEFUL_LINKS = [
    ['label' => 'Off Plan Projects', 'href' => '/new-projects/'],
    ['label' => 'Area Guides', 'href' => '/area-guides/'],
    ['label' => 'Top Developers', 'href' => '/developers/'],
    ['label' => 'Meet the team', 'href' => '/team/'],
    ['label' => 'Our Awards', 'href' => '/about/our-awards/'],
    ['label' => 'News & Insights', 'href' => '/blog/'],
];

$data = $model['data'] ?? ['hits' => [], 'nbHits' => 0];
$list = $data['hits'] ?? [];
$page = max(1, $pageNum);
$start = ($page - 1) * 20;
$hits = count($list) ? array_slice($list, $start, 20) : synth_hits($routeBase, $page);
$nbHits = $data['nbHits'] ?? count($hits);
$f = route_filters($routeBase);
$rent = str_starts_with($routeBase, '/let');
$h1 = listing_title_from_route($routeBase, $rent, true);
$baseRoute = rtrim($routeBase, '/');
$content = is_array($data['content'] ?? null) ? ($data['content'][0] ?? null) : ($data['content'] ?? null);
$contentDesc = $content['description'] ?? null;

$spotlight = project_hits(1)[0] ?? null;
$expert = team_members(1)[0] ?? null;

$typeOptions_ = listing_type_options($routeBase);
$priceOptions_ = listing_price_options($routeBase);
$bedOptions_ = listing_bed_options($routeBase);
$sizeOptions_ = listing_size_options($routeBase);
$amenityOptions_ = listing_amenity_options($routeBase);

$togBtnClass = 'tog-btn  btn btn-primary';
?><!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<?php require __DIR__ . '/../includes/header.php'; ?>
<main>

<div>
  <div class="se-r min-vh-100">
    <div class="search-filters-section">
      <div class="search-filters-container container">
        <div class="mutil-select-wrap">
          <div class="multi-select-input" id="multi-select-input">
            <div class="filter search-box">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none" class="search-icon">
                <path d="M14.5 14L11.0355 10.5355M11.0355 10.5355C11.9404 9.63071 12.5 8.38071 12.5 7C12.5 4.23858 10.2614 2 7.5 2C4.73858 2 2.5 4.23858 2.5 7C2.5 9.76142 4.73858 12 7.5 12C8.88071 12 10.1307 11.4404 11.0355 10.5355Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
              <div class="autosuggest__container">
                <input id="search-input-field" type="text" placeholder="Area, project or community" class="autosuggest__input" autocomplete="off" value="<?= esc($f['area'] ? area_label($f['area']) : '') ?>">
              </div>
            </div>
          </div>
        </div>
        <div class="filters-section d-none d-xl-flex">
          <?= type_select($typeOptions_) ?>
          <?= filter_dropdown('Price', $priceOptions_, 'price-filter-dropdown') ?>
          <?= filter_dropdown('Beds', $bedOptions_, 'bedroom-filter-dropdown') ?>
          <?= filter_dropdown('Size', $sizeOptions_) ?>
          <div class="cta-section">
            <button class="button button-gray filter-button" type="button">
              <span>Filters</span>
              <svg class="d-inline d-md-none" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                <path d="M8.75 5L16.875 5M8.75 5C8.75 5.69036 8.19036 6.25 7.5 6.25C6.80964 6.25 6.25 5.69036 6.25 5M8.75 5C8.75 4.30964 8.19036 3.75 7.5 3.75C6.80964 3.75 6.25 4.30964 6.25 5M3.125 5H6.25M8.75 15H16.875M8.75 15C8.75 15.6904 8.19036 16.25 7.5 16.25C6.80964 16.25 6.25 15.6904 6.25 15M8.75 15C8.75 14.3096 8.19036 13.75 7.5 13.75C6.80964 13.75 6.25 14.3096 6.25 15M3.125 15L6.25 15M13.75 10L16.875 10M13.75 10C13.75 10.6904 13.1904 11.25 12.5 11.25C11.8096 11.25 11.25 10.6904 11.25 10M13.75 10C13.75 9.30964 13.1904 8.75 12.5 8.75C11.8096 8.75 11.25 9.30964 11.25 10M3.125 10H11.25" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
              <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-down-icon d-none d-md-inline">
                <path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
            </button>
            <a class="button button-orange" href="<?= esc($baseRoute . '/') ?>">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none" class="search-icon">
                <path d="M14.5 14L11.0355 10.5355M11.0355 10.5355C11.9404 9.63071 12.5 8.38071 12.5 7C12.5 4.23858 10.2614 2 7.5 2C4.73858 2 2.5 4.23858 2.5 7C2.5 9.76142 4.73858 12 7.5 12C8.88071 12 10.1307 11.4404 11.0355 10.5355Z" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"></path>
              </svg>
              <span>Search</span>
            </a>
            <div class="sb-myacc icon wishlist-icn button button-blue d-none d-md-flex ma-save-search">
              <div class="search-icon">
                <span class="search-save search icon-save"></span>
                <span class="search-save search icon-saved"></span>
              </div>
              <span class="save-text button-text">Save</span>
              <span class="saved-text button-text">Saved</span>
            </div>
          </div>
        </div>
      </div>
      <div class="search-filters-container mobile-toggle-filter container">
        <div>
          <a type="button" class="tog-btn active btn btn-primary" href="<?= esc($baseRoute . '/') ?>">
            <?= $rent ? 'Rentals' : 'Sales' ?>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-down-icon">
              <path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </a>
        </div>
        <div>
          <a type="button" class="<?= esc($togBtnClass) ?>" href="<?= esc('/' . ($rent ? 'let' : 'buy') . '/properties-' . ($rent ? 'for-rent' : 'for-sale') . '/') ?>">
            <?= $rent ? 'Sales' : 'Rentals' ?>
            <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-down-icon">
              <path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path>
            </svg>
          </a>
        </div>
        <div><?= filter_dropdown('Property Type', $typeOptions_, '', $togBtnClass) ?></div>
        <div><?= filter_dropdown('Price', $priceOptions_, '', $togBtnClass) ?></div>
        <div><?= filter_dropdown('Beds', $bedOptions_, '', $togBtnClass) ?></div>
        <div><?= filter_dropdown('Size', $sizeOptions_, '', $togBtnClass) ?></div>
        <div><?= filter_dropdown('Amenities', $amenityOptions_, '', $togBtnClass) ?></div>
      </div>
    </div>

    <div class="property-breadcrumb-wrap">
      <div class="breadcrumbs-wrap">
        <div class="breadcrumbs-container container">
          <nav class="breadcrumbs">
            <ol class="breadcrumb">
              <li class="enable-link-home breadcrumb-item">
                <a class="breadcrumb-link enable-link" href="/">Home</a>
              </li>
              <li class=" breadcrumb-item active">
                <a aria-current="page" class="breadcrumb-link disable-link" href="<?= esc($baseRoute . '/') ?>"><?= esc($h1) ?></a>
              </li>
            </ol>
          </nav>
        </div>
      </div>
    </div>

    <div class="search-results-section list-page">
      <div class="info-map-sort-wrap container">
        <div class="info-map-sort-section">
          <div class="bottom-section">
            <div class="fit-bk-search">
              <div class="h1-section">
                <h1><?= esc($h1) ?></h1>
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="info-icon">
                  <path d="M9.375 9.375L9.40957 9.35771C9.88717 9.11891 10.4249 9.55029 10.2954 10.0683L9.70458 12.4317C9.57507 12.9497 10.1128 13.3811 10.5904 13.1423L10.625 13.125M17.5 10C17.5 14.1421 14.1421 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 5.85786 5.85786 2.5 10 2.5C14.1421 2.5 17.5 5.85786 17.5 10ZM10 6.875H10.0063V6.88125H10V6.875Z" stroke="#9399A4" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
              </div>
              <p class="info d-none d-xl-block"><span><?= number_format($nbHits, 0, '.', ',') ?></span> listings</p>
            </div>
            <div class="map-sort-section">
              <div class="d-block d-xl-none info"><span><?= number_format($nbHits, 0, '.', ',') ?></span> listings</div>
              <div class="d-none d-xl-block">
                <button class="map-button" type="button">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" class="map-icon">
                    <path d="M10 7C10 8.10457 9.10457 9 8 9C6.89543 9 6 8.10457 6 7C6 5.89543 6.89543 5 8 5C9.10457 5 10 5.89543 10 7Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M13 7C13 11.7614 8 14.5 8 14.5C8 14.5 3 11.7614 3 7C3 4.23858 5.23858 2 8 2C10.7614 2 13 4.23858 13 7Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  <span class="button-text">Map</span>
                </button>
              </div>
              <div class="d-none d-xl-block">
                <button class="map-button list-grid" type="button">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" class="grid-icon">
                    <path d="M8.00008 4.00033C8.36827 4.00033 8.66675 3.70185 8.66675 3.33366C8.66675 2.96547 8.36827 2.66699 8.00008 2.66699C7.63189 2.66699 7.33341 2.96547 7.33341 3.33366C7.33341 3.70185 7.63189 4.00033 8.00008 4.00033Z" stroke="#07234B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M8.00008 8.66699C8.36827 8.66699 8.66675 8.36851 8.66675 8.00033C8.66675 7.63214 8.36827 7.33366 8.00008 7.33366C7.63189 7.33366 7.33341 7.63214 7.33341 8.00033C7.33341 8.36851 7.63189 8.66699 8.00008 8.66699Z" stroke="#07234B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M8.00008 13.3337C8.36827 13.3337 8.66675 13.0352 8.66675 12.667C8.66675 12.2988 8.36827 12.0003 8.00008 12.0003C7.63189 12.0003 7.33341 12.2988 7.33341 12.667C7.33341 13.0352 7.63189 13.3337 8.00008 13.3337Z" stroke="#07234B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M12.6667 4.00033C13.0349 4.00033 13.3334 3.70185 13.3334 3.33366C13.3334 2.96547 13.0349 2.66699 12.6667 2.66699C12.2986 2.66699 12.0001 2.96547 12.0001 3.33366C12.0001 3.70185 12.2986 4.00033 12.6667 4.00033Z" stroke="#07234B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M12.6667 8.66699C13.0349 8.66699 13.3334 8.36851 13.3334 8.00033C13.3334 7.63214 13.0349 7.33366 12.6667 7.33366C12.2986 7.33366 12.0001 7.63214 12.0001 8.00033C12.0001 8.36851 12.2986 8.66699 12.6667 8.66699Z" stroke="#07234B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M12.6667 13.3337C13.0349 13.3337 13.3334 13.0352 13.3334 12.667C13.3334 12.2988 13.0349 12.0003 12.6667 12.0003C12.2986 12.0003 12.0001 12.2988 12.0001 12.667C12.0001 13.0352 12.2986 13.3337 12.6667 13.3337Z" stroke="#07234B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M3.33341 4.00033C3.7016 4.00033 4.00008 3.70185 4.00008 3.33366C4.00008 2.96547 3.7016 2.66699 3.33341 2.66699C2.96522 2.66699 2.66675 2.96547 2.66675 3.33366C2.66675 3.70185 2.96522 4.00033 3.33341 4.00033Z" stroke="#07234B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M3.33341 8.66699C3.7016 8.66699 4.00008 8.36851 4.00008 8.00033C4.00008 7.63214 3.7016 7.33366 3.33341 7.33366C2.96522 7.33366 2.66675 7.63214 2.66675 8.00033C2.66675 8.36851 2.96522 8.66699 3.33341 8.66699Z" stroke="#07234B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M3.33341 13.3337C3.7016 13.3337 4.00008 13.0352 4.00008 12.667C4.00008 12.2988 3.7016 12.0003 3.33341 12.0003C2.96522 12.0003 2.66675 12.2988 2.66675 12.667C2.66675 13.0352 2.96522 13.3337 3.33341 13.3337Z" stroke="#07234B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  <span class="button-text">Grid</span>
                </button>
              </div>
              <div class="d-none d-xl-block"><div class="sort-divider"></div></div>
              <div class="d-flex align-items-center">
                <p class="sort-txt">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M2 5L5 2M5 2L8 5M5 2V11M14 11L11 14M11 14L8 11M11 14L11 5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg> Sort:
                </p>
                <div class="sort-dropdown dropdown">
                  <button class="sort-section dropdown-toggle" type="button" aria-expanded="false">
                    <div class="sort-field">
                      <p class="text button-text">
                        <span>Most Recent</span>
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-down-icon">
                          <path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                      </p>
                    </div>
                  </button>
                </div>
              </div>
            </div>
          </div>
          <div class="bottom-section mob-view-tab d-block d-xl-none">
            <div class="map-sort-section">
              <div class="d-block">
                <button class="map-button" type="button">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none" class="map-icon">
                    <path d="M10 7C10 8.10457 9.10457 9 8 9C6.89543 9 6 8.10457 6 7C6 5.89543 6.89543 5 8 5C9.10457 5 10 5.89543 10 7Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M13 7C13 11.7614 8 14.5 8 14.5C8 14.5 3 11.7614 3 7C3 4.23858 5.23858 2 8 2C10.7614 2 13 4.23858 13 7Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  <span class="button-text">Map</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="new-layout-with-sidebar container list-k">
        <div>
          <div class="property-list-container">
            <div class="property-list-section list-view" id="property-page-1">
              <?php if (count($hits)): ?>
                <?php foreach ($hits as $i => $h): ?>
                  <?= property_card($h, true) ?>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="no-results">No properties found for this search.</p>
              <?php endif; ?>
            </div>
          </div>
          <?= listing_pagination($route, $baseRoute, $page, $nbHits) ?>
        </div>
        <div class="side-bar-listing-page">
          <div class="sticky-container">
            <?php if ($spotlight): ?>
              <?php $spotImg = img_of($spotlight['images'] ?? $spotlight['image'] ?? ''); ?>
              <div class="content-cta-section sub-menu offplan">
                <div class="image-bg" style="background-image:url(<?= esc($spotImg) ?>);background-size:cover;background-position:center">
                  <div class="spotlight">Spotlight Property</div>
                  <div class="content">
                    <p class="heading"><?= esc($spotlight['title'] ?? '') ?></p>
                    <?php if (!empty($spotlight['developer'])): ?>
                      <p class="description">By <?= esc($spotlight['developer']) ?></p>
                    <?php endif; ?>
                    <a class="button button-orange" href="<?= esc('/new-projects/' . ($spotlight['slug'] ?? '') . '/') ?>">
                      <span>View Project</span>
                    </a>
                  </div>
                </div>
              </div>
            <?php endif; ?>
            <div class="alldepartments-popular-search">
              <div class="popular_links_holder">
                <div class="default-psearch-wrapper psearch">
                  <h4>Popular Searches</h4>
                  <div>
                    <div class="column-links">
                      <?php foreach (LISTING_POPULAR_RENT as $l): ?>
                        <div><a href="<?= esc($l['href']) ?>"><?= esc($l['label']) ?></a></div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="alldepartments-popular-search">
              <div class="popular_links_holder">
                <div class="default-psearch-wrapper psearch">
                  <h4>Useful Links</h4>
                  <div class="column-links">
                    <?php foreach (LISTING_USEFUL_LINKS as $l): ?>
                      <div><a class="sub-menu-link" href="<?= esc($l['href']) ?>"><?= esc($l['label']) ?></a></div>
                    <?php endforeach; ?>
                  </div>
                </div>
              </div>
            </div>
            <?php if ($expert): ?>
              <?php $expPhone = preg_replace('/\s+/', '', (string) ($expert['phone'] ?? '')); ?>
              <div class="property-nego-card-wrap sr">
                <div class="border-side">
                  <h4>Connect with Our Property Experts Today!</h4>
                  <div class="bottom-section">
                    <a class="img-section img-zoom" href="<?= esc('/team/' . ($expert['slug'] ?? '') . '/') ?>">
                      <?php if (!empty($expert['image'])): ?>
                        <img loading="lazy" draggable="false" src="<?= esc($expert['image']) ?>" alt="<?= esc($expert['name'] ?? '') ?>">
                      <?php endif; ?>
                    </a>
                    <div class="nego-info">
                      <a href="<?= esc('/team/' . ($expert['slug'] ?? '') . '/') ?>">
                        <p class="name"><?= esc($expert['name'] ?? '') ?></p>
                        <p class="designation"><?= esc($expert['designation'] ?? '') ?></p>
                      </a>
                    </div>
                  </div>
                  <div class="cta-section">
                    <a class="button button-orange" href="<?= esc('tel:' . $expPhone) ?>">
                      <span><?= country_flag() ?> Call <?= esc(explode(' ', (string) ($expert['name'] ?? ''))[0]) ?></span>
                    </a>
                    <a class="button button-white" href="<?= esc('https://wa.provident.ae/inquire?phone=' . preg_replace('/\D/', '', (string) ($expert['phone'] ?? ''))) ?>" target="_blank" rel="noreferrer">
                      <span>WhatsApp</span>
                    </a>
                  </div>
                </div>
              </div>
            <?php endif; ?>
            <div class="card-view">
              <img loading="lazy" draggable="false" src="https://d3h330vgpwpjr8.cloudfront.net/x/368x220/Rectangle_551_3ae6d0ae77_2f860c8381.webp" srcset="https://d3h330vgpwpjr8.cloudfront.net/x/368x220/Rectangle_551_3ae6d0ae77_2f860c8381.webp 368w" sizes="(min-width: 180px) 368px" alt="Find The Best Mortgage in Dubai - Zoya Ventures Real Estate">
              <div class="content">
                <h4>Find The Best Mortgage in Dubai</h4>
                <div class="description">
                  <p>
                    <span style="white-space:pre-wrap">Get the best mortgage rates and terms in the UAE.
Your journey begins here.</span>
                  </p>
                </div>
                <a class="button button-orange" href="/property-services/mortgages/">
                  <span>Get Pre-Approved Now</span>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?= mortgage_calculator() ?>

  <div class="container">
    <div class="qes-bk com">
      <?= questionnaire([
          'title' => 'Confused About Where to Buy or Invest in Dubai?',
          'content' => ['data' => ['content' => '<p><strong>Take the 30-Second Dubai Property </strong>Quiz and instantly get matched with the <strong>best projects</strong> in Dubai — tailored to your <strong>budget, goals, and lifestyle</strong>. Perfect for <strong>investors and end users</strong> alike.</p><ul><li><strong>Personalized Results in Seconds</strong> – Instantly discover which Dubai areas and projects best fit your needs.</li><li><strong>Handpicked Projects You Can Trust</strong> – Explore Dubai’s most in-demand developments, carefully curated by our experts for quality, price, and potential.</li><li><strong>Smart Investment Insights</strong> – Instantly see which projects offer the best returns and long-term growth, based on real market data and performance.</li><li><strong>Free Dubai Investment Guidebook</strong> – Get the must-read guide packed with 2025 market insights, top launches, and expert tips to help you invest smart.</li></ul>']],
          'content1' => ['data' => ['content1' => '<p><strong>Take the 30-Second Quiz Now</strong></p><p>Find your ideal property in Dubai — and unlock your <strong>personalized results</strong> plus the <strong>Dubai Investment Guidebook</strong> instantly.</p>']],
      ]) ?>
    </div>
  </div>

  <?php if ($contentDesc): ?>
    <div class="text-copy-wrap section-p" id="contentsection-text-copy">
      <div class="text-copy-container container">
        <h2 class="title"><?= esc($content['title'] ?? '') ?></h2>
        <?= read_more(rich($contentDesc), 4, 'description') ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php

function listing_pagination(string $route, string $baseRoute, int $page, int $nbHits): string
{
    $per = 20;
    $total = max(1, (int) ceil($nbHits / $per));
    $nums = listing_page_numbers($page, $total);
    $pageUrl = fn (int $n) => $baseRoute . '/page/' . $n . '/';
    $left = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
    $right = '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"><path d="M8.25 4.5L15.75 12L8.25 19.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path></svg>';

    $html = '<div class="pagination-wrapper search-pagination-wrapper container"><div><div class="pagination-container">';
    $html .= '<a class="button button-white pagination-button button-back' . ($page <= 1 ? ' button-disabled' : '') . '" href="' . ($page > 1 ? esc($pageUrl($page - 1)) : '') . '" aria-disabled="' . ($page <= 1 ? 'true' : 'false') . '">' . $left . '</a>';
    $html .= '<div class="pagination-numbers">';
    foreach ($nums as $n) {
        if ($n === '…') {
            $html .= '<span class="pagination-dots">...</span>';
        } else {
            $html .= '<a class="pagination-number' . ($n === $page ? ' active' : '') . '" href="' . ($n !== $page ? esc($pageUrl((int) $n)) : '') . '">' . $n . '</a>';
        }
    }
    $html .= '</div>';
    $html .= '<a class="button button-white pagination-button button-next' . ($page >= $total ? ' button-disabled' : '') . '" href="' . ($page < $total ? esc($pageUrl($page + 1)) : '') . '" aria-disabled="' . ($page >= $total ? 'true' : 'false') . '">' . $right . '</a>';
    $html .= '</div></div></div>';
    return $html;
}

function listing_page_numbers(int $page, int $total): array
{
    if ($total <= 8) return range(1, $total);
    if ($page <= 6) return [1, 2, 3, 4, 5, 6, '…', $total];
    if ($page >= $total - 4) {
        $out = [1, '…'];
        for ($i = $total - 4; $i <= $total; $i++) $out[] = $i;
        return $out;
    }
    return [1, '…', $page - 2, $page - 1, $page, $page + 1, $page + 2, '…', $total];
}

function listing_title_from_route(string $route, bool $rent, bool $h1 = false): string
{
    $f = route_filters($route);
    $verb = $rent ? 'for rent' : 'for sale';
    $type = ($f['type'] && $f['type'] !== 'properties') ? str_replace('-', ' ', $f['type']) : 'properties';
    $parts = [];
    if ($h1) {
        if ($type === 'properties') {
            $parts[] = 'Properties ' . $verb . ' in Dubai';
        } else {
            $parts[] = ucfirst($type) . ' ' . $verb . ' in Dubai';
        }
        if ($f['area']) $parts[] = 'in ' . area_label($f['area']);
        if ($f['priceMin'] !== null) $parts[] = 'above AED ' . number_format($f['priceMin'], 0, '.', ',');
        if ($f['priceMax'] !== null) $parts[] = 'under AED ' . number_format($f['priceMax'], 0, '.', ',');
        if ($f['bedsMin'] !== null || $f['bedsMax'] !== null) {
            if ($f['bedsMax'] === 0) $parts[] = 'Studios';
            elseif ($f['bedsMin'] !== null && $f['bedsMax'] !== null && $f['bedsMin'] !== $f['bedsMax']) $parts[] = 'with ' . $f['bedsMin'] . ' to ' . $f['bedsMax'] . ' Bedrooms';
            else {
                $b = $f['bedsMin'] ?? $f['bedsMax'] ?? 0;
                $parts[] = 'with ' . $b . ' Bedroom' . ($b !== 1 ? 's' : '');
            }
        }
        if ($f['sizeMin'] !== null && $f['sizeMax'] !== null) $parts[] = 'with size ' . number_format($f['sizeMin'], 0, '.', ',') . ' to ' . number_format($f['sizeMax'], 0, '.', ',') . ' sqft';
        elseif ($f['sizeMin'] !== null) $parts[] = 'above ' . number_format($f['sizeMin'], 0, '.', ',') . ' sqft';
        elseif ($f['sizeMax'] !== null) $parts[] = 'under ' . number_format($f['sizeMax'], 0, '.', ',') . ' sqft';
        foreach ($f['amenities'] as $a) $parts[] = 'with ' . str_replace('-', ' ', $a);
    } else {
        $parts[] = $type . ' ' . $verb;
        if ($f['area']) $parts[] = 'in ' . area_label($f['area']);
    }
    return implode(' ', $parts);
}
?>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php render_site_footer_scripts(); ?>
</body>
</html>

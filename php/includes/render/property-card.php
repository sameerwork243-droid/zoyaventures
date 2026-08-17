<?php
// property-card.php — PropertyCard / CardGallery / SaveButton / MoreBox ports
// (src/components/property-card.tsx, card-gallery.tsx, save-button.tsx).
// Client behaviour (gallery paging, save toggle) is wired in listing-ui.js + later JS phases.

require_once __DIR__ . '/../store.php';

function signature_badge_svg(): string
{
    static $s = null;
    if ($s === null) {
        $f = __DIR__ . '/signature-badge.svg';
        $s = is_file($f) ? (string) file_get_contents($f) : '';
    }
    return $s;
}

function more_box_svg(): string
{
    static $s = null;
    if ($s === null) {
        $f = __DIR__ . '/more-box.svg';
        $s = is_file($f) ? (string) file_get_contents($f) : '';
    }
    return $s;
}

function card_gallery(array $imgs, string $link, string $alt, ?int $count = null): string
{
    $n = max(1, count($imgs));
    $html = '<div class="swiper"><div class="swiper-wrapper">';
    foreach (array_slice($imgs, 0, 4) as $j => $img) {
        if (!is_array($img)) $img = ['big' => is_string($img) ? $img : null];
        $mobile = $img['340x252'] ?? $img['464x312'] ?? $img['696x520'] ?? $img['big'] ?? null;
        $desktop = $img['464x312'] ?? $img['340x252'] ?? $img['696x520'] ?? $img['big'] ?? null;
        $lazy = $j < 3 ? 'eager' : 'lazy';
        $html .= '<div class="swiper-slide"' . ($j === 0 ? '' : ' style="display:none"') . '>';
        $html .= '<a class="img-section" href="' . esc($link) . '">';
        if ($mobile) $html .= '<img class="d-block d-lg-none" loading="' . $lazy . '" src="' . esc($mobile) . '" alt="' . esc($alt) . '">';
        if ($desktop) $html .= '<img class="d-none d-lg-block" loading="' . $lazy . '" src="' . esc($desktop) . '" alt="' . esc($alt) . '">';
        $html .= '</a></div>';
    }
    $html .= '</div><div class="swiper-pagination"></div>';
    $html .= '<div class="custom-prev"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-left"><path d="m15 18-6-6 6-6"></path></svg></div>';
    $html .= '<div class="custom-next"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"></path></svg></div>';
    $html .= '<span class="count"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="photo-icon">'
        . '<path d="M1.5 10.5L4.93934 7.06066C5.52513 6.47487 6.47487 6.47487 7.06066 7.06066L10.5 10.5M9.5 9.5L10.4393 8.56066C11.0251 7.97487 11.9749 7.97487 12.5607 8.56066L14.5 10.5M2.5 13H13.5C14.0523 13 14.5 12.5523 14.5 12V4C14.5 3.44772 14.0523 3 13.5 3H2.5C1.94772 3 1.5 3.44772 1.5 4V12C1.5 12.5523 1.94772 13 2.5 13ZM9.5 5.5H9.505V5.505H9.5V5.5ZM9.75 5.5C9.75 5.63807 9.63807 5.75 9.5 5.75C9.36193 5.75 9.25 5.63807 9.25 5.5C9.25 5.36193 9.36193 5.25 9.5 5.25C9.63807 5.25 9.75 5.36193 9.75 5.5Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
        . ($count ?? $n) . '</span>';
    $html .= '</div>';
    return $html;
}

function save_button_markup(string $propertyRef, string $slug, string $title, int $price, string $thumb = '', string $variant = 'circle'): string
{
    if ($variant === 'button') {
        return '<button type="button" class="detail-save-btn" data-save-ref="' . esc($propertyRef) . '" data-save-slug="' . esc($slug) . '" data-save-title="' . esc($title) . '" data-save-price="' . $price . '" data-save-thumb="' . esc($thumb) . '">'
            . '<svg width="16" height="16" viewBox="0 0 20 20" fill="none"><path d="M10 17.5S3 13.2 3 8.1C3 5.6 4.9 3.8 7.1 3.8c1.3 0 2.3.6 2.9 1.6.6-1 1.6-1.6 2.9-1.6 2.2 0 4.1 1.8 4.1 4.3 0 5.1-7 9.4-7 9.4Z" stroke="currentColor" fill="none" stroke-width="1.5" stroke-linejoin="round"></path></svg>'
            . 'Save property</button>';
    }
    return '<div class="sb-myacc icon wishlist-icn" data-save-wrap data-save-ref="' . esc($propertyRef) . '" data-save-slug="' . esc($slug) . '" data-save-title="' . esc($title) . '" data-save-price="' . $price . '" data-save-thumb="' . esc($thumb) . '">'
        . '<a href="#" title="Save this property" aria-label="Save this property">'
        . '<span class="property-save icon-save"></span>'
        . '<span class="property-save icon-saved"></span>'
        . '</a></div>';
}

function property_card(array $hit, bool $list = false, bool $signature = false): string
{
    $link = prop_link($hit);
    $imgs = $hit['images'] ?? [];
    if (!is_array($imgs)) $imgs = [];
    $desc = long_desc($hit);
    $neg = neg_of($hit);
    $cardPhone = $neg['phone'] ?? '+971 50 440 2783';
    $alt = $hit['building'][0] ?? 'Property';
    if (is_array($alt)) $alt = 'Property';
    $count = $hit['imageCount'] ?? count($imgs);

    $classes = 'property-card' . ($list ? ' list-view' : '') . ($signature ? ' singnature' : '');
    $idAttr = 'property-card-' . ($hit['id'] ?? '') . '-' . ($hit['crm_id'] ?? '');

    $html = '<div class="property-card-wrapper">';
    $html .= '<div class="' . $classes . '" id="' . esc($idAttr) . '">';
    $html .= '<div class="img-section listview-img-section">';
    $html .= card_gallery($imgs, $link, $alt, $count);
    if ($signature) $html .= '<p class="img-tag hidee">' . signature_badge_svg() . '</p>';
    $html .= '</div>';
    $html .= '<div class="content-section">';
    $html .= '<div class="pr-bk">';
    $html .= '<a class="price" href="' . esc($link) . '">' . price_fmt_html($hit['price'] ?? null, $hit['price_qualifier'] ?? null) . '</a>';
    $html .= save_button_markup($link, (string) ($hit['slug'] ?? ''), (string) ($hit['title'] ?? $hit['building'][0] ?? 'Property'), (int) ($hit['price'] ?? 0), (string) ($imgs[0]['340x252'] ?? ''));
    $html .= '</div>';
    $html .= '<a class="ammenities" href="' . esc($link) . '">' . esc($hit['description'] ?? $hit['building'][0] ?? 'View Details') . '</a>';
    $html .= '<p class="address">'
        . '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M10 7C10 8.10457 9.10457 9 8 9C6.89543 9 6 8.10457 6 7C6 5.89543 6.89543 5 8 5C9.10457 5 10 5.89543 10 7Z" stroke="#9399A4" stroke-linecap="round" stroke-linejoin="round"></path><path d="M13 7C13 11.7614 8 14.5 8 14.5C8 14.5 3 11.7614 3 7C3 4.23858 5.23858 2 8 2C10.7614 2 13 4.23858 13 7Z" stroke="#9399A4" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
        . esc(address_of($hit)) . '</p>';
    $html .= '<div class="info-section">';
    $html .= '<p class="type">' . esc($hit['building'][0] ?? $hit['building_type'] ?? 'Property') . '</p>';
    $html .= '<p class="p-hypen"></p>';
    if (($hit['bedroom'] ?? null) !== null) {
        $html .= '<p class="bedrooms"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="bed-icon"><path d="M14.6666 12.6667V10.6667M14.6666 10.6667V8C14.6666 6.52724 13.4727 5.33333 12 5.33333H7.99998V10.6667M14.6666 10.6667H7.99998M7.99998 10.6667H1.33331M1.33331 10.6667V4M1.33331 10.6667V12.6667M5.99999 7.33333C5.99999 8.06973 5.40303 8.66667 4.66665 8.66667C3.93027 8.66667 3.33332 8.06973 3.33332 7.33333C3.33332 6.59695 3.93027 6 4.66665 6C5.40303 6 5.99999 6.59695 5.99999 7.33333Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path></svg><span>' . esc((string) $hit['bedroom']) . '</span></p>';
    }
    if (($hit['bathroom'] ?? null) !== null) {
        $html .= '<p class="bathrooms"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="bath-icon"><path d="M8 3.33333C10.2091 3.33333 12 5.12419 12 7.33333V8H4V7.33333C4 5.12419 5.79086 3.33333 8 3.33333ZM8 3.33333V2" stroke="#35373C" stroke-linecap="round" stroke-linejoin="round"></path><path d="M4 10.3335H4.00999M4 13.3335H4.00999M7.99501 10.3335H8.00499M7.99501 13.3335H8.00499M11.99 10.3335H12M11.99 13.3335H12" stroke="#35373C" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path></svg><span>' . esc((string) $hit['bathroom']) . '</span></p>';
    }
    $fmin = $hit['floorarea_min'] ?? null;
    $fmax = $hit['floorarea_max'] ?? null;
    if ($fmin !== null || $fmax !== null) {
        $v = ($fmin !== null && $fmin !== $fmax) ? (int) $fmin : (int) $fmax;
        $html .= '<p class="size"><svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-4-icon"><path d="M2.5 2.5V5.5M2.5 2.5H5.5M2.5 2.5L6 6M2.5 13.5V10.5M2.5 13.5H5.5M2.5 13.5L6 10M13.5 2.5L10.5 2.5M13.5 2.5V5.5M13.5 2.5L10 6M13.5 13.5H10.5M13.5 13.5V10.5M13.5 13.5L10 10" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path></svg><span>' . number_format($v, 0, '.', ',') . ' sq ft</span></p>';
    }
    $html .= '</div>';
    $html .= '<p class="long-description"><span>' . esc($desc) . '</span><a class="read-more-text" href="' . esc($link) . '">more</a></p>';
    $html .= '<div class="cta-section">';
    $html .= '<a class="property-cta email" href="/book-a-viewing/?id=' . esc(rawurlencode((string) ($hit['crm_id'] ?? $hit['id'] ?? ''))) . '">'
        . '<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg" class="phone-icon"><path d="M14.5 5V12C14.5 12.8284 13.8284 13.5 13 13.5H3C2.17157 13.5 1.5 12.8284 1.5 12V5M14.5 5C14.5 4.17157 13.8284 3.5 13 3.5H3C2.17157 3.5 1.5 4.17157 1.5 5M14.5 5V5.16181C14.5 5.6827 14.2298 6.1663 13.7861 6.43929L8.78615 9.51622C8.30404 9.8129 7.69596 9.8129 7.21385 9.51622L2.21385 6.43929C1.77023 6.1663 1.5 5.6827 1.5 5.16181V5" stroke="#35373C" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
        . '<span>Book a Viewing</span></a>';
    $html .= '<a href="tel:' . esc(preg_replace('/\s+/', '', $cardPhone)) . '" class="property-cta">'
        . country_flag()
        . '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="phone-icon"><path d="M14.5 11.3v2a1.34 1.34 0 0 1-1.47 1.34 13.2 13.2 0 0 1-5.74-2 13.2 13.2 0 0 1-4-4A13.2 13.2 0 0 1 1.3 2.97 1.34 1.34 0 0 1 2.63 1.5h2a1.34 1.34 0 0 1 1.34 1.14c.07.66.27 1.3.47 1.87a1.34 1.34 0 0 1-.33 1.4l-.87.87a10.7 10.7 0 0 0 4 4l.87-.87a1.34 1.34 0 0 1 1.4-.33c.57.2 1.21.4 1.87.47.62.06 1.1.6 1.1 1.25Z" stroke="#EE7133" stroke-linecap="round" stroke-linejoin="round"></path></svg>'
        . '<span>Call</span></a>';
    $html .= '<a href="' . esc(wa_link_property($hit)) . '" target="_blank" class="property-cta whats whats-icon-only" rel="noreferrer" aria-label="WhatsApp about ' . esc($hit['title'] ?? $hit['building'][0] ?? 'this property') . '">'
        . '<svg width="17" height="16" viewBox="0 0 17 16" fill="none"><path fill="#67C15E" d="M8.5 0C4.06 0 .5 3.56.5 8c0 1.4.37 2.77 1.07 3.98L.5 16l4.2-1.1a8 8 0 0 0 3.8.97c4.44 0 8-3.56 8-7.95S12.94 0 8.5 0Zm4.68 11.3c-.2.57-1.17 1.09-1.6 1.13-.42.04-.9.2-3.03-.63-2.56-1-4.17-3.6-4.3-3.77-.12-.17-1.02-1.36-1.02-2.6 0-1.23.65-1.83.88-2.08.23-.25.5-.31.67-.31h.48c.15 0 .36-.06.56.42l.78 1.9c.06.15.1.32.02.49-.07.17-.12.26-.23.4l-.35.43c-.12.11-.24.24-.1.47.14.23.6 1 1.3 1.61.9.8 1.65 1.05 1.9 1.17.23.12.37.1.5-.06l.75-.87c.16-.19.31-.15.52-.09l1.9.9c.24.11.4.17.46.26.06.1.06.56-.14 1.13Z"/></svg></a>';
    $html .= '</div></div></div></div>';
    return $html;
}

function more_box(string $title, string $subtitle, string $href, string $btn): string
{
    return '<div class="property-card-wrapper more-box" tabindex="-1" style="width:100%;display:inline-block">'
        . '<div class="property-card"><div>' . more_box_svg()
        . '<div class="price">' . esc($title) . '</div>'
        . '<p>' . esc($subtitle) . '</p>'
        . '<a class="button button-orange more-btn" href="' . esc($href) . '">' . esc($btn) . '</a>'
        . '</div></div></div>';
}
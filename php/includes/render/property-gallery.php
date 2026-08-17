<?php
// property-gallery.php — PropertyGallery port (src/components/property-gallery.tsx).
// Lightbox open/close/step/share behaviour runs in property.js.

require_once __DIR__ . '/../functions.php';

function property_gallery(array $srcs, string $type, ?string $location = null, ?string $title = null): string
{
    $srcs = array_values(array_filter($srcs, fn ($s) => $s !== null && $s !== ''));
    $n = count($srcs);
    $main = $srcs[0] ?? null;
    $side = array_slice($srcs, 1, 2);
    $mapsUrl = $location ? 'https://www.google.com/maps/search/?api=1&query=' . rawurlencode($location) : null;
    $photosLabel = $n > 1 ? 'Photos (' . $n . ')' : 'Photo';
    $lightboxSrcs = array_map(fn ($s) => cft($s, 1200, 675), $srcs);
    $shareTitle = $title ? $title : $type . ' - Zoya Ventures Real Estate';

    $html = '<div class="pe-gallery" data-srcs="' . esc(json_encode($lightboxSrcs, JSON_UNESCAPED_SLASHES)) . '">';
    $html .= '<div class="pe-gallery-main img-zoom">';
    if ($main) {
        $html .= '<img loading="eager" src="' . esc(cft($main, 1200, 675)) . '" alt="' . esc($type) . ' - main image" data-gallery-open="0">';
    }
    if ($n > 1) {
        $html .= '<button type="button" class="pe-gallery-actions pe-gallery-photos" data-gallery-open="0">'
            . '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg>'
            . esc($photosLabel) . '</button>';
    }
    $html .= '<div class="pe-gallery-actions">';
    if ($mapsUrl) {
        $html .= '<a class="pe-gallery-btn" href="' . esc($mapsUrl) . '" target="_blank" rel="noreferrer">'
            . '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>'
            . 'Location</a>';
    }
    $html .= '<button type="button" class="pe-gallery-btn" data-gallery-share data-share-title="' . esc($shareTitle) . '">'
        . '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><path d="m8.59 13.51 6.83 3.98M15.41 6.51l-6.82 3.98"></path></svg>'
        . 'Share</button>';
    $html .= '</div>';
    $html .= '<span class="pe-gallery-share-msg" style="display:none">Link copied to clipboard</span>';
    $html .= '</div>';

    if (count($side)) {
        $html .= '<div class="pe-gallery-side">';
        foreach ($side as $i => $s) {
            $html .= '<div class="pe-gallery-side-item img-zoom" data-gallery-open="' . ($i + 1) . '">'
                . '<img loading="lazy" src="' . esc(cft($s, 696, 520)) . '" alt="' . esc($type) . ' - image ' . ($i + 2) . '"></div>';
        }
        $html .= '</div>';
    }
    if ($n > 1) {
        $html .= '<div class="pe-gallery-mobile-count" data-gallery-open="0">'
            . '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="3" rx="2"></rect><circle cx="9" cy="9" r="2"></circle><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"></path></svg>'
            . esc($photosLabel) . '</div>';
    }

    // Lightbox (rendered closed; opened via property.js)
    $html .= '<div class="pe-lightbox" role="dialog" aria-modal="true" style="display:none" data-gallery-lightbox>'
        . '<div class="pe-lightbox-stage">'
        . '<button type="button" class="pe-lightbox-close" aria-label="Close" data-gallery-close>'
        . '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M18 6 6 18M6 6l12 12"></path></svg></button>'
        . '<button type="button" class="pe-lightbox-nav prev" aria-label="Previous" data-gallery-prev>'
        . '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"></path></svg></button>'
        . '<div class="pe-lightbox-img"><img src="' . esc(cft($srcs[0], 1200, 675)) . '" alt="' . esc($type) . ' - image 1"></div>'
        . '<button type="button" class="pe-lightbox-nav next" aria-label="Next" data-gallery-next>'
        . '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"></path></svg></button>'
        . '<div class="pe-lightbox-count">1 / ' . $n . '</div>'
        . '<div class="pe-lightbox-thumbs">';
    foreach (array_slice($srcs, 0, 8) as $i => $s) {
        $html .= '<div class="pe-lightbox-thumb' . ($i === 0 ? ' active' : '') . '" data-gallery-thumb="' . $i . '">'
            . '<img src="' . esc(cft($s, 150, 100)) . '" alt="' . esc($type) . ' - thumbnail ' . ($i + 1) . '"></div>';
    }
    $html .= '</div></div></div>';
    $html .= '</div>';
    return $html;
}
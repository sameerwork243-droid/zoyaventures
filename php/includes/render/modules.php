<?php
// modules.php — ModuleRenderer port (src/components/modules.tsx + faq.tsx + office-card.tsx
// + contact-enquiry-form.tsx + list-property-form.tsx, static server-side).
// Sliders render all slides statically (slick DOM); client carousel behaviour is
// wired by assets/js/content-ui.js.

require_once __DIR__ . '/../store.php';
require_once __DIR__ . '/rich.php';
require_once __DIR__ . '/property-card.php';
require_once __DIR__ . '/read-more.php';
require_once __DIR__ . '/questionnaire.php';

/* ------------------------------ shared helpers ------------------------------ */

/** Static slick DOM shell - all slides rendered visible (initial frame). */
function slick_shell(string $className, array $slides, int $perView, bool $arrows = false): string
{
    $total = count($slides);
    $w = $perView > 0 ? (100 / $perView) : 100;
    $out = '<div class="slick-slider custom-slider slick-initialized ' . $className . '" dir="ltr">';
    $out .= '<div class="slick-list"><div class="slick-track" style="width:100%;left:0;">';
    foreach ($slides as $i => $s) {
        $out .= '<div data-index="' . $i . '" tabindex="-1" aria-hidden="false" class="slick-slide slick-active' . ($i === 0 ? ' slick-current' : '') . '" style="width:' . $w . '%"><div>' . $s . '</div></div>';
    }
    $out .= '</div></div>';
    if ($arrows) {
        $out .= '<div class="custom-slider-arrows">'
            . '<button class="button button-white pagination-button button-back" disabled aria-label="Previous">'
            . '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" class="arrow-left-icon"><path d="M15.75 19.5 8.25 12l7.5-7.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
            . '</button>'
            . '<button class="button button-white pagination-button button-next"' . ($total <= $perView ? ' disabled' : '') . ' aria-label="Next">'
            . '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" class="arrow-right-icon"><path d="M8.25 4.5 15.75 12l-7.5 7.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
            . '</button></div>';
    }
    $out .= '</div>';
    return $out;
}

/** FaqList port (src/components/faq.tsx) - initial state: all collapsed. */
function faq_list(array $items, ?string $title = null): string
{
    if (!count($items)) return '';
    $out = '<div class="faq-section section-p"><div class="faq-container container">';
    if ($title) $out .= '<h2 class="title">' . esc($title) . '</h2>';
    $out .= '<div class="faq-list">';
    foreach ($items as $f) {
        $out .= '<div class="accordion-item">'
            . '<button class="accordion-button collapsed" aria-expanded="false" type="button">'
            . '<span>' . esc((string) ($f['question'] ?? '')) . '</span>'
            . '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 3v10M3 8h10" stroke="#07234B" stroke-linecap="round" /></svg>'
            . '</button>'
            . '<div class="accordion-collapse collapse"><div class="accordion-body">' . rich((string) ($f['answer'] ?? '')) . '</div></div>'
            . '</div>';
    }
    $out .= '</div></div></div>';
    return $out;
}

/** Star icon repeated n times (reviews slider). */
function review_stars(int $n = 5): string
{
    $out = '';
    $path = 'M15.7691 4.85712C15.8545 4.65179 16.1454 4.65179 16.2308 4.85712L19.0654 11.6724C19.2454 12.1052 19.6525 12.4009 20.1197 12.4384L27.4774 13.0282C27.699 13.046 27.7889 13.3226 27.62 13.4673L22.0143 18.2692C21.6583 18.5742 21.5028 19.0526 21.6116 19.5086L23.3242 26.6884C23.3758 26.9047 23.1405 27.0757 22.9507 26.9598L16.6515 23.1122C16.2515 22.8679 15.7484 22.8679 15.3484 23.1122L9.04922 26.9598C8.85945 27.0757 8.62413 26.9047 8.67573 26.6884L10.3884 19.5086C10.4971 19.0526 10.3417 18.5742 9.98569 18.2692L4.37993 13.4673C4.21104 13.3226 4.30092 13.046 4.52259 13.0282L11.8802 12.4384C12.3475 12.4009 12.7545 12.1052 12.9345 11.6724L15.7691 4.85712Z';
    for ($i = 0; $i < $n; $i++) {
        $out .= '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32" fill="none" class="star-icon"><path d="' . $path . '" fill="#EE7133" stroke="#EE7133" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
    }
    return $out;
}

/* ------------------------------ global modules ------------------------------ */

/** DeveloperSlider (modules.tsx). */
function module_developer_slider(array $m): string
{
    $h = $m['heading'] ?? '';
    $h = (is_string($h) && !str_contains($h, "\xEF\xBF\xBD")) ? $h : "Partners with Dubai's leading developers";
    $slides = [];
    foreach ([...dev_logos(), ...dev_logos()] as $d) {
        $file = rawurlencode((string) $d['file']);
        $slides[] = '<div class="developer-card" tabindex="-1" style="width:100%;display:inline-block">'
            . '<div class="developer-image img-zoom">'
            . '<a class="developer-image img-zoom" href="/new-projects/developed-by-' . esc((string) $d['slug']) . '/">'
            . '<img loading="lazy" draggable="false" src="https://d3h330vgpwpjr8.cloudfront.net/x/296x/' . $file . '" '
            . 'srcSet="https://d3h330vgpwpjr8.cloudfront.net/x/118x/' . $file . ' 118w, https://d3h330vgpwpjr8.cloudfront.net/x/158x/' . $file . ' 158w, https://d3h330vgpwpjr8.cloudfront.net/x/296x/' . $file . ' 296w" '
            . 'sizes="100px 158px" alt="' . esc($d['name'] . ' - Zoya Ventures Real Estate') . '" />'
            . '</a></div></div>';
    }
    return '<div class="developer-slider-wrap">'
        . '<div class="developer-slider-container container">'
        . '<div class="d-block d-xl-flex align-items-center row">'
        . '<div class="col-xl-2 col-md-12"><p class="heading">' . esc($h) . '</p></div>'
        . '<div class="col-xl-10 col-md-12"><div class="slider-section">'
        . slick_shell('developer-slider', $slides, 5)
        . '</div></div></div></div></div>';
}

/** ReviewsSlider (modules.tsx). */
function module_reviews_slider(): string
{
    $hj = home_json();
    $reviews = $hj['reviews'] ?? [];
    $clean = [];
    foreach ($reviews as $i => $r) {
        if (!is_array($r)) continue;
        $icon = ($i % 2) ? 'women_icon_db5442e706.webp' : 'man_icon_98ac9e68af.webp';
        $card = '<div class="review-card">'
            . '<div class="d-flex card-bio">'
            . '<img loading="lazy" draggable="false" src="https://d3h330vgpwpjr8.cloudfront.net/x/70x70/' . $icon . '" '
            . 'srcSet="https://d3h330vgpwpjr8.cloudfront.net/x/70x70/' . $icon . ' 70w" sizes="(min-width: 100px) 70px" alt="' . esc(($r['name'] ?? '') . ' - Zoya Ventures Real Estate') . '" />'
            . '<div><p class="name">' . esc((string) ($r['name'] ?? '')) . '</p><p class="date">' . esc((string) ($r['date'] ?? '')) . '</p>'
            . '<div class="icons-wrap">' . review_stars(5) . '</div></div></div>'
            . '<p class="title-review">' . esc((string) ($r['title'] ?? '')) . '</p>';
        if (!empty($r['description'])) {
            $card .= '<p class="review">' . esc((string) $r['description']) . '<span class="read-more">more</span></p>';
        }
        $card .= '</div>';
        $clean[] = $card;
    }
    return '<div class="review-slider-wrap section-m reviews_slider">'
        . '<div class="review-slider-container container">'
        . '<div class="d-flex">'
        . '<div><h2 class="title">Why Our Clients Trust Us</h2>'
        . '<div class="description"><p>Discover what our customers are saying about their experiences.</p></div></div>'
        . '<a class="button button-orange more-btn" href="/about/reviews/">See all reviews</a>'
        . '</div>'
        . slick_shell('review-slider', $clean, 3)
        . '</div></div>';
}

/** Communities (modules.tsx) - static: desktop tabs + mobile accordion (collapsed). */
function module_dubai_communities(): string
{
    $coms = communities();
    $links = '';
    foreach ($coms as $c) {
        $links .= '<a href="/buy/properties-for-sale/in-' . esc($c['slug']) . '/">' . esc($c['label']) . '</a>';
    }
    $tabs = '';
    foreach (['For Sale', 'For rent', 'Off Plan'] as $t) {
        $tabs .= '<div class="accordion-item"><h2 class="title accordion-header">'
            . '<button type="button" aria-expanded="false" class="accordion-button collapsed">' . esc($t) . '</button></h2>'
            . '<div class="accordion-collapse collapse"><div class="cta-section accordion-body"><div class="tab-body">' . $links . '</div></div></div></div>';
    }
    return '<div class="dubai-communities-wrap section-p">'
        . '<div class="dubai-communities-container container">'
        . '<h2 class="title">Popular Properties in Dubai Communities</h2>'
        . '<div class="dubai-communities-tab-section d-none d-md-block">'
        . '<div class="tab-header-section"><div class="custom-tabs tab-header">'
        . '<button class="tab-button button selected-tab" type="button">For Sale</button>'
        . '<button class="tab-button button button-white" type="button">For rent</button>'
        . '<button class="tab-button button button-white" type="button">Off Plan</button>'
        . '</div></div><div class="tab-body">' . $links . '</div></div>'
        . '<div class="dubai-communities-tab-section d-block d-md-none accordion">' . $tabs . '</div>'
        . '</div></div>';
}

/** ContactModule (modules.tsx). */
function module_contact_module(array $m): string
{
    $out = '<div class="global-contact-module"><div class="container"><div class="content">';
    if (!empty($m['heading'])) $out .= '<p class="heading">' . esc((string) $m['heading']) . '</p>';
    $out .= '<p class="title">' . esc((string) ($m['title'] ?? '')) . '</p>'
        . '<div class="cta-section">'
        . '<a class="button button-orange" href="/contact/"><span>Contact Us</span></a>'
        . '<a class="button button-white-outline" href="tel:+971568308221"><span>' . country_flag() . ' +971 568 308 221</span></a>'
        . '</div></div></div></div>';
    return $out;
}

/** NewsSection (modules.tsx) - shared by global-module news_slider + modules.featured-news. */
function module_news_section(array $m): string
{
    $hj = home_json();
    $posts = $hj['featuredNews'] ?? [];
    if (!count($posts)) return '';
    $feat = $posts[0];
    $rest = array_slice($posts, 1);
    $title = $m['title'] ?? 'News';
    $out = '<div class="slider-module-wrap news-slider-wrap section-p">'
        . '<div class="slider-module-container container">'
        . '<div class="category-tabs-section"><div class="tab-header-section">'
        . '<div class="top-section"><div class="content-section"><h2 class="title"><span>' . esc((string) $title) . '</span></h2></div></div>'
        . '<div class="custom-tabs category-tabs"></div>'
        . '<div class="cta-section"><a class="button button-orange more-btn" href="/blog/">More Insights</a></div>'
        . '</div></div>'
        . '<div class="news-section">'
        . '<div class="featured-news-card">'
        . '<div class="img-section-wrap img-zoom"><a class="img-section" href="/blog/' . esc((string) ($feat['slug'] ?? '')) . '/">';
    if (!empty($feat['category'])) $out .= '<p class="img-tag">' . esc((string) $feat['category']) . '</p>';
    if (!empty($feat['image'])) {
        $out .= '<img loading="lazy" draggable="false" src="' . esc((string) $feat['image']) . '" srcSet="' . esc((string) $feat['image']) . ' 1260w, " sizes="100px 1260px, " alt="' . esc(($feat['title'] ?? '') . ' - Zoya Ventures Real Estate') . '" />';
    }
    $out .= '</a></div>'
        . '<div class="content-section">'
        . '<a class="title" href="/blog/' . esc((string) ($feat['slug'] ?? '')) . '/">' . esc((string) ($feat['title'] ?? '')) . '</a>'
        . '<p class="date">' . esc((string) ($feat['date'] ?? '')) . '</p>';
    if (!empty($feat['description'])) $out .= '<p class="description">' . esc((string) $feat['description']) . '</p>';
    $out .= '<a class="button button-white" href="/blog/' . esc((string) ($feat['slug'] ?? '')) . '/">Continue Reading</a>'
        . '</div></div>'
        . '<div class="small-news-section">';
    foreach ($rest as $b) {
        if (!is_array($b)) continue;
        $out .= '<div class="small-news-card">'
            . '<div class="img-section-wrap img-zoom"><a class="img-section" href="/blog/' . esc((string) ($b['slug'] ?? '')) . '/">';
        if (!empty($b['image'])) {
            $out .= '<img loading="lazy" draggable="false" src="' . esc((string) $b['image']) . '" srcSet="' . esc((string) $b['image']) . ' 340w, " sizes="100px 340px, " alt="' . esc(($b['title'] ?? '') . ' - Zoya Ventures Real Estate') . '" />';
        }
        $out .= '</a></div>'
            . '<div class="content-section">'
            . '<a class="title" href="/blog/' . esc((string) ($b['slug'] ?? '')) . '/">' . esc((string) ($b['title'] ?? '')) . '</a>'
            . '<p class="date">' . esc((string) ($b['date'] ?? '')) . '</p>'
            . '</div></div>';
    }
    $out .= '</div></div></div></div>';
    return $out;
}

function module_featured_slider($m)
{
    $isSignature = !empty($m['is_signature']);
    $ids = $isSignature ? signature_ids() : featured_ids();
    $sales = [];
    foreach ($ids as $l) {
        $h = by_link($l);
        if ($h) $sales[] = $h;
        if (count($sales) >= 6) break;
    }
    $cta = $m['cta_text']['cta'] ?? ($m['cta'] ?? null);
    $ctaLink = cta_href($cta, $isSignature ? '/buy/properties-for-sale/above-20000000/' : '/buy/properties-for-sale/');
    $ctaLabel = ($cta['cta_label'] ?? '') ?: ($isSignature ? 'Explore Signature' : 'View more');
    if ($isSignature) {
        $out = '<div class="singnature-slider-module-wrap" id="singnature">'
            . '<div class="singnature-slider-module-container container"><div class="row">'
            . '<div class="col-xl-3 col-lg-12"><div class="content-section">';
        if (!empty($m['logo_image']['url'])) {
            $out .= '<img loading="lazy" draggable="false" src="' . esc(cft($m['logo_image']['url'], 216, 96))
                . '" srcSet="' . esc(cft($m['logo_image']['url'], 160, 71)) . ' 160w, ' . esc(cft($m['logo_image']['url'], 216, 96)) . ' 216w"'
                . ' sizes="(max-width: 1199px) 160px, (min-width: 1199px) 216px" alt="banner-bg - Zoya Ventures Real Estate" class="sign-img" />';
        }
        $out .= '<div class="row"><div class="col-xl-12 col-md-9"><div class="content">'
            . rich($m['description']['data']['description'] ?? null)
            . '</div></div><div class="col-xl-12 col-md-3"><div class="cta-section">'
            . '<a class="button button-orange" href="' . esc($ctaLink) . '"><span>' . esc($ctaLabel) . '</span></a>'
            . '</div></div></div></div></div>'
            . '<div class="col-xl-1 col-lg-12"></div>'
            . '<div class="col-xl-8 col-lg-12"><div><div class="singnature-slider-tab-section">';
        $slides = [];
        foreach ($sales as $h) {
            $slides[] = property_card($h, false, true);
        }
        $slides[] = more_box('Dive in the World of Luxury Estate', 'Discover Refined Living with Exceptional Exclusivity', $ctaLink, 'View more properties');
        $out .= slick_shell('signature-slider', $slides, 2)
            . '</div></div></div></div></div></div>';
        return $out;
    }
    $out = '<div class="featured-slider-module-wrap section-m">'
        . '<div class="featured-slider-module-container container">'
        . '<div class="content-section tiv"><h2 class="title"><span>' . esc(($m['title'] ?? '') ?: 'Explore Property in Dubai.') . '</span></h2></div>'
        . '<div class="featured-slider-tab-section">'
        . '<div class="tab-header-section">'
        . '<div class="custom-tabs tab-header">'
        . '<button class="tab-button button selected-tab" type="button">For Sale</button>'
        . '<button class="tab-button button button-white" type="button">For Rent</button>'
        . '<button class="tab-button button button-white" type="button">Off Plan</button>'
        . '</div>'
        . '<div class="cta-section"><a class="button button-orange more-btn" href="/buy/properties-for-sale/">View more</a></div>'
        . '</div>'
        . '<div class="tab-body">';
    $slides = [];
    foreach ($sales as $h) {
        $slides[] = property_card($h, true, false);
    }
    $slides[] = more_box('Explore Thousands of Properties for Sale', 'Browse Through Our Extensive Listings to Find Your Dream Home', '/buy/properties-for-sale/', 'View more');
    $out .= slick_shell('featured-slider', $slides, 3)
        . '</div></div></div></div>';
    return $out;
}

function module_content_and_links($m)
{
    $cards = $m['cards'] ?? [];
    $cls = count($cards) === 3 ? 'three' : 'null';
    $out = '<div class="content-links-wrap section-p ">'
        . '<div class="content-links-container container">'
        . '<div class="content-section">'
        . '<h2 class="title">' . esc((string) ($m['title'] ?? '')) . '</h2>'
        . '<div class="description">' . rich($m['content']['data']['content'] ?? null) . '</div>'
        . '</div>'
        . '<div class="links-section ' . $cls . '">';
    foreach ($cards as $i => $c) {
        if (!is_array($c)) continue;
        $label = $c['cta']['cta_label'] ?? ($c['title'] ?? '');
        $href = !empty($c['cta']) ? cta_href($c['cta']) : null;
        $richText = $c['content']['data']['content'] ?? null;
        $text = $richText ?: ($c['description'] ?? '');
        $delay = $c['delay'] ?? ($i * 200);
        $out .= '<div class="link-item-wrap"><div delay="' . esc((string) $delay) . '" class="link-item">'
            . '<div class="icon-section">';
        if (!empty($c['icon']['url'])) {
            $out .= '<img loading="lazy" draggable="false" src="' . esc(cft($c['icon']['url'], 48, 48))
                . '" srcSet="' . esc(cft($c['icon']['url'], 40, 40)) . ' 40w, ' . esc(cft($c['icon']['url'], 48, 48)) . ' 48w"'
                . ' sizes="(max-width: 1199px) 40px, (min-width: 1199px) 48px" alt="icon - Zoya Ventures Real Estate" width="48" height="48" />';
        }
        $out .= '</div><div class="link-content ' . $cls . '">';
        if ($href && $href !== '#') {
            $out .= '<a class="link-title" href="' . esc($href) . '"><span>' . esc((string) $label) . '</span>'
                . '<svg width="12" height="12" class="arrow-up-right-icon" viewBox="0 0 12 12" fill="none">'
                . '<path d="M2.25 9.75 9.75 2.25M9.75 2.25 4.125 2.25M9.75 2.25V7.875" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" />'
                . '</svg></a>';
        } elseif ($label !== null && $label !== '') {
            $out .= '<span class="link-title"><span>' . esc((string) $label) . '</span></span>';
        }
        if ($richText) {
            $out .= '<div class="link-description">' . rich($richText) . '</div>';
        } else {
            $out .= '<p class="link-description">' . esc((string) $text) . '</p>';
        }
        $out .= '</div></div></div>';
    }
    $out .= '</div></div></div>';
    return $out;
}

function module_ads_banner($m)
{
    $b = $m['marketing_banner'] ?? [];
    $out = '<div class="ads-banner-wrap section-m' . (!empty($m['small']) ? ' ads-banner-wrap-small' : ' ads-banner-wrap-card') . '">'
        . '<div class="">'
        . '<div class="ads-banner-container ' . (!empty($m['small']) ? 'null ' : '') . 'container">'
        . '<div class="gradient-overlay">'
        . '<div class="banner-section">';
    if (!empty($b['bg_image']['url'])) {
        $out .= '<div class="bg-img"><img loading="lazy" draggable="false" src="' . esc(cft($b['bg_image']['url'], 1128, 368))
            . '" alt="' . esc(($b['title'] ?? '') . ' - Zoya Ventures Real Estate') . '" /></div>';
    }
    $out .= '<div class="content-section"><div class="content">';
    if (!empty($b['heading'])) {
        $out .= '<p class="heading">' . esc((string) $b['heading']) . '</p>';
    }
    $out .= '<p class="title">' . esc((string) ($b['title'] ?? '')) . '</p>'
        . '<div class="description">' . rich($b['description']['data']['description'] ?? null) . '</div>'
        . '</div>';
    if (!empty($b['cta'])) {
        $out .= '<div class="cta-section cta-flex"><a class="button button-orange btn2" href="' . esc(cta_href($b['cta'])) . '">'
            . '<span>' . esc(($b['cta']['cta_label'] ?? '') ?: 'Find out more') . '</span></a></div>';
    }
    $out .= '</div></div></div></div></div></div>';
    return $out;
}

function module_tile_block($m)
{
    $style = trim(str_replace('ads_banner', '', (string) ($m['style'] ?? '')));
    $alignRight = ($m['img_align'] ?? '') === 'right';
    $bgColor = $m['bg_color'] ?? '';
    $bgClass = ($bgColor === 'ash' || $bgColor === 'white' || $bgColor === 'light')
        ? ' ' . $bgColor
        : ($alignRight ? ' white' : '');
    $magic = is_string($m['cta']['custom_link'] ?? null) && (str_starts_with($m['cta']['custom_link'], '#') || str_starts_with($m['cta']['custom_link'], '$'));
    $isAward = $style === 'award' || str_contains($style, 'award');
    $isYoutube = is_string($m['video_url'] ?? null) && (bool) preg_match('/youtu\.?be/', $m['video_url']);
    $out = '<div class="tile-block-wrapper ' . ($style ? $style . ' ' : '') . 'section-m' . $bgClass . '">'
        . '<div class="tile-block-container ' . ($alignRight ? 'align-img-right contain-image ' : '') . 'container">'
        . '<div class="img-section"><div>';
    if (!empty($m['image']['url']) && !$isYoutube) {
        $W = $alignRight ? 640 : 696;
        $H = $alignRight ? 500 : 400;
        $sw = $alignRight ? 340 : 336;
        $sh = $alignRight ? 0 : 240;
        $base = cft($m['image']['url'], $W, $H);
        $small = $sh ? cft($m['image']['url'], $sw, $sh) : cfw($m['image']['url'], $sw);
        $out .= '<img loading="lazy" draggable="false" src="' . esc($base)
            . '" srcSet="' . esc($small) . ' ' . $sw . 'w, ' . esc($base) . ' ' . $W . 'w, "'
            . ' sizes="(max-width: 480px) ' . $sw . 'px, (min-width: 700px) ' . $W . 'px, "'
            . ' alt="' . esc(($m['title'] ?? '') . ' - Zoya Ventures Real Estate') . '" />';
    }
    if (!empty($m['video_url']) && $isYoutube) {
        $out .= '<a class="video-link-wrap" href="' . esc((string) $m['video_url']) . '" target="_blank" rel="noreferrer" aria-label="Play video">'
            . '<button class="play-button" aria-label="play button" type="button"></button></a>';
    }
    $out .= '</div></div>'
        . '<div class="content-section"><div>';
    if ($isAward) {
        $out .= award_badge_markup();
    }
    if (!empty($m['heading'])) {
        $out .= '<p class="heading">' . esc((string) $m['heading']) . '</p>';
    }
    $out .= '<div class="design_title">' . rich($m['design_title']['data']['design_title'] ?? null) . '</div>'
        . '<h3 class="title">' . esc((string) ($m['title'] ?? '')) . '</h3>'
        . '<div class="description">' . rich($m['description']['data']['description'] ?? null) . '</div>';
    if (is_array($m['icon_stats'] ?? null) && count($m['icon_stats']) > 0) {
        $out .= '<div class="icon-stats-section">';
        foreach ($m['icon_stats'] as $s) {
            if (!is_array($s)) continue;
            $out .= '<div class="icon-stat">';
            if (!empty($s['image']['url'])) {
                $out .= '<img draggable="false" src="' . esc((string) $s['image']['url']) . '" alt="" />';
            }
            $out .= '<p class="text">' . esc((string) ($s['text'] ?? '')) . '</p></div>';
        }
        $out .= '</div>';
    }
    if (!empty($m['cta'])) {
        $label = ($m['cta']['cta_label'] ?? '') ?: 'Find out more';
        if ($magic) {
            $out .= '<button class="button  button-orange"><span>' . esc((string) $label) . '</span></button>';
        } else {
            $out .= '<a href="' . esc(cta_href($m['cta'])) . '" class="button  ' . ($isAward ? 'button-blue' : 'button-orange') . '">'
                . '<span>' . esc((string) $label) . '</span>'
                . '<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-right-icon">'
                . '<path d="M9.5 3L14.5 8M14.5 8L9.5 13M14.5 8H2.5" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" />'
                . '</svg></a>';
        }
    }
    $out .= '</div></div></div></div>';
    return $out;
}

function award_badge_markup(): string
{
    $badgePaths = [
        'M0 0V110.311L38.4164 131L76.8328 110.311V0H0Z',
        'M72.4851 117.064H71.7341V118.707H71.0474V117.064H70.3031V116.402H72.4861V117.064H72.4851ZM75.5354 118.707H74.8487V117.757L74.3291 118.682H74.0208L73.5012 117.757V118.707H72.8145V116.402H73.3985L74.1754 117.795L74.9524 116.402H75.5363V118.707H75.5354Z',
        'M76.8328 0H0V76.8328H76.8328V0Z',
        'M19.116 11.8746V12.9148C19.116 14.8413 18.4994 16.402 17.2663 17.5958C16.0331 18.7906 14.434 19.3879 12.4882 19.3879C10.407 19.3879 8.67348 18.7137 7.30586 17.3653C5.93823 15.9977 5.26402 14.341 5.26402 12.3952C5.26402 10.4494 5.93823 8.77347 7.28665 7.40585C8.65427 6.03822 10.3302 5.36401 12.3336 5.36401C14.8576 5.36401 17.054 6.59718 18.2094 8.50456L15.9361 9.81456C15.3003 8.71681 13.9135 7.9456 12.3144 7.9456C11.0236 7.9456 9.96427 8.36914 9.13544 9.21718C8.30756 10.046 7.90227 11.1053 7.90227 12.3961C7.90227 13.6869 8.32581 14.7271 9.15465 15.5559C10.0027 16.3838 11.1196 16.7891 12.5065 16.7891C14.5675 16.7891 15.9544 15.8258 16.4365 14.2267H12.3528V11.8766H19.1151L19.116 11.8746Z',
        'M23.0969 11.1427C23.5358 9.90955 24.6345 9.29297 25.8792 9.29297V12.0676C25.1656 11.9716 24.5068 12.1252 23.9392 12.5305C23.3716 12.9348 23.0969 13.5898 23.0969 14.5147V19.1189H20.7362V9.48601H23.0969V11.1427Z',
        'M29.8668 15.3233C30.1943 16.5373 31.1 17.1346 32.5829 17.1346C33.527 17.1346 34.2588 16.8071 34.7409 16.1714L36.7443 17.3267C35.8002 18.6944 34.3942 19.3878 32.5444 19.3878C30.9454 19.3878 29.6738 18.9056 28.7105 17.9433C27.7472 16.98 27.2651 15.7661 27.2651 14.3024C27.2651 12.8387 27.7472 11.644 28.6913 10.6807C29.6354 9.69819 30.8685 9.21606 32.3514 9.21606C33.7575 9.21606 34.933 9.69819 35.8387 10.6807C36.7635 11.6632 37.2255 12.8579 37.2255 14.3024C37.2255 14.6299 37.1871 14.9574 37.1295 15.3233H29.8668ZM34.7409 13.3967C34.4518 12.0867 33.4885 11.4509 32.3524 11.4509C31.0232 11.4509 30.1175 12.1636 29.8284 13.3967H34.7409Z',
        'M46.1852 9.48592H48.6707V19.1188H46.1852V17.9817C45.4341 18.9258 44.394 19.3878 43.0446 19.3878C41.6952 19.3878 40.6561 18.9056 39.7312 17.9231C38.8255 16.9406 38.3636 15.7267 38.3636 14.3014C38.3636 12.8762 38.8265 11.6814 39.7312 10.6989C40.6561 9.71642 41.7538 9.21509 43.0446 9.21509C44.3354 9.21509 45.4332 9.67705 46.1852 10.6211V9.48496V9.48592ZM43.5075 17.0184C44.2787 17.0184 44.9136 16.7678 45.4149 16.2674C45.9345 15.7468 46.1852 15.0918 46.1852 14.3024C46.1852 13.5129 45.9345 12.8579 45.4149 12.3566C44.9136 11.836 44.2787 11.5863 43.5075 11.5863C42.7363 11.5863 42.1015 11.837 41.6002 12.3566C41.0988 12.8579 40.8491 13.5129 40.8491 14.3024C40.8491 15.0918 41.0998 15.7468 41.6002 16.2674C42.1005 16.7678 42.7363 17.0184 43.5075 17.0184Z',
        'M56.8208 11.8744H54.6435V15.8813C54.6435 16.9214 55.3946 16.9406 56.8208 16.8638V19.1179C53.4104 19.503 52.158 18.5205 52.158 15.8813V11.8744H50.4821V9.48589H52.158V7.5401L54.6435 6.78906V9.48589H56.8208V11.8744Z',
        'M12.0656 22.5708C13.3564 22.5708 14.4542 23.0135 15.3406 23.8808C16.2271 24.7481 16.6698 25.8266 16.6698 27.0982C16.6698 28.3698 16.2271 29.4483 15.3406 30.3156C14.4542 31.1828 13.3564 31.6256 12.0656 31.6256H9.6963V36.0569H7.03789V22.5708H12.0666H12.0656ZM12.0656 29.14C13.2018 29.14 14.0306 28.2535 14.0306 27.0982C14.0306 25.9428 13.2028 25.0563 12.0656 25.0563H9.6963V29.14H12.0656Z',
        'M20.9667 22.6956H18.4811V36.0568H20.9667V22.6956Z',
        'M30.9463 26.4237H33.4319V36.0556H30.9463V34.9185C30.1953 35.8626 29.1552 36.3245 27.8058 36.3245C26.4564 36.3245 25.4173 35.8424 24.4924 34.8609C23.5867 33.8784 23.1248 32.6644 23.1248 31.2392C23.1248 29.8139 23.5867 28.6192 24.4924 27.6367C25.4173 26.6542 26.515 26.1528 27.8058 26.1528C29.0966 26.1528 30.1943 26.6148 30.9463 27.5589V26.4227V26.4237ZM28.2687 33.9562C29.0399 33.9562 29.6748 33.7055 30.1761 33.2051C30.6957 32.6846 30.9463 32.0296 30.9463 31.2401C30.9463 30.4507 30.6957 29.7957 30.1761 29.2943C29.6748 28.7738 29.0399 28.5241 28.2687 28.5241C27.4975 28.5241 26.8627 28.7748 26.3613 29.2943C25.86 29.7957 25.6103 30.4507 25.6103 31.2401C25.6103 32.0296 25.861 32.6846 26.3613 33.2051C26.8617 33.7055 27.4985 33.9562 28.2687 33.9562Z',
        'M37.0161 34.8619C36.0528 33.8794 35.5707 32.6846 35.5707 31.2402C35.5707 29.7957 36.0528 28.601 37.0161 27.6185C37.9986 26.636 39.2126 26.1548 40.657 26.1548C42.526 26.1548 44.1827 27.1181 44.9722 28.6403L42.8343 29.8927C42.4492 29.1032 41.6203 28.6019 40.6378 28.6019C39.155 28.6019 38.0563 29.6997 38.0563 31.2411C38.0563 31.9922 38.3069 32.628 38.7881 33.1293C39.2692 33.6105 39.8858 33.8611 40.6378 33.8611C41.6395 33.8611 42.4684 33.379 42.8535 32.5895L45.0115 33.8227C44.1645 35.345 42.526 36.3275 40.658 36.3275C39.2135 36.3275 37.9996 35.8453 37.0171 34.8628',
        'M48.98 32.2611C49.3075 33.475 50.2132 34.0724 51.696 34.0724C52.6401 34.0724 53.3719 33.7449 53.8541 33.1091L55.8575 34.2645C54.9134 35.6321 53.5074 36.3255 51.6576 36.3255C50.0585 36.3255 48.7869 35.8434 47.8237 34.8811C46.8604 33.9178 46.3782 32.7038 46.3782 31.2401C46.3782 29.7765 46.8604 28.5817 47.8044 27.6184C48.7485 26.6359 49.9817 26.1538 51.4646 26.1538C52.8706 26.1538 54.0462 26.635 54.9518 27.6184C55.8767 28.6009 56.3387 29.7957 56.3387 31.2401C56.3387 31.5676 56.3002 31.8951 56.2426 32.2611H48.98ZM53.8541 30.3345C53.565 29.0245 52.6017 28.3887 51.4655 28.3887C50.1363 28.3887 49.2307 29.1013 48.9416 30.3345H53.8541Z',
        'M15.186 40.1816V42.598H11.5451V52.9935H8.88668V42.598H5.26401V40.1816H15.186Z',
        'M19.3273 53.2635C17.9021 53.2635 16.6881 52.7814 15.7056 51.7989C14.7231 50.8164 14.241 49.6024 14.241 48.1772C14.241 46.7519 14.7231 45.5572 15.7056 44.5747C16.6881 43.5922 17.9021 43.0908 19.3273 43.0908C20.7526 43.0908 21.9665 43.5912 22.949 44.5747C23.9315 45.5572 24.4328 46.7519 24.4328 48.1772C24.4328 49.6024 23.9325 50.8164 22.949 51.7989C21.9665 52.7814 20.7526 53.2635 19.3273 53.2635ZM19.3273 50.8356C20.0783 50.8356 20.6949 50.5849 21.1963 50.0845C21.6966 49.5832 21.9473 48.9474 21.9473 48.1772C21.9473 47.4069 21.6966 46.7711 21.1963 46.2698C20.6949 45.7694 20.0793 45.5187 19.3273 45.5187C18.5753 45.5187 17.9597 45.7694 17.4583 46.2698C16.9762 46.7711 16.7265 47.4059 16.7265 48.1772C16.7265 48.9484 16.9772 49.5832 17.4583 50.0845C17.9587 50.5849 18.5753 50.8356 19.3273 50.8356Z',
        'M9.40529 70.7197L5.62895 57.2336H8.42183L11.0034 67.2325L13.8165 57.2336H16.0897L18.922 67.2325L21.5036 57.2336H24.2964L20.5211 70.7197H17.4958L14.9526 61.8186L12.4296 70.7197H9.40529Z',
        'M29.1533 70.9898C27.728 70.9898 26.5141 70.5077 25.5316 69.5252C24.5491 68.5427 24.067 67.3287 24.067 65.9035C24.067 64.4782 24.5491 63.2643 25.5316 62.2818C26.5141 61.2993 27.728 60.8172 29.1533 60.8172C30.5785 60.8172 31.7925 61.2993 32.775 62.2818C33.7575 63.2643 34.2396 64.4782 34.2396 65.9035C34.2396 67.3287 33.7575 68.5427 32.775 69.5252C31.7925 70.5077 30.5785 70.9898 29.1533 70.9898ZM29.1533 68.581C29.9043 68.581 30.5209 68.3303 31.0222 67.8299C31.5226 67.3286 31.7733 66.6928 31.7733 65.9225C31.7733 65.1523 31.5226 64.5164 31.0222 64.0151C30.5209 63.5147 29.9053 63.264 29.1533 63.264C28.4013 63.264 27.7857 63.5147 27.2843 64.0151C26.8022 64.5164 26.5525 65.1513 26.5525 65.9225C26.5525 66.6938 26.8032 67.3286 27.2843 67.8299C27.7847 68.3303 28.4013 68.581 29.1533 68.581Z',
        'M38.7293 70.9898C37.304 70.9898 36.0901 70.5077 35.1076 69.5252C34.1251 68.5427 33.643 67.3287 33.643 65.9035C33.643 64.4782 34.1251 63.2643 35.1076 62.2818C36.0901 61.2993 37.304 60.8172 38.7293 60.8172C40.1546 60.8172 41.3685 61.2993 42.351 62.2818C43.3335 63.2643 43.8156 64.4782 43.8156 65.9035C43.8156 67.3287 43.3335 68.5427 42.351 69.5252C41.3685 70.5077 40.1546 70.9898 38.7293 70.9898ZM38.7293 68.581C39.4803 68.581 40.0969 68.3303 40.5982 67.8299C41.0986 67.3286 41.3493 66.6928 41.3493 65.9225C41.3493 65.1523 41.0986 64.5164 40.5982 64.0151C40.0969 63.5147 39.4813 63.264 38.7293 63.264C37.9773 63.264 37.3617 63.5147 36.8603 64.0151C36.3782 64.5164 36.1285 65.1513 36.1285 65.9225C36.1285 66.6938 36.3792 67.3286 36.8603 67.8299C37.3607 68.3303 37.9773 68.581 38.7293 68.581Z',
        'M56.3816 57.2336V70.7197H53.9161L47.5408 62.3588V70.7197H44.8824V57.2336H47.348L53.7229 65.6148V57.2336H56.3816Z',
        'M60.6884 62.3564C61.4394 61.2212 62.6726 60.8172 63.9047 60.8172V63.8698C63.1293 63.7522 62.4007 63.9268 61.7705 64.3988C61.1597 64.8515 60.8225 65.5942 60.8225 66.6215V70.7197H58.1641V60.9622H60.6884V62.3564Z',
        'M69.0606 67.7638C69.4088 69.1548 70.4127 69.828 72.0735 69.828C73.1192 69.828 73.9331 69.4556 74.4621 68.7512L76.6992 70.0258C75.6535 71.5453 74.0685 72.322 71.9447 72.322C70.154 72.322 68.7188 71.7767 67.6732 70.6861C66.6082 69.5761 66.0763 68.1867 66.0763 66.5378C66.0763 64.8889 66.6082 63.5175 67.6538 62.4075C68.6995 61.2975 70.0672 60.7338 71.7952 60.7338C73.2979 60.7338 74.6014 61.2975 75.647 62.4075C76.6732 63.5175 77.1863 64.8889 77.1863 66.5378C77.1863 66.8739 77.1476 67.2474 77.0901 67.6447L69.0606 67.7638ZM74.5277 65.518C74.2215 64.0483 73.1758 63.3431 71.9076 63.3431C70.4488 63.3431 69.4218 64.1241 69.1156 65.518H74.5277Z',
        'M12.0656 83.4284C13.3564 83.4284 14.4542 83.8712 15.3406 84.7384C16.2271 85.6057 16.6698 86.6842 16.6698 87.9558C16.6698 89.2275 16.2271 90.306 15.3406 91.1732C14.4542 92.0405 13.3564 92.4832 12.0656 92.4832H9.6963V96.9145H7.03789V83.4284H12.0666H12.0656ZM12.0656 89.9976C13.2018 89.9976 14.0306 89.1111 14.0306 87.9558C14.0306 86.8004 13.2028 85.9139 12.0656 85.9139H9.6963V89.9976H12.0656Z',
        'M20.9667 83.5532H18.4811V96.9144H20.9667V83.5532Z',
        'M26.7049 80.4863H29.1905V96.9143H26.7049V95.7772C25.9539 96.7213 24.9138 97.1832 23.5644 97.1832C22.215 97.1832 21.1759 96.7011 20.251 95.7186C19.3453 94.7361 18.8834 93.5221 18.8834 92.0968C18.8834 90.6716 19.3453 89.4769 20.251 88.4944C21.1759 87.5119 22.2736 87.0105 23.5644 87.0105C24.8552 87.0105 25.9529 87.4725 26.7049 88.4166V87.2804V80.4863ZM24.0273 94.8138C24.7985 94.8138 25.4334 94.5632 25.9347 94.0628C26.4543 93.5423 26.7049 92.8872 26.7049 92.0978C26.7049 91.3084 26.4543 90.6534 25.9347 90.152C25.4334 89.6315 24.7985 89.3818 24.0273 89.3818C23.2561 89.3818 22.6213 89.6325 22.1199 90.152C21.6186 90.6534 21.3689 91.3084 21.3689 92.0978C21.3689 92.8872 21.6196 93.5423 22.1199 94.0628C22.6203 94.5632 23.2571 94.8138 24.0273 94.8138Z',
        'M31.1499 96.9144V83.4283H33.7238C35.1271 83.4283 36.2441 83.7945 37.055 84.5455C37.866 85.2966 38.277 86.2824 38.277 87.5221C38.277 88.1243 38.1616 88.6489 37.9498 89.0948C37.738 89.5408 37.4126 89.9133 36.9735 90.212C37.8282 90.531 38.5156 91.0611 39.017 91.8122C39.5387 92.5632 39.8004 93.4659 39.8004 94.5206C39.8004 95.8056 39.3895 96.838 38.5676 97.6176C37.7458 98.3973 36.6624 98.7871 35.3172 98.7871H31.1499V96.9144ZM33.6355 91.1344H33.6723V94.9965H35.5112C36.1217 94.9965 36.601 94.8873 36.9682 94.6497C37.3354 94.4313 37.5289 94.069 37.5289 93.5625C37.5289 93.0559 37.3354 92.6843 36.9682 92.4468C36.6009 92.2093 36.1217 92.1001 35.5112 92.1001H33.6355V91.1344ZM35.1624 84.9672H33.6355V88.9975H35.1624C35.7537 88.9975 36.2529 88.869 36.6404 88.6122C37.0279 88.3747 37.2216 87.9791 37.2216 87.4257C37.2216 86.8723 37.0279 86.4729 36.6404 86.2353C36.2529 85.9785 35.7537 85.8501 35.1624 85.8501V84.9672Z',
        'M44.2016 84.0884C44.9526 82.9532 46.1858 82.5492 47.4179 82.5492V85.6018C46.6425 85.4842 45.9139 85.6588 45.2837 86.1308C44.6729 86.5835 44.3357 87.3262 44.3357 88.3535V92.4517H41.6773V82.5492H44.2016V84.0884ZM47.6147 92.4517H45.0585V82.5492H47.6147V92.4517Z',
        'M51.2256 84.0884C51.9766 82.9532 53.2098 82.5492 54.4419 82.5492V85.6018C53.6665 85.4842 52.9379 85.6588 52.3077 86.1308C51.6969 86.5835 51.3597 87.3262 51.3597 88.3535V92.4517H48.7013V82.5492H51.2256V84.0884ZM54.6387 92.4517H52.0825V82.5492H54.6387V92.4517Z',
        'M58.402 92.4517V82.5493H60.8867V83.6864C61.6377 82.7423 62.6778 82.2803 64.0272 82.2803C65.3766 82.2803 66.4157 82.7624 67.3406 83.7449C68.2463 84.7274 68.7082 85.9414 68.7082 87.3666C68.7082 88.7919 68.2463 89.9866 67.3406 90.9691C66.4157 91.9516 65.318 92.453 64.0272 92.453C62.7364 92.453 61.6387 91.991 60.8867 91.0469V92.1831V92.4517H58.402ZM64.0798 90.1543C64.851 90.1543 65.4859 89.9036 65.9872 89.4032C66.5068 88.8827 66.7574 88.2277 66.7574 87.4382C66.7574 86.6488 66.5068 85.9938 65.9872 85.4924C65.4859 84.9719 64.851 84.7222 64.0798 84.7222C63.3086 84.7222 62.6738 84.9729 62.1724 85.4924C61.6711 85.9938 61.4214 86.6488 61.4214 87.4382C61.4214 88.2277 61.6721 88.8827 62.1724 89.4032C62.6728 89.9036 63.3096 90.1543 64.0798 90.1543Z',
        'M8.22678 102.435C9.35995 102.435 10.3158 102.773 11.0753 103.449C11.8539 104.126 12.2432 105.02 12.2432 106.132C12.2432 107.243 11.8539 108.137 11.0753 108.814C10.3158 109.49 9.35995 109.829 8.22678 109.829H5.26401V113.424H2.6056V102.435H8.22678ZM8.14803 107.536C8.95777 107.536 9.57437 107.302 9.998 106.834C10.4216 106.347 10.6335 105.737 10.6335 105.004C10.6335 104.271 10.4216 103.67 9.998 103.202C9.57437 102.734 8.95777 102.5 8.14803 102.5H5.26401V107.536H8.14803Z',
        'M16.0305 113.424H13.2456V102.435H16.0305V103.552C16.8402 102.548 17.8268 102.045 19.0106 102.045C20.1345 102.045 21.0225 102.443 21.6751 103.238C22.3277 104.033 22.6552 105.024 22.6552 106.212V113.424H19.8703V106.945C19.8703 106.192 19.6522 105.571 19.2161 105.083C18.7799 104.596 18.2238 104.352 17.5677 104.352C16.9117 104.352 16.3791 104.578 15.9699 105.03C15.5799 105.464 15.385 106.023 15.385 106.706V113.424H16.0305Z',
        'M26.4631 99.6032H29.2674V113.424H26.4631V112.366C25.6534 113.349 24.6668 113.841 23.4831 113.841C22.3592 113.841 21.4902 113.443 20.8376 112.648C20.1851 111.852 19.8576 110.862 19.8576 109.674V102.403H22.6424V108.783C22.6424 109.536 22.8606 110.157 23.2967 110.645C23.7328 111.132 24.2889 111.376 24.945 111.376C25.601 111.376 26.1336 111.15 26.5429 110.698C26.9329 110.264 27.1278 109.705 27.1278 109.022V102.403H26.4631V99.6032Z',
        'M34.9159 113.958C33.6749 113.958 32.6839 113.562 31.9431 112.77C31.2018 111.998 30.8305 110.957 30.8305 109.647C30.8305 108.337 31.2018 107.306 31.9431 106.533C32.6839 105.761 33.6749 105.385 34.9159 105.385C36.1569 105.385 37.148 105.761 37.8887 106.533C38.6299 107.306 39.0012 108.337 39.0012 109.647C39.0012 110.957 38.6299 111.998 37.8887 112.77C37.148 113.562 36.1569 113.958 34.9159 113.958ZM34.8958 111.526C35.568 111.526 36.0892 111.319 36.4582 110.905C36.8271 110.491 37.0117 109.965 37.0117 109.328C37.0117 108.69 36.8271 108.164 36.4582 107.75C36.0892 107.336 35.568 107.129 34.8958 107.129C34.2237 107.129 33.7025 107.336 33.3335 107.75C32.9645 108.164 32.7799 108.69 32.7799 109.328C32.7799 109.965 32.9645 110.491 33.3335 110.905C33.7025 111.319 34.2237 111.526 34.8958 111.526Z',
        'M43.5624 113.424H40.7776V102.435H43.5624V103.552C44.3721 102.548 45.3587 102.045 46.5424 102.045C47.6663 102.045 48.5543 102.443 49.2069 103.238C49.8595 104.033 50.187 105.024 50.187 106.212V113.424H47.4021V106.945C47.4021 106.192 47.184 105.571 46.7479 105.083C46.3117 104.596 45.7556 104.352 45.0995 104.352C44.4435 104.352 43.9109 104.578 43.5017 105.03C43.1117 105.464 42.9168 106.023 42.9168 106.706V113.424H43.5624Z',
        'M54.1199 113.424H51.335V102.435H54.1199V103.552C54.9296 102.548 55.9162 102.045 57.0999 102.045C58.2238 102.045 59.1118 102.443 59.7644 103.238C60.417 104.033 60.7445 105.024 60.7445 106.212V113.424H57.9596V106.945C57.9596 106.192 57.7415 105.571 57.3054 105.083C56.8692 104.596 56.3131 104.352 55.657 104.352C55.001 104.352 54.4684 104.578 54.0592 105.03C53.6692 105.464 53.4743 106.023 53.4743 106.706V113.424H54.1199Z',
        'M68.4337 113.958C67.1927 113.958 66.2016 113.562 65.4609 112.77C64.7196 111.998 64.3483 110.957 64.3483 109.647C64.3483 108.337 64.7196 107.306 65.4609 106.533C66.2016 105.761 67.1927 105.385 68.4337 105.385C69.6747 105.385 70.6657 105.761 71.4065 106.533C72.1477 107.306 72.519 108.337 72.519 109.647C72.519 110.957 72.1477 111.998 71.4065 112.77C70.6657 113.562 69.6747 113.958 68.4337 113.958ZM68.4136 111.526C69.0858 111.526 69.607 111.319 69.976 110.905C70.3449 110.491 70.5295 109.965 70.5295 109.328C70.5295 108.69 70.3449 108.164 69.976 107.75C69.607 107.336 69.0858 107.129 68.4136 107.129C67.7415 107.129 67.2203 107.336 66.8513 107.75C66.4823 108.164 66.2977 108.69 66.2977 109.328C66.2977 109.965 66.4823 110.491 66.8513 110.905C67.2203 111.319 67.7415 111.526 68.4136 111.526Z',
    ];
    $out = '<a href="/blog/provident-estate-great-place-to-work-certification/" aria-label="Great Place to Work certification">'
        . '<svg xmlns="http://www.w3.org/2000/svg" width="77" height="131" viewBox="0 0 77 131" fill="none">';
    foreach ($badgePaths as $i => $d) {
        $fill = $i === 0 ? '#002171' : ($i === 1 ? '#002171' : ($i === 2 ? '#FF1628' : 'white'));
        $out .= '<path d="' . $d . '" fill="' . $fill . '"></path>';
    }
    $out .= '</svg></a>'
        . '<div class="award-bki">'
        . '<div class="text"><a href="/about/our-awards/">100+Awards</a></div>'
        . '<div class="google"><svg width="18" height="18" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Google">'
        . '<path fill="#FFC107" d="M43.611 20.083H42V20H24v8h11.303c-1.649 4.657-6.08 8-11.303 8-6.627 0-12-5.373-12-12s5.373-12 12-12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 12.955 4 4 12.955 4 24s8.955 20 20 20 20-8.955 20-20c0-1.341-.138-2.65-.389-3.917z" />'
        . '<path fill="#FF3D00" d="M6.306 14.691l6.571 4.819C14.655 15.108 18.961 12 24 12c3.059 0 5.842 1.154 7.961 3.039l5.657-5.657C34.046 6.053 29.268 4 24 4 16.318 4 9.656 8.337 6.306 14.691z" />'
        . '<path fill="#4CAF50" d="M24 44c5.166 0 9.86-1.977 13.409-5.192l-6.19-5.238C29.211 35.091 26.715 36 24 36c-5.202 0-9.619-3.317-11.283-7.946l-6.522 5.025C9.505 39.556 16.227 44 24 44z" />'
        . '<path fill="#1976D2" d="M43.611 20.083H42V20H24v8h11.303c-.792 2.237-2.231 4.166-4.087 5.571.001-.001.002-.001.003-.002l6.19 5.238C36.971 39.205 44 34 44 24c0-1.341-.138-2.65-.389-3.917z" />'
        . '</svg></div>'
        . '<div class="google-review">'
        . '<div class="txt">4.7 </div>'
        . '<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg">'
        . '<path d="M6.15503 4.83041L1.50914 5.53539L1.42685 5.55292C1.30229 5.58753 1.18873 5.65612 1.09778 5.75169C1.00682 5.84726 0.941731 5.96637 0.90915 6.09688C0.87657 6.22739 0.877667 6.36461 0.912329 6.49452C0.946991 6.62444 1.01398 6.7424 1.10645 6.83636L4.47217 10.2652L3.67844 15.1086L3.66897 15.1925C3.66135 15.3273 3.6881 15.4618 3.74649 15.5823C3.80488 15.7028 3.89281 15.8048 4.00127 15.878C4.10974 15.9512 4.23484 15.9929 4.36378 15.9989C4.49271 16.0048 4.62084 15.9748 4.73505 15.9119L8.89014 13.6255L13.0358 15.9119L13.1086 15.947C13.2288 15.9965 13.3594 16.0117 13.4871 15.991C13.6147 15.9703 13.7348 15.9144 13.835 15.829C13.9352 15.7437 14.012 15.632 14.0573 15.5054C14.1027 15.3788 14.115 15.2419 14.0931 15.1086L13.2986 10.2652L16.6658 6.8356L16.7226 6.77082C16.8038 6.66623 16.857 6.541 16.8768 6.40789C16.8966 6.27477 16.8824 6.13853 16.8356 6.01305C16.7887 5.88756 16.7109 5.77732 16.6101 5.69355C16.5093 5.60977 16.3891 5.55547 16.2617 5.53616L11.6158 4.83041L9.53896 0.42525C9.47887 0.297618 9.38584 0.190142 9.2704 0.114987C9.15496 0.039832 9.02172 0 8.88577 0C8.74982 0 8.61659 0.039832 8.50115 0.114987C8.38571 0.190142 8.29267 0.297618 8.23258 0.42525L6.15503 4.83041Z" fill="#F89811" />'
        . '</svg>'
        . '<div class="txt-1">440+ Reviews</div>'
        . '</div></div>';
    return $out;
}

function module_content_and_stats($m)
{
    $out = '<div class="content-and-stats-wrap section-m">'
        . '<div class="content-and-stats-container container">';
    if (!empty($m['heading'])) {
        $out .= '<p class="heading">' . esc((string) $m['heading']) . '</p>';
    }
    $out .= '<div class="content-section">';
    if (!empty($m['title'])) {
        $out .= '<p class="main-content">' . esc((string) $m['title']) . '</p>';
    }
    if (!empty($m['description']['data']['description'])) {
        $out .= '<div class="description">' . rich($m['description']['data']['description']) . '</div>';
    }
    $out .= '</div></div></div>';
    return $out;
}

function module_faq($m)
{
    $items = [];
    foreach (($m['faqs'] ?? []) as $f) {
        if (!is_array($f)) continue;
        $answer = $f['answer']['data']['answer'] ?? '';
        if (!$answer) continue;
        $items[] = ['question' => $f['question'] ?? '', 'answer' => $answer];
    }
    return faq_list($items, $m['title'] ?? null);
}

function module_our_services($m)
{
    $placeholder = 'https://d3h330vgpwpjr8.cloudfront.net/x/1128x752/placeholder.jpg';
    $out = '<div class="our-services-wrap section-m grid">'
        . '<div class="our-services-container container">';
    if (!empty($m['heading'])) {
        $out .= '<p class="heading">' . esc((string) $m['heading']) . '</p>';
    }
    $out .= '<div class="design_title">' . rich($m['design_title']['data']['design_title'] ?? null) . '</div>'
        . '<h2 class="title">' . esc(((string) ($m['title'] ?? '')) ?: ((string) ($m['heading'] ?? ''))) . '</h2>'
        . '<div class="services-section">';
    $slides = [];
    foreach (($m['services'] ?? []) as $s) {
        if (!is_array($s)) continue;
        $href = !empty($s['cta']) ? cta_href($s['cta']) : '#';
        $label = $s['cta']['cta_label'] ?? ($s['title'] ?? '');
        $u = $s['image']['url'] ?? '';
        $src = $u ? cft($u, 1128, 752) : $placeholder;
        $card = '<div class="service-item">'
            . '<a class="img-section img-zoom" href="' . esc($href) . '">'
            . '<img loading="lazy" draggable="false" src="' . esc($src) . '" alt="' . esc(((string) $label ?: 'Service') . ' - Zoya Ventures Real Estate') . '" />'
            . '</a>'
            . '<div class="content-section false">'
            . '<a class="title" href="' . esc($href) . '"><span>' . esc((string) $label) . '</span></a>';
        if (!empty($s['description'])) {
            $card .= '<p class="description">' . esc((string) $s['description']) . '</p>';
        }
        $card .= '</div></div>';
        $slides[] = $card;
    }
    $out .= slick_shell('services-slider', $slides, 4, true)
        . '</div></div></div>';
    return $out;
}

function module_office_location($m)
{
    $offices = $m['offices'] ?? [];
    if (!count($offices)) return '';
    $out = '<div class="office-listing-wrap section-m">'
        . '<div class="office-listing-container container">'
        . '<div class="office-listing-section">';
    foreach ($offices as $o) {
        if (!is_array($o)) continue;
        $href = '/contact/' . esc((string) ($o['slug'] ?? '')) . '/';
        $maps = (isset($o['latitude'], $o['longitude']) && $o['latitude'] !== null && $o['longitude'] !== null)
            ? 'https://www.google.com/maps/search/?api=1&query=' . esc((string) $o['latitude']) . ',' . esc((string) $o['longitude'])
            : null;
        $phoneRaw = (string) ($o['phone'] ?? '');
        $out .= '<div class="office-item" data-title="' . esc((string) ($o['title'] ?? '')) . '" data-address="' . esc((string) ($o['address'] ?? '')) . '"'
            . ' data-phone="' . esc($phoneRaw) . '" data-phone-tel="' . esc(preg_replace('/\s/', '', $phoneRaw)) . '"'
            . ($maps ? ' data-maps="' . esc($maps) . '"' : '') . ' data-href="' . $href . '">'
            . '<a class="img-section img-zoom" href="' . $href . '">';
        if (!empty($o['tile_image']['url'])) {
            $out .= '<img loading="lazy" draggable="false" src="' . esc((string) $o['tile_image']['url']) . '" alt="' . esc(($o['title'] ?? '') . ' - Zoya Ventures Real Estate') . '" />';
        }
        $out .= '</a>'
            . '<div class="about-office">'
            . '<a class="name" href="' . $href . '">' . esc((string) ($o['title'] ?? '')) . '</a>'
            . '<p class="address">' . esc((string) ($o['address'] ?? '')) . '</p>';
        if ($maps) {
            $out .= '<a href="' . $maps . '" class="maps-link" target="_blank" rel="noreferrer"><span>View on Google Maps</span></a>';
        }
        $out .= '</div>'
            . '<div class="divider"></div>'
            . '<div class="phone-section">'
            . '<p class="sub-title">Phone</p>';
        if ($phoneRaw !== '') {
            $out .= '<a href="tel:' . esc(preg_replace('/\s/', '', $phoneRaw)) . '" class="phone">' . esc($phoneRaw) . '</a>';
        }
        $out .= '</div>'
            . '<div class="divider"></div>'
            . '<div class="email-section">'
            . '<div class="office-contact-modal-wrap">'
            . '<button class="button button-orange trigger-button" type="button" data-office-trigger>Contact Office</button>'
            . '</div></div></div>';
    }
    $out .= '</div></div></div>';
    return $out;
}

function countries_options(string $selected = '+971'): string
{
    $countries = [
        ['code' => 'AE', 'name' => 'United Arab Emirates', 'dial' => '+971', 'flag' => "\xF0\x9F\x87\xA6\xF0\x9F\x87\xAA"],
        ['code' => 'GB', 'name' => 'United Kingdom', 'dial' => '+44', 'flag' => "\xF0\x9F\x87\xAC\xF0\x9F\x87\xA7"],
        ['code' => 'US', 'name' => 'United States', 'dial' => '+1', 'flag' => "\xF0\x9F\x87\xBA\xF0\x9F\x87\xB8"],
        ['code' => 'IN', 'name' => 'India', 'dial' => '+91', 'flag' => "\xF0\x9F\x87\xAE\xF0\x9F\x87\xB3"],
        ['code' => 'PK', 'name' => 'Pakistan', 'dial' => '+92', 'flag' => "\xF0\x9F\x87\xB5\xF0\x9F\x87\xB0"],
        ['code' => 'SA', 'name' => 'Saudi Arabia', 'dial' => '+966', 'flag' => "\xF0\x9F\x87\xB8\xF0\x9F\x87\xA6"],
        ['code' => 'EG', 'name' => 'Egypt', 'dial' => '+20', 'flag' => "\xF0\x9F\x87\xAA\xF0\x9F\x87\xAC"],
        ['code' => 'PH', 'name' => 'Philippines', 'dial' => '+63', 'flag' => "\xF0\x9F\x87\xB5\xF0\x9F\x87\xAD"],
        ['code' => 'BD', 'name' => 'Bangladesh', 'dial' => '+880', 'flag' => "\xF0\x9F\x87\xA7\xF0\x9F\x87\xA9"],
        ['code' => 'LK', 'name' => 'Sri Lanka', 'dial' => '+94', 'flag' => "\xF0\x9F\x87\xB1\xF0\x9F\x87\xB0"],
        ['code' => 'JO', 'name' => 'Jordan', 'dial' => '+962', 'flag' => "\xF0\x9F\x87\xAF\xF0\x9F\x87\xB4"],
        ['code' => 'LB', 'name' => 'Lebanon', 'dial' => '+961', 'flag' => "\xF0\x9F\x87\xB1\xF0\x9F\x87\xA7"],
        ['code' => 'IQ', 'name' => 'Iraq', 'dial' => '+964', 'flag' => "\xF0\x9F\x87\xAE\xF0\x9F\x87\xB6"],
        ['code' => 'IR', 'name' => 'Iran', 'dial' => '+98', 'flag' => "\xF0\x9F\x87\xAE\xF0\x9F\x87\xB7"],
        ['code' => 'OM', 'name' => 'Oman', 'dial' => '+968', 'flag' => "\xF0\x9F\x87\xB4\xF0\x9F\x87\xB2"],
        ['code' => 'QA', 'name' => 'Qatar', 'dial' => '+974', 'flag' => "\xF0\x9F\x87\xB6\xF0\x9F\x87\xA6"],
        ['code' => 'KW', 'name' => 'Kuwait', 'dial' => '+965', 'flag' => "\xF0\x9F\x87\xB0\xF0\x9F\x87\xA6"],
        ['code' => 'BH', 'name' => 'Bahrain', 'dial' => '+973', 'flag' => "\xF0\x9F\x87\xA7\xF0\x9F\x87\xAD"],
    ];
    $out = '';
    foreach ($countries as $c) {
        $out .= '<option value="' . $c['dial'] . '"' . ($selected === $c['dial'] ? ' selected' : '') . '>'
            . $c['flag'] . ' ' . $c['dial'] . '</option>';
    }
    return $out;
}

function module_partner($m)
{
    $placeholder = 'https://d3h330vgpwpjr8.cloudfront.net/x/304x160/placeholder.jpg';
    $out = '<div class="our-partner-wrap section-m">'
        . '<div class="our-partner-container container">'
        . '<h2 class="title">' . esc((string) ($m['title'] ?? '')) . '</h2>'
        . '<div class="description">' . rich($m['content']['data']['content'] ?? null) . '</div>'
        . '<div class="partner-section">';
    $slides = [];
    foreach (($m['itemlist'] ?? []) as $p) {
        if (!is_array($p)) continue;
        $u = $p['image']['url'] ?? '';
        $src = $u ? cft($u, 304, 160) : $placeholder;
        $slides[] = '<div class="partner-item">'
            . '<div class="img-section img-zoom">'
            . '<img loading="lazy" draggable="false" src="' . esc($src) . '" alt="' . esc(($p['name'] ?? '') . ' - Zoya Ventures Real Estate') . '" />'
            . '</div>'
            . '<div class="content-section">'
            . '<p class="title">' . esc((string) ($p['name'] ?? '')) . '</p>'
            . '<div class="description">' . rich($p['description']['data']['description'] ?? null) . '</div>'
            . '</div></div>';
    }
    $out .= slick_shell('partner-slider', $slides, 5, true)
        . '</div></div></div>';
    return $out;
}

function module_images_slider($m)
{
    $out = '<div class="images-slider-wrap section-p">'
        . '<div class="images-slider-container container">';
    $slides = [];
    $i = 0;
    foreach (($m['images'] ?? []) as $im) {
        if (!is_array($im) || empty($im['url'])) continue;
        $i++;
        $slides[] = '<div class="image-item"><img loading="lazy" src="' . esc(cft($im['url'], 1128, 752)) . '" alt="Zoya Ventures Real Estate ' . $i . '" /></div>';
    }
    $out .= slick_shell('images-slider', $slides, 3, true)
        . '</div></div>';
    return $out;
}

function module_form_module($m)
{
    $isValuation = ($m['form'] ?? '') === 'Book_a_Valuation';
    $out = '<div class="contact-form-wrapper  section-p" id="' . esc(($m['form'] ?? '') ?: 'General_Enquiry') . '">'
        . '<div class="contact-form-container  container">'
        . '<div class="content-section">'
        . '<h3 class="title">' . esc((string) ($m['title'] ?? '')) . '</h3>';
    if (is_array($m['stats'] ?? null) && count($m['stats']) > 0) {
        $out .= '<div class="stats-section">';
        foreach ($m['stats'] as $s) {
            if (!is_array($s)) continue;
            $out .= '<div class="stat-item">'
                . '<div class="stat-title">' . esc((string) ($s['title'] ?? '')) . '</div>'
                . '<div class="stat-description">' . esc((string) ($s['description'] ?? '')) . '</div>'
                . '</div>';
        }
        $out .= '</div>';
    }
    $out .= '<div class="description">' . rich($m['content']['data']['content'] ?? null) . '</div>'
        . '<div class="cta-section">'
        . '<div class="cta-item">'
        . '<div class="cta-icon">'
        . '<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="whatsapp-icon">'
        . '<path fill-rule="evenodd" clip-rule="evenodd" d="M9.9971 0C4.48428 0 0 4.48553 0 9.99991C0 12.1868 0.705268 14.215 1.90417 15.8612L0.658162 19.5766L4.50185 18.3481C6.08275 19.3946 7.96934 20 10.0029 20C15.5157 20 20 15.5143 20 10.0001C20 4.48571 15.5157 0.000165304 10.0029 0.000165304L9.9971 0ZM7.20535 5.07951C7.01145 4.61511 6.86449 4.59753 6.57074 4.58558C6.47072 4.57978 6.35925 4.57397 6.23568 4.57397C5.85352 4.57397 5.45394 4.68564 5.21294 4.93252C4.91918 5.23233 4.19034 5.93182 4.19034 7.36633C4.19034 8.80084 5.23649 10.1882 5.37748 10.3823C5.52444 10.5761 7.41699 13.5626 10.3555 14.7798C12.6535 15.7321 13.3354 15.6439 13.8584 15.5322C14.6224 15.3676 15.5804 14.803 15.8214 14.1213C16.0624 13.4392 16.0624 12.8572 15.9918 12.7337C15.9213 12.6103 15.7272 12.5399 15.4335 12.3928C15.1397 12.2458 13.7114 11.5403 13.441 11.4462C13.1765 11.3463 12.9239 11.3817 12.7242 11.6639C12.442 12.0578 12.1658 12.4576 11.9424 12.6985C11.7661 12.8867 11.478 12.9102 11.2371 12.8102C10.9139 12.6751 10.0089 12.3574 8.89208 11.3639C8.02807 10.5939 7.4404 9.63573 7.27005 9.3477C7.09954 9.05386 7.25245 8.88313 7.38747 8.72452C7.5344 8.54218 7.67543 8.41293 7.82239 8.24236C7.96935 8.07197 8.05163 7.9837 8.14568 7.78378C8.24569 7.58982 8.17502 7.38989 8.10453 7.24289C8.03403 7.09589 7.44636 5.66138 7.20535 5.07951Z" fill="#67C15E" />'
        . '</svg></div>'
        . '<div class="cta-content">'
        . '<p class="cta-label">WhatsApp</p>'
        . '<a class="cta-value" href="https://wa.provident.ae/inquire?phone=971568308221&amp;text=Hello%20Zoya%20Ventures%2C%0A%0AI%20would%20like%20to%20know%20more%20about%20this%20page%3A%0A%0A%E2%80%A2%20Page%20Name%3A%20%0A%E2%80%A2%20Link%3A%20%0A%0AModifying%20this%20message%20will%20prevent%20it%20from%20being%20sent%20to%20the%20agent.&amp;utm_source=Browser%20Direct&amp;gclid=%22%22&amp;event_type=Whatsapp%20Click&amp;utm_platform=%22%22" target="_blank" rel="noreferrer">Click to WhatsApp</a>'
        . '</div></div>'
        . '<div class="divider"></div>'
        . '<div class="cta-item">'
        . '<div class="cta-icon">'
        . '<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg" class="phone-icon">'
        . '<path d="M1.5 5C1.5 10.5228 5.97715 15 11.5 15H13C13.8284 15 14.5 14.3284 14.5 13.5V12.5856C14.5 12.2414 14.2658 11.9414 13.9319 11.858L10.9831 11.1208C10.6904 11.0476 10.3823 11.157 10.2012 11.3984L9.5544 12.2608C9.36668 12.5111 9.04201 12.6218 8.74823 12.5142C6.5436 11.7066 4.79344 9.95641 3.98584 7.75177C3.87823 7.45799 3.98891 7.13332 4.2392 6.9456L5.10161 6.29879C5.34302 6.11773 5.45241 5.80964 5.37922 5.51689L4.64202 2.5681C4.55856 2.23422 4.25857 2 3.91442 2H3C2.17157 2 1.5 2.67157 1.5 3.5V5Z" stroke="#35373C" stroke-linecap="round" stroke-linejoin="round" />'
        . '</svg></div>'
        . '<div class="cta-content phone-content">'
        . '<p class="cta-label">Phone</p>'
        . '<a class="cta-value" href="tel:+971568308221"><span role="img" aria-label="United Arab Emirates" style="font-family: &quot;Apple Color Emoji&quot;, &quot;Segoe UI Emoji&quot;, &quot;Noto Color Emoji&quot;, &quot;Twemoji Mozilla&quot;, &quot;EmojiOne Color&quot;, &quot;Segoe UI Symbol&quot;, sans-serif; -webkit-font-smoothing: antialiased; text-transform: none; line-height: 1;">\xF0\x9F\x87\xA6\xF0\x9F\x87\xAA</span> +971 568 308 221</a>'
        . '</div></div>'
        . '<div class="divider"></div>'
        . '<div class="cta-item">'
        . '<div class="cta-icon">'
        . '<svg width="16" height="17" viewBox="0 0 16 17" fill="none" xmlns="http://www.w3.org/2000/svg" class="phone-icon">'
        . '<path d="M14.5 5V12C14.5 12.8284 13.8284 13.5 13 13.5H3C2.17157 13.5 1.5 12.8284 1.5 12V5M14.5 5C14.5 4.17157 13.8284 3.5 13 3.5H3C2.17157 3.5 1.5 4.17157 1.5 5M14.5 5V5.16181C14.5 5.6827 14.2298 6.1663 13.7861 6.43929L8.78615 9.51622C8.30404 9.8129 7.69596 9.8129 7.21385 9.51622L2.21385 6.43929C1.77023 6.1663 1.5 5.6827 1.5 5.16181V5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" />'
        . '</svg></div>'
        . '<div class="cta-content">'
        . '<p class="cta-label">Email</p>'
        . '<a class="cta-value" href="mailto:zoyaventure15@gmail.com" target="_blank" rel="noreferrer">zoyaventure15@gmail.com</a>'
        . '</div></div></div>'
        . '</div>'
        . '<div class="form-section">'
        . ($isValuation ? list_property_form() : contact_enquiry_form())
        . '</div></div></div>';
    return $out;
}

function contact_enquiry_form(): string
{
    $out = '<form class="custom-form" data-enquiry-form="contact" novalidate>'
        . '<div class="form-grid"><div class="form-section">'
        . '<div class="input-box input-box-name">'
        . '<label class="input-label" for="cef-name-1">Full Name</label>'
        . '<input class="input-field" type="text" name="name" id="cef-name-1" placeholder="Full Name" required />'
        . '</div>'
        . '<div class="input-box input-box-telephone">'
        . '<label class="input-label" for="cef-phone-1">Phone Number</label>'
        . '<div class="phone-field-row">'
        . '<select class="input-field country-select" aria-label="Country code" name="dial">' . countries_options('+971') . '</select>'
        . '<input class="input-field" type="tel" name="phone" id="cef-phone-1" placeholder="Phone Number" />'
        . '</div></div>'
        . '<div class="input-box input-box-email">'
        . '<label class="input-label" for="cef-email-1">Email Address</label>'
        . '<input class="input-field" type="email" name="email" id="cef-email-1" placeholder="Email Address" required />'
        . '</div>'
        . '<div class="input-box input-box-message">'
        . '<label class="input-label" for="cef-message-1">Message</label>'
        . '<textarea class="input-field input-textarea" name="message" id="cef-message-1" placeholder="Message"></textarea>'
        . '</div>'
        . '</div></div>'
        . '<div class="form-bottom">'
        . '<p class="success-msg" style="display:none">Thank you for your enquiry &mdash; one of our consultants will get back to you shortly.</p>'
        . '<p class="error-msg" style="display:none"></p>'
        . '<button class="reg-btn button button-orange" type="submit"><span>Submit</span></button>'
        . '</div>'
        . '</form>';
    return $out;
}

function list_property_form(): string
{
    $propertyTypes = ['Apartment', 'Villa', 'Townhouse', 'Penthouse', 'Office', 'Shop', 'Studio', 'Plot / Land', 'Warehouse', 'Other'];
    $bedrooms = ['Studio', '1', '2', '3', '4', '5', '6', '7+'];
    $bathrooms = ['1', '2', '3', '4', '5', '6+'];
    $ownership = ['Title Deed Available', 'Off-Plan / Under Construction', 'Other'];
    $languages = ['English', 'Arabic', 'Hindi', 'Urdu', 'Russian', 'Chinese', 'French', 'German', 'Portuguese', 'Other'];
    $opts = function (array $list, string $selected) {
        $o = '';
        foreach ($list as $v) {
            $o .= '<option value="' . esc($v) . '"' . ($v === $selected ? ' selected' : '') . '>' . esc($v) . '</option>';
        }
        return $o;
    };
    $out = '<form class="custom-form" data-enquiry-form="listing" novalidate>'
        . '<div class="form-grid">'
        . '<div class="input-box input-box-name">'
        . '<label class="input-label" for="lp-name-1">Full Name</label>'
        . '<input class="input-field" type="text" name="name" id="lp-name-1" placeholder="Full Name" required />'
        . '</div>'
        . '<div class="input-box">'
        . '<label class="input-label" for="lp-tx-1">I want to</label>'
        . '<select class="input-field" id="lp-tx-1" name="transaction">'
        . $opts(['Sale', 'Rent'], 'Sale')
        . '</select></div>'
        . '<div class="input-box input-box-telephone">'
        . '<label class="input-label" for="lp-phone-1">Phone Number</label>'
        . '<div class="phone-field-row">'
        . '<select class="input-field country-select" aria-label="Country code" name="dial">' . countries_options('+971') . '</select>'
        . '<input class="input-field" type="tel" name="phone" id="lp-phone-1" placeholder="Phone Number" required />'
        . '</div></div>'
        . '<div class="input-box">'
        . '<label class="input-label" for="lp-type-1">Property Type</label>'
        . '<select class="input-field" id="lp-type-1" name="property_type">'
        . $opts($propertyTypes, 'Apartment')
        . '</select></div>'
        . '<div class="input-box input-box-email">'
        . '<label class="input-label" for="lp-email-1">Email Address</label>'
        . '<input class="input-field" type="email" name="email" id="lp-email-1" placeholder="Email Address" required />'
        . '</div>'
        . '<div class="input-box">'
        . '<label class="input-label" for="lp-area-1">Community / Area</label>'
        . '<input class="input-field" type="text" id="lp-area-1" name="community" placeholder="e.g. Dubai Marina" />'
        . '</div>'
        . '<div class="input-box">'
        . '<label class="input-label" for="lp-lang-1">Preferred Language</label>'
        . '<select class="input-field" id="lp-lang-1" name="preferred_language">'
        . $opts($languages, 'English')
        . '</select></div>'
        . '<div class="input-box">'
        . '<label class="input-label" for="lp-beds-1">Bedrooms</label>'
        . '<select class="input-field" id="lp-beds-1" name="bedrooms">'
        . $opts($bedrooms, '2')
        . '</select></div>'
        . '<div class="input-box">'
        . '<label class="input-label" for="lp-baths-1">Bathrooms</label>'
        . '<select class="input-field" id="lp-baths-1" name="bathrooms">'
        . $opts($bathrooms, '2')
        . '</select></div>'
        . '<div class="input-box">'
        . '<label class="input-label" for="lp-size-1">Size (sq ft)</label>'
        . '<input class="input-field" type="number" min="0" id="lp-size-1" name="size_sqft" placeholder="e.g. 1,500" />'
        . '</div>'
        . '<div class="input-box">'
        . '<label class="input-label" for="lp-price-1">Expected Price (AED)</label>'
        . '<input class="input-field" type="number" min="0" id="lp-price-1" name="expected_price" placeholder="e.g. 2,500,000" />'
        . '</div>'
        . '<div class="input-box">'
        . '<label class="input-label" for="lp-own-1">Ownership Status</label>'
        . '<select class="input-field" id="lp-own-1" name="ownership">'
        . '<option value="">Select ownership status</option>'
        . $opts($ownership, '')
        . '</select></div>'
        . '<div class="input-box">'
        . '<label class="input-label" for="lp-addr-1">Property Address</label>'
        . '<input class="input-field" type="text" id="lp-addr-1" name="property_address" placeholder="e.g. Marina Gate 1, Dubai Marina" />'
        . '</div>'
        . '<div class="input-box input-box-message">'
        . '<label class="input-label" for="lp-msg-1">Additional Details</label>'
        . '<textarea class="input-field input-textarea" id="lp-msg-1" name="message" placeholder="Tell us more about your property"></textarea>'
        . '</div>'
        . '<div class="form-bottom">'
        . '<label class="bv-check"><input type="checkbox" name="consent" /><span>I consent to being contacted about my property and agree to the <a href="/terms-and-conditions/">Terms &amp; Conditions</a> and <a href="/privacy-policy/">Privacy Policy</a>.</span></label>'
        . '<p class="success-msg" style="display:none">Thank you &mdash; your property details have been received. One of our consultants will contact you shortly.</p>'
        . '<p class="error-msg" style="display:none"></p>'
        . '<button class="reg-btn button button-orange" type="submit"><span>Submit Details</span></button>'
        . '</div>'
        . '</div></form>';
    return $out;
}

function module_icon_cards($m)
{
    $gridCls = 'icon-cards icon-cards--grid' . (($m['size'] ?? '') === 'three' ? ' icon-cards--grid-three' : '');
    $out = '<div class="icon-cards-wrap section-p">'
        . '<div class="icon-cards-container container">'
        . '<h2 class="title">' . esc((string) ($m['title'] ?? '')) . '</h2>'
        . '<div class="description">' . rich($m['description']['data']['description'] ?? null) . '</div>'
        . '<div class="' . $gridCls . '">';
    foreach (($m['cards'] ?? []) as $c) {
        if (!is_array($c)) continue;
        $out .= '<div class="icon-card">';
        if (!empty($c['icon']['url'])) {
            $out .= '<div class="icon-card-icon"><img loading="lazy" src="' . esc((string) $c['icon']['url']) . '" alt="card icon" /></div>';
        }
        $out .= '<div class="content-section">';
        if (!empty($c['heading'])) {
            $out .= '<p class="icon-card-heading">' . esc((string) $c['heading']) . '</p>';
        }
        $out .= '<p class="icon-card-title">' . esc((string) ($c['title'] ?? '')) . '</p>';
        if (!empty($c['description'])) {
            $out .= '<p class="icon-card-description">' . esc((string) $c['description']) . '</p>';
        }
        $out .= '</div></div>';
    }
    $out .= '</div></div></div>';
    return $out;
}

/** DreamHomeQuiz data (src/components/dream-home-quiz.tsx) for the client modal. */
function quiz_data(): array
{
    static $data = null;
    if ($data !== null) return $data;
    $big = function (string $u) {
        return str_replace('/340x252/', '/696x520/', $u);
    };
    $typeImgs = [
        'apartments' => $big('https://d3h330vgpwpjr8.cloudfront.net/x/property/PS-20012631/images/iblock/655/6554193dedbb2fed4c9309c4a2a23020/340x252/download-_1_.webp'),
        'villas' => $big('https://d3h330vgpwpjr8.cloudfront.net/x/property/PS-2804264/images/iblock/c8c/c8c6468b86c4718f6901fa53bb3b9a4d/340x252/2.webp'),
        'townhouses' => $big('https://d3h330vgpwpjr8.cloudfront.net/x/property/PS-1912251/images/iblock/394/394122d0270bff6ffc30e6b976640d0e/340x252/download-_1_.webp'),
        'penthouses' => $big('https://d3h330vgpwpjr8.cloudfront.net/x/property/PS-1307263/images/iblock/896/89694d014dc245b0f3f228b81f02fceb/340x252/img129.webp'),
    ];
    $areaImgs = [
        'downtown-dubai' => $big('https://d3h330vgpwpjr8.cloudfront.net/x/property/PR-22102512/images/iblock/684/8ees868xdreat1jwfitb9vq4l2z2xcjx/340x252/ADU00171.webp'),
        'dubai-hills-estate' => $big('https://d3h330vgpwpjr8.cloudfront.net/x/property/PS-11022626/images/iblock/fe8/00ih8rzvvjuz0lhzfitqasdpthw9rveo/340x252/img187.webp'),
        'dubai-marina' => $big('https://d3h330vgpwpjr8.cloudfront.net/x/property/PS-3103262/images/iblock/fb4/fb4a80fb9ee723cdbf358a57665c2d83/340x252/download-_1_.webp'),
        'palm-jumeirah' => $big('https://d3h330vgpwpjr8.cloudfront.net/x/property/PS-2007262/images/iblock/247/247f4cf40fec49aee0580a536cbec3ff/340x252/Pl_1.webp'),
    ];
    $questions = [
        [
            'key' => 'propertyType',
            'heading' => 'What is the ideal property type for you?',
            'rows' => 2,
            'options' => [
                ['id' => 'apartments', 'label' => 'Apartments', 'img' => $typeImgs['apartments']],
                ['id' => 'villas', 'label' => 'Villas', 'img' => $typeImgs['villas']],
                ['id' => 'townhouses', 'label' => 'Townhouses', 'img' => $typeImgs['townhouses']],
                ['id' => 'penthouses', 'label' => 'Penthouses', 'img' => $typeImgs['penthouses']],
            ],
        ],
        [
            'key' => 'area',
            'heading' => 'What is your area of preference in Dubai?',
            'rows' => 2,
            'options' => [
                ['id' => 'downtown-dubai', 'label' => 'Downtown Dubai', 'img' => $areaImgs['downtown-dubai']],
                ['id' => 'business-bay', 'label' => 'Business Bay'],
                ['id' => 'arabian-ranches', 'label' => 'Arabian Ranches'],
                ['id' => 'dubai-hills-estate', 'label' => 'Dubai Hills Estate', 'img' => $areaImgs['dubai-hills-estate']],
                ['id' => 'dubai-marina', 'label' => 'Dubai Marina', 'img' => $areaImgs['dubai-marina']],
                ['id' => 'damac-islands', 'label' => 'Damac Islands'],
                ['id' => 'palm-jumeirah', 'label' => 'Palm Jumeirah', 'img' => $areaImgs['palm-jumeirah']],
                ['id' => 'dubai-creek-harbour', 'label' => 'Dubai Creek Harbour'],
                ['id' => 'palm-jebel-ali', 'label' => 'Palm Jebel Ali'],
                ['id' => 'expo-city', 'label' => 'Expo City'],
            ],
        ],
        [
            'key' => 'budget',
            'heading' => 'What is your ideal budget?',
            'rows' => 1,
            'options' => [
                ['id' => 'not-decided', 'label' => 'Not yet Decided'],
                ['id' => 'under-250k', 'label' => 'Less than $250,000'],
                ['id' => '250k-500k', 'label' => '$250,000 - $500,000'],
                ['id' => '500k-1m', 'label' => '$500,000 - $1,000,000'],
                ['id' => '1m-3m', 'label' => '$1,000,000 - $3,000,000'],
                ['id' => 'above-3m', 'label' => 'Above $3,000,000'],
                ['id' => 'open-budget', 'label' => 'Open Budget'],
            ],
        ],
        [
            'key' => 'bedrooms',
            'heading' => 'How many bedrooms would you like in your property?',
            'rows' => 1,
            'options' => [
                ['id' => 'studio', 'label' => 'Studio'],
                ['id' => '1', 'label' => '1 Bedroom'],
                ['id' => '2', 'label' => '2 Bedrooms'],
                ['id' => '3', 'label' => '3 Bedrooms'],
                ['id' => '4', 'label' => '4 Bedrooms'],
                ['id' => '5-plus', 'label' => '5 Bedrooms and above'],
            ],
        ],
        [
            'key' => 'buyerType',
            'heading' => 'Are you an end-user or an investor?',
            'rows' => 1,
            'options' => [
                ['id' => 'end-user', 'label' => "I'm an End-user"],
                ['id' => 'investor', 'label' => "I'm an Investor"],
            ],
        ],
        [
            'key' => 'purchaseTimeline',
            'heading' => 'When are you looking to buy a property in Dubai?',
            'rows' => 1,
            'options' => [
                ['id' => 'immediately', 'label' => 'Immediately'],
                ['id' => 'within-1-month', 'label' => 'Within 1 month'],
                ['id' => 'within-3-months', 'label' => 'Within 3 months'],
                ['id' => 'within-6-months', 'label' => 'Within 6 months'],
                ['id' => 'exploring', 'label' => 'I am just exploring options at this stage'],
            ],
        ],
        [
            'key' => 'communicationMethod',
            'heading' => 'Preferred way of communication?',
            'rows' => 1,
            'options' => [
                ['id' => 'call', 'label' => 'Call', 'icon' => 'phone'],
                ['id' => 'email', 'label' => 'Email', 'icon' => 'email'],
                ['id' => 'whatsapp', 'label' => 'WhatsApp', 'icon' => 'whatsapp'],
            ],
        ],
        [
            'key' => 'preferredContactTime',
            'heading' => 'Preferred time to contact?',
            'rows' => 1,
            'options' => [
                ['id' => 'morning', 'label' => 'Morning'],
                ['id' => 'afternoon', 'label' => 'Afternoon'],
                ['id' => 'night', 'label' => 'Night'],
            ],
        ],
    ];
    $countries = [];
    foreach ([['AE', 'United Arab Emirates', '+971'], ['GB', 'United Kingdom', '+44'], ['US', 'United States', '+1'], ['IN', 'India', '+91'], ['PK', 'Pakistan', '+92'], ['SA', 'Saudi Arabia', '+966'], ['EG', 'Egypt', '+20'], ['PH', 'Philippines', '+63'], ['BD', 'Bangladesh', '+880'], ['LK', 'Sri Lanka', '+94'], ['JO', 'Jordan', '+962'], ['LB', 'Lebanon', '+961'], ['IQ', 'Iraq', '+964'], ['IR', 'Iran', '+98'], ['OM', 'Oman', '+968'], ['QA', 'Qatar', '+974'], ['KW', 'Kuwait', '+965'], ['BH', 'Bahrain', '+973']] as $c) {
        $countries[] = ['code' => $c[0], 'name' => $c[1], 'dial' => $c[2]];
    }
    $data = [
        'questions' => $questions,
        'languages' => ['English', 'Arabic', 'Russian', 'French', 'German', 'Hindi', 'Urdu', 'Chinese', 'Spanish'],
        'countries' => $countries,
    ];
    return $data;
}

function module_questionnaire($m)
{
    $out = '<div class="qes-bk">'
        . '<script type="application/json" data-quiz-json>' . json_encode(quiz_data(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>'
        . '<div class="container">'
        . '<div class="question-banner-wrap"><div class="">'
        . '<div class="question-banner-container">'
        . '<div class="bg-img">'
        . '<img loading="lazy" draggable="false" src="https://d3h330vgpwpjr8.cloudfront.net/x/640x700/pro_quiz_banner_a8c3cbc202.webp"'
        . ' srcSet="https://d3h330vgpwpjr8.cloudfront.net/x/320x260/pro_quiz_banner_a8c3cbc202.webp 320w, https://d3h330vgpwpjr8.cloudfront.net/x/640x700/pro_quiz_banner_a8c3cbc202.webp 640w"'
        . ' sizes="(max-width: 480px) 320px, (min-width: 481px) 640px"'
        . ' alt="Confused About Where to Buy or Invest in Dubai? - Zoya Ventures Real Estate" />'
        . '</div>'
        . '<div class="content-section"><div class="div-pad">'
        . '<div class="content">'
        . '<p class="title">' . esc((string) ($m['title'] ?? '')) . '</p>'
        . '<div class="description">' . rich($m['content']['data']['content'] ?? null) . '</div>'
        . '</div>'
        . '<div class="cta-section">'
        . '<button type="button" class="button button-orange cursur" data-quiz-trigger>Find My Dream Home!</button>'
        . '<div class="help-info">'
        . '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">'
        . '<path d="M12 6V12H16.5M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="url(#paint0_linear_9303_7430)" stroke-linecap="round" stroke-linejoin="round"></path>'
        . '<defs><linearGradient id="paint0_linear_9303_7430" x1="12" y1="3" x2="12" y2="21" gradientUnits="userSpaceOnUse">'
        . '<stop stop-color="#07224B"></stop><stop offset="1" stop-color="#EA6C2E"></stop>'
        . '</linearGradient></defs></svg>'
        . 'It takes only 30 seconds'
        . '</div>'
        . '</div>'
        . '<div class="content"><div class="description">' . rich($m['content1']['data']['content1'] ?? null) . '</div></div>'
        . '</div></div>'
        . '</div></div></div></div></div>';
    return $out;
}

function module_career_listing($m)
{
    $out = '<div class="career-listing-wrap section-p" id="careers-listing">'
        . '<div class="career-listing-container container">'
        . '<h2 class="title">' . esc((string) ($m['title'] ?? '')) . '</h2>'
        . '<div class="career-listing-section">';
    foreach (($m['careers'] ?? []) as $c) {
        if (!is_array($c)) continue;
        $out .= '<div class="career-item">'
            . '<p class="title">' . esc((string) ($c['title'] ?? '')) . '</p>'
            . '<div class="sub-section">'
            . '<p class="location">' . esc((string) ($c['location'] ?? '')) . '</p>'
            . '<a class="career-link" href="/careers/' . esc((string) ($c['slug'] ?? '')) . '/"><span>View Details</span></a>'
            . '</div></div>';
    }
    $out .= '</div>'
        . '<div class="content-cta">'
        . '<a class="cta" href="/contact/"><span>Nothing quite right for you?</span></a>'
        . '<p class="cta-text"><span>We&rsquo;re always on the lookout for standout individuals</span></p>'
        . '</div>'
        . '</div></div>';
    return $out;
}

function module_news_listing()
{
    $posts = blog_posts(100);
    if (!count($posts)) return '';
    $out = '<div class="news-listing-wrap section-p">'
        . '<div class="news-listing-container container"><div class="row">';
    foreach ($posts as $b) {
        $out .= '<div class="col-xl-4 col-md-6"><div class="news-card">'
            . '<div class="img-section-wrap img-zoom"><a class="img-section" href="/blog/' . esc((string) ($b['slug'] ?? '')) . '/">';
        if (!empty($b['image'])) {
            $out .= '<img loading="lazy" src="' . esc(cft((string) $b['image'], 1128, 752)) . '" alt="' . esc((string) ($b['title'] ?? '')) . '" />';
        }
        $out .= '</a></div>'
            . '<div class="content-section">';
        if (!empty($b['category'])) {
            $out .= '<p class="img-tag">' . esc((string) $b['category']) . '</p>';
        }
        $out .= '<a class="title" href="/blog/' . esc((string) ($b['slug'] ?? '')) . '/">' . esc((string) ($b['title'] ?? '')) . '</a>'
            . '<p class="date">' . esc((string) ($b['date'] ?? '')) . '</p>';
        if (!empty($b['description'])) {
            $out .= '<p class="description">' . esc(mb_substr(strip_html((string) $b['description']), 0, 160)) . '</p>';
        }
        $out .= '</div></div></div>';
    }
    $out .= '</div></div></div>';
    return $out;
}

function module_team_listing()
{
    $members = team_members(1000);
    if (!count($members)) return '';
    $langs = [];
    foreach ($members as $m) {
        foreach (($m['languages'] ?? []) as $l) {
            $langs[(string) $l] = 1;
        }
    }
    $langs = array_keys($langs);
    sort($langs);
    $filtered = array_values(array_filter($members, function ($m) {
        return in_array('Management', $m['category'] ?? [], true);
    }));
    $perPage = 20;
    $totalPages = max(1, (int) ceil(count($filtered) / $perPage));
    $pageMembers = array_slice($filtered, 0, $perPage);
    $stats = [
        ['value' => (string) count($members), 'text' => 'Professionals'],
        ['value' => count($langs) . '+', 'text' => 'Languages Spoken'],
        ['value' => '17+', 'text' => 'Proven industry presence'],
    ];
    $tabs = ['Management', 'Associates', 'Sales Managers', 'Managers', 'Primary Brokers', 'Secondary Brokers'];
    $out = '<div class="team-listing-wrap listing-wrap">'
        . '<script type="application/json" data-team-json>' . json_encode(array_map(function ($m) {
            return [
                'slug' => $m['slug'] ?? '',
                'name' => $m['name'] ?? '',
                'designation' => $m['designation'] ?? '',
                'image' => $m['image'] ?? '',
                'category' => array_values((array) ($m['category'] ?? [])),
                'languages' => array_values((array) ($m['languages'] ?? [])),
            ];
        }, $members), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>'
        . '<div class="container">'
        . '<div class="statastic">';
    foreach ($stats as $s) {
        $out .= '<div class="item"><div class="value">' . esc($s['value']) . '</div><div class="text">' . esc($s['text']) . '</div></div>';
    }
    $out .= '</div>'
        . '<div class="team-listing-container container">'
        . '<div class="category-section-wrap category-sectionn d-none d-xl-block"><div class="category-sectionn">'
        . '<div class="category-tabs-section"><div class="tab-header-section">'
        . '<div class="custom-tabs category-tabs">';
    foreach ($tabs as $i => $t) {
        $out .= '<button type="button" class="tab-button button ' . ($i === 0 ? 'selected-tab' : 'button-white') . '" data-team-tab="' . esc($t) . '">' . esc($t) . '</button>';
    }
    $out .= '</div></div></div></div></div>'
        . '<div class="max-filter">'
        . '<div class="search-team-filter">'
        . '<div class="search-box-comm">'
        . '<input class="form-control search" type="text" placeholder="Search Name, Designation..." data-team-search />'
        . '</div>'
        . '<div class="select-boxes">'
        . '<div class="d-block d-xl-none tab-width"><div class="react-select-wrap">'
        . '<select class="select-field" aria-label="Category" data-team-category>'
        . '<option value="">Management</option>';
    foreach (array_slice($tabs, 1) as $t) {
        $out .= '<option value="' . esc($t) . '">' . esc($t) . '</option>';
    }
    $out .= '</select></div></div>'
        . '<div class="react-select-wrap">'
        . '<select class="select-field" aria-label="Language" data-team-language>'
        . '<option value="">All Languages</option>';
    foreach ($langs as $l) {
        $out .= '<option value="' . esc($l) . '">' . esc($l) . '</option>';
    }
    $out .= '</select></div>'
        . '</div></div></div>'
        . '<div class="team-category-select-section"></div>'
        . '<div class="team-listing-section" data-team-list>'
        . '<div class="team-listing-section-inner">';
    foreach ($pageMembers as $t) {
        $out .= '<div class="team-card-wrap"><div class="team-card rounded-card">'
            . '<a class="img-section img-zoom" href="/team/' . esc((string) ($t['slug'] ?? '')) . '/">';
        if (!empty($t['image'])) {
            $out .= '<img loading="lazy" draggable="false" src="' . esc((string) $t['image']) . '" alt="' . esc((string) ($t['name'] ?? '')) . '" />';
        }
        $out .= '</a>'
            . '<a href="/team/' . esc((string) ($t['slug'] ?? '')) . '/"><p class="name">' . esc((string) ($t['name'] ?? '')) . '</p></a>'
            . '<p class="designation">' . esc((string) ($t['designation'] ?? '')) . '</p>'
            . '</div></div>';
    }
    $out .= '</div></div>'
        . '<nav class="pagination-wrapper" aria-label="Team pagination"><div><div class="pagination-container">'
        . '<button class="button button-white pagination-button button-back button-disabled" type="button" data-team-back>'
        . '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-left-icon">'
        . '<path d="M15.75 19.5L8.25 12L15.75 4.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        . '<span>Back</span></button>'
        . '<div class="pagination-select-wrap">'
        . '<span class="page-text">Page:</span>'
        . '<span class="pagination-current" data-team-page>1 of ' . $totalPages . '</span>'
        . '</div>'
        . '<button class="button button-white pagination-button button-next' . ($totalPages === 1 ? ' button-disabled' : '') . '"' . ($totalPages === 1 ? ' disabled' : '') . ' type="button" data-team-next>'
        . '<span>Next</span>'
        . '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="arrow-right-icon">'
        . '<path d="M8.25 4.5L15.75 12L8.25 19.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round" /></svg>'
        . '</button>'
        . '</div></div></nav>'
        . '</div></div></div>';
    return $out;
}

function module_communities_listing()
{
    $list = communities();
    if (!count($list)) return '';
    $out = '<div class="communities-listing-wrap section-p">'
        . '<div class="communities-listing-container container"><div class="row">';
    foreach ($list as $c) {
        $out .= '<div class="col-xl-3 col-md-4 col-sm-6">'
            . '<a class="community-card" href="/buy/properties-for-sale/in-' . esc((string) ($c['slug'] ?? '')) . '/">'
            . '<p class="name">' . esc((string) ($c['label'] ?? '')) . '</p>'
            . '<p class="count">View Properties</p>'
            . '</a></div>';
    }
    $out .= '</div></div></div>';
    return $out;
}

function module_developer_listing()
{
    $devs = developer_hits();
    $items = [];
    foreach ($devs as $d) {
        $developer = (string) ($d['developer'] ?? '');
        if ($developer === '') continue;
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($developer));
        $items[] = ['developer' => $developer, 'slug' => $slug];
    }
    if (!count($items)) return '';
    $out = '<div class="developer-listing-wrap section-p">'
        . '<div class="developer-listing-container container"><div class="row">';
    foreach ($items as $d) {
        $out .= '<div class="col-xl-3 col-md-4 col-sm-6">'
            . '<a class="developer-card" href="/new-projects/developed-by-' . esc($d['slug']) . '/">'
            . '<p class="name">' . esc($d['developer']) . '</p>'
            . '<p class="count">Projects</p>'
            . '</a></div>';
    }
    $out .= '</div></div></div>';
    return $out;
}

function module_renderer($m)
{
    if (!is_array($m) || empty($m['strapi_component'])) return '';
    switch ($m['strapi_component']) {
        case 'modules.global-module':
            $cm = $m['choose_module'] ?? '';
            if ($cm === 'developer_slider') return module_developer_slider($m);
            if ($cm === 'reviews_slider') return module_reviews_slider();
            if ($cm === 'dubai_communities') return module_dubai_communities();
            if ($cm === 'contact_module') return module_contact_module($m);
            if ($cm === 'news_slider') return module_news_section($m);
            return '';
        case 'modules.listing-module':
            $mod = $m['module'] ?? '';
            if ($mod === 'news_listing') return module_news_listing();
            if ($mod === 'team_listing') return module_team_listing();
            if ($mod === 'communities_listing') return module_communities_listing();
            if ($mod === 'developer_listing') return module_developer_listing();
            return '';
        case 'modules.content-and-links':
            return module_content_and_links($m);
        case 'modules.ads-banner':
            return module_ads_banner($m);
        case 'modules.tile-block':
            return module_tile_block($m);
        case 'modules.featured-prop-slider':
            return module_featured_slider($m);
        case 'modules.featured-news':
            return module_news_section($m);
        case 'modules.questionnaire':
            return module_questionnaire($m);
        case 'modules.content-and-stats':
            return module_content_and_stats($m);
        case 'modules.faq':
            return module_faq($m);
        case 'modules.our-services':
            return module_our_services($m);
        case 'modules.office-location':
            return module_office_location($m);
        case 'modules.partner':
            return module_partner($m);
        case 'modules.form-module':
            return module_form_module($m);
        case 'modules.images-slider':
            return module_images_slider($m);
        case 'modules.icon-cards':
            return module_icon_cards($m);
        case 'modules.career-listing':
            return module_career_listing($m);
        default:
            return '';
    }
}

function module_wrap($m)
{
    if (!is_array($m)) return '';
    if (($m['strapi_component'] ?? '') === 'components.rich-text-block') {
        $html = $m['text']['data']['text'] ?? null;
        if (!$html) return '';
        return '<div class="text-copy-wrap section-p">'
            . '<div class="text-copy-container container">'
            . rich($html)
            . '</div></div>';
    }
    return module_renderer($m);
}
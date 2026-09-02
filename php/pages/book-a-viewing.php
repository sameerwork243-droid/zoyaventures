<?php
// pages/book-a-viewing.php — BookViewingPage port (src/components/book-viewing-page.tsx
// + book-viewing-form.tsx + PropertyPreviewCard), static SSR.
// Submission (POST /api/inquiries kind=viewing) is wired in assets/js/property.js.

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/store.php';
require_once __DIR__ . '/../includes/render/property-card.php';
require_once __DIR__ . '/../includes/head.php';

$page_title = 'Book a Viewing';
$page_description = "Whether you have a question about our services, need help, or just want to provide feedback, please fill out the form and we'll get back to you as soon as possible.";

$ref = trim((string) ($_GET['id'] ?? ''));
$property = null;
if ($ref !== '') {
    $dbp = db_property_by_ref($ref);
    if ($dbp) {
        $property = $dbp['data'];
    } else {
        foreach ([...corpus('buy'), ...corpus('let')] as $h) {
            if ((string) ($h['crm_id'] ?? '') === $ref || (string) ($h['id'] ?? '') === $ref) {
                $property = $h;
                break;
            }
        }
    }
}

function bav_price_text(array $p): string
{
    $qual = $p['price_qualifier'] ?? 'AED';
    if (is_array($qual)) $qual = $qual[0] ?? 'AED';
    return trim($qual . ' ' . number_format((int) ($p['price'] ?? 0), 0, '.', ','));
}

function bav_preview_image(array $p): string
{
    $imgs = $p['images'] ?? [];
    if (!is_array($imgs)) $imgs = [];
    foreach ($imgs as $im) {
        if (!is_array($im)) continue;
        $u = $im['srcUrl'] ?? $im['url'] ?? $im['696x520'] ?? $im['464x312'] ?? $im['340x252'] ?? null;
        if ($u) return (string) $u;
    }
    return '/images/property-placeholder.svg';
}

function bav_specs(array $p): array
{
    $out = [];
    $bed = $p['bedroom'] ?? $p['bedrooms_min'] ?? null;
    $bath = $p['bathroom'] ?? null;
    $area = $p['floorarea_min'] ?? $p['floorarea_max'] ?? $p['area_sqft'] ?? null;
    if ($bed !== null) $out[] = $bed . ' ' . ((int) $bed === 1 ? 'bed' : 'beds');
    if ($bath !== null) $out[] = $bath . ' ' . ((int) $bath === 1 ? 'bath' : 'baths');
    if ($area !== null && $area !== '') $out[] = number_format((int) $area, 0, '.', ',') . ' sqft';
    return $out;
}

function bav_preview_card(array $p): string
{
    $title = (string) ($p['title'] ?? ((string) ($p['building'][0] ?? 'Property') . ' in ' . (string) ($p['display_address'] ?? 'Dubai')));
    $location = (string) ($p['display_address'] ?? ($p['address_full']['area'] ?? $p['community'] ?? 'Dubai'));
    $img = bav_preview_image($p);
    $ref = $p['crm_id'] ?? $p['id'] ?? null;
    $sp = bav_specs($p);
    $html = '<div class="bv-preview">'
        . '<div class="bv-preview-img">'
        . '<img loading="eager" src="' . esc($img) . '" alt="' . esc($title) . '" />';
    if ($ref !== null) {
        $html .= save_button_markup('/book-a-viewing/', (string) ($p['slug'] ?? ''), $title, (int) ($p['price'] ?? 0), $img, 'button');
    }
    $html .= '</div><div class="bv-preview-body">'
        . '<p class="bv-preview-price">' . esc(bav_price_text($p)) . '</p>'
        . '<h3 class="bv-preview-title">' . esc($title) . '</h3>'
        . '<p class="bv-preview-loc">'
        . '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"></path><circle cx="12" cy="10" r="3"></circle></svg>'
        . esc($location) . '</p>';
    if (count($sp)) $html .= '<p class="bv-preview-specs">' . esc(implode('  |  ', $sp)) . '</p>';
    $html .= '</div></div>';
    return $html;
}

function bav_form(?array $p): string
{
    $countries = [
        ['code' => 'AE', 'dial' => '+971'], ['code' => 'GB', 'dial' => '+44'], ['code' => 'US', 'dial' => '+1'],
        ['code' => 'IN', 'dial' => '+91'], ['code' => 'PK', 'dial' => '+92'], ['code' => 'SA', 'dial' => '+966'],
        ['code' => 'EG', 'dial' => '+20'], ['code' => 'PH', 'dial' => '+63'], ['code' => 'BD', 'dial' => '+880'],
        ['code' => 'LK', 'dial' => '+94'], ['code' => 'JO', 'dial' => '+962'], ['code' => 'LB', 'dial' => '+961'],
        ['code' => 'IQ', 'dial' => '+964'], ['code' => 'IR', 'dial' => '+98'], ['code' => 'OM', 'dial' => '+968'],
        ['code' => 'QA', 'dial' => '+974'], ['code' => 'KW', 'dial' => '+965'], ['code' => 'BH', 'dial' => '+973'],
    ];
    $flags = ['AE' => '🇦🇪', 'GB' => '🇬🇧', 'US' => '🇺🇸', 'IN' => '🇮🇳', 'PK' => '🇵🇰', 'SA' => '🇸🇦',
        'EG' => '🇪🇬', 'PH' => '🇵🇭', 'BD' => '🇧🇩', 'LK' => '🇱🇰', 'JO' => '🇯🇴', 'LB' => '🇱🇧',
        'IQ' => '🇮🇶', 'IR' => '🇮🇷', 'OM' => '🇴🇲', 'QA' => '🇶🇦', 'KW' => '🇰🇼', 'BH' => '🇧🇭'];
    $opts = '';
    foreach ($countries as $c) {
        $sel = $c['dial'] === '+971' ? ' selected' : '';
        $opts .= '<option value="' . esc($c['dial']) . '"' . $sel . '>' . $flags[$c['code']] . ' ' . esc($c['dial']) . '</option>';
    }
    $langs = '';
    foreach (['English', 'Arabic', 'Russian', 'Hindi', 'Urdu', 'Chinese', 'French', 'German'] as $i => $l) {
        $langs .= '<option value="' . esc($l) . '"' . ($i === 0 ? ' selected' : '') . '>' . esc($l) . '</option>';
    }

    $propertyRef = is_array($p) ? (string) ($p['crm_id'] ?? $p['id'] ?? '') : '';
    $propertySlug = is_array($p) ? (string) ($p['slug'] ?? '') : '';
    $propertyTitle = is_array($p) ? (string) ($p['title'] ?? '') : '';

    return '<form class="bv-form" data-bv-form novalidate data-property-ref="' . esc($propertyRef)
        . '" data-property-slug="' . esc($propertySlug) . '" data-property-title="' . esc($propertyTitle) . '">'
        . '<div class="bv-form-grid">'
        . '<div class="bv-field"><label class="bv-label" for="bv-name">Name <span class="bv-req">*</span></label>'
        . '<input class="bv-input" id="bv-name" type="text" placeholder="Full Name" required></div>'
        . '<div class="bv-field"><label class="bv-label" for="bv-email">Email <span class="bv-req">*</span></label>'
        . '<input class="bv-input" id="bv-email" type="email" placeholder="you@example.com" required></div>'
        . '<div class="bv-field"><label class="bv-label" for="bv-phone">Phone <span class="bv-req">*</span></label>'
        . '<div class="bv-phone-row">'
        . '<select class="bv-input bv-country" aria-label="Country code">' . $opts . '</select>'
        . '<input class="bv-input" id="bv-phone" type="tel" placeholder="Phone Number" required>'
        . '</div></div>'
        . '<div class="bv-field"><label class="bv-label" for="bv-date">Date <span class="bv-req">*</span></label>'
        . '<input class="bv-input" id="bv-date" type="date" min="' . date('Y-m-d') . '" required></div>'
        . '<div class="bv-field"><label class="bv-label" for="bv-time">Time <span class="bv-req">*</span></label>'
        . '<input class="bv-input" id="bv-time" type="time" required></div>'
        . '<div class="bv-field"><label class="bv-label" for="bv-language">Preferred Language <span class="bv-req">*</span></label>'
        . '<select class="bv-input" id="bv-language">' . $langs . '</select></div>'
        . '<div class="bv-field bv-field-full"><label class="bv-label" for="bv-message">Message</label>'
        . '<textarea class="bv-input bv-textarea" id="bv-message" placeholder="Any questions or special requests?" rows="4"></textarea></div>'
        . '<label class="bv-check"><input type="checkbox" id="bv-mortgage"><span>Interested in mortgage advice?</span></label>'
        . '<div class="bv-submit-row">'
        . '<p class="bv-fail" style="display:none"></p>'
        . '<button class="bv-submit" type="submit"><span>Submit Details</span></button>'
        . '<p class="bv-footnote">By clicking Submit, you agree to our <a href="/terms-and-conditions/">Terms &amp; Conditions</a> and <a href="/privacy-policy/">Privacy Policy</a>.</p>'
        . '</div></div>'
        . '<div class="bv-success" style="display:none">'
        . '<div class="bv-success-icon"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"></path></svg></div>'
        . '<h3 class="bv-success-title">Viewing Request Submitted</h3>'
        . '<p class="bv-success-text">Your viewing request has been received. Our team will contact you shortly.</p>'
        . ($propertyTitle !== '' ? '<p class="bv-success-prop">' . esc($propertyTitle) . '</p>' : '')
        . '</div>'
        . '</form>';
}

$body = '<div class="bv-page">'
    . '<div class="bv-banner"><div class="bv-banner-inner container">'
    . '<nav class="breadcrumbs bv-breadcrumbs" aria-label="breadcrumb"><ol class="breadcrumb">'
    . '<li class="breadcrumb-item enable-link-home"><a class="breadcrumb-link enable-link" href="/">Home</a></li>'
    . '<li class="breadcrumb-item active"><a aria-current="page" class="breadcrumb-link disable-link" href="/book-a-viewing/">Book a Viewing</a></li>'
    . '</ol></nav>'
    . '<h1 class="bv-title">Book a Viewing</h1>'
    . '<p class="bv-desc">' . esc($page_description) . '</p>'
    . '</div></div>'
    . '<div class="bv-body container"><div class="bv-grid">'
    . '<div class="bv-form-col"><div class="bv-card">' . bav_form($property) . '</div></div>'
    . '<div class="bv-preview-col">'
    . ($property !== null
        ? bav_preview_card($property)
        : '<div class="bv-preview bv-preview-empty"><p class="bv-preview-note">'
            . 'Select a property from our <a href="/buy/properties-for-sale/">buy</a> or '
            . '<a href="/let/properties-for-rent/">rent</a> listings and click &quot;Book a Viewing&quot; to attach it to this request.'
            . '</p></div>')
    . '</div></div></div></div>';
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

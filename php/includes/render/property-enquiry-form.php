<?php
// property-enquiry-form.php — PropertyEnquiryForm port (src/components/property-enquiry-form.tsx).
// Submission logic (POST /api/inquiries kind=viewing, validation, status messages) runs in property.js.

require_once __DIR__ . '/../functions.php';

function property_enquiry_form(string $propertyRef, string $propertySlug, string $route): string
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

    return '<div class="book-a-viewing-form" id="bav-form">'
        . '<form class="custom-form" data-enquiry-form data-property-ref="' . esc($propertyRef) . '" data-property-slug="' . esc($propertySlug) . '" data-route="' . esc($route) . '" novalidate>'
        . '<div class="form-grid">'
        . '<div class="input-box input-box-name"><label class="input-label" for="bav-name">Full Name</label>'
        . '<input class="input-field" type="text" id="bav-name" name="name" placeholder="Full Name" required></div>'
        . '<div class="input-box input-box-email"><label class="input-label" for="bav-email">Email Address</label>'
        . '<input class="input-field" type="email" id="bav-email" name="email" placeholder="Email Address" required></div>'
        . '<div class="input-box input-box-telephone"><label class="input-label" for="bav-phone">Phone Number</label>'
        . '<div class="phone-field-row">'
        . '<select class="input-field country-select" aria-label="Country code">' . $opts . '</select>'
        . '<input class="input-field" type="tel" id="bav-phone" name="phone" placeholder="Phone Number" required>'
        . '</div></div>'
        . '<div class="input-box input-box-message"><label class="input-label" for="bav-message">Message</label>'
        . '<textarea class="input-field input-textarea" id="bav-message" name="message" placeholder="Message"></textarea></div>'
        . '<div class="input-box input-box-checkbox"><label class="input-label">'
        . '<input type="checkbox" class="checkbox-root" required>'
        . '<span>I agree to the <a href="/terms-and-conditions/">Terms &amp; Conditions</a> and <a href="/privacy-policy/">Privacy Policy</a></span>'
        . '</label></div>'
        . '</div>'
        . '<div class="form-bottom">'
        . '<p class="success-msg" style="display:none">Thank you for your enquiry — one of our consultants will get back to you shortly.</p>'
        . '<p class="error-msg" style="display:none"></p>'
        . '<button class="reg-btn button button-orange" type="submit" data-enquiry-submit><span>Request Information</span></button>'
        . '</div>'
        . '</form></div>';
}
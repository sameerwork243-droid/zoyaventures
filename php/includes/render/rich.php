<?php
// rich.php — Rich component port (src/components/rich.tsx)

require_once __DIR__ . '/../functions.php';

function rich(?string $html): string
{
    if ($html === null || $html === '') return '';
    return '<div>' . $html . '</div>';
}

function cta_href(?array $cta, string $fallback = '#'): string
{
    if (!$cta) return $fallback;
    if (!empty($cta['custom_link'])) return $cta['custom_link'];
    if (!empty($cta['menu']['slug'])) {
        $parent = $cta['menu']['strapi_parent'] ?? null;
        if (is_array($parent) && !empty($parent['slug'])) return '/' . $parent['slug'] . '/' . $cta['menu']['slug'] . '/';
        return '/' . $cta['menu']['slug'] . '/';
    }
    return $fallback;
}

function strip_html(?string $s): string
{
    if ($s === null) return '';
    $s = preg_replace('/<[^>]*>/', '', $s) ?? $s;
    $s = str_replace(['&amp;', '&#x27;', '&quot;'], ['&', "'", '"'], $s);
    return trim($s);
}

/** CountryFlag port (src/components/phone-flag.tsx) — default AE. */
function country_flag(string $code = 'AE'): string
{
    $flags = [
        'AE' => '🇦🇪', 'GB' => '🇬🇧', 'US' => '🇺🇸', 'IN' => '🇮🇳', 'PK' => '🇵🇰', 'SA' => '🇸🇦',
        'EG' => '🇪🇬', 'PH' => '🇵🇭', 'BD' => '🇧🇩', 'LK' => '🇱🇰', 'JO' => '🇯🇴', 'LB' => '🇱🇧',
        'IQ' => '🇮🇶', 'IR' => '🇮🇷', 'OM' => '🇴🇲', 'QA' => '🇶🇦', 'KW' => '🇰🇼', 'BH' => '🇧🇭',
    ];
    $flag = $flags[$code] ?? $flags['AE'];
    return '<span role="img" aria-label="' . esc($code) . '" style="font-family:Apple Color Emoji, Segoe UI Emoji, Noto Color Emoji, Twemoji Mozilla, EmojiOne Color, Segoe UI Symbol, sans-serif;-webkit-font-smoothing:antialiased;text-transform:none;line-height:1">' . $flag . '</span>';
}
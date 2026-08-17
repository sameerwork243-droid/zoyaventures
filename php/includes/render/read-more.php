<?php
// read-more.php — ReadMore component port (src/components/read-more.tsx)
// Clamping/measuring and the toggle run in listing-ui.js (client behaviour).

require_once __DIR__ . '/../functions.php';

function read_more(string $inner, int $lines = 4, string $className = '', string $moreLabel = 'Read More', string $lessLabel = 'Read Less'): string
{
    return '<div class="read-more-wrap ' . esc($className) . '">'
        . '<div class="read-more read-more-clamped" style="display:-webkit-box;-webkit-box-orient:vertical;-webkit-line-clamp:' . $lines . ';overflow:hidden">'
        . $inner
        . '</div>'
        . '<button type="button" class="read-more-toggle" aria-expanded="false" style="display:none">' . esc($moreLabel) . '</button>'
        . '</div>';
}
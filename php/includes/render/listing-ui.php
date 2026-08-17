<?php
// listing-ui.php — FilterDropdown / TypeSelect / MortgageCalculator ports
// (src/components/listing-ui.tsx). Open/close + calculator math run in listing-ui.js.

require_once __DIR__ . '/../functions.php';

function filter_dropdown(string $label, array $options, string $className = '', string $btnClass = 'custom-dropdown-toggle filter-dropdown-toggle dropdown-toggle'): string
{
    $html = '<div class="filter-dropdown dropdown' . ($className ? ' ' . $className : '') . '">';
    $html .= '<button class="' . esc($btnClass) . '" type="button" aria-expanded="false">';
    $html .= '<span><span>' . esc($label) . '</span></span>';
    $html .= '<svg class="arrow-down-icon" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13 5.5L8 10.5L3 5.5" stroke="#fff" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
    $html .= '</button>';
    $html .= '<div class="dropdown-menu">';
    foreach ($options as $o) {
        $html .= '<a class="dropdown-item" href="' . esc($o['href']) . '">' . esc($o['label']) . '</a>';
    }
    $html .= '</div></div>';
    return $html;
}

function type_select(array $options, string $label = 'Property Type'): string
{
    $html = '<div class="react-select-wrap filter-select building-type-select">';
    $html .= '<div class="react-select css-b62m3t-container">';
    $html .= '<div class="react-select__control css-14qho42-control">';
    $html .= '<div class="react-select__value-container react-select__value-container--has-value css-hlgwow">';
    $html .= '<div class="react-select__single-value css-1ubv46r-singleValue">' . esc($label) . '</div>';
    $html .= '</div>';
    $html .= '<div class="react-select__indicators css-1wy0on6">';
    $html .= '<span class="react-select__indicator-separator css-1uei4ir-indicatorSeparator"></span>';
    $html .= '<div class="dropdown-indicator react-select__indicator react-select__dropdown-indicator css-15ctyzv-indicatorContainer" aria-hidden="true">';
    $html .= '<svg class="arrow-down-icon" width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path></svg>';
    $html .= '</div></div></div>';
    $html .= '<div class="react-select__menu" style="display:none">';
    $html .= '<div class="react-select__menu-list">';
    foreach ($options as $o) {
        $html .= '<a class="react-select__option" href="' . esc($o['href']) . '">' . esc($o['label']) . '</a>';
    }
    $html .= '</div></div></div></div>';
    return $html;
}

function mortgage_calculator(string $initialPrice = '3,000,000', string $currency = 'AED', string $heading = 'Mortgage Calculator', bool $panel = false): string
{
    $p = (float) str_replace(',', '', $initialPrice);
    $down = 25.0;
    $rate = 3.75;
    $years = 25.0;
    $principal = $p * (1 - $down / 100);
    $r = $rate / 12 / 100;
    $n = $years * 12;
    $monthly = ($p > 0 && $r > 0 && $n > 0) ? ($principal * $r) / (1 - pow(1 + $r, -$n)) : 0;
    $fmt = fn (float $v) => $v ? number_format((float) round($v), 0, '.', ',') : '0';

    $inputs = fn () =>
        '<div class="input-section">'
        . '<div class="label-bk"><label>Total Price</label>'
        . '<input type="text" inputmode="decimal" class="input-item" value="' . esc($initialPrice) . '">'
        . '<span class="fix-txt">' . esc($currency) . '</span></div>'
        . '<div class="label-bk"><label>Down Payment (%)</label>'
        . '<input type="number" class="input-item" value="' . $down . '"></div>'
        . '<div class="label-bk"><label>Interest Rate (%)</label>'
        . '<input type="number" class="input-item" value="' . $rate . '"></div>'
        . '<div class="label-bk"><label>Loan Period (Years)</label>'
        . '<input type="number" class="input-item" value="' . $years . '"></div>'
        . '</div>';

    if ($panel) {
        return '<div class="property-mortagage-wrap" id="mortgage-calculator">'
            . '<h2 class="heading">' . esc($heading) . '</h2>'
            . '<div class="property-calc"><div class="calculator-section">' . $inputs() . '</div>'
            . '<div class="result-section">'
            . '<div class="pric-bx"><p class="per-txt">Monthly repayment</p>'
            . '<p class="results">' . esc($currency) . ' ' . $fmt($monthly) . ' /month</p></div>'
            . '<div class="div-bor"></div>'
            . '<div class="nn-bt">'
            . '<div class="one-bk"><p class="tit">Total Loan Amount</p><p class="con">' . esc($currency) . ' ' . $fmt($principal) . '</p></div>'
            . '<div class="one-bk"><p class="tit">Duration</p><p class="con">' . $years . ' Years</p></div>'
            . '<div class="one-bk tif"><a class="button button-orange trigger-button" href="/property-services/mortgages/"><span>Get a free consultation</span></a></div>'
            . '</div></div></div></div>';
    }

    return '<div class="results-calculator section-m"><div class="container">'
        . '<div class="property-mortagage-wrap" id="mortgage-calculator">'
        . '<h2 class="title">' . esc($heading) . '</h2>'
        . '<p class="content">Calculate your monthly mortgage repayments</p>'
        . '<div class="calculator-section"><p class="label">Total Price (' . esc($currency) . ')</p>'
        . '<input type="text" inputmode="decimal" class="input-item" value="' . esc($initialPrice) . '">'
        . '</div>'
        . '<div class="input-section"><p class="label">Down Payment (%)</p>'
        . '<input type="number" class="input-item" value="' . $down . '"></div>'
        . '<div class="input-section"><p class="label">Interest Rate (%)</p>'
        . '<input type="number" class="input-item" value="' . $rate . '"></div>'
        . '<div class="input-section"><p class="label">Loan Period Yearly</p>'
        . '<input type="number" class="input-item" value="' . $years . '"></div>'
        . '<div class="result-section">'
        . '<div class="left-side"><p class="text">Monthly Payments</p>'
        . '<p class="results">' . esc($currency) . ' ' . $fmt($monthly) . ' /month</p></div>'
        . '<div class="right-side"><a class="button button-orange trigger-button" href="/property-services/mortgages/"><span>Get a free consultation</span></a></div>'
        . '</div></div><div class="divider"></div></div></div>';
}
<?php
// footer.php — site footer (port of src/components/footer.tsx)
// Renders link columns, settings selects (currency/unit), GPTW badge, socials, terms, copyright.

require_once __DIR__ . '/functions.php';

$FOOTER_COLS = [
    ['title' => 'buy', 'links' => [
        ['Properties for Sale', '/buy/properties-for-sale/'],
        ['Guide to Buying', '/property-buying-dubai-guide/'],
        ['Signature Collection', 'https://providentestate.com/#singnature'],
        ['Mortgages', '/property-services/mortgages/'],
        ['Property Management', '/property-services/property-management/'],
        ['Legal Services', '/property-services/conveyancing/'],
        ['Currency Exchange', '/ifx-dubai/'],
        ['Snagging & Inspection', '/property-services/property-snagging/'],
    ]],
    ['title' => 'sell', 'links' => [
        ['List your Property', '/list-your-property/'],
        ['Guide to Selling', '/property-selling-dubai-guide/'],
        ['Book a Valuation', '/list-your-property/'],
    ]],
    ['title' => 'Off plan', 'links' => [
        ['New Projects', '/new-projects/'],
        ['Guide to Buying Off Plan', '/offplan-property-buying-dubai-guide/'],
        ['Best Dubai Communities', '/area-guides/'],
        ['Top Dubai Developers', '/developers/'],
        ['Snagging & Inspection', '/property-services/property-snagging/'],
        ['Upcoming Roadshows', '/roadshow/'],
        ['Branded Residences', '/branded-residences-in-dubai/'],
    ]],
    ['title' => 'rent', 'links' => [
        ['Properties to Rent', '/let/properties-for-rent/'],
        ['Guide to Renting', '/property-renting-dubai-guide/'],
        ['Short Term Rentals', '/property-services/short-term-rentals/'],
        ['Property Management', '/property-services/property-management/'],
    ]],
    ['title' => 'services', 'links' => [
        ['Properties for Sale', '/buy/properties-for-sale/'],
        ['Leasing', '/property-services/leasing/'],
        ['Mortgages', '/property-services/mortgages/'],
        ['Conveyancing', '/property-services/conveyancing/'],
        ['Property Management', '/property-services/property-management/'],
        ['Snagging & Inspection', '/property-services/property-snagging/'],
        ['Holiday Homes', '/property-services/short-term-rentals/'],
        ['Currency Exchange', '/ifx-dubai/'],
        ['Partner with Zoya Ventures', '/property-services/partner-program/'],
        ['PRYPCO', '/property-services/prypco/'],
        ['Ethnovate', '/property-services/ethnovate/'],
    ]],
    ['title' => 'About', 'links' => [
        ['About Us', '/about/'],
        ['Meet The Team', '/team/'],
        ['Our Awards', '/about/our-awards/'],
        ['Careers', '/careers/'],
        ['Philanthropy', '/about/philanthropy/'],
        ['Dubai News & Blog', '/blog/'],
        ['Sustainability Initiative', '/about/sustainability-initiative/'],
    ]],
];

const FOOTER_WA_LINK = 'https://wa.provident.ae/inquire?phone=971568308221&text=Hello%20Zoya%20Ventures%2C%0A%0AI%20would%20like%20to%20know%20more%20about%20this%20page%3A%0A%0A%E2%80%A2%20Page%20Name%3A%20%0A%E2%80%A2%20Link%3A%20%0A%0AModifying%20this%20message%20will%20prevent%20it%20from%20being%20sent%20to%20the%20agent.&utm_source=Browser%20Direct&gclid=%22%22&event_type=Whatsapp%20Click&utm_platform=%22%22';

$FOOTER_CURRENCIES = ['Pound Sterling - GBP £', 'UAE Dirams - AED د.إ', 'USD - $', 'EUR - €', 'SAR - ر.س'];
$FOOTER_UNITS = ['SQ M', 'SQ FT'];

function footer_settings_selects(): void
{
    global $FOOTER_CURRENCIES, $FOOTER_UNITS;
    ?>
    <div class="footer-cta-section-wrap settings">
      <p class="settings-heading">Settings</p>
      <div class="react-select-wrap filter-select currency-type-select">
        <div class="react-select css-b62m3t-container">
          <div class="react-select__control css-14qho42-control js-footer-select" data-options='<?= esc(json_encode($FOOTER_CURRENCIES, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS)) ?>' role="button" tabindex="0">
            <div class="react-select__value-container react-select__value-container--has-value css-hlgwow">
              <div class="react-select__single-value css-1ubv46r-singleValue"><?= esc($FOOTER_CURRENCIES[0]) ?></div>
            </div>
            <div class="react-select__indicators css-1wy0on6">
              <span class="react-select__indicator-separator css-1uei4ir-indicatorSeparator"></span>
              <div class="dropdown-indicator react-select__indicator react-select__dropdown-indicator css-15ctyzv-indicatorContainer" aria-hidden="true">
                <svg width="16" height="16" class="arrow-down-icon">
                  <path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="react-select-wrap filter-select currency-type-select">
        <div class="react-select css-b62m3t-container">
          <div class="react-select__control css-14qho42-control js-footer-select" data-options='<?= esc(json_encode($FOOTER_UNITS, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS)) ?>' role="button" tabindex="0">
            <div class="react-select__value-container react-select__value-container--has-value css-hlgwow">
              <div class="react-select__single-value css-1ubv46r-singleValue"><?= esc($FOOTER_UNITS[0]) ?></div>
            </div>
            <div class="react-select__indicators css-1wy0on6">
              <span class="react-select__indicator-separator css-1uei4ir-indicatorSeparator"></span>
              <div class="dropdown-indicator react-select__indicator react-select__dropdown-indicator css-15ctyzv-indicatorContainer" aria-hidden="true">
                <svg width="16" height="16" class="arrow-down-icon">
                  <path d="M13 5.5L8 10.5L3 5.5" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
}
?>
<div class="footer-wrap section-p">
  <div class="footer-container container">
    <div class="d-flex justify-content-between">
      <div class="footer-cta-section-wrap d-none d-xl-grid">
        <?php foreach ($FOOTER_COLS as $c): ?>
          <div class="footer-cta-section">
            <p class="title"><?= esc($c['title']) ?></p>
            <div class="cta-section">
              <?php foreach ($c['links'] as [$label, $href]): ?>
                <a class="cta" href="<?= esc($href) ?>"><span><?= esc($label) ?></span></a>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="footer-cta-section-wrap settings d-block d-xl-none">
      <?php footer_settings_selects(); ?>
    </div>
    <div class="footer-cta-section-wrap d-block d-xl-none">
      <?php foreach ($FOOTER_COLS as $i => $c): ?>
        <div class="footer-cta-section accordion accordion-item js-footer-accordion">
          <p class="title accordion-header">
            <button type="button" aria-expanded="false" class="accordion-button collapsed js-footer-accordion-toggle"><?= esc($c['title']) ?></button>
          </p>
          <div class="accordion-collapse" hidden>
            <div class="cta-section accordion-body">
              <?php foreach ($c['links'] as [$label, $href]): ?>
                <a class="cta" href="<?= esc($href) ?>"><span><?= esc($label) ?></span></a>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="divider d-none d-md-block"></div>
    <div class="footer-bottom-section">
      <div class="footer-cta-section-wrap settings d-none d-xl-block">
        <?php footer_settings_selects(); ?>
      </div>
      <div class="bottom-section new-logo-gptw">
        <div class="gptw">
          <a href="/blog/provident-estate-great-place-to-work-certification/">
            <svg width="77" height="131" viewBox="0 0 77 131" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Great Place To Work">
              <rect width="77" height="131" fill="#002171"/>
              <path d="M38.5 16 14 38.5V94.5L38.5 117 63 94.5V38.5L38.5 16Z" stroke="#FF1628" stroke-width="2.5"/>
              <text x="38.5" y="72" text-anchor="middle" fill="#FF1628" font-size="17" font-family="Arial, sans-serif" font-weight="bold">GPTW</text>
              <text x="38.5" y="92" text-anchor="middle" fill="#fff" font-size="10" font-family="Arial, sans-serif">Great Place To Work</text>
              <text x="38.5" y="104" text-anchor="middle" fill="#fff" font-size="9" font-family="Arial, sans-serif">CERTIFIED 2026</text>
            </svg>
          </a>
          <div class="d-block d-md-none">
            <p>Zoya Ventures Real Estate is proud to announce that we are now officially certified as a Great Place to Work®</p>
          </div>
        </div>
        <div class="no-top">
          <div class="socials-section">
            <a href="https://facebook.com/providentestate" target="_blank" rel="noreferrer" aria-label="Facebook" class="fb-icon"></a>
            <a href="https://twitter.com/providentagents" target="_blank" rel="noreferrer" aria-label="Twitter" class="tw-icon"></a>
            <a href="https://instagram.com/providentestate/" target="_blank" rel="noreferrer" aria-label="Instagram" class="ig-icon"></a>
            <a href="https://ae.linkedin.com/company/providentestate" target="_blank" rel="noreferrer" aria-label="LinkedIn" class="in-icon"></a>
            <a href="https://youtube.com/@Providentestate" target="_blank" rel="noreferrer" aria-label="YouTube" class="yt-icon"></a>
            <a href="https://t.me/dubaipropertynews" target="_blank" rel="noreferrer" aria-label="Telegram" class="tg-icon"></a>
            <a href="<?= FOOTER_WA_LINK ?>" target="_blank" rel="noreferrer" aria-label="WhatsApp" class="wa-icon"></a>
          </div>
          <div class="terms-section">
            <a href="/privacy-policy/">Privacy Policy</a> <span>/</span>
            <a href="/terms-and-conditions/">Terms &amp; Conditions</a> <span>/</span>
            <a href="/sitemap/">Sitemap</a>
          </div>
          <div class="copyright-section">
            <p>Copyright © <?= date('Y') ?>. Zoya Ventures Real Estate</p>
            <span>|</span>
            <p class="">ORN No:<span class="orn-no">1933</span></p>
          </div>
          <div class="copyright-section">
            <p>PROVIDENT® is a registered trademark since 2008</p>
          </div>
          <p class="site-by">Site by <a rel="nofollow" href="https://www.starberry.tv" target="_blank" class="site-by-name">Starberry</a></p>
        </div>
      </div>
    </div>
  </div>
</div>
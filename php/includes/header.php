<?php
// header.php — site header (port of src/components/header.tsx + header-account-button.tsx)
// Expects: $transparent (bool). Renders <header> markup; mobile drawer markup included (toggled by main.js).

require_once __DIR__ . '/functions.php';

$header_transparent = $transparent ?? false;

const CF16 = 'https://d3h330vgpwpjr8.cloudfront.net/x/16x16/';

$MENUS = [
    [
        'label' => 'Buy', 'href' => '/buy/properties-for-sale/', 'intro' => 'Properties for Sale in Dubai',
        'columns' => [
            ['heading' => 'Properties by Type', 'links' => [
                ['label' => 'Apartments', 'href' => '/buy/apartment-for-sale/', 'icon' => 'apartment_navbar_a62fb5b437.webp'],
                ['label' => 'Villas', 'href' => '/buy/villa-for-sale/', 'icon' => 'villa_navbar_b49863c21e.webp'],
                ['label' => 'Townhouses', 'href' => '/buy/townhouse-for-sale/', 'icon' => 'navbar_townhouse_de60dd8da9.webp'],
                ['label' => 'Penthouses', 'href' => '/buy/penthouse-for-sale/', 'icon' => 'navbar_penthouse_5550318b46.webp'],
                ['label' => 'Commercial', 'href' => '/buy/commercial-properties-for-sale/', 'icon' => 'commercial_navbar_c346b05385.webp'],
                ['label' => 'See All Properties', 'href' => '/buy/properties-for-sale/', 'icon' => 'grid_01_50def6e330.webp'],
            ]],
            ['heading' => 'Buyer Resources', 'links' => [
                ['label' => "Buyer's Guide", 'href' => '/property-buying-dubai-guide/'],
                ['label' => 'Mortgage', 'href' => '/property-services/mortgages/'],
                ['label' => 'Signature by Zoya Ventures', 'href' => '/signature/'],
                ['label' => 'Snagging & Inspection', 'href' => '/property-services/property-snagging/'],
            ]],
        ],
        'cta' => ['title' => 'Signature Collection', 'label' => 'Explore Signature', 'href' => '/signature/', 'image' => 'signature_property_47dbd09aff.webp'],
    ],
    [
        'label' => 'Rent', 'href' => '/let/properties-for-rent/', 'intro' => 'Properties for Rent in Dubai',
        'columns' => [
            ['heading' => 'Properties by Type', 'links' => [
                ['label' => 'Apartments', 'href' => '/let/apartment-for-rent/', 'icon' => 'apartment_navbar_a62fb5b437.webp'],
                ['label' => 'Villas', 'href' => '/let/villa-for-rent/', 'icon' => 'villa_navbar_b49863c21e.webp'],
                ['label' => 'Townhouses', 'href' => '/let/townhouse-for-rent/', 'icon' => 'navbar_townhouse_de60dd8da9.webp'],
                ['label' => 'Penthouses', 'href' => '/let/penthouse-for-rent/', 'icon' => 'navbar_penthouse_5550318b46.webp'],
                ['label' => 'Commercial', 'href' => '/let/commercial-properties-for-rent/', 'icon' => 'commercial_navbar_c346b05385.webp'],
                ['label' => 'See All Properties', 'href' => '/let/properties-for-rent/', 'icon' => 'grid_01_50def6e330.webp'],
            ]],
            ['heading' => 'Tenant Resources', 'links' => [
                ['label' => "Tenant's Guide", 'href' => '/property-renting-dubai-guide/'],
                ['label' => 'Property Management', 'href' => '/property-services/property-management/'],
                ['label' => 'Short Term Rentals', 'href' => '/property-services/short-term-rentals/'],
            ]],
        ],
        'cta' => ['title' => 'Hot Properties', 'subtitle' => 'in Downtown', 'label' => 'Explore Now', 'href' => '/let/properties-for-rent/in-downtown-dubai/', 'image' => 'downtown_img_d032cd58af.webp'],
    ],
    [
        'label' => 'Projects', 'href' => '/new-projects/', 'intro' => 'Off Plan Projects in Dubai',
        'columns' => [
            ['heading' => 'Projects by Type', 'links' => [
                ['label' => 'Apartments', 'href' => '/new-projects/type-apartment/', 'icon' => 'apartment_navbar_a62fb5b437.webp'],
                ['label' => 'Villas', 'href' => '/new-projects/type-villa/', 'icon' => 'villa_navbar_b49863c21e.webp'],
                ['label' => 'Townhouses', 'href' => '/new-projects/type-townhouse/', 'icon' => 'navbar_townhouse_de60dd8da9.webp'],
                ['label' => 'Commercial', 'href' => '/commercial-new-projects/', 'icon' => 'commercial_navbar_c346b05385_1a44a09441.webp'],
                ['label' => 'Penthouses', 'href' => '/new-projects/type-penthouse/', 'icon' => 'navbar_penthouse_5550318b46.webp'],
                ['label' => 'Mansions', 'href' => '/new-projects/type-mansions/', 'icon' => 'villa_navbar_b49863c21e.webp'],
                ['label' => 'See All New Projects', 'href' => '/new-projects/', 'icon' => 'grid_01_50def6e330.webp'],
            ]],
            ['heading' => 'Guide to Buying Off Plan', 'links' => [
                ['label' => 'Off Plan Guide', 'href' => '/offplan-property-buying-dubai-guide/'],
                ['label' => 'Best Dubai Communities', 'href' => '/area-guides/'],
                ['label' => 'Upcoming Roadshows', 'href' => '/roadshow/'],
                ['label' => 'Branded Residences', 'href' => '/branded-residences-in-dubai/'],
            ]],
        ],
        'cta' => ['title' => 'Tilal Binghatti', 'subtitle' => 'By Binghatti', 'label' => 'View Project', 'href' => '', 'image' => 'tilal_binghatii_feature_cf7cf5fbcd.webp'],
    ],
    [
        'label' => 'Developers', 'href' => '/developers/', 'intro' => 'Top Developers in Dubai',
        'columns' => [
            ['heading' => '', 'links' => [
                ['label' => 'Emaar Properties', 'href' => '/new-projects/developed-by-emaar-properties/'],
                ['label' => 'Damac Properties', 'href' => '/new-projects/developed-by-damac-properties/'],
                ['label' => 'Sobha Realty', 'href' => '/new-projects/developed-by-sobha-realty/'],
                ['label' => 'Nakheel Properties', 'href' => '/new-projects/developed-by-nakheel/'],
                ['label' => 'Binghatti Properties', 'href' => '/new-projects/developed-by-binghatti/'],
                ['label' => 'Meraas', 'href' => '/new-projects/developed-by-meraas/'],
                ['label' => 'Danube Properties', 'href' => '/new-projects/developed-by-danube-properties/'],
                ['label' => 'Aldar Properties', 'href' => '/new-projects/developed-by-aldar-properties/'],
                ['label' => 'Iman Developers', 'href' => '/new-projects/developed-by-iman-developers/'],
                ['label' => 'H&H Development', 'href' => '/new-projects/developed-by-hh-development/'],
                ['label' => 'BEYOND', 'href' => '/new-projects/developed-by-beyond/'],
                ['label' => 'LEOS Developments', 'href' => '/new-projects/developed-by-leos-developments/'],
                ['label' => 'All Developers', 'href' => '/developers/'],
            ]],
        ],
        'cta' => ['title' => 'Emaar Properties', 'label' => 'View All Projects', 'href' => '/new-projects/developed-by-emaar-properties/', 'image' => 'emaar_properties_f2c4d0a72c.webp'],
    ],
    [
        'label' => 'Areas', 'href' => '/area-guides/', 'intro' => 'Top Areas in Dubai',
        'columns' => [
            ['heading' => '', 'links' => [
                ['label' => 'Dubai Creek Harbour', 'href' => '/area-guides/dubai-creek-harbour/'],
                ['label' => 'Business Bay', 'href' => '/area-guides/business-bay/'],
                ['label' => 'Dubai Marina', 'href' => '/area-guides/dubai-marina/'],
                ['label' => 'Palm Jumeirah', 'href' => '/area-guides/palm-jumeirah/'],
                ['label' => 'Downtown Dubai', 'href' => '/area-guides/downtown-dubai/'],
                ['label' => 'Jumeirah Village Circle', 'href' => '/area-guides/jumeirah-village-circle/'],
                ['label' => 'EMAAR Beachfront', 'href' => '/area-guides/emaar-beachfront/'],
                ['label' => 'Sobha Hartland', 'href' => '/area-guides/sobha-hartland/'],
                ['label' => 'Expo City', 'href' => '/area-guides/expo-city/'],
                ['label' => 'Dubai Hills Estate', 'href' => '/area-guides/dubai-hills-estate/'],
                ['label' => 'Dubai Islands', 'href' => '/area-guides/dubai-islands/'],
                ['label' => 'Palm Jebel Ali', 'href' => '/area-guides/palm-jebel-ali/'],
                ['label' => 'DAMAC Islands', 'href' => '/area-guides/damac-islands/'],
                ['label' => 'The Oasis', 'href' => '/area-guides/the-oasis-by-emaar/'],
                ['label' => 'All Areas in Dubai', 'href' => '/area-guides/'],
            ]],
        ],
        'cta' => ['title' => 'Best Dubai Communities', 'label' => 'Explore Now', 'href' => '/area-guides/', 'image' => 'area_beachfront_4e41f7a01a.webp'],
    ],
    [
        'label' => 'Services', 'href' => '/property-services/',
        'columns' => [
            ['heading' => 'Our Services', 'links' => [
                ['label' => 'Property Management', 'href' => '/property-services/property-management/', 'icon' => 'property_management_b164aaddda.webp'],
                ['label' => 'List Your Property', 'href' => '/list-your-property/', 'icon' => 'list_your_property_c93b24a87b.webp'],
                ['label' => 'Mortgages', 'href' => '/property-services/mortgages/', 'icon' => 'mortgage_6c1a1f2967.webp'],
                ['label' => 'Conveyancing', 'href' => '/property-services/conveyancing/', 'icon' => 'convancying_9336e8a2bc.webp'],
                ['label' => 'Short Term Rentals', 'href' => '/property-services/short-term-rentals/', 'icon' => 'short_term_rentals_0b6826eaba.webp'],
                ['label' => 'Property Snagging', 'href' => '/property-services/property-snagging/', 'icon' => 'property_snagging_029ca1dcc2.webp'],
                ['label' => 'Partner Program', 'href' => '/property-services/partner-program/', 'icon' => 'partner_program_d717d0cfd9.webp'],
                ['label' => 'Currency Exchange', 'href' => '/ifx-dubai/', 'icon' => 'currency_exchange_2f26732e5f.webp'],
                ['label' => 'PRYPCO', 'href' => '/property-services/prypco/', 'icon' => 'prypco_b6f3bcb341.webp'],
                ['label' => 'Ethnovate', 'href' => '/property-services/ethnovate/', 'icon' => 'ethnovate_bcb86c20fc.webp'],
                ['label' => 'Plots', 'href' => '/property-services/plots/', 'icon' => 'plot_1_02_035f1e1bd0.webp'],
            ]],
        ],
    ],
    ['label' => 'Blogs', 'href' => '/blog/', 'columns' => [], 'plain' => true],
    [
        'label' => 'More',
        'columns' => [
            ['heading' => '', 'off' => false, 'links' => [
                ['label' => 'About us', 'href' => '/about/'],
                ['label' => 'Meet the Team', 'href' => '/team/'],
                ['label' => 'Careers', 'href' => '/careers/'],
                ['label' => 'Contact Us', 'href' => '/contact/'],
            ]],
        ],
    ],
];

const WA_LINK = 'https://wa.provident.ae/inquire?phone=971568308221&text=Hello%20Zoya%20Ventures%2C%0A%0AI%20would%20like%20to%20know%20more%20about%20this%20page%3A%0A%0A%E2%80%A2%20Page%20Name%3A%20%0A%E2%80%A2%20Link%3A%20%0A%0AModifying%20this%20message%20will%20prevent%20it%20from%20being%20sent%20to%20the%20agent.&utm_source=Browser%20Direct&gclid=%22%22&event_type=Whatsapp%20Click&utm_platform=%22%22';

$header_user = get_auth_user();
$header_t = $header_transparent;
$header_btn = $header_t ? 'button-white-outline' : 'button-white';
?>
<div class="<?= $header_t ? 'header-wrap header-transparent' : 'header-wrap' ?>">
  <div class="header container">
    <a href="/" class="site-brand d-inline-flex align-items-center" style="display:inline-flex;align-items:center;justify-content:flex-start;gap:7px;width:auto;height:auto">
      <img draggable="false" src="/lloo.png" alt="Zoya Ventures Real Estate" class="site-brand-mark" style="display:block;width:90px;max-width:90px;height:auto" />
      <span class="<?= $header_t ? 'site-brand-text site-brand-text-white' : 'site-brand-text' ?>" aria-hidden="true" style="display:flex;flex-direction:column;align-items:flex-start;justify-content:center;line-height:1.05;text-align:left">
        <span class="site-brand-top" style="font-size:1.08rem;font-weight:600;letter-spacing:-0.02em;color:<?= $header_t ? '#fff' : '#111827' ?>;white-space:nowrap">Zoya Ventures</span>
        <span class="site-brand-bottom" style="font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:<?= $header_t ? '#fff' : '#07234b' ?>;white-space:nowrap">Real Estate</span>
      </span>
    </a>
    <div class="nav-menu-section">
      <?php foreach ($MENUS as $m): ?>
        <?php if (!empty($m['plain'])): ?>
          <div class="nav-menu nav-menu-list">
            <a class="main-menu" href="<?= esc($m['href']) ?>"><span><?= esc($m['label']) ?></span></a>
          </div>
        <?php else: ?>
          <div class="nav-menu nav-menu-list">
            <?php if (!empty($m['href'])): ?>
              <a class="main-menu" href="<?= esc($m['href']) ?>"><span><?= esc($m['label']) ?></span></a>
            <?php else: ?>
              <button class="main-menu"><span><?= esc($m['label']) ?></span></button>
            <?php endif; ?>
            <div class="sub-menu-wrap">
              <div class="sub-menu-section">
                <div class="menu-section-only">
                  <?php if (!empty($m['intro'])): ?><p class="h4"><?= esc($m['intro']) ?></p><?php endif; ?>
                  <?php foreach ($m['columns'] as $ci => $c): ?>
                    <div class="sub-menu<?= ($c['off'] ?? true) !== false ? ' offplan' : '' ?><?= $ci > 0 ? ' bt' : '' ?>">
                      <?php if (!empty($c['heading'])): ?><p class="heading"><?= esc($c['heading']) ?></p><?php endif; ?>
                      <div class="sub-menu-list">
                        <?php foreach ($c['links'] as $l): ?>
                          <a class="sub-menu-link" href="<?= esc($l['href']) ?>">
                            <?php if (!empty($l['icon'])): ?>
                              <img loading="eager" draggable="false" src="<?= CF16 . $l['icon'] ?>" srcSet="<?= CF16 . $l['icon'] ?> 16w" sizes="(min-width: 100px) 16px" alt="banner-bg - Zoya Ventures Real Estate" />
                            <?php endif; ?>
                            <?= esc($l['label']) ?>
                          </a>
                        <?php endforeach; ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
                <?php if (!empty($m['cta'])): $cta = $m['cta']; ?>
                  <div class="divider"></div>
                  <div class="content-cta-section sub-menu offplan">
                    <div class="image-bg">
                      <?php if (!empty($cta['image'])): ?>
                        <img loading="eager" draggable="false" src="https://d3h330vgpwpjr8.cloudfront.net/x/340x270/<?= $cta['image'] ?>" alt="banner-bg - Zoya Ventures Real Estate" />
                      <?php endif; ?>
                      <div class="content">
                        <p class="heading"><?= esc($cta['title']) ?></p>
                        <?php if (!empty($cta['subtitle'])): ?><p class="description"><?= esc($cta['subtitle']) ?></p><?php endif; ?>
                        <a class="button button-orange" href="<?= esc($cta['href']) ?>"><span><?= esc($cta['label']) ?></span></a>
                      </div>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
      <div class="dev-to d-none d-md-block">
        <div class="dev-toggle">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M17.4107 11.1607L16.4652 10.2152C16.3226 10.0726 16.2041 9.90811 16.1139 9.72785L15.2139 7.92775C15.1087 7.71732 14.8282 7.6718 14.6618 7.83817C14.4848 8.0152 14.2257 8.08234 13.985 8.01356L12.9242 7.71049C12.5214 7.59539 12.1033 7.83687 12.0017 8.24333C11.9257 8.54718 12.049 8.86597 12.3095 9.0397L12.7985 9.36566C13.2907 9.69383 13.3597 10.3903 12.9414 10.8086L12.7746 10.9754C12.5988 11.1512 12.5 11.3897 12.5 11.6383V11.9807C12.5 12.3205 12.4076 12.6539 12.2328 12.9453L11.1373 14.7712C10.8195 15.3009 10.247 15.625 9.6293 15.625C9.14368 15.625 8.75 15.2313 8.75 14.7457V13.7694C8.75 13.0027 8.28322 12.3133 7.57136 12.0285L7.02624 11.8105C6.20812 11.4832 5.72825 10.6305 5.87311 9.76135L5.87897 9.72616C5.91765 9.49413 5.99964 9.27144 6.12066 9.06973L6.19517 8.94555C6.60286 8.26607 7.39368 7.91624 8.17069 8.07164L9.15223 8.26795C9.63113 8.36373 10.1033 8.0758 10.2375 7.6062L10.4113 6.99812C10.5352 6.56434 10.3326 6.1038 9.92909 5.90204L9.375 5.625L9.29917 5.70083C8.94754 6.05246 8.47063 6.25 7.97335 6.25H7.82258C7.61603 6.25 7.41746 6.33254 7.27141 6.47859C7.03519 6.71481 6.67345 6.77423 6.37465 6.62483C5.97027 6.42264 5.82361 5.91899 6.05622 5.53131L7.2328 3.57033C7.3502 3.37467 7.43041 3.16006 7.47044 2.93728M17.4107 11.1607C17.4695 10.7824 17.5 10.3948 17.5 10C17.5 5.85786 14.1421 2.5 10 2.5C9.11236 2.5 8.26073 2.6542 7.47044 2.93728M17.4107 11.1607C16.8528 14.7517 13.7474 17.5 10 17.5C5.85786 17.5 2.5 14.1421 2.5 10C2.5 6.74551 4.57291 3.97517 7.47044 2.93728" stroke="#9399A4" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span class="">AED</span>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M13 5.5L8 10.5L3 5.5" stroke="#9399A4" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
        </div>
      </div>
      <div class="d-none d-xl-flex log-in-btn">
        <?php if ($header_user): $href = in_array($header_user['role'], ['admin', 'agent'], true) ? '/admin' : '/dashboard'; ?>
          <a href="<?= $href ?>" class="button list-prop-btn <?= $header_btn ?>" aria-label="My Account">
            <svg class="user-icon user-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path fill="none" d="M2 13.3333C3.55719 11.6817 5.67134 10.6667 8 10.6667C10.3287 10.6667 12.4428 11.6817 14 13.3333M11 5C11 6.65685 9.65685 8 8 8C6.34315 8 5 6.65685 5 5C5 3.34315 6.34315 2 8 2C9.65685 2 11 3.34315 11 5Z" stroke="#07234B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </a>
        <?php else: ?>
          <a href="/login" class="button list-prop-btn <?= $header_btn ?>" aria-label="Login">
            <svg class="user-icon user-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
              <path fill="none" d="M2 13.3333C3.55719 11.6817 5.67134 10.6667 8 10.6667C10.3287 10.6667 12.4428 11.6817 14 13.3333M11 5C11 6.65685 9.65685 8 8 8C6.34315 8 5 6.65685 5 5C5 3.34315 6.34315 2 8 2C9.65685 2 11 3.34315 11 5Z" stroke="#07234B" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Login
          </a>
        <?php endif; ?>
      </div>
      <div class="nav-menu d-xl-flex d-md-none nav-menu-property-list-button">
        <a class="button list-prop-btn <?= $header_btn ?>" href="/list-your-property/">List Your Property</a>
      </div>
      <a href="<?= WA_LINK ?>" class="nav-menu nav-menu-icon-wrap" aria-label="WhatsApp Us" target="_blank" rel="noreferrer">
        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="whatsapp-icon menu-icon">
          <path fill-rule="evenodd" clip-rule="evenodd" d="M9.9971 0C4.48428 0 0 4.48553 0 9.99991C0 12.1868 0.705268 14.215 1.90417 15.8612L0.658162 19.5766L4.50185 18.3481C6.08275 19.3946 7.96934 20 10.0029 20C15.5157 20 20 15.5143 20 10.0001C20 4.48571 15.5157 0.000165304 10.0029 0.000165304L9.9971 0ZM7.20535 5.07951C7.01145 4.61511 6.86449 4.59753 6.57074 4.58558C6.47072 4.57978 6.35925 4.57397 6.23568 4.57397C5.85352 4.57397 5.45394 4.68564 5.21294 4.93252C4.91918 5.23233 4.19034 5.93182 4.19034 7.36633C4.19034 8.80084 5.23649 10.1882 5.37748 10.3823C5.52444 10.5761 7.41699 13.5626 10.3555 14.7798C12.6535 15.7321 13.3354 15.6439 13.8584 15.5322C14.6224 15.3676 15.5804 14.803 15.8214 14.1213C16.0624 13.4392 16.0624 12.8572 15.9918 12.7337C15.9213 12.6103 15.7272 12.5399 15.4335 12.3928C15.1397 12.2458 13.7114 11.5403 13.441 11.4462C13.1765 11.3463 12.9239 11.3817 12.7242 11.6639C12.442 12.0578 12.1658 12.4576 11.9424 12.6985C11.7661 12.8867 11.478 12.9102 11.2371 12.8102C10.9139 12.6751 10.0089 12.3574 8.89208 11.3639C8.02807 10.5939 7.4404 9.63573 7.27005 9.3477C7.09954 9.05386 7.25245 8.88313 7.38747 8.72452C7.53443 8.54218 7.67543 8.41293 7.82239 8.24236C7.96935 8.07197 8.05163 7.9837 8.14568 7.78378C8.24569 7.58982 8.17502 7.38989 8.10453 7.24289C8.03403 7.09589 7.44636 5.66138 7.20535 5.07951Z" fill="#67C15E"/>
        </svg>
      </a>
      <a href="tel:+971568308221" class="nav-menu nav-menu-icon-wrap" aria-label="Call Us">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="mobile-icon menu-icon">
          <path d="M10.5 1.5H8.25C7.00736 1.5 6 2.50736 6 3.75V20.25C6 21.4926 7.00736 22.5 8.25 22.5H15.75C16.9926 22.5 18 21.4926 18 20.25V3.75C18 2.50736 16.9926 1.5 15.75 1.5H13.5M10.5 1.5V3H13.5V1.5M10.5 1.5H13.5M10.5 20.25H13.5" stroke="<?= $header_t ? '#fff' : '#07234B' ?>" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>
      <a class="nav-menu nav-menu-icon-wrap" aria-label="Search Properties" href="/buy/properties-for-sale/">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="16" viewBox="0 0 17 16" fill="none" class="search-icon menu-icon">
          <path d="M14.5 14L11.0355 10.5355M11.0355 10.5355C11.9404 9.63071 12.5 8.38071 12.5 7C12.5 4.23858 10.2614 2 7.5 2C4.73858 2 2.5 4.23858 2.5 7C2.5 9.76142 4.73858 12 7.5 12C8.88071 12 10.1307 11.4404 11.0355 10.5355Z" stroke="<?= $header_t ? '#fff' : '#07234B' ?>" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </a>
      <button class="nav-menu nav-menu-icon-wrap js-mobile-drawer-open" type="button">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="bars-icon menu-icon">
          <path d="M3.75 6.75H20.25M3.75 12H20.25M3.75 17.25H20.25" stroke="<?= $header_t ? '#fff' : '#07234B' ?>" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </button>
    </div>
  </div>
  <div class="mobile-drawer-overlay js-mobile-drawer" hidden>
    <div class="mobile-drawer">
      <div class="mobile-drawer-header">
        <a href="/" class="site-brand d-inline-flex align-items-center" style="display:inline-flex;align-items:center;justify-content:flex-start;gap:7px;width:auto;height:auto">
          <img draggable="false" src="/lloo.png" alt="Zoya Ventures Real Estate" class="site-brand-mark" style="display:block;width:90px;max-width:90px;height:auto"/>
          <span class="site-brand-text" aria-hidden="true" style="display:flex;flex-direction:column;align-items:flex-start;justify-content:center;line-height:1.05;text-align:left">
            <span class="site-brand-top" style="font-size:1.08rem;font-weight:600;letter-spacing:-0.02em;color:#111827;white-space:nowrap">Zoya Ventures</span>
            <span class="site-brand-bottom" style="font-size:.72rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:#07234b;white-space:nowrap">Real Estate</span>
          </span>
        </a>
        <button class="mobile-drawer-close js-mobile-drawer-close" type="button" aria-label="Close">
          <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
            <path d="M5 5l10 10M15 5 5 15" stroke="#35373C" stroke-width="1.4" stroke-linecap="round"/>
          </svg>
        </button>
      </div>
      <div class="mobile-drawer-body">
        <?php foreach ($MENUS as $i => $m): ?>
          <?php if (!empty($m['plain'])): ?>
            <a class="mobile-nav-item js-mobile-drawer-close" href="<?= esc($m['href']) ?>"><?= esc($m['label']) ?></a>
          <?php else: ?>
            <div class="accordion-item">
              <p class="title accordion-header">
                <button type="button" aria-expanded="false" class="accordion-button collapsed js-mobile-accordion"><?= esc($m['label']) ?></button>
              </p>
              <div class="accordion-collapse" hidden>
                <div class="cta-section accordion-body">
                  <?php foreach ($m['columns'] as $c): foreach ($c['links'] as $l): ?>
                    <a class="cta js-mobile-drawer-close" href="<?= esc($l['href']) ?>"><span><?= esc($l['label']) ?></span></a>
                  <?php endforeach; endforeach; ?>
                </div>
              </div>
            </div>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <div class="mobile-drawer-footer">
        <a class="button list-prop-btn button-white-outline js-mobile-drawer-close" href="/list-your-property/">List Your Property</a>
      </div>
    </div>
  </div>
</div>
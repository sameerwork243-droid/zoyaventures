<?php
// pages/property.php — PropertyDetailPage port (src/components/property-detail.tsx)
// Expects: $route (canonical property route, e.g. /buy/1-bedroom-...-31307), $model['data'] as $data.

require_once __DIR__ . '/../includes/render/property-gallery.php';
require_once __DIR__ . '/../includes/render/property-enquiry-form.php';
require_once __DIR__ . '/../includes/render/property-card.php';
require_once __DIR__ . '/../includes/render/listing-ui.php';
require_once __DIR__ . '/../includes/render/read-more.php';
require_once __DIR__ . '/../includes/render/rich.php';
require_once __DIR__ . '/../includes/head.php';

$p = $model['data'] ?? [];
if (!is_array($p)) $p = [];

$kind = (str_contains(strtolower((string) ($p['search_type'] ?? '')), 'rent') || str_starts_with($route, '/let')) ? 'let' : 'buy';
$sale = $kind === 'let';
$purpose = $sale ? 'For Rent' : 'For Sale';
$similar = similar_properties($p, $kind);

$images = [];
foreach ($p['images'] ?? [] as $im) {
    if (!is_array($im)) continue;
    $src = $im['srcUrl'] ?? $im['url'] ?? null;
    if ($src) $images[] = $src;
}

$title = $p['title'] ?? (($p['building'][0] ?? 'Property') . ' in ' . ($p['display_address'] ?? 'Dubai'));
$completion = str_replace('-', ' ', (string) ($p['status'] ?? 'Ready'));
$furnishing = $p['furnishing'] ?? 'N/A';
if (is_array($furnishing)) $furnishing = $furnishing[0] ?? 'N/A';
$building = is_array($p['building'] ?? null) ? ($p['building'][0] ?? null) : ($p['building'] ?? null);
$neg = neg_of($p);
if ($neg === []) $neg = [];
$phone = $neg['phone'] ?? '+971 568 308 221';
$type = $building ?? $p['building_type'] ?? 'Property';
$size = $p['floorarea_min'] ?? $p['floorarea_max'] ?? null;
$amenities = is_array($p['accommodation_summary'] ?? null)
    ? $p['accommodation_summary']
    : (is_array($p['amenities'] ?? null) ? $p['amenities'] : []);
$amenities = array_filter($amenities);
$description = $p['long_description'] ?? $p['description'] ?? $p['introtext'] ?? '';
$completionYear = $p['completion_year'] ?? 'N/A';
$pricePerSqFt = ($size && !empty($p['price'])) ? (int) round((int) $p['price'] / (int) $size) : null;
$isSignature = ((int) ($p['price'] ?? 0)) >= 20000000;
$status = $p['status'] ?? 'Ready';
$qualifier = is_array($p['price_qualifier'] ?? null) ? ($p['price_qualifier'][0] ?? 'AED') : ($p['price_qualifier'] ?? 'AED');
$qualifier = $qualifier !== '' ? $qualifier : 'AED';
$waHit = $p;
$waHit['search_type'] = $sale ? 'rent' : 'sale';
$waHref = wa_link_property($waHit);
$negUrl = $neg['url'] ?? '';
$bedroom = $p['bedroom'] ?? null;

function key_info(string $label, string $value): string
{
    return '<div class="key-info-item"><div><p class="label">' . esc($label) . '</p><p class="value">' . esc($value) . '</p></div></div>';
}
?><!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<?php require __DIR__ . '/../includes/header.php'; ?>
<main>

<div>
  <div class="property-breadcrumb-wrap">
    <div class="breadcrumbs-wrap">
      <div class="breadcrumbs-container container">
        <nav class="breadcrumbs">
          <ol class="breadcrumb">
            <li class="enable-link-home breadcrumb-item">
              <a class="breadcrumb-link enable-link" href="/">Home</a>
            </li>
            <li class=" breadcrumb-item">
              <a class="breadcrumb-link enable-link" href="<?= $sale ? '/let/properties-for-rent/' : '/buy/properties-for-sale/' ?>">
                <?= $sale ? 'Properties for Rent' : 'Properties for Sale' ?>
              </a>
            </li>
            <li class=" breadcrumb-item active">
              <a aria-current="page" class="breadcrumb-link disable-link" href="<?= esc($route . '/') ?>"><?= esc($title) ?></a>
            </li>
          </ol>
        </nav>
      </div>
    </div>
  </div>

  <div class="property-banner-wrap">
    <div class="property-banner-container">
      <div class="property-banner">
        <div class="images-section">
          <div class="dd-v-i"></div>
          <?php if ($status): ?>
            <div class="property-status-badge"><?= esc(str_replace('-', ' ', (string) $status)) ?></div>
          <?php endif; ?>
          <?php if ($isSignature): ?>
            <div class="signature-badge">
              <img src="/images/signature-badge.svg" alt="Signature Project">
            </div>
          <?php endif; ?>
          <?= property_gallery($images, $type, $p['display_address'] ?? null, $title) ?>
        </div>
      </div>
    </div>
  </div>

  <div class="container">
    <div class="property-detail-body">
      <div class="left-section">
        <div class="property-info-wrapper">
          <div class="property-info-container">
            <h1 style="position:absolute;top:0;opacity:0;font-size:10px">
              <?= esc($type) ?> for <?= $sale ? 'rent' : 'sale' ?> with <?= esc((string) $bedroom) ?> bedroom in <?= esc($p['display_address'] ?? 'Dubai') ?> at <?= !empty($p['price']) ? 'AED ' . number_format((int) $p['price'], 0, '.', ',') : '' ?> [<?= esc((string) ($p['crm_id'] ?? '')) ?>]
            </h1>
            <div class="price-section">
              <h2 class="price">
                <?= esc(($qualifier !== 'AED' ? $qualifier . ' ' : 'AED ')) ?><?= number_format((int) ($p['price'] ?? 0), 0, '.', ',') ?>
              </h2>
              <?php if ($pricePerSqFt): ?>
                <p class="price-per-sqft">AED <?= number_format($pricePerSqFt, 0, '.', ',') ?> / sq ft</p>
              <?php endif; ?>
            </div>
            <button class="mortgage-link" type="button">Calculate your mortgage repayments</button>
            <div class="detail-save-wrap">
              <?= save_button_markup($route . '/', (string) ($p['slug'] ?? ''), $title, (int) ($p['price'] ?? 0), $images[0] ?? '', 'button') ?>
            </div>
            <div class="description-section">
              <p class="description1"><?= esc((string) ($p['introtext'] ?? '')) ?></p>
              <p class="description2"><?= esc((string) ($p['display_address'] ?? $p['address'] ?? '')) ?></p>
            </div>
            <div class="info-section">
              <p class="bedrooms">
                <svg width="16" height="16" class="bed-icon" viewBox="0 0 16 16" fill="none">
                  <path d="M14.6666 12.6667V10.6667M14.6666 10.6667V8C14.6666 6.52724 13.4727 5.33333 12 5.33333H7.99998V10.6667M14.6666 10.6667H7.99998M7.99998 10.6667H1.33331M1.33331 10.6667V4M1.33331 10.6667V12.6667M5.99999 7.33333C5.99999 8.06973 5.40303 8.66667 4.66665 8.66667C3.93027 8.66667 3.33332 8.06973 3.33332 7.33333C3.33332 6.59695 3.93027 6 4.66665 6C5.40303 6 5.99999 6.59695 5.99999 7.33333Z" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span><?= esc((string) $bedroom) ?> Bed<?= $bedroom !== 1 ? 's' : '' ?></span>
              </p>
              <p class="bathrooms">
                <svg width="16" height="16" class="bath-icon" viewBox="0 0 16 16" fill="none">
                  <path d="M8 3.33333C10.2091 3.33333 12 5.12419 12 7.33333V8H4V7.33333C4 5.12419 5.79086 3.33333 8 3.33333ZM8 3.33333V2" stroke="#35373C" stroke-linecap="round" stroke-linejoin="round"></path>
                  <path d="M4 10.3335H4.00999M4 13.3335H4.00999M7.99501 10.3335H8.00499M7.99501 13.3335H8.00499M11.99 10.3335H12M11.99 13.3335H12" stroke="#35373C" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"></path>
                </svg>
                <span><?= esc((string) ($p['bathroom'] ?? '')) ?> Bath<?= ($p['bathroom'] ?? 0) !== 1 ? 's' : '' ?></span>
              </p>
              <?php if ($size !== null): ?>
                <p class="size">
                  <svg width="16" height="16" class="arrow-4-icon" viewBox="0 0 16 16" fill="none">
                    <path d="M2.5 2.5V5.5M2.5 2.5H5.5M2.5 2.5L6 6M2.5 13.5V10.5M2.5 13.5H5.5M2.5 13.5L6 10M13.5 2.5L10.5 2.5M13.5 2.5V5.5M13.5 2.5L10 6M13.5 13.5H10.5M13.5 13.5V10.5M13.5 13.5L10 10" stroke="#07234B" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  <span><?= number_format((int) $size, 0, '.', ',') ?> sq ft</span>
                </p>
              <?php endif; ?>
            </div>
            <div class="key-info-section">
              <p class="heading">Key Information</p>
              <div class="key-infos">
                <?= key_info('Property Type', $type) ?>
                <?= key_info('Purpose', $purpose) ?>
                <?= key_info('Completion', $completion) ?>
                <?= key_info('Completion Year', (string) $completionYear) ?>
                <?= key_info('Furnishing Type', (string) $furnishing) ?>
                <?php if ($size): ?><?= key_info('Size', number_format((int) $size, 0, '.', ',') . ' sq ft') ?><?php endif; ?>
                <?= key_info('Property ID', (string) ($p['crm_id'] ?? '')) ?>
              </div>
            </div>
            <div class="divider"></div>
            <?php if (count($amenities)): ?>
              <div class="property-features-section">
                <p class="heading">Amenities</p>
                <div class="features-wrap">
                  <?php foreach ($amenities as $a): ?>
                    <div class="feature-item">
                      <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M4 10.5l4 4L16 6.5" stroke="#EE7133" stroke-linecap="round" stroke-linejoin="round"></path>
                      </svg>
                      <p class="feature-text"><?= esc((string) $a) ?></p>
                    </div>
                  <?php endforeach; ?>
                </div>
              </div>
            <?php endif; ?>
            <div class="divider"></div>
            <div>
              <div class="long-description-section" id="contentsection-property">
                <p class="heading">Description</p>
                <?= read_more(rich($description), 4, 'long-description') ?>
              </div>

              <?php if (!empty($p['floor_plans'])): ?>
                <div class="floor-plans-section">
                  <p class="heading">Floor Plans</p>
                  <div class="floor-plans-gallery">
                    <?php foreach ($p['floor_plans'] as $i => $plan): ?>
                      <div class="floor-plan-item">
                        <img src="<?= esc(cft($plan['url'] ?? '', 400, 300)) ?>" alt="Floor plan <?= $i + 1 ?>">
                      </div>
                    <?php endforeach; ?>
                  </div>
                </div>
              <?php endif; ?>

              <?= mortgage_calculator(
                  !empty($p['price']) ? number_format((int) $p['price'], 0, '.', ',') : '3,000,000',
                  (string) $qualifier,
                  'Calculate Mortgage Repayments',
                  true
              ) ?>

              <div class="similar-properties-section">
                <p class="heading">Similar Properties</p>
                <div class="similar-properties-slider">
                  <?php if (count($similar)): ?>
                    <?php foreach ($similar as $s): ?>
                      <?= property_card($s, false) ?>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p class="no-results">No similar properties found.</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="right-section-wrap sticky-sidebar">
        <div class="right-section">
          <div class="property-nego-card-wrap">
            <div class="border-side">
              <div class="top-section">
                <a href="<?= esc('tel:' . preg_replace('/\s+/', '', (string) $phone)) ?>" class="button button-orange">
                  <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone">
                    <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                  </svg>
                  Call
                </a>
                <a href="<?= esc($waHref) ?>" class="button button-green" target="_blank" rel="noreferrer">
                  <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                    <path d="M4.5 6.5C4.5 5.96957 4.71071 5.46086 5.08579 5.08579C5.46086 4.71071 5.96957 4.5 6.5 4.5L7.5 6.5L6.73 7.65438C7.03544 8.38421 7.61579 8.96456 8.34562 9.27L9.5 8.5L11.5 9.5C11.5 10.0304 11.2893 10.5391 10.9142 10.9142C10.5391 11.2893 10.0304 11.5 9.5 11.5C8.17392 11.5 6.90215 10.9732 5.96447 10.0355C5.02678 9.09785 4.5 7.82608 4.5 6.5Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                    <path d="M4.9956 13.1945C6.25594 13.9239 7.73853 14.1701 9.16697 13.8871C10.5954 13.6041 11.8722 12.8114 12.7593 11.6566C13.6464 10.5017 14.0832 9.06373 13.9883 7.61063C13.8935 6.15753 13.2734 4.78852 12.2437 3.75883C11.214 2.72915 9.84503 2.10907 8.39193 2.01422C6.93882 1.91936 5.50082 2.3562 4.34601 3.24328C3.1912 4.13037 2.39841 5.40715 2.11545 6.83559C1.83249 8.26403 2.07868 9.74662 2.80811 11.007L2.02623 13.3413C1.99685 13.4294 1.99259 13.524 2.01392 13.6144C2.03525 13.7044 2.08133 13.7874 2.147 13.8531C2.21266 13.9187 2.29532 13.9648 2.38571 13.9861C2.47609 14.0075 2.57063 14.0032 2.65873 13.9738L4.9956 13.1945Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
                  </svg>
                  WhatsApp
                </a>
              </div>
              <div class="bottom-section">
                <a class="img-section img-zoom" href="<?= $negUrl ? esc($negUrl) : '/team/' ?>">
                  <div class="img-section">
                    <?php if ($negUrl): ?>
                      <img draggable="false" src="<?= esc($negUrl) ?>" alt="nego">
                    <?php else: ?>
                      <img draggable="false" src="https://d3h330vgpwpjr8.cloudfront.net/x/200x200/man_icon_98ac9e68af.webp" alt="nego">
                    <?php endif; ?>
                  </div>
                </a>
                <div class="nego-info">
                  <a href="/team/">
                    <p class="name"><?= esc($neg['name'] ?? 'Zoya Ventures Real Estate') ?></p>
                    <p class="designation"><?= esc($neg['designation'] ?? 'Property Consultant') ?></p>
                    <?php if (!empty($neg['brn_number'])): ?>
                      <p class="orn-no">BRN No: <?= esc((string) $neg['brn_number']) ?></p>
                    <?php endif; ?>
                  </a>
                </div>
                <div class="d-flex d-md-none team-icon-only">
                  <a href="<?= esc('tel:' . preg_replace('/\s+/', '', (string) $phone)) ?>" class="ph">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
                      <path d="M14.5 11.3v2a1.34 1.34 0 0 1-1.47 1.34 13.2 13.2 0 0 1-5.74-2 13.2 13.2 0 0 1-4-4A13.2 13.2 0 0 1 1.3 2.97 1.34 1.34 0 0 1 2.63 1.5h2a1.34 1.34 0 0 1 1.34 1.14c.07.66.27 1.3.47 1.87a1.34 1.34 0 0 1-.33 1.4l-.87.87a10.7 10.7 0 0 0 4 4l.87-.87a1.34 1.34 0 0 1 1.4-.33c.57.2 1.21.4 1.87.47.62.06 1.1.6 1.1 1.25Z" stroke="#EE7133" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                  </a>
                  <a href="<?= esc($waHref) ?>" class="wa" target="_blank" rel="noreferrer">
                    <svg width="17" height="16" viewBox="0 0 17 16" fill="none">
                      <path fill-rule="evenodd" clip-rule="evenodd" d="M8.83317 1.3335C5.15125 1.3335 2.1665 4.31825 2.1665 8.00016C2.1665 9.0935 2.43009 10.1266 2.89742 11.0381L2.18009 14.051C2.16035 14.1341 2.1622 14.2208 2.18547 14.303C2.20874 14.3851 2.25266 14.4599 2.31303 14.5203C2.37341 14.5807 2.44823 14.6246 2.53038 14.6479C2.61253 14.6711 2.69927 14.673 2.78234 14.6532L2.79525 13.9359C3.70675 14.4032 4.73984 14.6668 5.83317 14.6668C9.5151 14.6668 12.4998 11.6821 12.4998 8.00016C12.4998 4.31825 9.5151 1.3335 5.83317 1.3335ZM3.1665 8.00016C3.1665 4.87058 5.70359 2.3335 8.83317 2.3335C11.9628 2.3335 14.4998 4.87058 14.4998 8.00016C14.4998 11.1297 11.9628 13.6668 8.83317 13.6668C7.84284 13.6668 6.91317 13.4132 6.10425 12.9677C5.9954 12.9078 5.86814 12.8906 5.74725 12.9193L3.34109 13.4922L3.914 11.0861C3.94278 10.9652 3.92552 10.8379 3.86559 10.7291C3.42009 9.92008 3.1665 8.9905 3.1665 8.00016ZM7.00175 9.83158C7.99967 10.8294 9.33025 11.4988 10.8145 11.6582C11.7958 11.7634 12.4998 10.9549 12.4998 10.1151V9.53208C12.4998 9.19235 12.3903 8.86167 12.1875 8.58911C11.9847 8.31654 11.6995 8.11662 11.3741 8.019L11.3277 8.00508L11.2802 7.99575L10.6117 7.86408C10.3967 7.80843 10.1724 7.79873 9.95344 7.83561C9.73447 7.87248 9.52573 7.9551 9.34084 8.07808C9.12323 7.90666 8.92667 7.7101 8.75525 7.4925C8.87827 7.30759 8.96092 7.09882 8.99781 6.87982C9.0347 6.66082 9.02499 6.4365 8.96934 6.2215L8.8375 5.55308L8.82817 5.50558L8.81425 5.45916C8.71664 5.1338 8.51673 4.84857 8.2442 4.64579C7.97167 4.44302 7.64103 4.3335 7.30134 4.3335H6.71817C5.87842 4.3335 5.06984 5.03733 5.17509 6.01875C5.33442 7.50291 6.00375 8.83366 7.00175 9.83158ZM9.80609 8.98333C9.88009 8.90934 9.97275 8.85678 10.0742 8.8312C10.1757 8.80563 10.2822 8.80801 10.3824 8.83808L11.0868 8.97683C11.2062 9.01268 11.3109 9.08606 11.3853 9.18608C11.4597 9.28609 11.4998 9.40743 11.4998 9.53208V10.1151C11.4998 10.4352 11.2395 10.698 10.9213 10.6638C10.1261 10.5789 9.36029 10.3159 8.68067 9.8945C8.32817 9.67609 8.0065 9.41059 7.73217 9.10109C7.90825 8.9815 8.05059 8.81884 8.14684 8.62875C8.24309 8.43867 8.28984 8.22747 8.28284 8.01517C8.27583 7.80286 8.21533 7.59542 8.10667 7.41275C7.998 7.23008 7.84467 7.07817 7.66092 6.97142C7.48833 6.86175 7.29517 6.78584 7.09259 6.74806C6.89 6.71029 6.68212 6.71167 6.48017 6.75217C6.21625 6.805 5.96642 6.91725 5.74725 7.08125C5.7735 7.2585 5.80692 7.428 5.84775 7.58925C6.23417 8.70067 6.86409 9.71866 7.00175 9.83158Z" fill="white"></path>
                    </svg>
                  </a>
                </div>
              </div>
              <?= property_enquiry_form((string) ($p['crm_id'] ?? ''), (string) ($p['slug'] ?? ''), $route) ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="floating-cta-shell-wrap detail-prop">
    <div class="floating-cta-shell container">
      <div class="floating-section">
        <a class="button button-orange" href="#bav-form">
          <span>Email</span>
        </a>
        <a href="<?= esc('tel:' . preg_replace('/\s+/', '', (string) $phone)) ?>" class="button button-orange">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-phone">
            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
          </svg>
          <span>Call</span>
        </a>
        <a href="<?= esc($waHref) ?>" class="button button-green" target="_blank" rel="noreferrer">
          <svg width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M4.5 6.5C4.5 5.96957 4.71071 5.46086 5.08579 5.08579C5.46086 4.71071 5.96957 4.5 6.5 4.5L7.5 6.5L6.73 7.65438C7.03544 8.38421 7.61579 8.96456 8.34562 9.27L9.5 8.5L11.5 9.5C11.5 10.0304 11.2893 10.5391 10.9142 10.9142C10.5391 11.2893 10.0304 11.5 9.5 11.5C8.17392 11.5 6.90215 10.9732 5.96447 10.0355C5.02678 9.09785 4.5 7.82608 4.5 6.5Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
            <path d="M4.9956 13.1945C6.25594 13.9239 7.73853 14.1701 9.16697 13.8871C10.5954 13.6041 11.8722 12.8114 12.7593 11.6566C13.6464 10.5017 14.0832 9.06373 13.9883 7.61063C13.8935 6.15753 13.2734 4.78852 12.2437 3.75883C11.214 2.72915 9.84503 2.10907 8.39193 2.01422C6.93882 1.91936 5.50082 2.3562 4.34601 3.24328C3.1912 4.13037 2.39841 5.40715 2.11545 6.83559C1.83249 8.26403 2.07868 9.74662 2.80811 11.007L2.02623 13.3413C1.99685 13.4294 1.99259 13.524 2.01392 13.6144C2.03525 13.7044 2.08133 13.7874 2.147 13.8531C2.21266 13.9187 2.29532 13.9648 2.38571 13.9861C2.47609 14.0075 2.57063 14.0032 2.65873 13.9738L4.9956 13.1945Z" stroke="white" stroke-linecap="round" stroke-linejoin="round"></path>
          </svg>
        </a>
      </div>
    </div>
  </div>
</div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php render_site_footer_scripts(); ?>
</body>
</html>
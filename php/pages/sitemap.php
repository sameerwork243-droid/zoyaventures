<?php
// sitemap.php — sitemap page (port of the Gatsby sitemap page static markup)

require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/head.php';

$page_title = 'Sitemap';

$groups = [
    ['href' => '/buy/properties-for-sale/', 'label' => 'Buy', 'children' => [
        ['href' => '/buy/apartment-for-sale/', 'label' => 'Apartments'],
        ['href' => '/buy/villa-for-sale/', 'label' => 'Villas'],
        ['href' => '/buy/townhouse-for-sale/', 'label' => 'Townhouses'],
        ['href' => '/buy/penthouse-for-sale/', 'label' => 'Penthouses'],
        ['href' => '/buy/commercial-properties-for-sale/', 'label' => 'Commercial'],
    ]],
    ['href' => '/let/properties-for-rent/', 'label' => 'Rent', 'children' => [
        ['href' => '/let/apartment-for-rent/', 'label' => 'Apartments'],
        ['href' => '/let/villa-for-rent/', 'label' => 'Villas'],
        ['href' => '/let/townhouse-for-rent/', 'label' => 'Townhouses'],
        ['href' => '/let/penthouse-for-rent/', 'label' => 'Penthouses'],
        ['href' => '/let/commercial-properties-for-rent/', 'label' => 'Commercial'],
    ]],
    ['href' => '/new-projects/', 'label' => 'Projects', 'children' => [
        ['href' => '/new-projects/type-apartment/', 'label' => 'Apartments'],
        ['href' => '/new-projects/type-villa/', 'label' => 'Villas'],
        ['href' => '/new-projects/type-townhouse/', 'label' => 'Townhouses'],
        ['href' => '/commercial-new-projects/', 'label' => 'Commercial'],
        ['href' => '/new-projects/type-penthouse/', 'label' => 'Penthouses'],
        ['href' => '/new-projects/type-mansions/', 'label' => 'Mansions '],
    ]],
    ['href' => '/developers/', 'label' => 'Developers ', 'children' => [
        ['href' => '/new-projects/developed-by-emaar-properties/', 'label' => 'Emaar Properties'],
        ['href' => '/new-projects/developed-by-damac-properties/', 'label' => 'Damac Properties'],
        ['href' => '/new-projects/developed-by-sobha-realty/', 'label' => 'Sobha Realty'],
        ['href' => '/new-projects/developed-by-nakheel/', 'label' => 'Nakheel Properties'],
        ['href' => '/new-projects/developed-by-binghatti/', 'label' => 'Binghatti Properties'],
        ['href' => '/new-projects/developed-by-meraas/', 'label' => 'Meraas'],
        ['href' => '/new-projects/developed-by-danube-properties/', 'label' => 'Danube Properties'],
        ['href' => '/new-projects/developed-by-aldar-properties/', 'label' => 'Aldar Properties'],
        ['href' => '/new-projects/developed-by-iman-developers/', 'label' => 'Iman Developers'],
        ['href' => '/new-projects/developed-by-hh-development/', 'label' => 'H&H Development'],
        ['href' => '/new-projects/developed-by-beyond/', 'label' => 'BEYOND'],
        ['href' => '/new-projects/developed-by-leos-developments/', 'label' => 'LEOS Developments'],
    ]],
    ['href' => '/area-guides/', 'label' => 'Areas', 'children' => [
        ['href' => '/area-guides/dubai-creek-harbour/', 'label' => 'Dubai Creek Harbour'],
        ['href' => '/area-guides/business-bay/', 'label' => 'Business Bay'],
        ['href' => '/area-guides/dubai-marina/', 'label' => 'Dubai Marina'],
        ['href' => '/area-guides/palm-jumeirah/', 'label' => 'Palm Jumeirah'],
        ['href' => '/area-guides/downtown-dubai/', 'label' => 'Downtown Dubai'],
        ['href' => '/area-guides/jumeirah-village-circle/', 'label' => 'Jumeirah Village Circle'],
        ['href' => '/area-guides/emaar-beachfront/', 'label' => 'EMAAR Beachfront'],
        ['href' => '/area-guides/sobha-hartland/', 'label' => 'Sobha Hartland'],
        ['href' => '/area-guides/expo-city/', 'label' => 'Expo City'],
        ['href' => '/area-guides/dubai-hills-estate/', 'label' => 'Dubai Hills Estate'],
        ['href' => '/area-guides/dubai-islands/', 'label' => 'Dubai Islands'],
        ['href' => '/area-guides/palm-jebel-ali/', 'label' => 'Palm Jebel Ali'],
        ['href' => '/area-guides/damac-islands/', 'label' => 'DAMAC Islands'],
        ['href' => '/area-guides/the-oasis-by-emaar/', 'label' => 'The Oasis'],
    ]],
    ['href' => '/property-services/', 'label' => 'Services', 'children' => [
        ['href' => '/property-services/property-management/', 'label' => 'Property Management'],
        ['href' => '/list-your-property/', 'label' => 'List Your Property'],
        ['href' => '/property-services/mortgages/', 'label' => 'Mortgages'],
        ['href' => '/property-services/conveyancing/', 'label' => 'Conveyancing'],
        ['href' => '/property-services/short-term-rentals/', 'label' => 'Short Term Rentals'],
        ['href' => '/property-services/property-snagging/', 'label' => 'Property Snagging'],
        ['href' => '/property-services/partner-program/', 'label' => 'Partner Program'],
        ['href' => '/ifx-dubai/', 'label' => 'Currency Exchange'],
        ['href' => '/property-services/prypco/', 'label' => 'PRYPCO'],
        ['href' => '/property-services/ethnovate/', 'label' => 'Ethnovate'],
        ['href' => '/property-services/plots/', 'label' => 'Plots'],
    ]],
    ['href' => '/blog/', 'label' => 'Blogs'],
    ['href' => '/about/', 'label' => 'About us'],
    ['href' => '/team/', 'label' => 'Meet the Team'],
    ['href' => '/careers/', 'label' => 'Careers'],
    ['href' => '/about/our-awards/', 'label' => 'Our Awards'],
    ['href' => '/contact/', 'label' => 'Contact Us'],
    ['href' => '/real-estate-guides/', 'label' => 'Real Estate Guides'],
    ['href' => '/complaints-procedure/', 'label' => 'Complaints Procedure'],
    ['href' => '/about/philanthropy/', 'label' => 'Philanthropy'],
    ['href' => '/about/reviews/', 'label' => 'Testimonials'],
    ['href' => '/about/sustainability-initiative/', 'label' => 'Sustainability Initiative'],
];

$list = '';
foreach ($groups as $g) {
    $list .= '<li class="parent"><a class="main-menu" href="' . esc($g['href']) . '"><span>' . esc($g['label']) . '</span></a>';
    if (!empty($g['children'])) {
        $list .= '<div><div><ul class="sub">';
        foreach ($g['children'] as $c) {
            $list .= '<li class="child"><a class="sub-menu-link" href="' . esc($c['href']) . '"><span>' . esc($c['label']) . '</span></a></li>';
        }
        $list .= '</ul></div></div>';
    }
    $list .= '</li>';
}
?>
<!DOCTYPE html>
<html lang="en">
<head><?php render_head(); ?></head>
<body>
<?php require __DIR__ . '/../includes/header.php'; ?>
<main>
  <div class="breadcrumbs-wrap"><div class="breadcrumbs-container container"><nav class="breadcrumbs"><ol class="breadcrumb"><li class="enable-link-home breadcrumb-item"><a aria-current="false" class="breadcrumb-link enable-link" href="/">Home</a></li><li class=" breadcrumb-item active"><a aria-current="page" class="breadcrumb-link disable-link" href="/sitemap/">Sitemap</a></li></ol></nav></div></div>
  <div class="sitemap-page container">
    <h1>Sitemap</h1>
    <div class="sitemap-block">
      <ul class="list"><?php echo $list; ?></ul>
    </div>
  </div>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>
<?php render_site_footer_scripts(); ?>
</body>
</html>
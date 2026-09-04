<?php
/**
 * Generates corpus (new-projects) + rich detail (projects-detail) JSON for the
 * 12 Zoya Ventures development projects, using images downloaded from
 * cdn.behomes.tech (hosted locally at /images/projects/).
 */
error_reporting(E_ALL);

$root = dirname(__DIR__);

function img_cfg(string $path): array {
    // Single downloaded image mapped to the size keys the renderers read.
    return ['340x252' => $path, '464x312' => $path, '696x520' => $path];
}
function media_obj(string $path): array {
    return ['url' => $path, 'hash' => basename($path), 'name' => basename($path)];
}

// slug => [title, developer, district/display_address, price(int), deliveryYear, status, about, features[], imageFile]
$projects = [
 'riviera-azure' => [
   'title' => 'Riviera Azure', 'developer' => 'Azizi', 'display_address' => 'Nad Al Sheba (Meydan One)',
   'price' => 1901000, 'completion_year' => '2025', 'status' => 'ready',
   'building_type' => ['apartment'],
   'about' => 'Striking, exquisite and bespoke, Riviera Azure is a statement of high-end beachfront living. An outstanding crystal lagoon, a vibrant boulevard, and an endless array of recreational activities are just some of the amenities offered to you at Riviera Azure. Designed with solar panels and vertical greenery inspired by the water movement across the lagoon, this architectural masterpiece is the ultimate expression of luxury and comfort.',
   'features' => ['Lounge area for residents','CCTV Security system','Outdoor swimming pool','Restaurant','Gym','Shared Swimming Pool','Barbecue Area','Children\'s Pool','Children\'s Play Area','Public Parks','Near Golf','Space to work','Sports field','Sauna','Near School','Pet Friendly'],
   'img' => 'riviera-azure.jpg',
 ],
 'hayyan' => [
   'title' => 'Hayyan', 'developer' => 'Alef Group', 'display_address' => 'Sharjah',
   'price' => 2719000, 'completion_year' => '2028', 'status' => 'under-construction',
   'building_type' => ['townhouse'],
   'about' => 'Hayyan is a rare find in the UAE where you and your loved ones spend time in a tranquil environment, uniquely designed for an immersive experience of nature, culture and society in natural landscapes. Hayyan provides the most genuine living experience where people connect to nature in a tranquil habitat in Sharjah — featuring the largest swimmable water lagoon in Sharjah, the largest community park, the most dense green project, allotments for organic edible gardens, a club house and a community mall.',
   'features' => ['Lounge area for residents','Indoor swimming pool','CCTV Security system','Outdoor swimming pool','Shared Swimming Pool','Concierge Service','Near School','Public Parks'],
   'img' => 'hayyan.jpg',
 ],
 'riviera-reve' => [
   'title' => 'Riviera Rêve', 'developer' => 'Azizi', 'display_address' => 'Mohammed Bin Rashid City',
   'price' => 2620000, 'completion_year' => '2026', 'status' => 'under-construction',
   'building_type' => ['apartment'],
   'about' => 'Riviera is one of Azizi’s flagship developments located in Meydan at the heart of Mohammed Bin Rashid City (MBR City) in Dubai. In a prime location, close to Meydan Racecourse, the home of The Dubai World Cup and a variety of sports and leisure facilities, Riviera offers a desirable address and is within close proximity to Downtown Dubai and Dubai International Airport. Inspired by the French Riviera, the iconic mixed-use community will be home to residential apartments, retail outlets and a variety of onsite amenities.',
   'features' => ['Indoor swimming pool','CCTV Security system','Lounge area for residents','Restaurant','Shared Swimming Pool','Public Parks','Near School','Concierge Service','Near the beach','Sports field','Space to work','Outdoor swimming pool','Children\'s Play Area'],
   'img' => 'riviera-reve.jpg',
 ],
 'beach-oasis' => [
   'title' => 'Beach Oasis 1 & 2', 'developer' => 'Azizi', 'display_address' => 'Dubai Studio City',
   'price' => 881000, 'completion_year' => '2026', 'status' => 'under-construction',
   'building_type' => ['apartment'],
   'about' => 'Beach Oasis 1 and 2 is a contemporary low-rise residential development offering a range of studio, one-bedroom, and two-bedroom apartments. Situated in the vibrant Studio City, this community is designed to provide an exceptional living experience with excellent facilities and amenities.',
   'features' => ['CCTV Security system','Lounge area for residents','Outdoor swimming pool','Restaurant','Shared Swimming Pool','Concierge Service','Children\'s Play Area','Sports field','Space to work','Gym','Near Golf','Private beach','Barbecue Area','Near Mall','Near Hospital','Public Parks','Children\'s Pool'],
   'img' => 'beach-oasis.jpg',
 ],
 'opus' => [
   'title' => 'Opus', 'developer' => 'Omniyat', 'display_address' => 'Business Bay',
   'price' => 5124299, 'completion_year' => '2023', 'status' => 'ready',
   'building_type' => ['apartment'],
   'about' => 'The Opus is an iconic lifestyle development by Omniyat and designed by world-renowned architect Zaha Hadid, it features a limited number of furnished residences. Comprising of two structures forming a single cube that appears to hover over the ground, the project is also home to ME Hotel by Melia Group of Hotel. Providing an extraordinary degree of personalized and luxurious five-star services for guests and owners alike, the development redefines the art of living in the midst of visual opulence.',
   'features' => ['Private cinema for residents','Lounge area for residents','CCTV Security system','Restaurant','Outdoor swimming pool','Near Golf','Public Parks','Concierge Service','Space to work','Sports field','Shared Swimming Pool','Barbecue Area','Gym','Servicing of apartments','SPA','Valet'],
   'img' => 'opus.jpg',
 ],
 'davinci-tower' => [
   'title' => 'DaVinci Tower', 'developer' => 'Dar Al Arkan', 'display_address' => 'Business Bay',
   'price' => 7144171, 'completion_year' => '2025', 'status' => 'ready',
   'building_type' => ['apartment'],
   'about' => 'Dar Al Arkan presents a new luxury venture DaVinci Tower by Pagani within Dubai that features the state of the art design 2, 3 & 4 bedroom apartments and penthouses. This unique design high-rise façade is designed with the magnetic art elevation that presents an unmatched architecture that is further lined with premium amenities at service. With its strategic location near to the Dubai Canal, the development overlooks the world’s tallest architecture Burj Khalifa. While located in proximity to the major highways, and marks easy navigation to the rest of the city.',
   'features' => ['Outdoor swimming pool','CCTV Security system','Lounge area for residents','Restaurant','Shared Swimming Pool','Concierge Service','Public Parks'],
   'img' => 'davinci-tower.jpg',
 ],
 'ocean-house' => [
   'title' => 'Ocean House', 'developer' => 'Ellington', 'display_address' => 'Palm Jumeirah',
   'price' => 22580828, 'completion_year' => '2026', 'status' => 'under-construction',
   'building_type' => ['apartment'],
   'about' => 'Ocean House by Ellington Properties introduces residents to exclusive Oceanside living, to create a home of unique experiences curated for, and by, residents seeking a lifestyle incomparable to anywhere else. Ocean House features a remarkable collection of beachfront residential apartments on the Palm with views of the Burj Khalifa, Burj Al Arab, Marina Skyline, Palm Jumeirah, and Arabian Gulf. Featuring 2 to 6-bedroom apartments, duplexes, and penthouses with a curated lifestyle and private club amenities — from a 50-metre Olympic swimming pool, a wellness studio, a clubhouse, a fitness studio, a cinema room and a games room.',
   'features' => ['Private cinema for residents','Lounge area for residents','CCTV Security system','Indoor swimming pool','Outdoor swimming pool','Restaurant','Concierge Service','Barbecue Area','Public Parks','Gym','Valet','Sports field','Children\'s Play Area','Space to work','Shared Swimming Pool'],
   'img' => 'ocean-house.jpg',
 ],
 'safa-one-de-grisogono' => [
   'title' => 'Safa One de Grisogono', 'developer' => 'Damac', 'display_address' => 'Al Wasl',
   'price' => 3017000, 'completion_year' => '2026', 'status' => 'under-construction',
   'building_type' => ['apartment'],
   'about' => 'Safa One by de GRISOGONO rises along Dubai’s illustrious Sheikh Zayed Road at the edge of the evergreen Safa Park. Opening onto scenic views of the city and the sea, Safa One is surrounded by Dubai’s most sought-after neighbourhoods such as the iconic Burj area with its world-renowned landmarks, Business Bay and the timeless community of Jumeira. Minutes away from Safa One are the urban leisure avenues of City Walk and Box Park as well as Jumeira Beach.',
   'features' => ['Outdoor swimming pool','Lounge area for residents','CCTV Security system','Restaurant','Observation deck','Sports field','Space to work','Shared Swimming Pool','Barbecue Area','Gym','Children\'s Pool','Public Parks','Children\'s Play Area','Orangery'],
   'img' => 'safa-one-de-grisogono.jpg',
 ],
 'seslia-tower' => [
   'title' => 'Seslia Tower', 'developer' => 'Tiger Properties', 'display_address' => 'Jumeirah Village Triangle',
   'price' => 943065, 'completion_year' => '2025', 'status' => 'ready',
   'building_type' => ['apartment'],
   'about' => 'It’s an Island that provides investment services to individuals who seek to maximize their return on investment. With a vision of industrial style in mind, SESLIA Residential Tower was designed to be as functional as possible whilst showcasing the beauty of raw materials and their contrasts to one another. An industrial style approach is used across all of the tower’s public areas that will instantly immerse its visitors in a creative and modern atmosphere.',
   'features' => ['CCTV Security system','Outdoor swimming pool','Lounge area for residents','Public Parks','Barbecue Area','Shared Swimming Pool','Concierge Service','Gym','Children\'s Play Area','Sports field','Yoga classes','Near School','Near Expo','Near Mall'],
   'img' => 'seslia-tower.jpg',
 ],
 'cavalli-couture' => [
   'title' => 'Cavalli Couture', 'developer' => 'Damac', 'display_address' => 'Al Wasl',
   'price' => 21904000, 'completion_year' => '2025', 'status' => 'ready',
   'building_type' => ['apartment'],
   'about' => 'CAVALLI COUTURE is more than a place to live. It is a way of life, inspired by one of our most majestic natural environments: Amazonia. Rich in greenery and water features, this landmark gives those at the apex of society the opportunity to experience the wonders of the rainforest, right here in Dubai. The lagoons, which grace the podium level, are home to an archipelago of islands, each offering a distinct experience — from a fully-equipped gym, to a luxury spa, to a mouthwatering range of fine dining options. In a Dubai first, the building offers previously unseen panoramas of Safa Park’s trees and flower-lined pathways from one side, while the other presents a sweeping vista of the Dubai canal, business bay crossing and the placid waters of the gulf.',
   'features' => ['Outdoor swimming pool','Indoor swimming pool','Lounge area for residents','CCTV Security system','Restaurant','Private Pool','Concierge Service','Space to work','Sports field','Shared Swimming Pool'],
   'img' => 'cavalli-couture.jpg',
 ],
 'chic-tower' => [
   'title' => 'Chic Tower', 'developer' => 'Damac', 'display_address' => 'Business Bay',
   'price' => 3102000, 'completion_year' => '2026', 'status' => 'under-construction',
   'building_type' => ['apartment'],
   'about' => 'Set to become a stunning adornment of Business Bay, Chic Tower is an upscale 41-storey tower by DAMAC Properties in collaboration with the world-renowned de GRISOGONO jewellery brand. For a distinctive look, the high-end skyscraper will feature a cascade of undulating terraces inspired by the waves of Dubai Water Canal. The terraces will descend from a stepped roof, where water will trickle from one pool to another.',
   'features' => ['Outdoor swimming pool','CCTV Security system','Lounge area for residents','Restaurant','Barbecue Area','Shared Swimming Pool','Concierge Service','Near Mall','Gym','Public Parks','Pet Friendly','Space to work','Sports field','Observation deck','Orangery'],
   'img' => 'chic-tower.jpg',
 ],
 'sobha-one' => [
   'title' => 'Sobha One', 'developer' => 'Sobha', 'display_address' => 'Sobha Hartland',
   'price' => 1832550, 'completion_year' => '2026', 'status' => 'under-construction',
   'building_type' => ['apartment'],
   'about' => 'Let the journey begins at Sobha One, a new development by Sobha Group that features 1 to 4-bedroom waterfront apartments located at Sobha Hartland, Dubai. Discover a life that is both rich enough to appeal to the brilliance of modernity and close enough to nature to be considered interesting and elegant. The development presents a unique design of five interlinked towers, rising from 30 to 65 storeys, where residents are going to experience sky gardens like facilities. The advancement comes with its kind Putt golf course offering 18 holes for its lovers to unwind after a long day at work.',
   'features' => ['Outdoor swimming pool','CCTV Security system','Lounge area for residents','Restaurant','Shared Swimming Pool','Concierge Service','Gym','Children\'s Play Area','Sports field','Near Mall','Public Parks'],
   'img' => 'sobha-one.jpg',
 ],
];

function display_price(int $p): string {
    if ($p >= 1000000) return trim(rtrim(rtrim(sprintf('%.2f', $p / 1000000), '0'), '.') . 'M');
    if ($p >= 1000) return (string) round($p / 1000) . 'K';
    return (string) $p;
}

$BASE = '/images/projects/';
// Developer slugs matching the existing corpus/DB conventions.
$DEV_SLUG = [
    'Azizi' => 'azizi-developments',
    'Alef Group' => 'alef-group',
    'Omniyat' => 'omniyat',
    'Dar Al Arkan' => 'dar-al-arkan',
    'Tiger Properties' => 'tiger-group',
    'Ellington' => 'ellington-properties',
    'Damac' => 'damac-properties',
    'Sobha' => 'sobha-realty',
];
// These 5 already exist on the site under other slugs (original dataset) —
// only the 7 genuinely new projects are added, to avoid duplicates.
$EXISTING = ['ocean-house', 'safa-one-de-grisogono', 'cavalli-couture', 'chic-tower', 'sobha-one'];
$made = [];
foreach ($projects as $slug => $p) {
    if (in_array($slug, $EXISTING, true)) continue;
    $imgPath = $BASE . $p['img'];
    $price = (int) $p['price'];
    $disp = display_price($price);

    // ---------- corpus hit ----------
    $hit = [
        'rank' => null, 'slug' => $slug, 'title' => $p['title'], 'about' => $p['about'],
        'price' => $price, 'display_price' => $disp, 'currency' => 'AED',
        'developer' => $DEV_SLUG[$p['developer']] ?? trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($p['developer'])), '-'),
        'community' => trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($p['display_address'])), '-'),
        'display_address' => $p['display_address'],
        'completion_year' => $p['completion_year'], 'status' => $p['status'],
        'building_type' => $p['building_type'], 'min_bedrooms' => 1, 'max_bedrooms' => 6,
        'display_bedrooms' => null, 'department' => 'new_developments', 'search_type' => 'sales',
        'future_launch' => false, 'agent_category' => 'A', 'publish' => true,
        'images' => [img_cfg($imgPath)],
        'images1' => [img_cfg($imgPath)],
        'images2' => [],
        'banner_image' => $imgPath,
        'features' => array_map(fn($f) => ['name' => $f], $p['features']),
        'amenities' => array_map(fn($f) => ['text' => $f], $p['features']),
        'objectID' => 'zoya-' . $slug,
    ];
    $corpus = [
        'componentChunkName' => 'component---src-pages-new-projects-js',
        'path' => 'new-projects/' . $slug,
        'result' => ['serverData' => ['data' => ['status' => true, 'hits' => [$hit]]]],
    ];

    // ---------- rich detail ----------
    $detail = [
        'id' => 0, 'search_type' => 'sales', 'department' => 'new_developments',
        'status' => $p['status'], 'title' => $p['title'], 'slug' => $slug,
        'display_address' => $p['display_address'], 'building_type' => $p['building_type'],
        'completion_year' => $p['completion_year'], 'developer' => $p['developer'],
        'price' => $price, 'display_price' => $disp, 'currency' => 'AED',
        'lattitude' => null, 'longitude' => null,
        'min_bedrooms' => 1, 'max_bedrooms' => 6, 'display_bedrooms' => null,
        'about' => '<p>' . $p['about'] . '</p>',
        'tile_image' => media_obj($imgPath),
        'banner_image' => media_obj($imgPath),
        'banner_image_mobile' => media_obj($imgPath),
        'media_images' => [media_obj($imgPath)],
        'images' => [img_cfg($imgPath)],
        'floor_plans' => [],
        'payment_plan_text' => 'Flexible payment plans available on request',
        'payment_plans' => [],
        'community' => $p['display_address'], 'publish' => 1,
        'amenities' => array_map(fn($f) => ['text' => $f, 'image' => null], $p['features']),
        'features' => array_map(fn($f) => ['name' => $f], $p['features']),
        'characteristics_module' => null, 'location_tile' => null, 'brochure' => null,
        'video_module' => ['thumbnail' => null, 'video_url' => null],
        'more_info' => [
            ['question' => 'Who is the developer of ' . $p['title'] . '?', 'answer' => '<p>' . $p['developer'] . ' is the developer behind ' . $p['title'] . '.</p>'],
            ['question' => 'Where is ' . $p['title'] . ' located?', 'answer' => '<p>' . $p['title'] . ' is located in ' . $p['display_address'] . ', Dubai.</p>'],
            ['question' => 'How can I invest in ' . $p['title'] . '?', 'answer' => '<p>To invest, explore this project and speak with our specialists at Zoya Ventures Real Estate for expert guidance and support.</p>'],
        ],
        'add_plan' => [], 'country' => 'Dubai', 'future_launch' => false,
        'permit_number' => null, 'documents' => null, 'ads_image' => media_obj($imgPath),
        'ads_mobile_image' => media_obj($imgPath), 'ads_sidebar_image' => null,
        'agent_category' => 'A', 'agent_segmentation' => 'Standard',
        'seo' => ['metaTitle' => $p['title'] . ' | Zoya Ventures Real Estate', 'metaDescription' => substr($p['about'], 0, 150)],
    ];

    $corpusFile = $root . '/data/raw/projects/new-projects/' . $slug . '.json';
    $detailFile = $root . '/data/raw/projects-detail/' . $slug . '.json';
    file_put_contents($corpusFile, json_encode($corpus, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    file_put_contents($detailFile, json_encode($detail, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    $made[] = $slug . ' (price ' . $disp . ')';
}
echo "Generated " . count($made) . " projects:\n" . implode("\n", $made) . "\n";

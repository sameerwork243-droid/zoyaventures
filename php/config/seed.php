<?php
// seed.php — one-time DB seeding (port of src/server/seed.ts ensureSeeded)
// Run via CLI: php -r "require 'config/seed.php'; seed();"
// No-op unless PROVIDENT_DATABASE_URL (mysql://...) is configured.

require_once __DIR__ . '/env.php';
env_load(dirname(__DIR__) . '/.env');          // php/.env (deploy)
env_load(dirname(__DIR__, 2) . '/.env');       // repo root (dev)
require_once __DIR__ . '/../includes/auth.php';

const SEED_DEFAULT_AMENITIES = [
    'Swimming Pool',
    'Gym',
    'Balcony',
    'Maid Room',
    'Study Room',
    "Children's Play Area",
    'BBQ Area',
    'Covered Parking',
    'Security',
    'Concierge',
    'Waterfront',
    'Pet Friendly',
    'Smart Home',
    'Elevator',
    'Central AC',
];

const SEED_DEFAULT_CATEGORIES = [
    ['Apartment', 'apartment'],
    ['Villa', 'villa'],
    ['Townhouse', 'townhouse'],
    ['Penthouse', 'penthouse'],
    ['Mansions', 'mansions'],
    ['Duplex', 'duplex'],
    ['Studio', 'studio'],
    ['Commercial', 'commercial'],
];

const SEED_CONTACT_INFO = [
    ['phone', '+971 50 539 0249'],
    ['email', 'info@providentestate.com'],
    ['whatsapp', 'https://wa.provident.ae/inquire?phone=971505423503'],
    ['address', 'Dubai, United Arab Emirates'],
];

/** seed.ts slugify — replaces & with " and " before slugging. */
function seed_slugify(string $s): string
{
    $s = strtolower($s);
    $s = str_replace('&', ' and ', $s);
    $s = preg_replace('/[^a-z0-9]+/', '-', $s) ?? $s;
    return trim($s, '-');
}

function seed_strip_html(string $s): string
{
    $s = preg_replace('/<[^>]+>/', ' ', (string) $s) ?? (string) $s;
    $s = preg_replace('/\s+/', ' ', $s) ?? $s;
    return trim($s);
}

function seed_to_int(mixed $v): int
{
    $n = (float) $v;
    return is_finite($n) ? (int) round($n) : 0;
}

function seed_to_float(mixed $v): ?float
{
    $n = (float) $v;
    return is_finite($n) ? $n : null;
}

function seed_load_json_file(string $rel): ?array
{
    return load_json($rel);
}

/** List *.json files under APP_RAW_DIR/$dir (empty on failure). */
function seed_list_files(string $dir): array
{
    $path = APP_RAW_DIR . '/' . trim($dir, '/');
    if (!is_dir($path)) return [];
    $out = [];
    foreach (scandir($path) ?: [] as $f) {
        if (str_ends_with($f, '.json')) $out[] = $f;
    }
    sort($out);
    return $out;
}

function seed_count(string $table): int
{
    return (int) (db_row("SELECT COUNT(*) AS n FROM `$table`")['n'] ?? 0);
}

/** Port of src/server/seed.ts ensureSeeded(). */
function ensure_seeded(): void
{
    if (!db_enabled()) return;

    if (seed_count('roles') === 0) {
        db_run("INSERT INTO roles (name) VALUES ('admin'), ('user'), ('agent')");
    }

    if (seed_count('amenities') === 0) {
        foreach (SEED_DEFAULT_AMENITIES as $a) {
            db_run('INSERT INTO amenities (name) VALUES (?)', [$a]);
        }
    }

    if (seed_count('users') === 0) {
        $isProd = env('NODE_ENV') === 'production';
        $adminEmail = strtolower((string) (env('PROVIDENT_ADMIN_EMAIL') ?? ($isProd ? '' : 'sameerwork243@gmail.com')));
        $adminPassword = (string) (env('PROVIDENT_ADMIN_PASSWORD') ?? ($isProd ? '' : 'Sameer@12'));
        if ($adminEmail === '' || $adminPassword === '') {
            throw new RuntimeException(
                '[seed] Cannot create the initial admin user: set PROVIDENT_ADMIN_EMAIL and PROVIDENT_ADMIN_PASSWORD. This only runs against an empty users table.'
            );
        }
        $adminRole = (int) (db_row("SELECT id FROM roles WHERE name = 'admin'")['id'] ?? 1);
        $userRole = (int) (db_row("SELECT id FROM roles WHERE name = 'user'")['id'] ?? 2);
        db_run(
            'INSERT INTO users (email, password_hash, name, role_id, is_active, created_at) VALUES (?, ?, ?, ?, 1, ?)',
            [$adminEmail, hash_password($adminPassword), 'Administrator', $adminRole, db_now()]
        );
        if (!$isProd) {
            db_run(
                'INSERT INTO users (email, password_hash, name, role_id, is_active, created_at) VALUES (?, ?, ?, ?, 1, ?)',
                ['demo@provident.ae', hash_password('Demo@1234'), 'Demo User', $userRole, db_now()]
            );
        }
    }

    if (seed_count('categories') === 0) {
        foreach (SEED_DEFAULT_CATEGORIES as $i => $cat) {
            db_run("INSERT INTO categories (name, slug, type, sort) VALUES (?, ?, 'property', ?)", [$cat[0], $cat[1], $i]);
        }
    }

    if (seed_count('developers') === 0) {
        $devs = seed_load_json_file('developers.json');
        if (is_array($devs)) {
            foreach ($devs as $d) {
                db_run(
                    'INSERT INTO developers (name, slug, region, img, description, published, created_at) VALUES (?, ?, ?, ?, ?, 1, ?)',
                    [
                        (string) ($d['name'] ?? ''),
                        (string) ($d['slug'] ?? seed_slugify((string) ($d['name'] ?? ''))),
                        (string) ($d['region'] ?? ''),
                        (string) ($d['logo'] ?? ''),
                        (string) ($d['description'] ?? ''),
                        db_now(),
                    ]
                );
            }
        }
    }

    if (seed_count('contact_info') === 0) {
        foreach (SEED_CONTACT_INFO as [$k, $v]) {
            db_run('INSERT INTO contact_info (`key`, value) VALUES (?, ?)', [$k, $v]);
        }
    }

    seed_agents();
    seed_jobs();
    seed_projects();
    seed_project_details();
    seed_curated_projects();
    seed_properties();
    seed_developers();
    seed_communities();
}

/** Agents from the scraped team pages (data/raw/pages/team/*.json). */
function seed_agents(): void
{
    if (seed_count('agents') > 0) return;
    foreach (seed_list_files('pages/team') as $f) {
        $j = seed_load_json_file('pages/team/' . $f);
        $t = $j['result']['data']['strapiTeam'] ?? null;
        if (!is_array($t) || empty($t['slug'])) continue;
        try {
            db_run(
                'INSERT IGNORE INTO agents (name, slug, role, phone, email, languages, specialties, img, bio, brn_number, published, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?)',
                [
                    (string) ($t['name'] ?? ''),
                    (string) $t['slug'],
                    (string) ($t['designation'] ?? ''),
                    (string) ($t['phone'] ?? $t['office_phone'] ?? ''),
                    (string) ($t['email'] ?? ''),
                    json_encode($t['languages']['strapi_json_value'] ?? []),
                    json_encode($t['category']['strapi_json_value'] ?? []),
                    (string) ($t['extra']['profile_image'] ?? $t['image']['url'] ?? ''),
                    substr((string) ($t['about']['data']['about'] ?? ''), 0, 4000),
                    (string) ($t['license'] ?? ''),
                    db_now(),
                ]
            );
        } catch (Throwable $e) {
            error_log('[seed] agent skipped: ' . $f . ' ' . $e->getMessage());
        }
    }
    echo "[seed] agents ready\n";
}

/** Jobs from the scraped careers pages (data/raw/pages/careers/*.json). */
function seed_jobs(): void
{
    if (seed_count('jobs') > 0) return;
    foreach (seed_list_files('pages/careers') as $f) {
        $j = seed_load_json_file('pages/careers/' . $f);
        $c = $j['result']['data']['strapiCareer'] ?? null;
        if (!is_array($c) || empty($c['slug'])) continue;
        $details = (string) ($c['job_details']['data']['job_details'] ?? '');
        try {
            db_run(
                'INSERT IGNORE INTO jobs (title, slug, location, summary, job_details, published, created_at) VALUES (?, ?, ?, ?, ?, 1, ?)',
                [
                    trim((string) ($c['title'] ?? '')),
                    (string) $c['slug'],
                    (string) ($c['location'] ?? ''),
                    substr(seed_strip_html($details), 0, 240),
                    $details,
                    db_now(),
                ]
            );
        } catch (Throwable $e) {
            error_log('[seed] job skipped: ' . $f . ' ' . $e->getMessage());
        }
    }
    echo "[seed] jobs ready\n";
}

/** Projects from the scraped new-projects corpus (data/raw/projects/new-projects/*.json). */
function seed_projects(): void
{
    if (seed_count('projects') > 0) return;
    if (seed_count('project_details') > 0) return; // curated-only mode
    foreach (seed_list_files('projects/new-projects') as $f) {
        $j = seed_load_json_file('projects/new-projects/' . $f);
        $h = $j['result']['serverData']['data']['hits'][0] ?? null;
        if (!is_array($h) || empty($h['slug'])) continue;
        $images = [];
        foreach ((array) ($h['images'] ?? []) as $im) {
            $u = (string) ($im['696x520'] ?? $im['464x312'] ?? $im['340x252'] ?? '');
            if ($u !== '') $images[] = $u;
        }
        $banner = (array) (($h['banner_image'] ?? [])[0] ?? []);
        $bannerUrl = (string) ($banner['1650x'] ?? $banner['744x'] ?? $banner['376x'] ?? '');
        $bt = (array) ($h['building_type'] ?? []);
        if (!is_array($h['building_type'] ?? null)) {
            $bt = array_filter([$h['building_type'] ?? null]);
        }
        try {
            db_run(
                "INSERT INTO projects (slug, title, category, status, price, currency, community, developer, building_type, department, bedrooms_min, bedrooms_max, display_address, about, images, amenities, banner_image, completion_year, published, created_at) VALUES (?, ?, 'new-project', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?) ON DUPLICATE KEY UPDATE slug = slug",
                [
                    (string) $h['slug'],
                    (string) ($h['title'] ?? ''),
                    (string) ($h['status'] ?? 'ready'),
                    seed_to_int($h['price'] ?? 0),
                    (string) ($h['currency'] ?? 'AED'),
                    (string) ($h['community'] ?? ''),
                    (string) ($h['developer'] ?? ''),
                    json_encode($bt),
                    (string) ($h['department'] ?? ''),
                    seed_to_int($h['min_bedrooms'] ?? 0),
                    seed_to_int($h['max_bedrooms'] ?? 0),
                    (string) ($h['display_address'] ?? ''),
                    (string) ($h['about'] ?? ''),
                    json_encode($images),
                    json_encode((array) ($h['amenities'] ?? [])),
                    $bannerUrl,
                    $h['completion_year'] !== null ? seed_to_int($h['completion_year']) : null,
                    db_now(),
                ]
            );
        } catch (Throwable $e) {
            error_log('[seed] project skipped: ' . $f . ' ' . $e->getMessage());
        }
    }
    echo "[seed] projects ready\n";
}

/** Rich detail records for curated projects (data/raw/projects-detail/*.json). Insert-only. */
function seed_project_details(): void
{
    $dir = 'projects-detail';
    $files = seed_list_files($dir);
    if (!$files) return;
    $inserted = 0;
    foreach ($files as $f) {
        $j = seed_load_json_file($dir . '/' . $f);
        if (!is_array($j) || empty($j['slug'])) continue;
        $slug = preg_replace('/\.json$/', '', $f);
        if (!$slug) continue;
        try {
            db_run(
                'INSERT IGNORE INTO project_details (slug, data, updated_at) VALUES (?, ?, ?)',
                [$slug, json_encode(array_merge($j, ['slug' => $slug])), db_now()]
            );
            $inserted++;
        } catch (Throwable $e) {
            error_log('[seed] project detail skipped: ' . $f . ' ' . $e->getMessage());
        }
    }
    if ($inserted) echo "[seed] project_details ready ($inserted)\n";
}

/** Keep only curated projects (those with a project_details record); mirror live-site images. Runs once (marker). */
function seed_curated_projects(): void
{
    $marker = db_row("SELECT value FROM page_content WHERE `key` = 'projects_curated'");
    if ($marker) return;
    $details = db_rows('SELECT slug, data FROM project_details');
    if (!$details) return;
    db_run('DELETE FROM projects WHERE slug NOT IN (SELECT slug FROM project_details)');
    $synced = 0;
    foreach ($details as $d) {
        $j = json_decode((string) $d['data'], true);
        if (!is_array($j) || empty($j['slug'])) continue;
        $media = array_values(array_filter(array_map(
            fn($m) => (string) ($m['url'] ?? ''),
            (array) ($j['media_images'] ?? [])
        )));
        $legacy = array_values(array_filter(array_map(
            fn($m) => (string) ($m['url'] ?? ''),
            (array) ($j['images'] ?? [])
        )));
        $images = array_values(array_filter([$j['tile_image']['url'] ?? '', ...$media, ...$legacy]));
        $banner = (string) ($j['banner_image']['url'] ?? $j['ads_image']['url'] ?? $media[0] ?? $legacy[0] ?? '');
        $amenities = array_values(array_filter(array_map(
            fn($a) => (string) ($a['text'] ?? ''),
            (array) ($j['amenities'] ?? [])
        )));
        $buildingTypes = is_array($j['building_type'] ?? null) ? $j['building_type'] : [];
        try {
            db_run(
                "INSERT INTO projects (slug, title, category, status, price, currency, community, developer, building_type, department, bedrooms_min, bedrooms_max, display_address, about, images, amenities, banner_image, completion_year, published, created_at, updated_at) VALUES (?, ?, 'new-project', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?) ON DUPLICATE KEY UPDATE title = VALUES(title), status = VALUES(status), price = VALUES(price), currency = VALUES(currency), developer = VALUES(developer), building_type = VALUES(building_type), bedrooms_min = VALUES(bedrooms_min), bedrooms_max = VALUES(bedrooms_max), display_address = VALUES(display_address), about = VALUES(about), images = VALUES(images), amenities = VALUES(amenities), banner_image = VALUES(banner_image), completion_year = VALUES(completion_year), updated_at = VALUES(updated_at)",
                [
                    (string) $j['slug'],
                    (string) ($j['title'] ?? ''),
                    (string) ($j['status'] ?? 'ready'),
                    seed_to_int($j['price'] ?? 0),
                    (string) ($j['currency'] ?? 'AED'),
                    (string) ($j['community'] ?? ''),
                    (string) ($j['developer'] ?? ''),
                    json_encode($buildingTypes),
                    (string) ($j['department'] ?? ''),
                    seed_to_int($j['min_bedrooms'] ?? 0),
                    seed_to_int($j['max_bedrooms'] ?? 0),
                    (string) ($j['display_address'] ?? ''),
                    (string) ($j['about'] ?? ''),
                    json_encode($images),
                    json_encode($amenities),
                    $banner,
                    $j['completion_year'] !== null ? seed_to_int($j['completion_year']) : null,
                    db_now(),
                    db_now(),
                ]
            );
            $synced++;
        } catch (Throwable $e) {
            error_log('[seed] curated project skipped: ' . $j['slug'] . ' ' . $e->getMessage());
        }
    }
    db_run("INSERT INTO page_content (`key`, value) VALUES ('projects_curated', '1') ON DUPLICATE KEY UPDATE value = '1'");
    echo "[seed] curated projects ready ($synced)\n";
}

/** Properties + media + amenities from the scraped property detail pages (data/raw/properties/{buy,let}/*.json). */
function seed_properties(): void
{
    $marker = db_row("SELECT value FROM page_content WHERE `key` = 'properties_imported'");
    if ($marker) return;

    $agentIdByName = [];
    foreach (db_rows('SELECT id, name FROM agents') as $a) {
        $agentIdByName[strtolower(trim((string) $a['name']))] = (int) $a['id'];
    }

    foreach (['buy' => 'buy', 'let' => 'rent'] as $dirName => $txn) {
        foreach (seed_list_files('properties/' . $dirName) as $f) {
            $j = seed_load_json_file('properties/' . $dirName . '/' . $f);
            $p = $j['result']['serverData']['data']['data'] ?? null;
            if (!is_array($p) || empty($p['slug'])) continue;
            $building = array_values(array_filter((array) ($p['building'] ?? []), fn($b) => $b !== '' && $b !== null));
            $type = strtolower((string) ($building[0] ?? $p['department'] ?? 'apartment'));
            $agentName = strtolower(trim((string) ($p['link_to_employee']['name'] ?? '')));
            $agentId = $agentIdByName[$agentName] ?? null;
            $parkingRaw = (array) ($p['parking'] ?? []);
            $parking = seed_to_int($parkingRaw[0] ?? $p['parking'] ?? 0);
            $areaRaw = $p['floorarea_min'] ?? $p['floorarea_max'] ?? 0;
            try {
                $res = db_run(
                    "INSERT INTO properties (slug, title, category, property_type, transaction_type, status, price, price_qualifier, community, developer, location, latitude, longitude, display_address, bedroom, bathroom, area_sqft, parking, furnished, completion_status, introtext, long_description, featured, published, agent_id, created_at) VALUES (?, ?, ?, ?, ?, 'ready', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1, ?, ?) ON DUPLICATE KEY UPDATE slug = slug",
                    [
                        (string) $p['slug'],
                        (string) ($p['title'] ?? ''),
                        (string) ($building[0] ?? $p['department'] ?? 'Apartment'),
                        $type,
                        $txn,
                        seed_to_int($p['price'] ?? 0),
                        (string) ($p['price_qualifier'] ?? 'AED'),
                        (string) ($p['address']['area'] ?? $p['area'] ?? ''),
                        (string) ($p['developer'] ?? ''),
                        (string) ($p['display_address'] ?? ''),
                        seed_to_float($p['latitude'] ?? null),
                        seed_to_float($p['longitude'] ?? null),
                        (string) ($p['display_address'] ?? ''),
                        seed_to_int($p['bedroom'] ?? 0),
                        seed_to_int($p['bathroom'] ?? 0),
                        seed_to_int($areaRaw),
                        $parking,
                        (string) ($p['extra']['furnishing_type'] ?? $p['extra']['furnished'] ?? ''),
                        (string) ($p['extra']['completion_status'] ?? ''),
                        substr((string) ($p['introtext'] ?? ''), 0, 4000),
                        (string) ($p['long_description'] ?? ''),
                        $agentId,
                        db_now(),
                    ]
                );
                $pid = (int) ($res['lastId'] ?? 0);
                if (!$pid) continue;

                $urls = [];
                foreach ((array) ($p['images'] ?? []) as $im) {
                    $u = (string) ($im['url'] ?? $im['srcUrl'] ?? '');
                    if ($u !== '') $urls[] = $u;
                }
                $seen = [];
                $order = 0;
                foreach ($urls as $url) {
                    if (isset($seen[$url])) continue;
                    $seen[$url] = true;
                    db_run(
                        "INSERT INTO property_media (property_id, kind, url, is_featured, sort_order) VALUES (?, 'image', ?, ?, ?)",
                        [$pid, $url, $order === 0 ? 1 : 0, $order]
                    );
                    $order++;
                }

                foreach ((array) ($p['accommodation_summary'] ?? []) as $a) {
                    $name = trim((string) $a);
                    if ($name === '') continue;
                    db_run('INSERT IGNORE INTO amenities (name) VALUES (?)', [$name]);
                    $am = db_row('SELECT id FROM amenities WHERE name = ?', [$name]);
                    if ($am) db_run('INSERT IGNORE INTO property_amenities (property_id, amenity_id) VALUES (?, ?)', [$pid, (int) $am['id']]);
                }
            } catch (Throwable $e) {
                error_log('[seed] property skipped: ' . $f . ' ' . $e->getMessage());
            }
        }
    }
    db_run("INSERT INTO page_content (`key`, value) VALUES ('properties_imported', '1') ON DUPLICATE KEY UPDATE value = '1'");
    echo "[seed] properties ready\n";
}

/** Developers from the scraped developers.json or, failing that, distinct developers in the projects corpus. */
function seed_developers(): void
{
    if (seed_count('developers') > 0) return;
    $devs = seed_load_json_file('developers.json');
    if (is_array($devs) && $devs) {
        foreach ($devs as $d) {
            db_run(
                'INSERT INTO developers (name, slug, region, img, description, published, created_at) VALUES (?, ?, ?, ?, ?, 1, ?)',
                [
                    (string) ($d['name'] ?? ''),
                    (string) ($d['slug'] ?? seed_slugify((string) ($d['name'] ?? ''))),
                    (string) ($d['region'] ?? ''),
                    (string) ($d['logo'] ?? ''),
                    (string) ($d['description'] ?? ''),
                    db_now(),
                ]
            );
        }
        echo "[seed] developers ready\n";
        return;
    }
    $items = db_rows("SELECT DISTINCT developer FROM projects WHERE developer <> '' ORDER BY developer");
    foreach ($items as $it) {
        $name = trim((string) $it['developer']);
        if ($name === '') continue;
        db_run(
            "INSERT IGNORE INTO developers (name, slug, region, img, description, published, created_at) VALUES (?, ?, '', '', '', 1, ?)",
            [$name, seed_slugify($name), db_now()]
        );
    }
    if ($items) echo "[seed] developers ready (from projects)\n";
}

/** Communities/areas: homepage community list first, then distinct project communities. */
function seed_communities(): void
{
    if (seed_count('communities') > 0) return;
    $seen = [];
    $home = seed_load_json_file('home.json');
    foreach ((array) ($home['communities'] ?? []) as $c) {
        $href = (string) ($c['href'] ?? '');
        $m = null;
        if (preg_match('#in-([^/]+)/?$#', $href, $m)) {
            $slug = $m[1];
        } else {
            $slug = seed_slugify((string) ($c['label'] ?? ''));
        }
        $name = trim((string) ($c['label'] ?? $slug));
        if ($name === '' || isset($seen[$slug])) continue;
        $seen[$slug] = true;
        db_run("INSERT IGNORE INTO communities (name, slug, region, published, created_at) VALUES (?, ?, '', 1, ?)", [$name, $slug, db_now()]);
    }
    $items = db_rows("SELECT DISTINCT community FROM projects WHERE community <> '' ORDER BY community");
    foreach ($items as $it) {
        $name = trim((string) $it['community']);
        if ($name === '') continue;
        $slug = seed_slugify($name);
        if (isset($seen[$slug])) continue;
        $seen[$slug] = true;
        db_run("INSERT IGNORE INTO communities (name, slug, region, published, created_at) VALUES (?, ?, '', 1, ?)", [$name, $slug, db_now()]);
    }
    if ($items || $seen) echo "[seed] communities ready\n";
}

/** Full seed (CLI entry). */
function seed(): void
{
    ensure_seeded();
    echo "[seed] database ready\n";
}
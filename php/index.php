<?php
// index.php — front controller (port of src/app/[[...seg]]/page.tsx)
//
// All non-static requests are rewritten here by .htaccess.
// Route resolution order (mirrors the Next.js catch-all):
//   1. api/*                      → api/**/*.php
//   2. static pages               → pages/*.php
//   3. /sitemap, /book-a-viewing  → special pages
//   4. /new-projects/developed-by-{slug}, /new-projects/type-{type} → project hubs
//   5. data-driven routes (pages/listings/projects/properties) → classify → page template
//   6. /buy|/let aliases → redirect to base listing
//   7. listing fallback via baseListingRel (filter/area routes)
//   8. 404

require_once __DIR__ . '/config/env.php';
env_load(__DIR__ . '/.env');
env_load(__DIR__ . '/../.env'); // dev: repo root

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/store.php';

/* ------------------------------ route parsing ------------------------------ */

$route = $_GET['route'] ?? '';
$route = '/' . ltrim($route, '/');
$route = preg_replace('#/+#', '/', $route) ?: '/';

// normalize: strip trailing slash except root, drop trailing ".html"
if ($route !== '/' && str_ends_with($route, '/')) $route = rtrim($route, '/');
if (str_ends_with($route, '.html')) $route = substr($route, 0, -5);
if ($route === '') $route = '/';

/* ------------------------------ aliases (permanent redirects) ------------------------------ */

$ALIASES = [
    '/buy' => '/buy/properties-for-sale',
    '/let' => '/let/properties-for-rent',
];
if (isset($ALIASES[$route])) {
    header('Location: ' . $ALIASES[$route], true, 308);
    exit;
}

/* ------------------------------ pagination ------------------------------ */

$pageNum = 1;
$routeBase = $route;
if (preg_match('#^(.*?)/page/(\d+)$#', $route, $m)) {
    $routeBase = $m[1];
    $pageNum = (int) $m[2];
}

/* ------------------------------ api routes ------------------------------ */

if (str_starts_with($route, '/api/')) {
    $api = substr($route, 5);
    // /api/admin/{endpoint}/{id} → dispatch to admin/{endpoint}.php with ?id=
    if (preg_match('#^admin/([a-z0-9-]+)/(\d+)$#', $api, $m)) {
        $_GET['id'] = $m[2];
        $api = 'admin/' . $m[1];
    }
    $apiFile = __DIR__ . '/api/' . $api . '.php';
    if (is_file($apiFile)) {
        require $apiFile;
        exit;
    }
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Not found']);
    exit;
}

/* ------------------------------ static pages ------------------------------ */

switch ($route) {
    case '/':
        require __DIR__ . '/pages/home.php';
        exit;
    case '/login':
        require __DIR__ . '/pages/login.php';
        exit;
    case '/register':
        require __DIR__ . '/pages/register.php';
        exit;
    case '/forgot-password':
        require __DIR__ . '/pages/forgot-password.php';
        exit;
    case '/dashboard':
        require __DIR__ . '/pages/dashboard.php';
        exit;
    case '/admin':
        require __DIR__ . '/admin/index.php';
        exit;
    case '/sitemap':
        require __DIR__ . '/pages/sitemap.php';
        exit;
}

/* ------------------------------ project hubs ------------------------------ */

if (preg_match('#^/new-projects/developed-by-([a-z0-9-]+)$#', $routeBase, $m)) {
    $hub = developer_hub_data($m[1]);

    // DB merge (parity with Next page.tsx): replace hub hits with DB projects matching the developer
    $db = db_projects();
    if (count($db)) {
        $extra = array_values(array_filter($db, fn ($h) => db_dev_slug_key($h['developer'] ?? '') === $m[1]));
        if (count($extra)) {
            $hub['hits'] = $extra;
            $hub['nbHits'] = count($extra);
            $hub['nbPages'] = max(1, (int) ceil(count($extra) / ($hub['hitsPerPage'] ?? 20)));
        }
    }

    // Developer rows drive hub existence: when the table has rows and the slug
    // is absent, the hub does not exist (parity with Next's notFound).
    $devRows = db_rows('SELECT * FROM developers ORDER BY name ASC');
    if (count($devRows)) {
        $dev = null;
        foreach ($devRows as $d) {
            if ((string) ($d['slug'] ?? '') === $m[1]) {
                $dev = $d;
                break;
            }
        }
        if (!$dev) {
            http_response_code(404);
            require __DIR__ . '/pages/404.php';
            exit;
        }
        $name = (string) ($dev['name'] ?? '');
        if ($name !== (string) ($hub['hits'][0]['developer'] ?? '')) {
            $hub['hits'] = array_map(fn ($h) => array_merge($h, ['developer' => $name]), $hub['hits']);
        }
    }

    if (!count($hub['hits'])) {
        http_response_code(404);
        require __DIR__ . '/pages/404.php';
        exit;
    }
    $model = ['kind' => 'project', 'data' => $hub, 'route' => $routeBase];
    $isHub = true;
} elseif (preg_match('#^/new-projects/type-([a-z0-9-]+)$#', $routeBase, $m)) {
    $hub = type_hub_data($m[1]);

    // DB merge (parity with Next page.tsx): replace hub hits with DB projects of the type
    $db = db_projects();
    if (count($db)) {
        $extra = array_values(array_filter($db, function ($h) use ($m) {
            $bt = $h['building_type'] ?? [];
            if (!is_array($bt)) $bt = [$bt];
            foreach ($bt as $b) {
                if (db_type_slug_key($b) === $m[1]) return true;
            }
            return false;
        }));
        if (count($extra)) {
            $hub['hits'] = $extra;
            $hub['nbHits'] = count($extra);
            $hub['nbPages'] = max(1, (int) ceil(count($extra) / ($hub['hitsPerPage'] ?? 20)));
        }
    }

    if (!count($hub['hits'])) {
        http_response_code(404);
        require __DIR__ . '/pages/404.php';
        exit;
    }
    $model = ['kind' => 'project', 'data' => $hub, 'route' => $routeBase];
    $isHub = true;
} else {
    $isHub = false;
}

if (!isset($model)) {
    /* --------------------------- data-driven routes --------------------------- */

    $pd = get_page_data($routeBase);
    $model = $pd ? classify($pd, $routeBase) : null;

    // project route with mismatched slug → try project by slug
    if ($model && $model['kind'] === 'project' && preg_match('#^/new-projects/[a-z0-9-]+$#', $routeBase)) {
        $last = (string) end(array_values(array_filter(explode('/', $routeBase))));
        $cur = $model['data']['hits'][0] ?? null;
        if (!$cur || rtrim((string) ($cur['slug'] ?? ''), '.') !== $last) {
            $p = project_by_slug($last);
            if ($p) $model = ['kind' => 'project', 'data' => ['hits' => [$p], 'nbHits' => 1, 'page' => 0, 'nbPages' => 1, 'hitsPerPage' => 1, 'content' => null], 'route' => $routeBase];
        }
    } elseif (!$model && preg_match('#^/new-projects/[a-z0-9-]+$#', $routeBase)) {
        $last = (string) end(array_values(array_filter(explode('/', $routeBase))));
        $p = project_by_slug($last);
        if ($p) $model = ['kind' => 'project', 'data' => ['hits' => [$p], 'nbHits' => 1, 'page' => 0, 'nbPages' => 1, 'hitsPerPage' => 1, 'content' => null], 'route' => $routeBase];
    }

    // DB property detail fallback (dbPropertyByRoute port — parity with Next page.tsx)
    if (!$model && preg_match('#^/(buy|let)/#', $routeBase)) {
        $dbp = db_property_by_route($routeBase);
        if ($dbp) $model = ['kind' => 'property', 'data' => $dbp['data'], 'route' => $routeBase];
    }

    // DB-only fallbacks (no DB in fallback mode — corpus covers content pages)
    if (!$model && preg_match('#^/buy|^/let#', $routeBase)) {
        $base = base_listing_rel($routeBase);
        if ($base) {
            $basePd = get_page_data('/' . $base);
            if ($basePd) $model = classify($basePd, '/' . $base);
        }
    }

    // team/careers/new-projects single routes resolve via data/raw/pages already;
    // when the DB is enabled these are extended in later phases.

    if (!$model) {
        http_response_code(404);
        require __DIR__ . '/pages/404.php';
        exit;
    }

    /* --------------------------- listing merge + filters --------------------------- */

    if ($model['kind'] === 'listing') {
        $kind = str_starts_with($routeBase, '/let') ? 'let' : 'buy';
        $filters = route_filters($routeBase);
        $f = $_GET;
        $n = fn ($v) => ($v !== null && $v !== '' && is_numeric($v)) ? (int) $v : null;
        if ($filters['bedsMin'] === null && $n($f['minBedroom'] ?? null) !== null) $filters['bedsMin'] = $n($f['minBedroom']);
        if ($filters['bedsMax'] === null && $n($f['maxBedroom'] ?? null) !== null) $filters['bedsMax'] = $n($f['maxBedroom']);
        if ($filters['priceMin'] === null && $n($f['minPrice'] ?? null) !== null) $filters['priceMin'] = $n($f['minPrice']);
        if ($filters['priceMax'] === null && $n($f['maxPrice'] ?? null) !== null) $filters['priceMax'] = $n($f['maxPrice']);
        if ($filters['area'] === null && !empty($f['areas'])) {
            $filters['area'] = strtolower(preg_replace('/[^a-z0-9]+/', '-', (string) $f['areas']) ?? '');
        }

        $merged = [];
        foreach (corpus($kind) as $h) {
            if (match_hit($h, $filters)) $merged[] = $h;
        }
        // DB properties (phase 10+) appended here when db_enabled()
        foreach ($model['data']['hits'] ?? [] as $h) {
            if (match_hit($h, $filters)) $merged[] = $h;
        }
        $merged = dedupe_hits($merged);
        $model['data']['hits'] = $merged;
        $model['data']['nbHits'] = count($merged);
        $model['data']['nbPages'] = max(1, (int) ceil(count($merged) / ($model['data']['hitsPerPage'] ?? 20)));
    }

    /* --------------------------- page enrichment (DB) --------------------------- */
    // /about hero override, /careers jobs merge, legal pages: handled in later phases
    // when the DB layer is live. Fallback mode serves the JSON modules as-is.
}

/* --------------------------- render --------------------------- */

$transparent = $route === '/' || (str_starts_with($route, '/new-projects/') && $route !== '/new-projects/');

switch ($model['kind']) {
    case 'listing':
        require __DIR__ . '/pages/listing.php';
        break;
    case 'property':
        require __DIR__ . '/pages/property.php';
        break;
    case 'project':
        require __DIR__ . '/pages/projects.php';
        break;
    case 'page':
    default:
        require __DIR__ . '/pages/content.php';
        break;
}
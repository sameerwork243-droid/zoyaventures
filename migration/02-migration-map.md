# Migration Map — Zoya Ventures Real Estate → PHP 8 + MySQL + Apache

Phase 2 deliverable. Maps every original artifact to its PHP equivalent. Target docroot layout for shared hosting (WPArena): `public_html/`.

---

## 1. Target Directory Layout (PHP app root = `php/` dev mirror of `public_html/`)

```
public_html/
├── index.php                  # catch-all router (front controller)
├── .htaccess                  # clean URLs → index.php?route=...
├── config/
│   ├── config.php             # site constants, paths, upload limits
│   ├── database.php           # PDO MySQL singleton
│   └── env.php                # .env loader (PROVIDENT_DATABASE_URL etc.)
├── includes/
│   ├── functions.php          # helpers: esc, fmt_price, wa_link, cdn_url, etc.
│   ├── auth.php               # sessions, scrypt→password_hash, login/register/logout
│   ├── header.php             # <head> + header component
│   ├── footer.php             # footer + scripts
│   ├── navbar.php             # nav menus (from MENUS constant)
│   ├── db-queries.php         # ported crud/bridge query functions
│   └── render/
│       ├── module-renderer.php    # ModuleRenderer (modules.tsx port)
│       ├── listing.php            # listing page render
│       ├── property-detail.php    # property detail render
│       ├── property-card.php      # card render
│       ├── content-page.php       # generic content page render
│       └── pagination.php
├── pages/
│   ├── home.php
│   ├── listing.php            # /buy/... /let/...
│   ├── property.php           # /property/{slug}/
│   ├── projects.php           # /new-projects/{slug}/
│   ├── content.php            # all other content pages (blog, team, area-guides...)
│   ├── list-your-property.php
│   ├── contact.php
│   ├── login.php / register.php / forgot-password.php
│   ├── dashboard.php          # portal shell + dashboard-app port
│   └── 404.php
├── api/                       # JSON endpoints (mirror /api/*)
│   ├── auth/login.php, register.php, logout.php, me.php, change-password.php
│   ├── inquiries.php          # POST only (list-property/contact/enquiry/viewing)
│   ├── user/account.php, saved.php, viewings.php, inquiries.php,
│   │      notifications.php, password.php, profile.php
│   ├── admin/stats.php, upload.php, properties.php, properties.php?id=,
│   │      users.php, users.php?id=, inquiries.php, viewings.php,
│   │      content.php (about/contact/homepage/amenities/categories/project-details),
│   │      resource.php (generic CRUD: ?resource=services|agents|...)
├── admin/                      # admin SPA (admin-app.tsx port)
│   ├── index.php               # admin shell (serves app.js + app.css)
│   └── assets/js/admin-app.js, admin-styles.css
├── portal/
│   └── assets/js/portal.js     # portal shell + dashboard-app port
├── assets/
│   ├── css/  provident.css, header-styles.css, developer-styles.css,
│   │          app-styles.css, portal.css, app-shell.css   (copied verbatim)
│   ├── js/   main.js (vanilla ports of gsap/framer-motion effects), slick.js,
│   │          quiz.js, forms.js, gallery.js, filters.js, admin-app.js
│   └── images/ logo.png, lloo.png, favicon-32x32.png, icons/icon-512x512.png,
│              video-thumbnail.webp, property-placeholder.svg, banners/*,
│              login/login-bg.jpg, trustpilot/*, signature-badge.svg, more-box.svg
├── static/                     # hashed font/assets (keep exact filenames)
│   ├── PlusJakartaSans-4e71a2a40bfc8bd5b5660abd58f37aa6.woff2
│   ├── LoraBold-b58b749a7082c19e8aae0d73b3fec68c.woff2
│   ├── loader-90672f1deffccbcdf4f1be54ee7cd7ea.gif
│   ├── bg_bro-*.png, bg_bro1-*.png, bg_stats-*.jpg,
│   ├── family-community-*.svg, Signature-*.svg
├── media/hero.mp4
├── data/                        # JSON snapshot fallback (copied from repo)
│   └── raw/... (pages, listings, projects, properties, developers.json)
├── uploads/                     # admin media uploads (replaces @vercel/blob)
│   ├── images/…
├── .env                        # PROVIDENT_DATABASE_URL (mysql://), ADMIN_EMAIL/PASSWORD
└── schema.sql                  # full MySQL schema + seed
```

## 2. Route Map (Next.js → PHP)

| Next.js route | PHP target | Data source |
|---|---|---|
| `/` | `pages/home.php` | `src/data/home.json` (→ DB `homepage_content` or JSON) |
| `/buy/{type}/`, `/let/{type}/`, `/buy/properties-for-sale/in-{area}/`, `/buy/properties-for-sale/above-20000000/`, `/let/properties-for-rent/in-downtown-dubai/` | `pages/listing.php` (slug dispatch) | `data/raw/listings/{...}.json` |
| `/property/{slug}/` | `pages/property.php` | `data/raw/properties/{buy|let|new-projects}/{slug}.json` |
| `/new-projects/{slug}/` | `pages/projects.php` | `data/raw/projects/new-projects/{slug}.json` |
| `/developers/` | `pages/content.php` | `data/raw/developers.json` |
| `/about/`, `/blog/`, `/blog/{slug}/`, `/area-guides/`, `/area-guides/{slug}/`, `/team/`, `/team/{slug}/`, `/careers/`, `/contact/`, `/sell/`, `/property-services/`, `/roadshow/`, `/off-plan/`, legal pages | `pages/content.php` (module renderer) | `data/raw/pages/{dir}/{slug}.json` |
| `/list-your-property/` | `pages/list-your-property.php` | static + forms.js |
| `/login`, `/register`, `/forgot-password` | `pages/login.php` etc. | DB auth |
| `/dashboard` | `pages/dashboard.php` | DB + portal.js |
| `/admin` | `admin/index.php` | DB + admin-app.js |
| `/api/auth/*`, `/api/user/*`, `/api/admin/*`, `/api/inquiries` | `api/**/*.php` | DB |

`.htaccess`: `RewriteRule ^(.*)$ index.php?route=$1 [L,QSA]`; static files served directly; `index.php` resolves route → page include.

## 3. Component Map

| Next.js component | PHP equivalent |
|---|---|
| `layout.tsx` (metadata, GTM-PGNHTGZ5) | `includes/header.php` head block (GTM snippet kept verbatim) |
| `header.tsx` | `includes/navbar.php` + JS (mobile menu port) |
| `footer.tsx` | `includes/footer.php` (5 columns, newsletter form → api/inquiries.php, © 2024, Starberry) |
| `home.tsx` (HeroPage/Rich) | `includes/render/home-hero.php` + home.php |
| `modules.tsx` ModuleRenderer | `includes/render/module-renderer.php` (render functions per module type; block switch mirroring component switch) |
| `content-pages.tsx` | `pages/content.php` + module-renderer |
| `listing.tsx`, `listing-ui.tsx`, `filtering.ts` | `pages/listing.php` + `includes/render/listing.php` + `assets/js/filters.js` (PER_PAGE=9, qtype/area/beds/price/sort) |
| `property-card.tsx` | `includes/render/property-card.php` |
| `property-detail.tsx`, `property-gallery.tsx` | `pages/property.php` + gallery.js (pe-gallery grid, zoom) |
| `projects.tsx` | `pages/projects.php` |
| `search-hero.tsx` | `includes/render/search-hero.php` + search.js |
| `dream-home-quiz.tsx` | `assets/js/quiz.js` + section in content pages |
| `slick.tsx` | slick.js (CDN/vanilla) + slick.css already in provident.css |
| `newsletter.tsx` | footer.php form + api/inquiries.php |
| `save-button.tsx` | `assets/js/save-button.js` + api/user/saved.php |
| Forms (list-property, contact, property-enquiry, book-viewing) | `assets/js/forms.js` + `api/inquiries.php` |
| `portal/portal-shell.tsx` | `portal/assets/js/portal.js` (25 SVGs inline) + pages/dashboard.php |
| `dashboard/dashboard-app.tsx` | `portal/assets/js/dashboard-app.js` (fetches api/user/*) |
| `admin/admin-app.tsx` (89KB) | `admin/assets/js/admin-app.js` (fetches api/admin/*) |
| `auth/*` forms | `assets/js/auth.js` + api/auth/*.php |

## 4. API Map

| Node endpoint | PHP endpoint | Notes |
|---|---|---|
| `/api/auth/login` | `api/auth/login.php` | session cookie `provident_session`; password_verify |
| `/api/auth/register` | `api/auth/register.php` | validate email, role=user |
| `/api/auth/logout` | `api/auth/logout.php` | destroy session |
| `/api/auth/me` | `api/auth/me.php` | GET current user |
| `/api/auth/change-password` | `api/auth/change-password.php` | |
| `/api/inquiries` | `api/inquiries.php` | POST; type= list-property|contact|property-enquiry|book-viewing; same field names |
| `/api/user/account` | `api/user/account.php` | GET/PATCH |
| `/api/user/saved` | `api/user/saved.php` | GET/POST/DELETE |
| `/api/user/viewings` | `api/user/viewings.php` | GET/POST |
| `/api/user/inquiries` | `api/user/inquiries.php` | GET |
| `/api/user/notifications` | `api/user/notifications.php` | GET/PATCH |
| `/api/user/password` | `api/user/password.php` | POST |
| `/api/user/profile` | `api/user/profile.php` | GET/PATCH |
| `/api/admin/stats` | `api/admin/stats.php` | counts |
| `/api/admin/upload` | `api/admin/upload.php` | store in uploads/, return URL |
| `/api/admin/properties(+id)` | `api/admin/properties.php` | ?id=; GET/POST/PATCH/DELETE |
| `/api/admin/users(+id)` | `api/admin/users.php` | ?id= |
| `/api/admin/inquiries` | `api/admin/inquiries.php` | GET/PATCH/DELETE |
| `/api/admin/viewings` | `api/admin/viewings.php` | GET/PATCH/DELETE |
| `/api/admin/about|contact|homepage|amenities|categories|project-details` | `api/admin/content.php` | ?key= ; JSON get/put |
| `/api/admin/[resource](+/[id])` | `api/admin/resource.php` | ?resource=&id= |

## 5. DB Map (Node → PHP/MySQL)

| Node (ts) | PHP |
|---|---|
| `server/db.ts` (mysql2/pg dual driver, dbEnabled fallback) | `config/database.php` (PDO MySQL) + JSON fallback flag; keep `dbEnabled()` semantics |
| `server/auth-core.ts`, `session.ts` | `includes/auth.php` (password_hash/verify, PHP sessions + DB session tokens, TTL 7d/30d) |
| `server/seed.ts` (DEFAULT_AMENITIES, admin seed) | `schema.sql` INSERTs + `config/seed.php` on first run |
| `server/crud.ts` | `includes/db-queries.php` (prepared statements, list pagination, validation) |
| `server/property-bridge.ts` (DbHit, DEPARTMENT map, DEFAULT_NEGOTIATOR) | `includes/db-queries.php` + property render mapping |
| `server/content-bridge.ts` (page_content, team, jobs, projects, developers, communities, stats) | `includes/db-queries.php` content functions |
| `server/admin-resources.ts` | `api/admin/resource.php` config array |
| `lib/ref.ts` (getPageData/classify) | `includes/functions.php` route resolver (read data/raw JSON, classify page type) |
| `lib/store.ts` (loadRel/getListing/getProperty) | `includes/functions.php` JSON loaders (cached) |
| `lib/filtering.ts` | `includes/functions.php` filter/price-band logic + SQL WHERE builder |
| `lib/image.ts`, `url.ts` (CDN cft/cfw) | `includes/functions.php` cdn_url() with same `/x/{w}x{h}/` transform strings |
| `lib/utils.ts` (fmtPrice, waLink, mailto, esc, faqSchema) | `includes/functions.php` |
| `lib/site.ts` | `config/config.php` constants |
| `lib/data.ts`, `lib/types.ts`, `lib/legal-content.ts` | `config/config.php` arrays / `pages/legal-content.php` |

## 6. Asset Map

| Node asset | PHP target |
|---|---|
| `public/media/hero.mp4` | `media/hero.mp4` |
| `public/images/*`, `public/icons/*`, `public/login/*` | `assets/images/*`, `assets/icons/*`, `assets/login/*` (or `assets/images/login/login-bg.jpg`) |
| `public/static/*` (hashed) | `static/*` — **keep exact filenames** (referenced by provident.css @font-face and JS) |
| `src/data/home.json`, `data/raw/**` | `data/` (fallback; optionally seeded into DB) |
| `src/app/*.css` ×6 | `assets/css/` verbatim |
| Google Fonts (if any in layout) | keep Google Fonts link or local woff2 (local preferred for performance) |
| @vercel/blob uploads | `uploads/` + api/admin/upload.php |

## 7. Interactivity Map (JS ports — no frameworks on target)

| Effect | Node source | PHP/JS port |
|---|---|---|
| Sticky header + mobile menu | header.tsx + CSS | `assets/js/main.js` (vanilla) |
| Hero video autoplay + poster | home.tsx | HTML5 video attrs |
| Sliders/carousels (slick) | slick.tsx + slick.css | slick.js (vanilla or CDN slick 1.8.1) |
| gsap animations | gsap 3.15.0 | `assets/js/animations.js` (IntersectionObserver + CSS transitions; preserve easing/timing) |
| framer-motion page transitions | framer-motion 12.43.0 | CSS transitions/animations in main.js |
| Gallery zoom/lightbox | property-gallery.tsx | `assets/js/gallery.js` |
| Filter bar (listing) | listing-ui.tsx + filtering.ts | `assets/js/filters.js` (AJAX to listing.php?ajax=1 or server-side re-render) |
| Dream home quiz | dream-home-quiz.tsx | `assets/js/quiz.js` |
| Forms validation | client validation in forms | `assets/js/forms.js` (same rules/messages) |
| Save property | save-button.tsx | `assets/js/save-button.js` |
| Counters/stats | admin-app/dashboard | `assets/js/counters.js` |
| Toast notifications | (existing) | `assets/js/toast.js` |

## 8. Sequence of Work (phases 4–21 mapped to files)

1. **Phase 4 – Core infra**: `config/` + `includes/functions.php` + `config/database.php` + `schema.sql` + JSON fallback loader.
2. **Phase 5 – UI core**: copy CSS ×6 → assets/css; copy static fonts; header/footer/navbar; home.php.
3. **Phase 6 – Listing**: listing.php + property-card + filters + pagination (PER_PAGE 9) + `buy/let` routes incl. area/price sub-listings.
4. **Phase 7 – Property detail**: property.php + gallery + related + enquiry forms.
5. **Phase 8 – Portal**: dashboard.php + portal.js (25 SVGs) + api/user/*.
6. **Phase 9 – Auth**: login/register/forgot-password + api/auth/* + auth.php.
7. **Phase 10 – Admin**: admin/index.php + admin-app.js + api/admin/* + uploads.
8. **Phase 11 – Forms**: list-your-property.php + forms.js + inquiries.php.
9. **Phase 12 – Global JS**: animations.js, gallery.js, filters.js, quiz.js, slick.
10. **Phase 13 – Data seeding**: schema.sql seed + import script (data/raw → MySQL optional).
11. **Phase 14 – Routing**: .htaccess + index.php front controller + 404.
12. **Phase 15 – Performance**: caching (page cache files), CDN passthrough, asset minification.
13. **Phase 16 – Email**: mailer (SMTP or mail()); From: zoyaventure15@gmail.com; inquiry notifications.
14. **Phase 17 – Analytics**: GTM-PGNHTGZ5 in header.php.
15. **Phase 18 – Security**: PDO prepared statements, CSRF tokens on forms, session hardening, file upload validation, .htaccess protections (no listing, no .env access).
16. **Phase 19 – Hosting**: upload to WPArena public_html, configure .env, test with real domain.
17. **Phase 20 – Testing**: checklist per page/form/API (inventory §2–§6).
18. **Phase 21 – Final QA**: compare vs provident.ae and detail.html/list.html snapshots; final report.

## 9. Risks & Decisions to Confirm
- **Data source**: use `data/raw` JSON fallback as primary content source (fastest, zero-risk fidelity), DB for dynamic data (users, inquiries, saved, admin CRUD). Confirm with client.
- **Password compatibility**: existing users' scrypt hashes (`salt:hash` hex) cannot be verified by PHP `password_verify` → either (a) keep scrypt verifier port in PHP, (b) reset passwords on migration, or (c) one-time login → rehash. Needs decision.
- **Postgres → MySQL**: if DB is used for content, need full export/import (structure + data) from Neon to MySQL.
- **Sessions**: switch from DB-token sessions to PHP sessions + DB sync (or keep DB-token approach for shared hosting resilience).
- **Emails**: confirm SMTP availability on WPArena (or use PHP mail()).
- **Admin SPA**: 89KB admin-app.tsx port is the single largest JS artifact — port to vanilla JS modules or a single admin-app.js (no build step allowed).
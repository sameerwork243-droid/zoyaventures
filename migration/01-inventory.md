# Migration Inventory — Zoya Ventures Real Estate (provident-next)

Phase 1 deliverable. Complete audit of the existing Next.js application to be migrated to PHP 8.x + MySQL + Apache (shared hosting).

Audit date: 2026-08-17. Project root: `D:\hifi_marketing\New folder\zoyaventures`.

---

## 1. Overview

| Item | Value |
|---|---|
| Domain | provident.ae |
| Site name | Zoya Ventures Real Estate |
| Original stack | Next.js 16.3.0, React 19.2.8, TypeScript, Tailwind 4, framer-motion 12.43.0, gsap 3.15.0, mysql2 3.12.0, pg 8.23.0, @vercel/blob 2.7.0 |
| Target stack | PHP 8.x (PDO MySQL), Apache, `.htaccess`, shared hosting (WPArena), no Node runtime |
| Env keys | `PROVIDENT_DATABASE_URL` (mysql:// or postgres://), `PROVIDENT_ADMIN_EMAIL`, `PROVIDENT_ADMIN_PASSWORD`, `BLOB_READ_WRITE_TOKEN` (legacy, drop) |
| DB fallback | `dbEnabled()` false → JSON fallback from `data/raw` (must be replicated as PHP fallback mode) |

## 2. Routing / Page Inventory

### 2.1 Catch-all route
- `src/app/[[...seg]]/page.tsx` — single catch-all serving every content route from `data/raw` JSON via `lib/ref.ts` (`getPageData`, `classify`).
- Path handling: trailing slash normalization, `index` segments, `.html` suffix, prefix stripping (e.g. `/buy/properties-for-sale/` → listing data). 404 page for unknown routes.

### 2.2 Listing routes (38 JSON files, `data/raw/listings/`)
Buy (`/buy/`): `apartment-for-sale`, `commercial-properties-for-sale`, `duplex-for-sale`, `penthouse-for-sale`, `plots-for-sale`, `properties-for-sale` (base), `short-term-for-sale`, `townhouse-for-sale`, `villa-for-sale`, `whole-building-for-sale`.
Rent (`/let/`): `apartment-for-rent`, `commercial-properties-for-rent`, `duplex-for-rent`, `penthouse-for-rent`, `properties-for-rent` (base), `short-term-for-rent`, `townhouse-for-rent`, `villa-for-rent`, `whole-building-for-rent`.
Area/sub-filter listings (buy 18 + let 1):
- `/buy/properties-for-sale/in-{area}` × 17: al-habtoor-city-business-bay, al-habtoor-polo-resort-club-the-residences, damac-hills, damac-lagoons, downtown-dubai, dubai-creek-harbour-the-lagoons, dubai-hills-estate, dubai-marina, emaar-beachfront-dubai-harbour, jumeirah-beach-residence, jumeirah-golf-estates, jumeirah-village-circle, jumeirah-village-triangle, jumeirah, palm-jumeirah, sobha-hartland-mohammed-bin-rashid-city, the-springs
- `/buy/properties-for-sale/above-20000000`
- `/let/properties-for-rent/in-downtown-dubai`

### 2.3 Content pages (`data/raw/pages/`, 1333 files)
- `about/` 5, `area-guides/` 241, `blog/` 584, `careers/` 10, `contact/` 12, `off-plan/` 2, `property-services/` 15, `roadshow/` 10, `sell/` 3, `team/` 210.
- Static top-level pages (from `src/app`): `/`, `/login`, `/register`, `/forgot-password`, `/dashboard`, `/admin`, plus `[[...seg]]` catch-all.
- JSON shape: `result.serverData.data` (Strapi-style block data). Page types handled by `content-pages.tsx` + `modules.tsx` (ModuleRenderer).

### 2.4 Project & property data
- `data/raw/projects/new-projects/*.json` — 557 project hub files (`/new-projects/{slug}/`).
- `data/raw/projects-detail/*.json` — 10 rich project detail snapshots.
- `data/raw/properties/` — 1448 property snapshots: `buy/` 12, `let/` 16, `new-projects/` 1420 (property detail routes, e.g. `/property/{slug}/`; includes `.html`-suffixed variants).
- `data/raw/developers.json` — developers listing data (`/developers/`).

### 2.5 Homepage
- `src/data/home.json` keys: `featuredSliders`, `developers`, `featuredNews`, `reviews`, `communities`, `areas`. Rendered by `home.tsx` (HeroPage with hero video, Rich sections, ModuleRenderer).

## 3. Component Inventory (`src/components/`)

| Component | Purpose | Notes |
|---|---|---|
| `header.tsx` | Site header | MENUS array with CDN icons `/x/16x16/`, sticky, mobile menu, top bar |
| `footer.tsx` | Site footer | 5 columns (buy/sell/Off plan/rent/services), newsletter, © 2024, "Site by Starberry" |
| `home.tsx` | Homepage | HeroPage, Rich, featuredSliders, developers, featuredNews, reviews, communities, areas |
| `listing.tsx` / `listing-ui.tsx` | Listing page + filter UI | PER_PAGE=9, FilterState {qtype, area, beds, price, sort}, TYPE_MAP |
| `property-card.tsx` | Property card | CDN images, price fmt, badges |
| `property-detail.tsx` | Property detail page | Hero gallery, amenities, agents (DEFAULT_NEGOTIATOR), enquiry forms, related |
| `property-gallery.tsx` | Gallery | pe-gallery grid, zoom, thumbnails |
| `modules.tsx` (65KB) | ModuleRenderer | Renders Strapi content modules (hero, rich, sliders, banners, faqs, etc.) |
| `content-pages.tsx` (40KB) | Generic content page renderer | modules + SEO |
| `projects.tsx` | Project hub pages | `/new-projects/{slug}/` |
| `search-hero.tsx` | Search hero | buy/rent toggle + search inputs |
| `dream-home-quiz.tsx` | Quiz module | multi-step quiz |
| `slick.tsx` | Slick carousel wrapper | slick.css embedded in provident.css |
| `newsletter.tsx` | Newsletter form | |
| `save-button.tsx` | Saved property button | requires auth |
| `logo-data.ts` | Logo SVGs | |
| `property-enquiry-form.tsx`, `contact-enquiry-form.tsx`, `book-viewing-form.tsx` | Forms → `/api/inquiries` | |
| `list-property-form.tsx` | List Your Property form | see Forms |
| `portal/portal-shell.tsx` | Portal shell | 25 inline SVGs (launch, home, grid, heart, chat, calendar, bell, person, settings, logout, search, bookmark, building, tag, briefcase, users, star, question, image, phone, map, menu, expand-more, more, eye, eye-off) |
| `admin/admin-app.tsx` (89KB) | Admin SPA | Dashboard/stats, Properties, Inquiries, Viewings, Users, Services, Agents, Developers, Communities, Testimonials, FAQs, Media library, Jobs, Projects, page_content/contact_info/homepage_content editors, Amenities, Categories, Project details |
| `dashboard/dashboard-app.tsx` (33KB) | User dashboard | Saved, inquiries, viewings, notifications, account settings |
| `auth/*`, `account/*` | Auth + account forms | login/register/forgot-password, account forms |

## 4. API Endpoint Inventory (`src/app/api/`, 23 routes)

### Auth (`/api/auth/*`)
- `login` (POST), `register` (POST), `logout` (POST), `me` (GET), `change-password` (POST). Auth: cookie `provident_session`, httpOnly, sameSite=lax, secure on HTTPS; scrypt `salt:hash` hex; SHA-256 token hash stored in `sessions`; TTL 7d, remember 30d. Roles: `user`, `agent`, `admin`.

### User (`/api/user/*`)
- `account` (GET/PATCH), `saved` (GET/POST/DELETE), `viewings` (GET/POST), `inquiries` (GET), `notifications` (GET/PATCH), `password` (POST), `profile` (GET/PATCH).

### Admin (`/api/admin/*`)
- `properties` (GET/POST), `properties/[id]` (GET/PATCH/DELETE), `users` (GET/POST), `users/[id]` (GET/PATCH/DELETE), `stats` (GET), `upload` (POST), `inquiries` (GET/PATCH/DELETE), `viewings` (GET/PATCH/DELETE), `about`, `contact`, `homepage`, `amenities`, `categories`, `project-details` (key-value editors), `[resource]` + `[resource]/[id]` (generic CRUD: services, agents, developers, communities, testimonials, faqs, media_library, jobs, projects, team).

### Public
- `/api/inquiries` (POST) — List Your Property / contact / property enquiry / book viewing forms.

## 5. Database Schema (MySQL target)

`users` (id, name, email, phone, password salt+hash, role enum user/agent/admin, created_at, updated_at), `sessions` (id, user_id, token_hash, expires_at, created_at), `roles`, `properties`, `property_media`, `property_amenities`, `agents`, `services`, `developers`, `communities`, `testimonials`, `faqs`, `media_library`, `saved_properties` (user_id, property_id), `inquiries` (form type: list-property/contact/property-enquiry/book-viewing), `viewings`, `notifications`, `user_addresses`, `notification_preferences`, `password_updates`, `account_deletion_logs`, `page_content` (slug, content JSON), `contact_info` (key/value), `homepage_content` (key/value), `jobs`, `projects`, `project_details`.

Key port notes:
- `src/server/db.ts`: `dbEnabled()` + dual-driver (mysql2/pg). PHP: PDO MySQL only; keep JSON fallback mode reading `data/raw`.
- `src/server/seed.ts`: DEFAULT_AMENITIES list, seed user (from `PROVIDENT_ADMIN_EMAIL`/`PROVIDENT_ADMIN_PASSWORD`), content seeding.
- `src/server/crud.ts`: generic list/create/get/update/delete with validation + pagination.
- `src/server/property-bridge.ts`: DbHit shape, DEPARTMENT map, DEFAULT_NEGOTIATOR.
- `src/server/content-bridge.ts`: page_content, team, jobs, projects, developers, communities, site stats.
- `src/server/admin-resources.ts`: CRUD configs (per-resource fields/validation).

## 6. Forms (user-facing)

### List Your Property (`/list-your-property/`, `list-property-form.tsx`)
Fields (exact order): Full Name; "I want to" (Sale/Rent radio); Phone Number + country dial select (aria-label "Country code"); Property Type (default Apartment); Email Address; Community / Area; Preferred Language; Bedrooms; Bathrooms; Size; Expected Price; Ownership Status; Property Address; Additional Details; consent checkbox. Client validation → POST `/api/inquiries` (type `list-property`). Wrapper classes: `form.custom-form`, `.form-grid`, `.input-box`, `.input-field`, `.phone-field-row`, `.form-bottom`.

### Other forms
- Contact (`contact-enquiry-form.tsx`), Property enquiry (`property-enquiry-form.tsx`), Book viewing (`book-viewing-form.tsx`), Newsletter (`newsletter.tsx`), Login/Register/Forgot password, Search hero, Dream home quiz.

## 7. Data Layer & Utilities (`src/lib/`)

- `store.ts` — loadRel/getListing/getProperty; RAW = `data/raw`.
- `ref.ts` — getPageData/classify (route resolution).
- `filtering.ts` — PER_PAGE=9, FilterState {qtype, area, beds, price, sort}, TYPE_MAP, price bands (e.g. above-20000000).
- `image.ts` — CDN helpers `cft()/cfw()`: `https://d3h330vgpwpjr8.cloudfront.net/x/{w}x{h}/...` and `/x/{w}/...`; local override list (2 assets: careers-banner, about-video).
- `site.ts` — constants: name, domain provident.ae, phone +971 568 308 221, email zoyaventure15@gmail.com, WhatsApp 971568308221, socials [instagram, facebook, linkedin, x, youtube]; brand colors #EE7133 / #07234B.
- `url.ts` — toCloudFrontUrl.
- `utils.ts` — fmtPrice, waLink, mailto, esc, faqSchema.
- `data.ts`, `types.ts`, `props.tsx`, `legal-content.ts` (legal pages content).

Alt-text convention: `"{label} - Zoya Ventures Real Estate"`; lazy loading except hero. Property images via CDN; `media/hero.mp4` hero video.

## 8. Assets (`public/`)

- `media/hero.mp4` (hero video), `images/logo.png`, `images/lloo.png`, `favicon-32x32.png`, `icons/icon-512x512.png`, `images/video-thumbnail.webp`, `images/property-placeholder.svg`, `login/login-bg.jpg`, `images/banners/careers-banner.webp`, trustpilot/google stars SVGs, `data/signature-badge.svg`, `data/more-box.svg`.
- `static/` (hashed, must keep names): `PlusJakartaSans-4e71a2a40bfc8bd5b5660abd58f37aa6.woff2`, `LoraBold-b58b749a7082c19e8aae0d73b3fec68c.woff2`, `loader-90672f1deffccbcdf4f1be54ee7cd7ea.gif`, `bg_bro-*.png` ×2, `bg_stats-*.jpg`, `family-community-*.svg`, `Signature-*.svg`.
- Fonts: `@font-face` in provident.css → `/static/PlusJakartaSans-*.woff2` (Plus Jakarta Sans), `/static/LoraBold-*.woff2` (Lora).

## 9. Stylesheets

| File | Size | Role |
|---|---|---|
| `src/app/provident.css` | ~1,021,308 bytes (minified, single-line) | Primary stylesheet: destyle.css reset, Bootstrap vars, slick, swiper icons, fonts, `.icon` background-icon system, header, footer, listing, property detail, forms, portal, admin, responsive |
| `header-styles.css` | — | Header-specific |
| `developer-styles.css` | — | Developers pages |
| `app-styles.css` | — | App shell |
| `portal.css` | — | Portal shell |
| `app-shell.css` | — | Dashboard/admin shell |

CSS is plain CSS (no CSS modules) — copy verbatim to `assets/css/`. Icons are CSS background-images (sprite/data-URI via `.icon` class), not inline SVG.

## 10. Third-Party / Analytics
- GTM: `GTM-PGNHTGZ5` (in `layout.tsx`). Keep same GTM container ID in PHP.
- Slick slider (slick.css embedded in provident.css), swiper icons, gsap + framer-motion (must be ported to vanilla JS/animations).

## 11. Reference Files
- `detail.html` (93KB) and `list.html` (682KB) — pre-rendered property detail and listing page snapshots for fidelity QA.
- `data/raw/html/` — page snapshots.
- `.env.local` — production Vercel env (Postgres Neon + BLOB token). Migration note: real credentials live here; the MySQL local fallback line is commented (`mysql://root@127.0.0.1:3306/providentnext`) — use for local dev.
- `data/provident.db` (SQLite with WAL/SHM, active) — possible data source for direct import if DB not reachable.

## 12. Gaps / Open Questions
1. `.env.local` DB URL is Postgres (Neon) in production — confirm whether final PHP deployment uses the MySQL XAMPP fallback schema or requires a fresh MySQL dump/import from `data/raw` JSON snapshots.
2. `data/raw/html/` contents not yet enumerated (page snapshots for fidelity checks).
3. Admin uploads use @vercel/blob → must be replaced with local `uploads/` + `api/upload.php` (or simple file upload endpoint) and URL rewriting in content JSON.
4. Whether `data/provident.db` (SQLite) should be the canonical import source — it contains live DB state incl. users/sessions.
5. PHP version on target WPArena hosting (8.0 vs 8.1/8.2/8.3) affects available functions (e.g. `str_contains` needs 8.0+, enums 8.1+).
6. Email sending (forms → notifications) — check existing email flow (currently likely no SMTP in Node app; PHP must add mail()/SMTP with same From: zoyaventure15@gmail.com).

## 13. Audit Trails (subagent reports)
Three component-audit subagent reports were produced (content components; portal/admin/dashboard; forms/small components) covering modules.tsx, content-pages.tsx, listing.tsx, projects.tsx, property-detail.tsx, property-gallery.tsx, search-hero.tsx, dream-home-quiz.tsx, admin-app.tsx, dashboard-app.tsx, portal-shell.tsx, auth forms, all small forms/components. Notable findings:
- gsap/framer-motion NOT used in forms/small components — React state + inline SVG/CSS transitions only.
- 25 portal SVGs listed (see §3 portal-shell).
- Full List Your Property field inventory captured (§6).
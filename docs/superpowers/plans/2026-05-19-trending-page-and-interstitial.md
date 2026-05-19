# Trending Page And Interstitial Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build a functional data-driven trending page and apply the interstitial ad across the frontend with a 30-minute display interval.

**Architecture:** Add a server-side archive service to transform published catalogue rows into the page payload, then render existing page sections from that payload. Keep filtering and time-mode sorting in browser-side JavaScript using data attributes. Move the interstitial to the shared frontend layout and control display timing with `localStorage`.

**Tech Stack:** PHP 8.1, custom PHP framework, MySQL-backed `media_items`, vanilla JavaScript, CSS.

---

### Task 1: Trending Page Service

**Files:**
- Create: `src/App/Services/Archive/TrendingPageService.php`
- Create: `tests/TrendingPageServiceTest.php`
- Modify: `src/App/container-definitions.php`
- Modify: `src/App/Controllers/Archive/TrendingPageController.php`

- [ ] **Step 1: Write the failing service test**

Create `tests/TrendingPageServiceTest.php` with a fake database that returns published rows. Assert that `pageData()` exposes ranked items, stats, type filters, genre summaries, and fallback-safe watch URLs.

- [ ] **Step 2: Run the test to verify it fails**

Run: `php tests/TrendingPageServiceTest.php`
Expected: failure because `App\Services\Archive\TrendingPageService` does not exist.

- [ ] **Step 3: Implement the service**

Create `TrendingPageService` with `pageData()`, `getTrendingItems()`, score helpers, genre parsing, and payload mapping. Query published movie and tv_show items, sort by views, rating, popularity, and recency, then return named arrays for the template.

- [ ] **Step 4: Wire the service**

Inject the service into `TrendingPageController`, pass its `pageData()` result to the view, and register the service in `container-definitions.php`.

- [ ] **Step 5: Run the test to verify it passes**

Run: `php tests/TrendingPageServiceTest.php`
Expected: all assertions pass.

### Task 2: Dynamic Trending Markup

**Files:**
- Modify: `src/App/views/frontend/archive/trending-page/index.php`
- Modify: `src/App/views/frontend/archive/trending-page/components/trending-hero.php`
- Modify: `public/assets/frontend/css/trending-page.css`

- [ ] **Step 1: Convert static sections to loops**

Render stats, spotlight, sidebar ranks, trending grid, top chart, watched-today, regions, and genres from service arrays. Use real `href` values for media cards.

- [ ] **Step 2: Add data attributes**

Add `data-type`, `data-category`, `data-day-score`, `data-week-score`, and `data-month-score` to cards and rows so JavaScript can filter and sort without another request.

- [ ] **Step 3: Add empty states**

Render a compact empty message if the catalogue has no published items.

- [ ] **Step 4: Syntax check templates**

Run: `php -l src/App/views/frontend/archive/trending-page/index.php` and `php -l src/App/views/frontend/archive/trending-page/components/trending-hero.php`
Expected: no syntax errors.

### Task 3: Trending Browser Interactions

**Files:**
- Modify: `public/assets/frontend/js/trending-page.js`

- [ ] **Step 1: Remove page-local interstitial code**

Delete the old trending-only interstitial logic from `trending-page.js`; the shared app script will own it.

- [ ] **Step 2: Implement filtering and sorting**

Attach click listeners to `.tf-pill` and `.tts-btn`, filter all `[data-trending-item]` elements, reorder grid/table children by selected score, and update result labels.

- [ ] **Step 3: Keep interactions resilient**

Guard all DOM reads so the script does nothing on pages that do not contain trending elements.

### Task 4: Shared Interstitial

**Files:**
- Modify: `src/App/views/layouts/frontend/paper.php`
- Modify: `src/App/views/frontend/archive/trending-page/ad/trending-interstitial.php`
- Modify: `public/assets/frontend/css/paper.css`
- Modify: `public/assets/frontend/css/trending-page.css`
- Modify: `public/assets/frontend/js/app.js`

- [ ] **Step 1: Include interstitial in the shared layout**

Place the partial near the top of `<body>` after search so every frontend page has the same markup.

- [ ] **Step 2: Move ad CSS to shared CSS**

Copy interstitial styles from `trending-page.css` to `paper.css` and remove the duplicate page-specific block.

- [ ] **Step 3: Implement interval logic**

Add `initInterstitialAd()` to `app.js`: read `data-interval-minutes`, check `localStorage.vexioInterstitialNextAt`, show only when eligible, and set the next timestamp when shown/dismissed.

- [ ] **Step 4: Verify refresh behavior**

Open the site, confirm the ad appears once, refresh, and confirm it stays hidden until the stored timestamp is cleared or expires.

### Task 5: Verification

**Files:**
- All changed files.

- [ ] **Step 1: Run PHP tests**

Run: `php tests/TrendingPageServiceTest.php`
Expected: all assertions pass.

- [ ] **Step 2: Run PHP syntax checks**

Run: `Get-ChildItem src -Recurse -Filter *.php | ForEach-Object { php -l $_.FullName }`
Expected: no syntax errors.

- [ ] **Step 3: Start or reuse local server**

Run: `php -S 127.0.0.1:8091 -t public`
Expected: server responds for `/archive/trending`.

- [ ] **Step 4: Browser smoke test**

Verify desktop and mobile layouts, filter buttons, time buttons, media links, and interstitial interval behavior.

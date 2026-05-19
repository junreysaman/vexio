# Sitewide Responsive Polish Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Make the mobile sticky ad site-wide, fix mobile slider responsiveness, and apply trending-style card hover polish across frontend pages.

**Architecture:** Keep existing PHP templates and introduce shared behavior through the common frontend layout, shared CSS in `paper.css`, and small targeted CSS patches in page-specific stylesheets. Do not create support pages; keep footer support links inert.

**Tech Stack:** PHP 8.1 custom framework, vanilla CSS, vanilla JavaScript, Codex browser verification.

---

### Task 1: Site-Wide Sticky Mobile Ad

**Files:**
- Modify: `src/App/views/layouts/frontend/paper.php`
- Modify: `src/App/views/frontend/archive/trending-page/index.php`
- Modify: `src/App/views/frontend/archive/trending-page/ad/trending-mobile-sticky.php`
- Modify: `public/assets/frontend/css/paper.css`
- Modify: `public/assets/frontend/css/trending-page.css`
- Modify: `public/assets/frontend/js/app.js`

- [ ] Move the sticky ad partial from trending page content into the shared frontend layout.
- [ ] Make the ad markup ASCII-safe and remove inline JavaScript handlers.
- [ ] Add shared mobile sticky CSS to `paper.css`.
- [ ] Remove duplicate sticky ad CSS from `trending-page.css`.
- [ ] Add close/open behavior in `app.js`.

### Task 2: Slider Responsiveness

**Files:**
- Modify: `public/assets/frontend/css/paper.css`
- Modify: `public/assets/frontend/css/trending-page.css`
- Modify: `public/assets/frontend/css/watch-movie.css`
- Modify: `public/assets/frontend/css/watch-tv-show.css`
- Modify: `src/App/views/frontend/archive/trending-page/index.php` if markup needs slider semantics.

- [ ] Ensure `.hrow` sliders on home use touch scrolling on mobile and do not get blocked by parent overlays.
- [ ] Ensure trending `#trendTodayRow` is six cards per row on desktop and horizontal slider on mobile.
- [ ] Ensure cast rows on watch movie and TV pages scroll correctly on mobile.
- [ ] Preserve desktop arrow buttons and hide them on mobile.

### Task 3: Shared Trending Card Hover Style

**Files:**
- Modify: `public/assets/frontend/css/paper.css`
- Modify: `public/assets/frontend/css/trending-page.css`
- Modify: `src/App/views/frontend/partials/footer.php`

- [ ] Add shared hover/overlay/accent strip rules for existing card classes.
- [ ] Apply rules to home archive cards, home genre cards, trending cards, browse cards, and watch cards where selectors exist.
- [ ] Disable skipped footer support links so they do not imply missing pages.

### Task 4: Verification

**Files:**
- All changed files.

- [ ] Run `php tests/TrendingPageServiceTest.php`.
- [ ] Run PHP syntax checks for changed PHP files.
- [ ] Load `/`, `/archive/trending`, `/archive/browse`, `/genres`, `/login`, `/register`, and `/404`.
- [ ] Use browser checks to confirm mobile sliders can scroll horizontally and desktop trending Most Watched has six cards per row.

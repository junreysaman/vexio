# Sitewide Hero and Support Pages Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Normalize non-home page heroes and activate footer support/legal links with generated static pages.

**Architecture:** Add one static support controller with page payloads and one reusable support page view. Add shared hero CSS to the global frontend stylesheet and migrate existing non-home archive heroes onto that class system while keeping page-specific controls intact.

**Tech Stack:** PHP views/controllers, custom route table, global frontend CSS.

---

### Task 1: Static Support Pages

**Files:**
- Create: `src/App/Controllers/SupportPageController.php`
- Create: `src/App/views/frontend/support/page.php`
- Modify: `src/App/Config/Routes.php`
- Modify: `src/App/container-definitions.php`

- [ ] Add a controller that maps known slugs to page payloads and renders the reusable support view.
- [ ] Register routes for FAQ, Contact, Report Issue, Request Title, Privacy Policy, Terms of Use, DMCA, and Advertise.
- [ ] Add the controller to the container.
- [ ] Run PHP syntax checks.

### Task 2: Footer Links

**Files:**
- Modify: `src/App/views/frontend/partials/footer.php`
- Modify: `public/assets/frontend/css/paper.css`

- [ ] Replace disabled support/legal links with real URLs.
- [ ] Point genre footer links at existing genre archive routes.
- [ ] Remove disabled-link styling that no longer applies.

### Task 3: Shared Non-Home Hero

**Files:**
- Modify: `public/assets/frontend/css/paper.css`
- Modify: `public/assets/frontend/css/trending-page.css`
- Modify: `src/App/views/frontend/archive/trending-page/components/trending-hero.php`
- Modify: `src/App/views/frontend/archive/browse/components/archive-hero.php`
- Modify: `src/App/views/frontend/archive/genre-page/components/genre-hero.php`
- Modify: `src/App/views/frontend/archive/genre-page/components/styles.php`
- Modify: `src/App/views/frontend/archive/browse/components/styles.php`

- [ ] Add global `.vex-page-hero` classes.
- [ ] Move browse, genre, trending hero markup to the shared class names.
- [ ] Remove conflicting hero-specific dimensions only where needed.
- [ ] Verify homepage hero remains unchanged.

### Task 4: Verification

**Files:**
- Test affected files and routes.

- [ ] Run `php -l` over new/touched PHP files.
- [ ] Smoke test all support routes.
- [ ] Run existing trending service test.
- [ ] Browser-check desktop and mobile hero/footer behavior.

# Vexio Combined Admin, Importer, Refresh, and Card Design

## Context

The app is a custom PHP application with a small framework layer, route-level middleware, server-rendered views, and TMDB-backed catalogue services. The current route file is a flat list of mixed public, auth, admin, archive, API, support, and watch routes. `GuestOnlyMiddleware`, `AuthRequiredMiddleware`, and `AdminRequiredMiddleware` already exist, but login/register routes currently use the wrong middleware in the working tree, which lets the auth flow behave incorrectly.

The TMDB importer currently fetches result pages through JSON and imports one selected card at a time. `TmdbImporterService` already upserts movies and TV shows by TMDB identity and downloads local poster/backdrop images. The admin content edit page already supports manual updates, season generation, episode generation, and safe asset deletion on content deletion, but it does not yet provide a refresh action that pulls fresh TMDB data for an existing title.

Frontend card markup is duplicated across the homepage recently added row, archive browse results, genre result cards, and the trending page poster grid. The trending page also has custom spotlight, sidebar, rank table, and horizontal cards that are more layout-specific.

## Goals

1. Group routes by related application area so `Routes.php` is easier to scan and safer to edit.
2. Prevent logged-in users from accessing login/register pages.
3. Protect admin routes from guests and non-admin users.
4. Add a faster importer workflow that can import selected or visible TMDB results consecutively.
5. Add a refresh action on imported content edit pages to pull fresh TMDB metadata and replace stale local artwork.
6. Normalize reusable poster-card markup and styling across the real data-backed frontend card grids.

## Non-Goals

1. Do not add a background queue, worker daemon, or scheduler for imports.
2. Do not redesign the admin dashboard or importer from scratch.
3. Do not replace the entire frontend visual system.
4. Do not migrate custom layout cards that are intentionally not poster cards, such as the trending spotlight, sidebar cards, rank rows, and horizontal cards.
5. Do not change user roles beyond the existing `superuser` admin check.

## Proposed Approach

This will be one combined implementation executed in staged checkpoints:

1. Security foundation: route grouping and correct middleware assignments.
2. Admin workflow: bulk importer and edit-page TMDB refresh with safe old-asset cleanup.
3. Frontend normalization: shared media-card partial and shared CSS for data-backed poster cards.

This keeps the work unified while allowing each subsystem to be tested and reviewed independently.

## Route And Auth Design

`src/App/Config/Routes.php` will remain the central route registration file. The flat `$routes` list will be replaced with a `$routeGroups` associative array whose keys describe the application area:

1. Public frontend pages: home, genres, support pages.
2. Guest auth pages and submissions: `GET /login`, `GET /register`, `POST /login`, `POST /register`.
3. Authenticated account actions: `POST /logout`.
4. Admin dashboard, users, comments, content, and importer routes.
5. Archive pages: browse, genres, trending.
6. Public API routes: search and comments.
7. Watch routes: movie and TV show route variants.
8. Error route: `/404`.

Middleware assignment will be explicit:

1. Login and register routes use `GuestOnlyMiddleware`.
2. Logout uses `AuthRequiredMiddleware`.
3. All admin routes use `AdminRequiredMiddleware`.
4. Public archive, support, search, comment, and watch routes remain public unless already protected elsewhere.

`GuestOnlyMiddleware` already redirects logged-in `superuser` accounts to `/admin/dashboard` and regular accounts to `/`. `AdminRequiredMiddleware` already redirects guests to `/login` and non-admin users to `/`, with a flash error.

## Importer Design

The existing importer page will keep its discover/search filters, result grid, per-card status select, and featured toggle. The UI will add:

1. A checkbox on each result card.
2. A toolbar action for `Import selected`.
3. A toolbar action for `Import visible`.
4. A progress summary that reports pending, imported, failed, and skipped counts.
5. Per-card states while importing: queued, importing, imported, failed.

The existing `/admin/importer/import` endpoint will accept either:

1. The current single-item payload: `tmdb_id`.
2. A bulk payload: `tmdb_ids[]` or a comma-separated `tmdb_ids`.

The controller will normalize the request into unique positive TMDB ids and import them consecutively in a single request. Each item will call the existing `importMovie` or `importTvShow` service method based on the active tab. A failure for one TMDB id will be captured in that item result and will not stop the rest of the batch. Bulk requests will be capped at 20 ids per request so the synchronous import flow stays predictable.

The JSON response will include:

1. `ok`: true if the request was valid and processed.
2. `message`: a short summary.
3. `results`: an array of per-id results with `tmdb_id`, `ok`, `message`, and optional `item`.
4. `counts`: imported and failed totals.
5. `csrf_token`: one refreshed token for subsequent importer actions.

The importer will remain synchronous. This is simple and matches the current application architecture. If a very large import is requested later, a queued job design can be added as a separate feature.

## Edit Refresh Design

The admin edit page for imported content will add a `Refresh from TMDB` action when:

1. The item has a `tmdb_id`.
2. The item type is `movie` or `tv_show`.

The controller will add this route:

`POST /admin/content/{id}/refresh-tmdb`

The refresh handler will:

1. Load the existing item.
2. Store existing `poster_image` and `backdrop_image` paths.
3. Call `TmdbImporterService::importMovie()` or `TmdbImporterService::importTvShow()` using the existing TMDB id and current status/featured state.
4. Reload the refreshed item.
5. Delete old local poster/backdrop files only when the path is a local `/uploads/...` file, the refreshed path changed, and the old path is no longer used by the refreshed item.
6. Set a success or error flash and redirect back to the edit page.

This uses the existing upsert behavior, so refresh updates metadata, taxonomy links, content meta, and local images consistently with a fresh import.

## Asset Cleanup Design

Asset deletion will stay constrained to files below `public/uploads/`. Existing safe deletion logic already resolves real paths and verifies that the target remains inside the public path before unlinking.

Refresh cleanup will reuse that same safety rule. It will delete only replaced poster/backdrop files for the refreshed item. It will not delete files that were manually entered outside `/uploads/`, and it will not attempt to clean remote TMDB URLs.

## Card Normalization Design

A reusable frontend partial will be introduced for poster-style media cards:

`src/App/views/frontend/partials/media-card.php`

The partial will accept normalized data:

1. `title`
2. `poster`
3. `watchUrl`
4. `typeLabel` or `badgeText`
5. `badgeClass`
6. `rating`
7. `primaryMeta`
8. `secondaryMeta`
9. `synopsis`
10. Optional `data` attributes for filtering/sorting.

A shared CSS file, `public/assets/frontend/css/media-card.css`, will define the `.media-card` system and be included by pages that use the reusable card partial. To limit blast radius, the first migration will cover data-backed poster card grids:

1. Homepage recently added cards.
2. Archive browse dynamic cards.
3. Genre active-result cards.
4. Trending page `trendGrid` poster cards.

Special trending layouts will remain custom:

1. Spotlight card.
2. Spotlight sidebar cards.
3. Rank table rows.
4. Most-watched horizontal cards.
5. Region and genre cards.

The goal is consistent poster cards across the site without flattening intentionally distinct editorial layouts.

## Data Flow

### Route/Auth

Request path and method are matched by `Framework\Router`. Route middleware runs before global middleware in the current router implementation. Correct route-level middleware determines guest-only, authenticated-only, and admin-only access.

### Importer

Admin browser sends one or more TMDB ids to `/admin/importer/import`. The controller validates tab, status, featured, and ids. The controller calls `TmdbImporterService` once per id and returns JSON results. The frontend updates card state from the JSON response and refreshes the CSRF token once.

### Refresh

Admin edit page submits to the refresh route. The controller validates the existing item, captures old local asset paths, calls the TMDB import upsert, cleans replaced local assets, flashes the result, and redirects back to edit.

### Cards

Services continue returning existing payloads. Views normalize each item into the shared media-card partial. Filtering and sorting data attributes remain present for archive pages.

## Error Handling

1. Guest users hitting admin routes redirect to `/login`.
2. Logged-in non-admin users hitting admin routes redirect to `/` with the existing admin-access flash.
3. Logged-in users hitting login/register redirect away through `GuestOnlyMiddleware`.
4. Importer validation errors return JSON 422 with a refreshed CSRF token.
5. Bulk importer item failures are isolated to that item and shown in `results`.
6. Refresh failures flash the TMDB error message and keep the user on the edit page.
7. Asset deletion failures are ignored after safety checks, matching current deletion behavior.

## Testing Strategy

Tests will be added in a focused way because the repository currently has no committed test suite visible in the working tree.

1. Route configuration tests: assert login/register use guest middleware, logout uses auth middleware, and all `/admin` routes include admin middleware.
2. Middleware tests: assert guest, logged-in regular, and logged-in superuser redirect behavior for the auth/admin middleware.
3. Importer controller tests or service-level tests: assert single and bulk payload normalization, per-item failure isolation, and response shape.
4. Refresh tests: assert the correct import method is selected by type and replaced local assets are cleaned only under `/uploads/`.
5. View/partial tests or snapshot-style render checks: assert the media-card partial renders title, poster fallback, rating, badge, metadata, watch URL, and filter data attributes.

If the current app does not have a test runner configured, the implementation plan will include creating a small, project-local PHP test harness rather than introducing a large framework dependency.

## Risks And Mitigations

1. Bulk import request time may grow with many selected TMDB ids. Mitigation: cap batch size in the controller and surface a clear validation error if exceeded.
2. CSRF token rotation can break multiple sequential POSTs. Mitigation: bulk mode uses one request and returns one fresh token; single-card JS updates all token fields after each response.
3. Refresh may replace manually edited metadata. Mitigation: label the action clearly as TMDB refresh and require a submit/confirm action.
4. Card CSS changes could affect archive layout. Mitigation: migrate data-backed sections deliberately and keep archive sorting/filter data attributes intact.
5. Existing dirty working-tree edits may overlap `Routes.php`. Mitigation: preserve user changes and only replace the incorrect auth middleware usage as part of the grouped route design.

## Acceptance Criteria

1. `Routes.php` is grouped by related routes and still registers all existing routes.
2. Guests can access login/register and cannot access admin pages.
3. Logged-in users cannot access login/register.
4. Superusers can access admin pages.
5. Regular logged-in users cannot access admin pages.
6. Importer can import one item as before.
7. Importer can import selected or visible result cards in one consecutive bulk request.
8. Bulk import reports per-item success and failure without losing all progress.
9. Imported movie and TV show edit pages can refresh from TMDB.
10. Refresh deletes replaced old local poster/backdrop files under `/uploads/`.
11. Homepage recently added, archive browse, genre results, and suitable trending poster cards share the same media-card partial/style.
12. Verification commands pass or any environment blockers are documented.

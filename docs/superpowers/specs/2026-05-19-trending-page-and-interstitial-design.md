# Trending Page And Interstitial Design

## Goal

Make `/archive/trending` use real catalogue data and move the interstitial ad into the shared frontend layout with a browser-side interval so refreshes do not show it repeatedly.

## Architecture

Add a `TrendingPageService` that reads published `media_items`, computes ranked page sections, and exposes a compact payload to the existing controller. Keep rendering server-side in PHP and use `public/assets/frontend/js/trending-page.js` only for local filtering, sorting, and small interactions.

The interstitial ad becomes a shared partial included by `layouts/frontend/paper.php`. A shared frontend script controls whether it appears by comparing `Date.now()` to a `localStorage` timestamp. The default interval is 30 minutes.

## Functionality

- Trending hero, stats, spotlight, grid, top chart, watched-today, region, and genre sections use real data where the current schema supports it.
- Cards and ranking rows link to `MediaUrl::watchUrlForItem()` instead of placeholder toasts.
- Filter pills hide/show local card sets by type or ranking category.
- Time buttons reorder visible ranking cards by day, week, or month scores derived from existing aggregate fields.
- Empty catalogue states render useful fallback messaging instead of broken layouts.
- Site-wide interstitial uses one shared markup and CSS block, is dismissible, auto-dismisses after 5 seconds, and stores the next eligible display time for 30 minutes later.

## Files

- Create `src/App/Services/Archive/TrendingPageService.php` for page data.
- Modify `src/App/Controllers/Archive/TrendingPageController.php` to use the service.
- Modify `src/App/container-definitions.php` to wire the service.
- Modify `src/App/views/frontend/archive/trending-page/index.php` to render dynamic data and remove page-local interstitial inclusion.
- Move/reuse `src/App/views/frontend/archive/trending-page/ad/trending-interstitial.php` from trending-only usage into `src/App/views/layouts/frontend/paper.php`.
- Modify `public/assets/frontend/css/trending-page.css` and `public/assets/frontend/css/paper.css` so trending layout and interstitial styles work on the right pages.
- Modify `public/assets/frontend/js/trending-page.js` for trending interactions.
- Modify `public/assets/frontend/js/app.js` for site-wide interstitial interval behavior.
- Add tests under `tests/` for `TrendingPageService` payload and ordering.

## Testing

- Run the new PHP test script to verify ranking, filters, stats, and fallback data.
- Run PHP syntax checks on changed PHP files.
- Start the local PHP server and verify `/archive/trending` renders.
- Use browser verification for desktop/mobile trending page and interstitial interval behavior.

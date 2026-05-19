# Sitewide Responsive Polish Design

## Goal

Apply the trending mobile sticky ad across public frontend pages, repair mobile slider behavior, improve responsive layouts across common device sizes, and bring the trending card hover style into the broader site without creating support pages.

## Scope

Support pages are out of scope. Footer support links should not route to missing static pages in this pass.

## Design

1. Include the existing mobile sticky ad partial in the shared frontend layout so it appears across the public website. It stays mobile-only and sits above the bottom navigation without blocking it.
2. Move sticky ad styling into shared `paper.css` and remove trending-page-only ownership of the ad behavior. Add shared JavaScript for close handling.
3. Normalize horizontal slider behavior:
   - Desktop sections can use grids where requested or appropriate.
   - Mobile horizontal rows use `overflow-x:auto`, `scroll-snap-type:x mandatory`, fixed responsive card widths, `-webkit-overflow-scrolling:touch`, and no parent overflow that blocks swiping.
   - Existing arrow buttons remain desktop-only.
4. Apply the trending card feel across the site through shared card CSS:
   - Image zoom on hover.
   - Dark gradient overlays.
   - Strong title/meta spacing.
   - Bottom accent strip on hover.
   - Consistent transition timing.
   The implementation should target existing card classes such as home archive cards, genre cards, browse archive cards, watch related cards, and trending cards, while preserving each page's structure.
5. Responsive audit targets:
   - `/`
   - `/archive/trending`
   - `/archive/browse`
   - `/genres`
   - representative movie and TV watch URLs when available
   - `/login`, `/register`, and `/404`

## Testing

- PHP syntax checks on changed templates.
- Existing service test remains passing.
- Browser checks at desktop, tablet, and mobile widths where supported.
- Manual DOM measurement for sliders: mobile rows must have horizontal scroll capacity and desktop most-watched grid must show six cards per row.

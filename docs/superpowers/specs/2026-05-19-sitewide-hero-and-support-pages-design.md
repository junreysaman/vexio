# Sitewide Hero and Support Pages Design

## Scope

Normalize page hero layout and visual treatment across all non-home frontend pages that have archive-style heroes, while leaving the homepage hero unchanged. Restore footer support and legal links so they navigate to real static support pages.

## Approach

- Introduce a shared `.vex-page-hero` design in the global frontend stylesheet.
- Update browse, genre, and trending hero markup to use the shared shell while preserving their page-specific controls.
- Add a static support page controller and reusable support view driven by per-page payloads.
- Register routes for FAQ, Contact, Report Issue, Request Title, Privacy Policy, Terms of Use, DMCA, and Advertise.
- Update footer support, legal, and genre links to point to real routes.

## Constraints

- Do not change the homepage hero.
- Do not remove existing trending filters or genre filtering controls.
- Keep implementation in the existing PHP controller/view/router pattern.
- Keep support pages static and design-consistent, with no form processing in this pass.

## Verification

- PHP syntax checks for new and touched PHP files.
- Route smoke checks for generated support pages.
- Rendered layout checks for non-home heroes and footer links.

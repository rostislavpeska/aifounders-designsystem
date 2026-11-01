# Design-system tests

Playwright specs that run against the live local WordPress stack, where the styleguide is served at
`/?aifds_styleguide=1` (WP_DEBUG). Run with `npm test`, or one spec with `npx playwright test tests/layout.spec.js`.

## BASE_URL

`playwright.config.js` reads `BASE_URL` and falls back to `http://localhost:8090` (the default dev stack, unchanged).
Point the run at another stack, for example an isolated test stack, without editing any file:

    BASE_URL=http://localhost:8190 npm test

## Layout rules (`layout.spec.js`)

The L2 sweep crawls every styleguide tab per brand and viewport and checks six invariants (overflow, wide, occluded,
zero-size, console, requests; all must be zero) plus four rules judged against a reviewed baseline:

| Rule | What fails |
|---|---|
| `padding` | text in a listed component (`.btn`, `.badge`, `td`, `th`, `.blurb`, ...) is closer than 8 px to a side with a visible border or background |
| `child-overflow` | text or link text leaves its parent's content box by more than 1 px (scroll containers and ellipsis/line-clamp are by design) |
| `box-in-box` | a bordered/filled box inside a bordered/filled box; exceptions: badges, buttons, form controls, logo tiles, avatars, modal; styleguide chrome (`sg-*`) and doc tables are not boxes |
| `input-button-row` | an input and a button side by side in one form/group differ by more than 1 px in height or top |

Thresholds are frozen constants (`THRESHOLDS` in the spec). Never raise one to get green.

`layout-baseline.json` holds the violations that exist today (rule, brand, viewport, page = tab slug, selector, px).
Only new ones fail. When a baseline entry no longer occurs, delete it. Regenerate a slice after a reviewed change:

    LAYOUT_UPDATE_BASELINE=1 BASE_URL=http://localhost:8190 npx playwright test tests/layout.spec.js --workers=1

Review the diff before committing. Baseline selectors containing `:nth-child` shift when markup is inserted earlier
in the same parent.

Opt-outs live in the markup and are greppable: `data-sg-box-ok` (intentional box-in-box) and `data-sg-pad-ok`
(tight padding demo), next to the existing `data-sg-overlap-ok` and `data-sg-zero-ok`.

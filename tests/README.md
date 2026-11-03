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

## Spacing rules (`spacing.spec.js`, helper `helpers/spacing.js`)

`helpers/spacing.js` is the canonical copy of the spacing-rhythm helper. aifounders_web and aiguild carry
byte-identical copies in `tests/e2e/helpers/spacing.js`: edit this one, copy it over, compare `sha256sum`.

Inside `main`, every vertical stack (block, flex column, one-column grid) is cut into sibling pairs A, B.
`layout` = B.top - A.bottom (margins and row-gap included). The ink edge is what the eye sees: a background,
a border on that side, a shadow or a replaced element; for a text block its content box (padding is invisible).
`visual` = ink(B) - ink(A). Closed accordions (0-height clipping box) and absolute/fixed elements are skipped.

| Rule | What fails |
|---|---|
| `gap-doubled` | a flex/grid parent with row-gap >= 16 and a child margin >= 16 next to it (margin + gap doubled) |
| `gap-stacked` | gap >= 16 plus invisible padding >= 16 at the end of A or the start of B |
| `gap-touch` | two visible edges of different width less than 8 px apart (a tile glued to a hairline) |
| `gap-scale` | a gap between two blocks >= 32 px tall that is not a `--spacing-*` token (0, 2 ... 56, 80, 120) |

`gap-stacked` and `gap-scale` skip stretched cells (equal-height grid or flex-row items with `margin-top:auto`
actions): that gap is alignment. Thresholds (`SPACING_THRESHOLDS`) and the scale (`SPACING_SCALE`) are frozen
constants; the spec asserts the scale against `assets/css/tokens.css`. Never raise one to get green.

The spec has two parts. The rule self-tests use inline fixtures and need no stack (a failing and a passing
fixture per rule, a miniature of the Rejstřík defect, a closed accordion, equal-height cards). The template part
measures the four Templates items (`template-homepage`, `-page`, `-article`, `-archive`) per brand at 390 and
1280 against the reviewed `spacing-baseline.json` (page = item, viewport = `<brand>@<width>`). The Templates group
is served by the ds-tests stack below, not by the DS copy vendored into the AIF dev stack (:8090):

    BASE_URL=http://localhost:8195 npm run test:spacing

aifounders renders its own brand, so its real page is loaded directly at the full viewport. aiguild is measured
inside the styleguide iframe, where the brand is re-stamped (at 1280 the frame is narrower than the viewport;
the annotation `spacing-target` names the frame width).

Regenerate a baseline slice after a reviewed change (never together with `SPACING_INJECT_CSS`):

    SPACING_UPDATE_BASELINE=1 BASE_URL=http://localhost:8195 npx playwright test tests/spacing.spec.js --workers=1

What-if: `SPACING_INJECT_CSS=/tmp/fix.css` injects that CSS before measuring, to try a fix (or prove an old
defect) without editing the checkout a stack serves. The annotation `spacing-what-if` names the file.

## Run against the DS repo's own CSS (ds-tests, :8195)

The AIF test stack (`aif-tests`, :8190) serves the vendored DS copy that AIF production runs, which can differ from this
repo. Run this suite against the variant stack `ds-tests`, which mounts THIS worktree read-only as the plugin
(started by the site repo test tooling: `ds-tests.sh up` then `ds-tests.sh seed`, ports 8195 and 8197):

```
BASE_URL=http://localhost:8195 npm test
```

Check that it serves this repo's CSS: the sha256 of `/wp-content/plugins/aig-design-system/assets/css/components.css`
and `tokens.css` fetched from :8195 must equal the files in `assets/css/`. The layout baseline is generated against :8195.

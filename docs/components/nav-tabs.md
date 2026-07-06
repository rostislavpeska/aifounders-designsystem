# Nav tabs

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#nav-tabs` · **Specimen:** `/?aigds_styleguide=1&item=nav-tabs`

## Intent

Horizontal sibling navigation between the sections of one area — the AIF
author portal (Write article / My articles / Preferences / Profile) and the
auth pages are the production consumers. Reach for it when a page belongs to
a small set of peer views and the user switches BETWEEN PAGES (each tab is a
real link that navigates). Do NOT reach for it for in-page content switching
without navigation (that is the segmented control), for hierarchy
(breadcrumb), or for filtering (archive filters). The contract in one
breath: a nowrap, horizontally scrollable row with a hidden scrollbar that
**DOCKS on its section's bottom edge** — these are tabs, so the active
`<span>`'s constant white chip lands FLUSH on the (white) section below
(live-verified: hero bottom == chip bottom == next-section top); inactive
tabs are text-colored idiom links in the lead-sized accent voice; the active
tab centers itself in the row on load. Harvested 1:1 from AIF
`.author-tabs` (`page.css:2711`, rendered by `components/auth-nav.php` +
`author-nav.php`); RENAMED generic `.nav-tabs` (record-list precedent) —
theme alias `author-tabs → nav-tabs` at adoption.

## Anatomy

```html
<nav class="nav-tabs" aria-label="Author portal">
  <a href="…" class="nav-tabs__tab">Write article</a>
  <span class="nav-tabs__tab nav-tabs__tab--active">My articles</span>
  <a href="…" class="nav-tabs__tab">Preferences</a>
</nav>
```

- `.nav-tabs` — the scroll row (`overflow-x: auto`, scrollbar hidden, touch
  momentum). Keep the `aria-label`.
- `.nav-tabs__tab` — one tab; a link when navigable, a `<span>` when active
  (the production semantic: the current page is not a link).
- `.nav-tabs__tab--active` — the current page: constant white chip,
  no underline, `cursor: default`.

**Docking contract** (harvested `.page-hero:has(.author-tabs)` rule): the
tab row must be the LAST child of its hero/section, and that container's
bottom padding must be 0 so the chip touches the next section. The DS ships
`:where(.section-brand, .section-dark, .section-light, .content-section,
.page-hero):has(> .nav-tabs:last-child) { padding-bottom: 0 }` — zero
specificity, consumer-overridable; inline padding shorthands beat it, so
class-padded containers get it free, inline-styled ones must omit the
bottom padding themselves. Production also adds `--spacing-80` top padding
to the following section and `--spacing-48` under the hero `h1` — those
page-rhythm rules stay theme-side.

Behavior: `js/components/nav-tabs.js` — on load, scroll the active tab to
the horizontal center of the row (ported from the theme's inline script,
with a port fix: the original used `offsetLeft` without subtracting the
nav's own offset, which mis-scrolls in layouts where the nav is not the
offsetParent origin).

## Variants

None.

## States

- inactive hover/focus — underline thickens 1px → 2px (link idiom).
- `--active` — white chip, no underline, not clickable.

## Responsive

Dedicated mobile behavior at `max-width: 767px` (live-verified full-bleed
0→viewport-width on production): the row bleeds to the viewport edges via
its own negative `--spacing-24` margins (+ `--spacing-12` inner padding) and
tabs step down from the lead voice to `--body-md-size` with tighter padding.
The row scrolls inside itself — the page body never overflows — and the
centering JS keeps the active chip visible. Docking holds at every viewport.

## Tokens referenced

`--font-accent` `--lead-size` `--lead-leading` `--weight-bold` `--text`
`--white` `--spacing-8` `--spacing-12` `--spacing-16` `--spacing-24`
`--body-md-size` `--transition-fast`

## Surfaces

Inactive tabs read `--text` (surface-aware). The active chip is
**palette-direct `--white`** (accordion `--deep` precedent): production
shows a white chip on the brand hero AND on light pages — `var(--bg)` would
resolve to brand inside `.section-brand` and vanish. CAUTION: on dark
surfaces the inherited `--text` (paper) on the white chip would fail —
dark-surface tabs are unharvested territory; get a ruling before shipping
one there.

## Known friction

- **Link idiom alignment (GM exception):** live hover REMOVED the underline
  (outlawed pattern); DS thickens instead.
- **Leading normalized (GM exception):** live `1.7` → `--lead-leading`
  (brand-diverged 1.5/1.6) — visually nil on a nowrap single line.
- `.nav-tabs__tab` is in the global link chain's exclusion list (tabs are
  text-colored, not link-colored).
- The active tab must stay a `<span>` — making it a self-link breaks the
  production semantic and the centering JS's assumptions.
- **The tabs must dock** — a floating tab row (padding under it) reads as
  chips hanging in space, not tabs. Keep the row last in its section and let
  the docking rule zero the bottom padding (gate-asserted: chip bottom ==
  hero bottom == next-section top).

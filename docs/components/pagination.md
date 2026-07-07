# Pagination

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#pagination` · **Specimen:** `/?aifds_styleguide=1&item=pagination`

## Intent

Numbered page navigation for archives — the centered chip rail under an
article/signal/event grid. Reach for it wherever WordPress
`paginate_links()` renders (it styles that output directly — the inner
`.page-numbers` classes are WP core and unrenameable). Do NOT reach for it
for in-page steps (that is a future stepper) or infinite scroll (the AIF
archives also lazy-load; pagination is the no-JS fallback and the
crawlable path). The contract in one breath: a `--bg-alt` rail of square
44px touch-target chips, the current page as a white (`--bg`) chip, hover
only on real links, dots stay narrow. Harvested 1:1 — the block is
byte-identical in BOTH themes (AIF `page.css:2969` == AIG `blog.css:2772`).

## Anatomy

WordPress emits the markup; the DS styles it:

```html
<nav class="archive-pagination" aria-label="Pagination">
  <div class="nav-links">
    <a class="page-numbers" href="…">&laquo;</a>
    <span aria-current="page" class="page-numbers current">1</span>
    <a class="page-numbers" href="…">2</a>
    <span class="page-numbers dots">&hellip;</span>
    <a class="page-numbers" href="…">12</a>
    <a class="page-numbers" href="…">&raquo;</a>
  </div>
</nav>
```

- `.archive-pagination` — the wrapper (WP also adds `.navigation.pagination`
  — the theme CSS styled both; the DS anchors on `.archive-pagination`).
- `.nav-links` — the rail (inline-flex, wraps, `--bg-alt` fill).
- `.page-numbers` — a chip: link, `<span class="current">`, or
  `<span class="dots">`. 44px calibrated touch target.

## Variants

None — prev/next arrows are just chips with arrow glyph content.

## States

- link hover/focus — `--bg-band` fill (scoped to `<a>` ONLY; the current/
  dots spans are static — live hover recolored the current chip, a bug).
- `.current` — `--bg` fill (white on light), `aria-current="page"` from WP.
- `.dots` — narrow, non-interactive.

## Responsive

None needed — the rail wraps (`flex-wrap`) on narrow viewports.

## Tokens referenced

`--spacing-8` `--spacing-16` `--spacing-48` `--spacing-56` `--bg-alt`
`--bg-band` `--bg` `--body-md-font` `--size-14` `--weight-regular` `--text`

## Surfaces

Rail `--bg-alt`, hover `--bg-band`, current `--bg` — all scope roles, so the
component re-resolves on dark surfaces (unharvested there in production, but
the roles hold).

## Known friction

- **Square (GM exception):** live has `radius-sm` (4px) — retired by the
  radius ruling; DS chips are square.
- **Weight fiction fixed:** live declares `--fw-medium`/`--fw-semibold` but
  those v1 vars are UNDEFINED — every chip renders 400 (verified on :8090).
  The DS encodes rendered reality: `--weight-regular` (lead-leading
  precedent).
- **`--spacing-56` top margin (GM exception):** live used v1 `spacing-64`;
  64 is not on the harvested scale.
- `.page-numbers` is in the global link chain's exclusion list — chips are
  chips, not prose links (no underline, no link color).
- The 44px chip is the CALIBRATED touch-target constant (same value as the
  mobile button height — button-ladder precedent).

# Text elements (perex · blockquote · lists — the signifier family)

**Type:** pattern · **Status:** shipped · **git_path:** `assets/css/components.css#prose-defaults` · **Specimen:** `/?aifds_styleguide=1&item=text-elements`

## Intent
The text elements are the prose blocks that carry a **left-gutter signifier**: `.text--perex` (intro voice with a 4px support-colored border), `<blockquote>` (the same voice — one voice, no brand scoping), and `<ul>`/`<ol>` (arrow bullet / counter number in the gutter). Reach for them for editor-authored long-form content inside `<main>`: an article or page intro (perex), a pull-quote (plain `<blockquote>`, no class needed), and lists. Their whole point is the **indent-signifier system**: every member puts its marker in the left gutter and starts its text at ONE shared inset, `--flow-indent` (= `spacing-24`), so perex text, quote text, and list text land on the same vertical line by construction (operator-ratified 2026-07-03, `docs/proposals/INDENT-SIGNIFIER.md`). Do NOT reach for these to build UI chrome — the info box is the boxed member of the same family, and card/component text has its own classes. What breaks if you fight it: adding your own `padding-left` or a per-element `em` indent desynchronizes the family (the ratified token is rem-based/root-relative precisely because the 24px perex voice at 1.5em would land at 36px and misalign from the 18px lists).

## Anatomy
```html
<main>
  <p class="text--perex">Intro perex — lead voice, support-colored 4px left border.</p>
  <blockquote><p>A pull-quote reads in the perex voice.</p></blockquote>
  <ul><li>Arrow-bullet item</li><li>Text aligns with perex and quote</li></ul>
  <ol><li>Numbered step</li><li>Counter sits in the gutter</li></ol>
</main>
```
Mechanics (from `components.css` PROSE DEFAULTS / LISTS / BLOCKQUOTE):
- Bordered members (perex in content columns, blockquote) subtract the border from the inset: `border-left: var(--stroke-4) solid var(--perex-border); padding-left: calc(var(--flow-indent) - var(--stroke-4))` — the border lives INSIDE the indent so text lands exactly at `--flow-indent`.
- The perex border applies inside content columns only: `.content-container .text--perex` / `.article-layout__content .text--perex` (the PEREX IDIOM, harvested identical from both themes).
- Lists reset native styling (`list-style: none; padding-left: 0`) and each `li` gets `padding-left: var(--flow-indent)`; the `::before` marker is absolutely positioned at `left: 0`.
- UL bullet = a 14×14 arrow icon (masked SVG, calibrated raw px like all marker glyphs), colored by the `--bullet` surface role; perex-voice lists get a 16×16 arrow.
- OL numbers = CSS counter (`counter(main-list) "."`) at `--weight-medium`, inheriting the list's font.
- Blockquote is THE perex voice: `--font-accent`, `--lead-size`, `--weight-bold`, `--lead-leading` (brand-resolved 1.5/1.6). Inner `p`/`strong` inherit everything. When nested inside `.text--perex`, the duplicate border is stripped.

## Variants
Voice scoping for lists (the container class picks the size):
- Default / `.text--article`: `--body-lg-*` (18px article voice).
- `.text--body`: `--body-md-size` at `--weight-regular` / `--leading-body` (16px; harvested 1.55 leading was normalized to `leading-body` — GM exception). Also switches blockquote to a quiet 16px variant (`--description-font`).
- `.text--perex`: `--lead-*` (24px voice, 20px on mobile via the lead bundle's content-ramp step).

## States
None — static prose. Empty WYSIWYG paragraphs are killed (`main p:empty` hidden; `.article-layout__content p:empty` / `p:has(> br:only-child)` as the article safety net).

## Responsive
- `--flow-indent` is a **constant 24px, viewport-independent** — alignment holds at every width.
- Article-context blockquote margin steps at the 767 cut: `var(--flow-quote)` → `var(--flow-quote-mobile)` under `max-width: 767px`.
- The type sizes inside follow their bundles' MECHANISM LAW (lead steps to 20 under 768; body voices constant).

## Tokens referenced
`--flow-indent`, `--stroke-4`, `--perex-border`, `--bullet`, `--text`, `--font-accent`, `--lead-size`, `--lead-leading`, `--lead-font`, `--lead-weight`, `--weight-bold`, `--weight-regular`, `--weight-medium`, `--body-lg-font/-size/-weight/-leading`, `--body-md-size/-font`, `--leading-body`, `--description-font`, `--spacing-8`, `--spacing-56`, `--flow-tight`, `--flow-quote`, `--flow-quote-mobile`.

## Surfaces
Fully surface-aware through roles: `--perex-border` adapts per background (support on light, deep-muted on dark, support-strong on support — consolidated from AIG's scattered per-band overrides), `--bullet` supplies the marker color per surface (the old dark bullet override was deleted), and text reads `--text`. No element-level surface CSS exists.

## Known friction
- **One-voice blockquote is a ruling, not an accident**: AIF's quiet 18px base blockquote voice was dead code (always overridden where blockquotes appear) and was killed — GM exception noted for any rare bare page blockquote on AIF (`docs/DECISIONS.md`).
- The bullet arrow's raw 14/16px and its `margin-top` centering values are **component calibration** — retune them only when the line-height they center on changes.
- These rules are scoped to `main` (scope-narrowing to prose containers is queued in the rationalization queue) — content rendered outside `<main>` gets none of this.
- Markdown "loose lists" render `<li><p>`; contexts that indent `p` (e.g. numbered headings) must reset `li p` margins — already handled where the DS knows about it.

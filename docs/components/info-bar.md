# Info bar

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#info-bar` · **Specimen:** `/?aigds_styleguide=1&item=info-bar`

## Intent

THE STATEMENT STRIPE — a full-width band of 1..n bold claims with brand
rules, the credibility beat between a hero and the page body. The first
**benefits-family member resolved** (operator verdicts 2026-07-06, judged
live in the sandbox `infobar` + `aif-home-v2` pages): a **separate
component**, upgraded toward the blockquote grammar. Reach for it for
short statement rows (mission points, guarantees, "why us"). Do NOT reach
for it for titled benefit cards (family verdict pending), blurbs, or
callouts inside prose (info box).

## Anatomy

```html
<!-- the HOST section picks the world (light or dark) -->
<section class="section-dark" style="…">
  <div class="info-bar">
    <div class="info-bar__wrapper">
      <div class="info-bar__item"><p>Statement one.</p></div>
      <div class="info-bar__item"><p>Statement two.</p></div>
      <div class="info-bar__item"><p>Statement three.</p></div>
      <!-- 1..n — the /newsletter page carries four -->
    </div>
  </div>
</section>
```

- `.info-bar` — the band: **`--raised` fill** (one step off the host
  surface — on the dark homepage that IS production's inverse-secondary
  stripe), `--spacing-40` vertical rhythm. SURFACE-RIDING: the scope class
  goes on the consumer's section, never the bar.
- `__wrapper` — `--container-max` row, `--spacing-40` gaps, wraps.
- `__item` — **THE V2 SKIN**: 4px `--brand` left rule (production had
  2px) sitting **inside the perex indent** (operator ruling: identical to
  blockquote/perex — text lands at `--flow-indent`); statement voice =
  description font (Space Grotesk), `--body-md-size`, **bold** (production
  was regular), `--text`, `--leading-body`. Border-box 220px wrap floor —
  four statements fit one `--container-max` row; more/narrower wraps
  gracefully.

## Variants

None. 1..n items is content, not a variant.

## Responsive

≤767 (harvested cut): statements stack in a column. Between, the wrap
floor breaks rows naturally.

## Tokens referenced

`--raised` `--brand` `--stroke-4` `--flow-indent` `--spacing-24`
`--spacing-40` `--container-max` `--description-font` `--body-md-size`
`--weight-bold` `--leading-body` `--text`

## Surfaces

Fully surface-riding — this RESOLVES the AIG homepage light-flip
experiment ("applied at ALL widths to preview before deciding"): the bar
has no opinion; the host section picks dark (production home) or light,
and `--raised` + `--text` + `--brand` re-resolve. Gate-asserted on both.

## Known friction

- **THE V2 DELTAS vs production (operator-ruled)**: rule 2px → 4px
  (`--stroke-4`); statements regular → **bold**; item padding 32 → the
  perex indent (`calc(--flow-indent − --stroke-4)` — text at 24px, aligned
  with blockquotes); the band's hardcoded inverse-secondary → `--raised`.
- Adoption: AIG `.homepage-infobar__*` aliases to `.info-bar__*`; the AIG
  light-flip override block and both themes' page.css info-bar blocks die.
- The 220px wrap floor is a DS calibration (production never wraps its 3).
- The rest of the benefits family (cert-cards, blurbs, lp-what cells,
  footer-blurbs) is still awaiting the architecture verdict —
  [BENEFITS-FAMILY-MAP](../proposals/BENEFITS-FAMILY-MAP.md).

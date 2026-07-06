# Segmented control

**Type:** component · **Status:** shipped (promoted 2026-07-04, branch `forms-split`) · **git_path:** `assets/css/components.css#segmented` · **Specimen:** `/?aigds_styleguide=1&item=segmented`

## Intent
The Segmented control is a single-select switch rendered as a CONJOINED BUTTON GROUP: equal-width segments whose 2px borders overlap so adjacent edges read as one shared rule, with exactly ONE active segment filled in the control accent and lifted over its neighbours. Semantically it is a radio group. Reach for it when a small exclusive choice should read as a mode switch — especially when picking a segment reveals a panel of further inputs (the origin instance is the author-publish None / Podcast / Video media switch, where each segment discloses its panel; that disclosure is a composition fact, documented in forms-composition.md, not a segmented style). Do NOT use it for independent multi-pick options (Checkbox), for long option lists (Select), or for plain form questions where a visible labelled Radio group reads better. It is NOT a pill track — no rounded rail, no sliding thumb; it is buttons sharing borders. Promoted 2026-07-04 from the theme-local `.aif-form__toggle` (aifounders theme, `assets/css/author-forms.css`) into canonical CSS using only existing field tokens; the theme markup is now the drift to retire at adoption.

## Anatomy
Class map:

- `.segmented` — required container, plain `display: flex`.
- `.segmented-option` — required, one per segment; a `<button type="button">`. `flex: 1` (equal widths), field paddings, button-small typography, 2px `--field-border` border, `--field-bg` fill. Adjacent segments overlap borders via `margin-left: calc(-1 * var(--stroke-2)))` — "adjacent segments share ONE 2px rule, not two".
- `.segmented-option--active` — required on exactly one segment: `--control-accent` fill, `--control-accent-ink` text, `z-index: 2` (lifted over the shared borders).

From the specimen (`inc/styleguide.php` → `aigds_sg_item_segmented()`):

```html
<div class="segmented" role="tablist" aria-label="Media type">
  <button type="button" class="segmented-option segmented-option--active">None</button>
  <button type="button" class="segmented-option">Podcast</button>
  <button type="button" class="segmented-option">Video</button>
</div>
```

The specimen also shows a two-segment group and a disabled segment (`<button … disabled>`). Active-segment switching is not shipped as DS JS — wiring is an adoption concern.

## Variants
- No drawing variants. Segment count is free (specimen shows 2 and 3).
- **Scale** — padding reads `--field-pad-y/-x`, so the control gains the two-scale behaviour the theme-local original lacked: wrap in `.form-scale-small` for the compact size.

## States
- **Default** — `--field-bg` fill, `--field-border` border, `--text`.
- **Hover** — `:hover:not(.segmented-option--active):not(:disabled)`: border → `--field-border-strong`, bg → `--bg-alt`, `z-index: 1`. Hover deliberately skips the active AND disabled segments ("no affordance when the option can't be chosen").
- **Active** — `.segmented-option--active`: `--control-accent` fill, `--control-accent` border, `--control-accent-ink` text, `z-index: 2` (mirrors the checked checkbox / selected calendar day).
- **Focus** — `:focus-visible`: 2px `--link` outline, 2px offset, `z-index: 3`.
- **Disabled** — `:disabled`: text → `--disabled-text`, `cursor: not-allowed`, no hover.

## Responsive
No media query targets the segmented control. Because its padding rides the field-scale tokens, a `.form-scale-small` instance relaxes back to LARGE at `max-width: 767px` or `(pointer: coarse)` like every field. Segments `white-space: nowrap` — long labels don't wrap, they force width.

## Tokens referenced
`--field-pad-y`, `--field-pad-x`, `--field-bg`, `--field-border`, `--field-border-strong`, `--stroke-2`, `--stroke-style-solid`, `--button-small-font`, `--button-small-size`, `--button-small-weight`, `--text`, `--bg-alt`, `--transition-fast`, `--control-accent`, `--control-accent-ink`, `--link`, `--disabled-text`

## Surfaces
All colors are surface roles: `--field-bg`/`--field-border` adapt via the dark scopes, and `--control-accent`/`--control-accent-ink` are remapped by the brand scope (black fill, paper ink on brand) so the active segment never disappears. The styleguide's Form composition tab shows the control inside the brand-surface job-board filter (Remote / Hybrid / On-site) with identical markup.

## Known friction
- Promotion mapping (CSS comment): old theme values → DS tokens as `bg-secondary→field-bg`, `border-default→field-border`, `border-strong→field-border-strong`, `primary-button-bg→brand`, `text-button→text-on-brand`; button-sm type kept. The theme-local `.aif-form__toggle` is drift to retire at adoption (point `page-author-publish.php` at the canonical classes — VECTOR-DS §10).
- The negative-margin border join means z-index is load-bearing: hover=1, active=2, focus=3. Adding stacked contexts inside segments can break the shared-rule illusion.
- The specimen uses `role="tablist"` on the container; VECTOR-DS §6 calls it "semantically a radio group" — pick one ARIA pattern at adoption and wire real keyboard behaviour (none is shipped).

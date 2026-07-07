# Checkbox

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#selection-controls` · **Specimen:** `/?aifds_styleguide=1&item=checkbox`

## Intent
Checkbox is the control for INDEPENDENT options — zero, one, or many may be on at once — and for a single boolean opt-in. Reach for it when choices don't exclude each other (job-board "Employment type: Full-time / Contract / Internship" filters) or when one yes/no toggle must be explicit. Do NOT use it for mutually exclusive choices — that is Radio (or Segmented control when the choice drives disclosure); do NOT use a bare checkbox for legal/GDPR agreement — that is the Consent variant, which has its own doc and its own quieter voice. Checkbox, Radio, and Consent share ONE CSS system (`.selection-*`) but are three separate decisions — the stylesheet unifies them, the decision space separates them (VECTOR-DS §4). Contract in one breath: the native input is visually hidden and the styled chip (`.selection-control`) IS a field — it reads the `--field-*` surface roles, so it adapts to any background; checked fills the chip with `--control-accent` and draws a tick in `--control-accent-ink`; a checkbox GROUP carries the same label + helper scaffolding as a text input, so a form reads consistently whatever the control.

## Anatomy
Class map:

- `.selection-group` — column of items, `gap: var(--spacing-24)`. (`.selection-row` is the horizontal wrap variant, `gap: var(--spacing-32)`.)
- `.selection-item.selection-item--checkbox` — required; a `<label>` wrapping input + chip + content; pointer cursor, `gap: var(--selection-gap)`.
- `.selection-input` — required; the native `<input type="checkbox">`, visually hidden (absolute, opacity 0, zero size).
- `.selection-control` — required; the drawn chip: `--selection-size` square, `--field-bg` fill, 2px `--field-border-strong` border. The checked tick is a `::after` pseudo-element — no extra markup.
- `.selection-content` › `.selection-label` — required; the visible label (body font at `--selection-label-size`).

Group form — labelled and helped exactly like an input (from the specimen, `inc/styleguide.php` → `aifds_sg_item_checkbox()`):

```html
<div class="form-group" role="group" aria-labelledby="cbg-lbl">
  <div class="form-label-row"><label class="form-label" id="cbg-lbl">Employment type</label></div>
  <div class="selection-group">
    <label class="selection-item selection-item--checkbox">
      <input type="checkbox" class="selection-input" checked>
      <div class="selection-control"></div>
      <div class="selection-content"><span class="selection-label">Full-time</span></div>
    </label>
    <!-- more items… -->
  </div>
  <div class="form-helper-row"><span class="form-helper-text">Select all that apply.</span></div>
</div>
```

`role="group"` + `aria-labelledby` name the group for assistive tech (specimen convention).

## Variants
- `.selection-item--checkbox` — this component (square chip, tick glyph).
- Siblings on the same system: `.selection-item--radio` (see radio.md), `.selection-item--consent` (see consent.md).
- **Scale** — wrap in `.form-scale-small`: chip 24px → 20px, label → caption size, gap tightens (token remap, no drawing change).

## States
- **Unchecked (default)** — `--field-bg` chip, `--field-border-strong` border (the harvested default border was too faint; strengthened by operator ruling 2026-07-03).
- **Hover** — THE INPUT BORDER TRANSFORM (operator 2026-07-05: the `--link` jump was too much): chip rests at `--field-border` and hovers to `--field-border-strong` — 1:1 with `.form-control-wrapper`, one idiom across every input component. Disabled chips do not hover (`:not(:has(:disabled))` guard).
- **Focus** — `.selection-input:focus-visible + .selection-control`: 2px `--link` outline, 2px offset.
- **Checked** — chip fills `--control-accent`, border `--control-accent`; `::after` tick drawn in `--control-accent-ink`, sized from `--selection-size` (scales with the chip).
- **Disabled** — chip `--disabled-bg` / `--disabled-border`; label dims to `--disabled-text` (the same token a disabled input's text uses); the whole item gets `cursor: not-allowed`.
- **Error** — `.selection-item--error .selection-control`: border → `--status-error` (inherits the input error border rule).

## Responsive
No media query targets the checkbox directly. Inside `.form-scale-small`, the remapped tokens (`--selection-size`, `--selection-label-size`, `--selection-gap`) relax back to LARGE at `max-width: 767px` or `(pointer: coarse)` — touch devices always get the 24px chip.

## Tokens referenced
`--spacing-24`, `--spacing-32`, `--spacing-4`, `--selection-gap`, `--selection-size`, `--selection-label-size`, `--field-bg`, `--field-border`, `--field-border-strong`, `--stroke-2`, `--stroke-style-solid`, `--transition-fast`, `--link`, `--control-accent`, `--control-accent-ink`, `--disabled-bg`, `--disabled-border`, `--disabled-text`, `--body-md-font`, `--text`, `--leading-body`, `--status-error`

## Surfaces
Selection controls ARE fields (operator 2026-07-03): the chip reads `--field-*` tokens, so it adapts per surface like every input. On the BRAND surface (the AIF job-board filter specimen) the chip stays white with a black rule (field tokens transform) and the CHECKED accent becomes black with a paper tick — `--control-accent` / `--control-accent-ink` are remapped by the brand scope so the check never disappears into the background. Labels follow the surface text color.

## Known friction
- The tick's 2.5px stroke is a calibrated GLYPH stroke (the weight of the checkmark), deliberately off the `--stroke-*` scale — like the 14px bullet arrow (CSS comment). Don't "normalize" it.
- The chip currently "stays a light chip" per the section comment — the fully surface-aware chip is queued together with the `--inverted` alias retirement.
- Checkbox/Radio/Consent share all `.selection-*` rules: a change to the shared chip affects all three docs' components at once.

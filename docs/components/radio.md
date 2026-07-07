# Radio

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#selection-controls` · **Specimen:** `/?aifds_styleguide=1&item=radio`

## Intent
Radio is the control for MUTUALLY EXCLUSIVE options — exactly one of a small, always-visible set (the job-board "Seniority: Any / Senior / Lead" filter is the canonical instance). Reach for it when the user must see all options at once and pick one. Do NOT use it when the option list is long or secondary — that is Select (popover); do NOT use it for independent multi-pick options — that is Checkbox; if the single choice also reveals a panel of further inputs, consider the Segmented control (visually a conjoined button group, semantically still a radio group). Radio shares its entire CSS system (`.selection-*`) with Checkbox and Consent — one stylesheet, three decisions (VECTOR-DS §4). Contract in one breath: the native input is hidden, the round chip is a field (reads `--field-*` surface roles, adapts to any background); checked means a `--control-accent` border plus a `--control-accent` center dot (brand border + brand dot — ONE radio style everywhere, unified with the FluentForms/condensed drawing by operator ruling); a radio GROUP carries the same label + helper scaffolding as a text input.

## Anatomy
Class map (identical to Checkbox except the modifier):

- `.selection-group` / `.selection-row` — vertical (`--spacing-24` gap) or wrapping horizontal (`--spacing-32` gap) layout.
- `.selection-item.selection-item--radio` — required; a `<label>` wrapping input + chip + content.
- `.selection-input` — required; the native `<input type="radio">` (shared `name` per group), visually hidden.
- `.selection-control` — required; the chip, made round by `border-radius: var(--radius-full)` under the `--radio` modifier. The checked dot is a `::after` pseudo-element sized `calc(var(--selection-size) - 10px)`.
- `.selection-content` › `.selection-label` — required visible label.

Group form (from the specimen, `inc/styleguide.php` → `aifds_sg_item_radio()`):

```html
<div class="form-group" role="radiogroup" aria-labelledby="rbg-lbl">
  <div class="form-label-row"><label class="form-label" id="rbg-lbl">Seniority</label></div>
  <div class="selection-group">
    <label class="selection-item selection-item--radio">
      <input type="radio" name="rb-group" class="selection-input" checked>
      <div class="selection-control"></div>
      <div class="selection-content"><span class="selection-label">Any</span></div>
    </label>
    <!-- more items, same name… -->
  </div>
  <div class="form-helper-row"><span class="form-helper-text">Pick one.</span></div>
</div>
```

`role="radiogroup"` + `aria-labelledby` name the group for assistive tech (specimen convention).

## Variants
- `.selection-item--radio` — this component (round chip, center dot).
- Siblings on the same system: `.selection-item--checkbox` (see checkbox.md), `.selection-item--consent` (see consent.md).
- **Scale** — wrap in `.form-scale-small`: chip 24px → 20px, label → caption size (token remap only).

## States
- **Unselected (default)** — `--field-bg` round chip, 2px `--field-border-strong` border.
- **Hover** — THE INPUT BORDER TRANSFORM (operator 2026-07-05: the `--link` jump was too much): chip rests at `--field-border` and hovers to `--field-border-strong` — 1:1 with `.form-control-wrapper`, one idiom across every input component. Disabled chips do not hover (`:not(:has(:disabled))` guard).
- **Focus** — `.selection-input:focus-visible + .selection-control`: 2px `--link` outline, 2px offset.
- **Selected** — border → `--control-accent`; `::after` dot filled `--control-accent` (brand border + brand dot; the chip background stays the field bg — unlike the checkbox's full fill).
- **Disabled** — chip `--disabled-bg` / `--disabled-border`; label → `--disabled-text`; item `cursor: not-allowed`.
- **Error** — `.selection-item--error .selection-control`: border → `--status-error` (shared selection rule).

## Responsive
No media query targets the radio directly. Inside `.form-scale-small`, the selection tokens relax back to LARGE at `max-width: 767px` or `(pointer: coarse)`.

## Tokens referenced
`--spacing-24`, `--spacing-32`, `--spacing-4`, `--selection-gap`, `--selection-size`, `--selection-label-size`, `--field-bg`, `--field-border`, `--field-border-strong`, `--stroke-2`, `--stroke-style-solid`, `--radius-full`, `--transition-fast`, `--link`, `--control-accent`, `--disabled-bg`, `--disabled-border`, `--disabled-text`, `--body-md-font`, `--text`, `--leading-body`, `--status-error`

## Surfaces
Same as all selection controls: the chip reads `--field-*` roles and adapts per surface; labels follow the surface. On the BRAND surface (AIF job-board filter specimen) the selected accent is remapped by the brand scope (`--control-accent` → black) so the dot never disappears into the brand background. The styleguide's Form composition tab also shows an unchecked radio on DARK — "field tokens adapt the chip".

## Known friction
- The checked drawing was UNIFIED with the FluentForms/condensed drawing by operator ruling: brand border + brand dot, ONE radio style everywhere (CSS comment) — don't reintroduce a filled-circle variant.
- The dot size `calc(var(--selection-size) - 10px)` is a fixed inset, so the ring-to-dot proportion shifts slightly between the 24px and 20px chips.
- Shared `.selection-*` CSS: changes ripple to Checkbox and Consent.

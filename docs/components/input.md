# Input

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#forms` · **Specimen:** `/?aigds_styleguide=1&item=input`

## Intent
Input is the text field — the element you reach for whenever the user must type free-form text: an e-mail, a name, a search phrase, a URL. It is the anchor atom of the forms domain: its wrapper/label/helper/error/disabled system is the contract every other field-shaped control reuses. **Textarea is a variant of Input, not a sibling** — multi-line text shares the entire `.form-group` scaffolding and only swaps in `.form-control-wrapper--textarea` (VECTOR-DS §6/§10). Do NOT use Input when the answer set is known and finite — use Select (pick one from a list), Radio (small always-visible exclusive set), Checkbox (independent options), or Datepicker (a date). The contract in one breath: field colors are surface roles (`--field-*` tokens), so the same markup adapts to light, dark, and brand backgrounds; the field exists in two sizes (LARGE token root, SMALL via the `.form-scale-small` scope) and SMALL relaxes back to touch size on narrow viewports or coarse pointers; error and disabled are group-level states, not ad-hoc styling.

## Anatomy
Class map (nesting order):

- `.form-group` — required root; column flex, `gap: var(--spacing-8)`. Modifier: `.form-group--error`.
- `.form-label-row` — optional; holds `.form-label` (caption voice, `--text-secondary`) and the optional `.form-mandatory` marker (`*`, colored `--deep` — BRAND DEEP, NOT RED: both themes' FF overrides comment "brand deep instead of red"; `--status-error` is reserved for actual errors).
- `.form-control-wrapper` — required; the visible field box (bg, 2px border, padding, transitions). Variant: `.form-control-wrapper--textarea` (aligns content to `flex-start`).
- `.form-control` — required; the native `<input>`/`<textarea>`, stripped transparent inside the wrapper. `textarea.form-control` gets `min-height: 120px; resize: vertical`.
- `.form-icon` — optional; inline SVG inside the wrapper, sized to `--field-font-size` (never the 24px icon default), `display: block`, colored `--field-placeholder`.
- `.form-helper-row` › `.form-helper-text` — optional; meta voice, `--text-tertiary`; turns `--status-error` inside `.form-group--error`.

Minimal example (from the specimen, `inc/styleguide.php` → `aigds_sg_item_input()`):

```html
<div class="form-group">
  <div class="form-label-row">
    <label class="form-label" for="email">E-mail</label>
    <span class="form-mandatory">*</span>
  </div>
  <div class="form-control-wrapper">
    <input type="email" id="email" class="form-control" placeholder="you@example.com">
    <svg class="form-icon">…</svg> <!-- optional -->
  </div>
  <div class="form-helper-row">
    <span class="form-helper-text">Helper text under the field.</span>
  </div>
</div>
```

Textarea variant:

```html
<div class="form-control-wrapper form-control-wrapper--textarea">
  <textarea class="form-control" placeholder="Longer text…"></textarea>
</div>
```

## Variants
- **Textarea** — `.form-control-wrapper--textarea` + `textarea.form-control`. The only drawing variant.
- **Scale** — not a class on the component itself: wrap any ancestor in `.form-scale-small` to get the SMALL size (token remap; see forms-composition.md). LARGE is the token root, no class needed.
- **With icon** — slot, not variant: add `.form-icon` inside the wrapper.

## States
- **Default** — `--field-bg` fill, `var(--stroke-2)` `--field-border` border.
- **Hover** — wrapper border → `--field-border-strong`.
- **Focus** — `.form-group:focus-within .form-control-wrapper` (and `:focus-within` on the wrapper itself): bg → `--field-bg-focus`, border → `--field-border-strong`.
- **Error** — `.form-group--error`: wrapper border → `--status-error`; helper text → `--status-error`.
- **Disabled** — `.form-control:disabled`: text `--disabled-text`, `cursor: not-allowed`; the wrapper (via `:has(> .form-control:disabled)`) takes `--disabled-bg` / `--disabled-border` and loses its hover ("must LOOK disabled" — operator 2026-07-03).
- **Placeholder** — `--field-placeholder`, `opacity: 1`.

## Responsive
No media query targets Input directly. Sizing rides the field-scale tokens: inside `.form-scale-small`, the remapped tokens relax back to the LARGE values at `max-width: 767px` **or** `(pointer: coarse)` — any touch device gets finger-sized fields regardless of width. The 599 and 1023 cuts are not used by this component.

## Tokens referenced
`--spacing-8`, `--spacing-4`, `--caption-font`, `--caption-size`, `--weight-medium`, `--text-secondary`, `--status-error`, `--field-bg`, `--field-bg-focus`, `--field-border`, `--field-border-strong`, `--field-placeholder`, `--field-pad-y`, `--field-pad-x`, `--field-font-size`, `--stroke-2`, `--stroke-style-solid`, `--transition-fast`, `--body-md-font`, `--body-md-weight`, `--text`, `--meta-font`, `--meta-size`, `--meta-weight`, `--text-tertiary`, `--disabled-text`, `--disabled-bg`, `--disabled-border`

## Surfaces
Fields are micro-surfaces: all `--field-*` and `--disabled-*` names are tier-3 surface roles, re-declared by the dark scopes (`tokens/scopes/dark-1/2/3.json`) and the brand scope (`tokens/scopes/brand.json`). The same markup therefore works inside `.section-dark` and `.section-brand` with zero class changes — the styleguide's Form composition tab shows a large and a small field on dark ("scale × surface compose").

## Known friction
- The legacy `--inverted` form classes remain as **deprecated aliases** until adoption (CSS section header comment) — surfaces made them unnecessary.
- `.form-icon` is deliberately sized to `--field-font-size`, not the 24px icon default: a taller icon made iconed fields outgrow plain ones (52px vs 46px). `display: block` kills the inline-SVG baseline gap. Don't size field icons manually.
- Textarea folded into Input is flagged as "the weak split" in VECTOR-DS §10 — an open question whether it ever earns its own row.

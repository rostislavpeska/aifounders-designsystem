# Select

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#select-dropdown-datepicker` · **Specimen:** `/?aigds_styleguide=1&item=select`

## Intent
Select is the pick-ONE-from-a-KNOWN-set control: the closed trigger looks and behaves like a text field, and clicking it opens a floating menu of options. Reach for it when the option list is finite but too long or too secondary to keep permanently visible. Do NOT use it when the set is small and the choice benefits from being always visible — that is Radio (or Segmented control when the choice also drives disclosure); do NOT use it for free-form text (Input) or multi-select (Checkbox group). Contract in one breath: the trigger is a standard `.form-control-wrapper` (so it inherits every field state and surface behavior), the open menu (`.form-select-menu`) is a light panel floating above any surface, menu items follow the field scale, and open/closed is driven by the `.dropdown--open` class (a small DS script in `js/components/dropdown.js` handles toggle, selection, and click-outside). It shares the popover-panel mechanic with Datepicker (`shares_pattern: popover-panel`) but stays its own component — the intent differs.

## Anatomy
Class map:

- `.dropdown` — required root, `position: relative; width: 100%`. Modifier: `.dropdown--open` (shows the menu). Combine with `.form-group` for label/helper.
- `.form-control-wrapper.form-select-wrapper` — required trigger; adds `cursor: pointer; user-select: none` on top of the standard field box.
- `.form-control` — the value display inside the trigger (a `<span>` in the specimen).
- `.form-icon` — the chevron slot inside the trigger.
- `.form-select-menu` — required popover panel: absolute, `top: calc(100% + 4px)`, `--bg` fill, `--border-strong` 2px border, `--shadow-xl`, `z-index: 100`; `display: none` until `.dropdown--open`.
- `.form-select-item` — one option; padded with the field pads, field font size. Modifier: `.form-select-item--selected`.

Minimal example (from the specimen, `inc/styleguide.php` → `aigds_sg_item_select()`):

```html
<div class="form-group dropdown">
  <div class="form-label-row"><label class="form-label">Label</label></div>
  <div class="form-control-wrapper form-select-wrapper">
    <span class="form-control" style="display:flex; align-items:center;">Option two</span>
    <svg class="form-icon">…chevron-down…</svg>
  </div>
  <div class="form-select-menu">
    <div class="form-select-item">Option one</div>
    <div class="form-select-item form-select-item--selected">Option two</div>
    <div class="form-select-item">Option three</div>
  </div>
</div>
```

## Variants
- **Scale** — wrap in `.form-scale-small` for the SMALL size; the scope wraps the whole dropdown, so menu items shrink with the trigger (menu items read `--field-pad-y/-x` and `--field-font-size`). No other drawing variants exist.

## States
- **Closed / open** — `.form-select-menu` is `display: none`; `.dropdown--open .form-select-menu` shows it.
- **Trigger states** — inherited wholesale from the field wrapper: hover (border → `--field-border-strong`), focus-within (bg → `--field-bg-focus`), error via `.form-group--error`, disabled via `:disabled` machinery (see input.md).
- **Item hover** — `.form-select-item:hover` → `--bg-alt` fill.
- **Item selected** — `.form-select-item--selected` → same `--bg-alt` fill.

## Responsive
No media query targets Select directly. The trigger and menu items scale via the field-scale tokens; inside `.form-scale-small` they relax to LARGE at `max-width: 767px` or `(pointer: coarse)`.

## Tokens referenced
`--field-bg`, `--field-bg-focus`, `--field-border`, `--field-border-strong`, `--field-placeholder`, `--field-pad-y`, `--field-pad-x`, `--field-font-size`, `--stroke-2`, `--stroke-style-solid`, `--transition-fast`, `--bg`, `--bg-alt`, `--border-strong`, `--shadow-xl`, `--body-md-font`, `--text`, `--spacing-8`

## Surfaces
The trigger adapts per surface like every field (`--field-*` roles remap in the dark and brand scopes). The popover is deliberately different: per the CSS section comment, "popovers are light panels floating above any surface" — the menu reads `--bg` / `--border-strong` / `--shadow-xl` and presents as a light-1 panel rather than blending into the section behind it.

## Known friction
- Open/close, selection, and click-outside behavior live in `js/components/dropdown.js` — the markup alone renders a permanently closed (or, with `.dropdown--open` hardcoded, permanently open) control.
- The specimen's value display carries inline `display:flex; align-items:center` on the `.form-control` span — the base `.form-control` class was written for native inputs.
- Where the harvested themes diverged, AIF's strong menu border won (CSS comment: "AIF picks where the themes diverged: strong menu border").

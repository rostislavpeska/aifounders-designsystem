# Forms composition

**Type:** pattern · **Status:** shipped · **git_path:** `assets/css/components.css#forms` (sub-blocks: field scale, input pair) · **Specimen:** `/?aifds_styleguide=1&item=form-composition`

## Intent
Forms composition is the PATTERN row — the cross-cutting laws that make the individual form atoms (Input, Select, Datepicker, Checkbox, Radio, Consent, Segmented control, File dropzone) behave as ONE field system. Reach for this row when the question is not "which control" but "how do controls compose": how a form scales, how a field and a button conjoin, how labels/helpers/errors read consistently across control types, how a switch reveals its panel, how fields sit on dark or brand backgrounds. The model is one field system on three axes (styleguide statement): **ELEMENT** (each control is its own component), **SCALE** (LARGE is the token root; SMALL is the `.form-scale-small` scope that remaps size tokens — one drawing, sizes only, never family or color), **SURFACE** (field colors are surface roles, so every element adapts to any background). States (hover / focus / error / disabled / checked) ride on top of all three axes. Do NOT reach here for a single control's anatomy — read that component's doc; do NOT treat third-party form engines (FluentForms etc.) as DS elements — at adoption each site maps the engine's selectors onto these tokens and picks a scale by scope, owning no values.

## Anatomy
The pattern is made of conventions plus three shipped primitives.

**0. THE FORM STACK — `.form-stack`** (operator 2026-07-05: "clean form,
design-system based" — a modal demo hand-rolled the rhythm wrong and
exposed that the stack had no name). The named composition wrapper for a
whole form: flex column, `--spacing-16` group-to-group rhythm (production's
FF `12→20px` responsive margins + the forms-batch demos' 16 normalized to
one gap, GM), buttons keep natural width (`> .btn { align-self:
flex-start }`). EVERY form composes as `.form-stack` › `.form-group`s +
selection rows + a SIZED submit — **a ladder rung is REQUIRED on the
submit; the base `.btn` has no dimensions.** Consumers never hand-roll
form gaps again; the modal, the comments forms, and the specimens' own
demo columns all ride it.

```html
<div class="form-stack">
  <div class="form-group">…</div>
  <div class="form-group">…</div>
  <label class="selection-item selection-item--checkbox selection-item--consent">…</label>
  <button type="submit" class="btn btn--primary btn--md">Submit</button>
</div>
```

**1. Field scale — `.form-scale-small`** (operator law 2026-07-03). Every form element exists in TWO sizes from ONE root. LARGE is the token root: `--field-font-size`, `--field-pad-y`, `--field-pad-x`, `--selection-size`, `--selection-label-size`, `--selection-gap` (base tier, `tokens/base.tokens.json`). SMALL is a scope that remaps them:

```css
.form-scale-small {
  --field-font-size: var(--caption-size);
  --field-pad-y: var(--spacing-8);
  --field-pad-x: var(--spacing-12);
  --selection-size: 20px;
  --selection-label-size: var(--caption-size);
  --selection-gap: var(--spacing-8);
}
```

Scale = scope, exactly like surfaces for color. Wrap any subtree; same markup, smaller fields.

**2. Input pair — `.input-pair`** (operator 2026-07-03). A layout primitive conjoining ANY input + ANY button. Pairing changes ONLY layout facts: the field grows, the right border collapses at the join, the action stretches to match; on mobile it stacks full-width and the border restores. Generalized from the newsletter harvest (`hero-aif__form` / footer-dark / lp-sticky become consumers at rationalization).

```html
<div class="input-pair">
  <div class="form-control-wrapper">
    <input type="email" class="form-control" placeholder="Your e-mail">
  </div>
  <button type="submit" class="btn btn--lg btn--primary">Subscribe</button>
</div>
```

**3. Label / helper / error / mandatory conventions.** Every control type carries the SAME `.form-group` scaffolding: `.form-label-row` (`.form-label` + optional `.form-mandatory` asterisk in `--deep` — BRAND DEEP, NOT RED: both themes' FF overrides comment "brand deep instead of red"; `--status-error` stays reserved for actual errors, operator 2026-07-05) above, `.form-helper-row` › `.form-helper-text` below. Selection groups slot `.selection-group` between them (with `role="group"`/`role="radiogroup"` + `aria-labelledby`) so a form reads consistently whatever the control. `.form-group--error` paints the wrapper border and helper text `--status-error`; selection chips inherit the same border via `.selection-item--error`.

**4. Focus-within.** Focus is styled on the WRAPPER, not the input: `.form-group:focus-within .form-control-wrapper, .form-control-wrapper:focus-within` → `--field-bg-focus` bg + `--field-border-strong` border. Selection controls use `:focus-visible` on the hidden input to outline the chip in `--link`.

**5. Disclosure.** One control drives which composed block shows: the segmented None / Podcast / Video switch reveals its matching panel (audio → dropzone, video → URL input). The disclosure is a composition fact — the segmented control itself carries no reveal styling.

## Variants
- Scale scopes: LARGE (token root, no class) and SMALL (`.form-scale-small`). The only two — the closed set is the law.
- `.input-pair` has no variants; it accepts any field wrapper + any `.btn`. The production instances are the newsletter captures: AIF `btn--lg btn--primary` on BRAND (auto-darkens), AIG `btn--md btn--tertiary` on DARK — ONE component, ZERO newsletter-specific button styles (DECISIONS.md).

## States
The pattern defines no states of its own; it standardizes how the atoms' states compose:
- error is group-level (`.form-group--error`) and cascades to wrapper + helper; selection mirrors it with `.selection-item--error`;
- focus is wrapper-level (`:focus-within`);
- disabled is surface-aware (`--disabled-bg/-border/-text`) and identical across input text and selection labels.

## Responsive
- **Field-scale relax** (`assets/css/components.css`, the `.form-scale-small` media block): at `max-width: 767px` **or** `(pointer: coarse)`, every remapped token relaxes back to the LARGE values — a tablet or touch laptop is wide but still needs finger-sized fields, so the relax keys off `pointer: coarse`, not width alone. Result (styleguide note): a small field is ~36px for mouse users, growing to the ~46px comfortable touch target the moment a coarse pointer is present; the icon rides `--field-font-size` so it scales too. LARGE is already touch-sized — only SMALL relaxes.
- **Input pair**: base (below 600px) is a stacked column with `gap: var(--spacing-8)`; at `min-width: 600px` it becomes a row with `gap: 0`, the field's `border-right: 0` (the join), and the button stretching to the field height.
- The 1023 cut is not used anywhere in the forms system.

## Tokens referenced
`--field-font-size`, `--field-pad-y`, `--field-pad-x`, `--selection-size`, `--selection-label-size`, `--selection-gap`, `--caption-size`, `--body-md-size`, `--spacing-8`, `--spacing-12`, `--spacing-16`, `--status-error`, `--deep`, `--field-bg-focus`, `--field-border-strong`, `--link` (plus, transitively, every token the composed atoms consume)

## Surfaces
The SURFACE axis is half the pattern: all `--field-*` and `--disabled-*` names are tier-3 roles re-declared by `tokens/scopes/dark-1/2/3.json` and `tokens/scopes/brand.json`; `--control-accent`/`--control-accent-ink` remap on brand (black fill, paper ink) so checked accents never disappear into the brand background. The specimen proves composition: fields and selection controls on DARK ("scale × surface compose" — a small field on dark exercises both axes at once), and the AIF job-board filter on BRAND (checkboxes, radios, segmented, all SMALL, markup identical to every other surface — only color transforms).

## Known friction
- Third-party form engines are adoption concerns, not DS elements (CSS field-scale comment): their overrides map selectors onto these tokens and pick a scale by scope, owning no values. Don't write engine-specific values into the DS.
- The legacy `--inverted` form classes are DEPRECATED aliases pending retirement (CSS forms header) — surfaces replaced them.
- Newsletter capture carries two granted GM exceptions (DECISIONS.md): footer border 1px→2px, and the AIG footer button subtle-filled → tertiary-on-dark. `.btn--newsletter-*` was deleted (never shipped).
- The specimen's newsletter blocks still render the harvested production structure (`.hero-aif__form`, `.aif-ecomail-form--footer-dark`, `.mc4wp-form-fields`) — those context classes are harvest artifacts scheduled to become `.input-pair` consumers at rationalization, not DS API.

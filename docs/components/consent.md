# Consent

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#selection-controls` · **Specimen:** `/?aigds_styleguide=1&item=consent`

## Intent
Consent is the legal / GDPR opt-in: a checkbox underneath, but its own element — a mandatory legal agreement rendered in the SELECTION voice one size quieter. Reach for it whenever the user must explicitly agree to something legal before submitting: processing of personal data, terms of service, marketing consent. It is NEVER pre-checked (specimen rule). Do NOT use a plain Checkbox for legal agreement (wrong voice, wrong stakes) and do NOT use Consent for ordinary preference toggles — that is Checkbox. Consent shares the entire `.selection-*` CSS system with Checkbox and Radio, yet earns its own row: the stylesheet unifies the three, the decision space separates them (VECTOR-DS §4) — its distinct legal intent (mandatory, quiet voice) is the divergence. Contract in one breath: markup is a checkbox item with the `--consent` modifier added; the ONLY style delta is the label (and `.consent-note`) dropping to `--caption-size` — family, weight, and color stay `.selection-label`, links inside follow the standard link idiom with no special color; the error state (mandatory consent left unticked on submit) inherits the input error border on the chip and paints the note in the status-error voice.

## Anatomy
Class map — a checkbox item plus one modifier and one optional element:

- `.selection-item.selection-item--checkbox.selection-item--consent` — required; note the consent modifier is ADDED to the checkbox classes.
- `.selection-input` — required hidden `<input type="checkbox">` (never with `checked` by default).
- `.selection-control` — required chip (identical to Checkbox).
- `.selection-content` › `.selection-label` — required; typically contains inline links to the legal documents.
- `.consent-note` — optional; a second line inside `.selection-content` (used for the error message), same quiet `--caption-size`.
- Error modifier: `.selection-item--error` on the item.

From the specimen (`inc/styleguide.php` → `aigds_sg_item_consent()`):

```html
<label class="selection-item selection-item--checkbox selection-item--consent">
  <input type="checkbox" class="selection-input">
  <div class="selection-control"></div>
  <div class="selection-content">
    <span class="selection-label">I agree to the <a href="#">processing of personal data</a>
      and the <a href="#">terms of service</a>.</span>
  </div>
</label>

<!-- error: mandatory consent left unticked on submit -->
<label class="selection-item selection-item--checkbox selection-item--consent selection-item--error">
  <input type="checkbox" class="selection-input">
  <div class="selection-control"></div>
  <div class="selection-content">
    <span class="selection-label">I agree to the <a href="#">processing of personal data</a>.</span>
    <span class="consent-note">Required — please agree to continue.</span>
  </div>
</label>
```

## Variants
- No drawing variants of its own — Consent IS the variant (of the selection system). Operator law: "the SELECTION voice, one size quieter — nothing else changes."
- **Scale** — works in both field scales (`.form-scale-small` remaps the chip and gap; the consent label is already caption-sized at the root).

## States
- **Default** — unchecked chip, quiet caption-size label. Never pre-checked.
- **Hover / focus / checked / disabled** — inherited unchanged from the shared selection system (see checkbox.md): `--link` hover border, `--link` focus-visible outline, `--control-accent` checked fill + `--control-accent-ink` tick, `--disabled-*` set.
- **Error** — `.selection-item--error`: chip border → `--status-error` (the SAME rule the field wrapper uses via `.form-group--error`), and `.consent-note` → `--status-error` (mirrors a form helper in error). Used for a mandatory consent left unticked on submit.

## Responsive
No media query targets Consent directly. Chip and gap follow the field-scale tokens; inside `.form-scale-small` they relax back to LARGE at `max-width: 767px` or `(pointer: coarse)`. The label stays caption-sized at both scales (the consent delta applies on top of whatever `--selection-label-size` would be).

## Tokens referenced
`--caption-size`, `--status-error`, plus the shared selection set: `--selection-gap`, `--selection-size`, `--selection-label-size`, `--field-bg`, `--field-border-strong`, `--stroke-2`, `--stroke-style-solid`, `--transition-fast`, `--link`, `--control-accent`, `--control-accent-ink`, `--disabled-bg`, `--disabled-border`, `--disabled-text`, `--body-md-font`, `--text`, `--leading-body`, `--spacing-4`

## Surfaces
Identical to the other selection controls: the chip reads `--field-*` surface roles and the checked accent is the `--control-accent` role, so Consent works unchanged inside `.section-dark` and `.section-brand`. Links inside the label follow the standard LINK IDIOM — no consent-specific link color exists (CSS comment).

## Known friction
- The error affordance is the chip border + note only — there is no group-level `.form-group--error` wiring in the specimen; validation timing (on submit) is an adoption concern.
- Shared `.selection-*` CSS: any change to the checkbox chip or states changes Consent too. Keep the one-delta law (size only) when touching it.
- Do not pre-check: the specimen renders both consent examples unchecked by design.

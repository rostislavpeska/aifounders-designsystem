# Newsletter capture

**Type:** pattern · **Status:** shipped · **git_path:** `assets/css/components.css#newsletter-capture` · **Specimen:** `/?aifds_styleguide=1&item=form-composition`

## Intent
Newsletter capture is the conversion pattern: an email input conjoined flush with a submit button — the input-pair in production. Reach for it wherever a page captures newsletter signups (AIF brand hero, AIG dark footer). The defining law (operator, DECISIONS.md): **ONE component, ZERO newsletter-specific button styles** — the CTA is a PLAIN system button and the SURFACE supplies the look: AIF = `.btn .btn--lg .btn--primary` on the BRAND surface (where primary auto-resolves DARK via the tier-3 button roles — no class needed), AIG = `.btn .btn--md .btn--tertiary` on the DARK surface. The gate asserts this ("newsletter capture uses PLAIN system buttons — no special styles"). Do NOT invent a newsletter button: `.btn--newsletter-*` was deleted (never shipped to any site), and `.btn--primary-inverted` — a dark button for light/brand surfaces whose real use WAS the AIF newsletter CTA on the blue hero — is reclassified out of the button hierarchy and kept only for markup compat until the conversion-kit batch deprecates it. What ships today are the two harvested form contexts (`.hero-aif__form` at 60px, `.aif-ecomail-form--footer-dark` at 52px) styling the REAL production markup, pending full unification into a `newsletter-capture` component in the conversion-kit batch.

## Anatomy
Production HTML uses IDENTICAL structure on both sites (mc4wp/Ecomail):

```html
<!-- brand hero context (AIF) — 60px conjoined -->
<div class="hero-aif__form">
  <form class="aif-ecomail-form mc4wp-form">
    <div class="mc4wp-form-fields">
      <div class="form-control-wrapper">
        <svg class="form-control-icon"><!-- arrow-right, 18px --></svg>
        <input type="email" name="email" class="form-control" placeholder="Your e-mail" required autocomplete="email">
      </div>
      <button type="submit" class="btn btn--lg btn--primary"><!-- send icon --><span>Subscribe</span></button>
    </div>
  </form>
</div>

<!-- dark footer context (AIG) — same structure, 52px, .btn--md .btn--tertiary -->
<form class="aif-ecomail-form aif-ecomail-form--footer-dark">…</form>
```

Class map:
- `form.aif-ecomail-form` + `.mc4wp-form-fields` — the harvested production wrappers (required as-is).
- `.form-control-wrapper` — the bordered field shell holding icon + input; flexes to fill.
- `.form-control-icon` — the leading arrow icon slot (`--text-tertiary` on brand, `--dark-300` on dark footer).
- `input.form-control` — borderless/transparent inside the wrapper; placeholder reads the surface placeholder color.
- `.btn` — a PLAIN hierarchy button; conjoined-form geometry (`align-self: stretch; height: auto; flex-shrink: 0`) comes from the context, not a button style. Both contexts use the send icon inside the button.
- Context classes: `.hero-aif__form` (brand hero, 60px min-heights) | `.aif-ecomail-form--footer-dark` (dark footer, 52px).

## Variants
- **Brand-surface variant** — `.hero-aif__form` context: 60px conjoined row; field colors come from the surface (brand → white bg, strong dark border via `--field-border-strong`); button = `btn--lg btn--primary` which the brand scope's button roles resolve DARK automatically.
- **Dark-surface variant** — `.aif-ecomail-form--footer-dark` context: 52px row; `--black` field on a `--dark-700` 2px border, inverse text/placeholder; button = `btn--md btn--tertiary` (the AIG footer's harvested subtle-filled button → tertiary-on-dark is an intentional adoption delta, GM exception).

## States
- `:focus-within` on the dark footer's `.form-control-wrapper` brightens the border (`--dark-700` → `--dark-600`). Brand-context focus states come from the shared field system; the styleguide notes "focus the inputs — states are real".
- `::placeholder` styled in both contexts (`--text-tertiary` / `--dark-300`).
- Button hover/disabled states are the plain system button's own.

## Responsive
At `min-width: 600px` (the 599 cut) the hero context lays the fields out as a wrapping row with stretched alignment and drops the field wrapper's right border so it joins flush against the button; below the cut the pair stacks and the border restores (the input-pair behavior). The dark footer context is a single flex row at all widths in the shipped CSS.

## Tokens referenced
`--field-border-strong`, `--spacing-8`, `--spacing-16`, `--body-lg-font` (via `--body-lg-*`), `--body-lg-size`, `--body-md-size`, `--body-md-leading`, `--leading-body`, `--leading-none`, `--text`, `--text-tertiary`, `--link`, `--paper`, `--black`, `--dark-300`, `--dark-600`, `--dark-700`, `--stroke-2`

## Surfaces
The pattern is surface-driven by design. On the brand surface (`.section-brand`/`.article-hero`/hero) the field roles resolve to a white field with a strong black border, and `.btn--primary` resolves DARK via the brand scope's `--button-*` roles — the "newsletter law", no class needed (gate-asserted). The dark footer context hard-reads dark palette steps (`--black`/`--dark-700`/`--dark-300`) because it is itself the dark context. Status/validation coloring is not part of this pattern.

## Known friction
- **Interim harvested state**: the `.hero-aif__form` / `.aif-ecomail-form--footer-dark` rules are harvested as-is so the styleguide renders the REAL component; full unification into a `newsletter-capture` component happens in the conversion-kit batch. Expect these class names to be superseded.
- **GM exceptions** (DECISIONS.md): footer field border 1px → 2px (OPERATOR LAW: one component = same 2px border in every context); AIG footer button subtle-filled → tertiary-on-dark.
- The harvested hardcoded `bg-secondary` field background on brand was removed — it bypassed the tokens and "looked cheap on brand"; colors must come from the surface field roles.
- `.btn--primary-inverted` is NOT a hierarchy member (code comment ⚠): kept only for markup compat as the legacy newsletter CTA; DECISIONS.md reclassifies it toward `.btn--newsletter-primary` in the conversion-kit batch, after which the alias is deprecated. Don't showcase or reuse it in dark contexts.
- `.btn--newsletter-*` classes never shipped anywhere — deleted, not deprecated; do not resurrect them.

## Consent note — STANDARD anatomy (operator 2026-07-07)

Every email capture carries the `.mc4wp-consent-note` slot ("By
clicking … you agree to the processing of personal data" + link).
Production ships it on BOTH sites (verified live). Canon:
`--body-md-size`/`--leading-body` and the **TERTIARY voice
`--text-tertiary`** — quiet gray on every surface (gray-600 on light,
dark-300 on the dark band, black on brand — the brand scope maps
`text-tertiary → black`: gray on the saturated surface fails contrast,
and black chrome is that scope's standing rule). The scope carries the flip; the component has
NO per-context recolor. ⚠️ Deliberate delta from production (operator
2026-07-07, two vetoes): the harvested brand-tint cream
(#fffce3/#c1d1d9, production's `--color-primary-neutral-light`) DIES —
"rather grayish than pure white and definitely not a brand color";
`--text-note` was minted and killed the same day (DECISIONS). Links:
inherit + underline on light; the footer-dark skin pops them to `--link`
(production's inverse-link). The slot is optional to RENDER, never to
restyle. GM: margin-top 2→8 (AIF twin), leading 1.7→`--leading-body`.

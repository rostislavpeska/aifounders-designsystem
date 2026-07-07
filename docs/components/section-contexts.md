# Section contexts (surfaces)

**Type:** system · **Status:** shipped · **git_path:** `assets/css/components.css#section-contexts` (scopes in `assets/css/tokens.css` layer 3) · **Specimen:** `/?aifds_styleguide=1&item=surfaces`

## Intent
Section contexts are the surface system: a small set of scope classes that **re-map the semantic token vocabulary** for everything inside them, so identical component markup adapts to any background with zero extra classes. Reach for a surface class whenever a band of the page changes background — a dark footer strip, a brand-colored hero, a subtle support callout — and let inheritance + role re-resolution do the rest (the styleguide calls this "tier 2.5 — surfaces are token-remapping scopes", Carbon layer pattern, harvested from a 57-context census). Do NOT reach for per-component "inverted" variants (`.btn--*-inverted` classes are deprecated aliases — surfaces made them unnecessary), and NEVER write `.section-dark *`-style wildcards to force colors. The governing rule is the **TEXT INVERSION LAW** (operator 2026-07-03, Carbon style): text color is a SURFACE ROLE — a section sets `color: var(--text)` and inheritance does the rest; every explicit color in components.css reads a role. What breaks: a component that sets text to a palette neutral directly freezes on one surface (that is exactly the bug class the wildcard used to paper over) — lint LAW 1 catches it.

## Anatomy
```html
<section class="section-dark">
  <h2>Same markup as on light</h2>
  <p>Text, <a href="#">links</a>, buttons and badges all re-resolve.</p>
  <a class="btn btn--md btn--primary" href="#">Primary adapts</a>
</section>

<!-- brand + support scopes remap tokens only; the consumer paints the bg -->
<section class="section-brand" style="background: var(--bg);">…</section>
```
- `.section-light` / `.section-dark` (components.css) paint their own `background-color` (`--bg` / `--black`) and set `color: var(--text)`.
- `.section-brand`, `.surface-support`, and the `.content-section--*` scopes exist only in `tokens.css` as token-remapping blocks — they set **no properties**, only custom-property deltas; the consumer (or the styleguide chrome) applies `background: var(--bg)`.
- List bullets get one shared rule for `.section-light`/`.section-dark` — the surface supplies `--bullet` (replaced two section-scoped duplicates + a main-scoped dark override).

## Variants
The scope families (tokens.css layer 3 — "on-background transforms: deltas, same names"):
- **light-1** (root default): `:root, .section-light, .form-select-menu, .datepicker-calendar` — popover panels are deliberately pinned light-1.
- **light-2**: `.content-section--secondary` (bg → gray-50; perex border → brand-bright).
- **light-3**: `.content-section--tertiary` (bg → gray-150; raised → white; perex border → brand).
- **dark-1**: `.section-dark, .content-section--dark, .footer, .hero-card` — full inversion: text/border/link/bullet/badge/field/disabled/status roles all remap (e.g. link → brand-bright, status colors → their `-bright` variants, `--text-on-status` flips).
- **dark-2**: `.content-section--dark-secondary, .dark-blurb--secondary, .footer__newsletter-section` (bg → dark-900).
- **dark-3**: `.content-section--dark-tertiary, .persona-card` (bg → dark-850; raised → black).
- **brand**: `.section-brand, .article-hero` — bg → brand; link/border/raised/badge-bg → black; fields become white chips with black rules; `--control-accent` → black with paper ink; `--button-bg` → black (the "newsletter law": primary auto-darkens on brand with no class).
- **support**: `.surface-support, .smart-btn` — bg → support; badge/overlay/perex-border/tertiary-button deltas only.

Choosing: `.section-light` for default page bands; `.content-section--secondary/tertiary` for subtle light banding; `.section-dark` (or `--dark-secondary/tertiary` for layered darks) for inverted bands; `.section-brand` for brand-color heroes/CTA bands; `.surface-support` for the support-tinted accent surface (the smart button IS this scope).

## States
Not applicable — surfaces have no states of their own; they change how component states resolve (e.g. disabled roles remap on dark).

## Responsive
None — surface scopes are width-independent.

## Tokens referenced
The scopes remap (names, not values): `--bg`, `--bg-alt`, `--bg-band`, `--text`, `--text-secondary`, `--text-tertiary`, `--border`, `--border-medium`, `--border-strong`, `--raised`, `--link`, `--link-hover`, `--bullet`, `--overlay-hover`, `--badge-bg`, `--badge-text`, `--perex-border`, `--disabled-bg/-text/-border`, `--field-bg/-bg-focus/-border/-border-strong/-placeholder`, `--button-bg/-bg-hover/-text/-border/-border-hover`, `--button-secondary-*`, `--button-tertiary-*`, `--control-accent`, `--control-accent-ink`, `--text-on-brand`, `--text-disabled`, `--status-success/-warning/-error`, `--text-on-status`, `--icon-smart-accent`, `--selection-bg`, `--selection-text`.

## Surfaces
This IS the surface system. Composition fact: scopes add no new names — the same vocabulary resolves differently per scope (a component written against roles works on every surface unmodified; the buttons specimen proves it with identical hierarchy classes on all four surfaces).

## Known friction
- **The deleted-wildcard law**: `.section-dark *` was a sledgehammer that lost specificity ties by file order (the perex-on-dark bug) and fought icon colors for years. It is DELETED and banned forever — `build/lint-css.mjs` **LAW 3** ("no surface wildcards: `.section-* *` selectors stay banned") makes regression impossible. LAW 1 (text inversion) bans setting text to palette neutrals directly.
- `.section-brand` / `.surface-support` do not paint a background in components.css — forgetting `background: var(--bg)` on a bare scope div renders transparent.
- Popovers (`.form-select-menu`, `.datepicker-calendar`) are intentionally in the light-1 scope list: they float ABOVE any surface as light panels — don't "fix" them to inherit dark.
- `.article-hero` is a brand scope in tokens.css; it also carries the brand link idiom (added by the 2026-07-03 audit — it never had it live).
- Legacy `-inverted` component classes remain only as deprecated markup-compat aliases until adoption.

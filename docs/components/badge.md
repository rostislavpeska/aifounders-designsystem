# Badge

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#badges` · **Specimen:** `/?aifds_styleguide=1&item=badges`

## Intent
A badge is a small inline label — category tag, source attribution, location, promo flag — modeled as 4 independent axes, not a flat variant list (operator model 2026-07-02): 1· color (basic grey | colored category accent), 2· icon (with `__icon` slot | without), 3· behavior (clickable `<a>` with hover glow | static `<span>` with no hover, ever), 4· surface (light | brand/colored background as in article heros). Reach for it to tag or attribute content. Do NOT reach for it as a small button (use `.btn--sm` — badges have no action semantics), and never expect hover feedback from a `<span>` badge — the STATIC BADGE LAW makes all hover rules anchor-only. The harvested variant classes below MAP onto the axes (default = basic; editorial/weekly-summary/signal/location/promo = colored; promo and any `span.badge` = static; inverse/special/special-inverse = surface remaps); replacing them with axis-based classes is on the rationalization queue. Base look: inline-flex, square corners, `--button-small-*` type, hover rendered as an `::after` overlay tinted by the surface-aware `--overlay-hover` role.

## Anatomy
```html
<!-- clickable, basic grey -->
<a href="#" class="badge badge--default">basic (grey)</a>

<!-- with icon slot (12px) -->
<a href="#" class="badge badge--location">
  <span class="badge__icon"><!-- 12px icon svg --></span>
  Prague
</a>

<!-- static: a <span> — inert, no glow, default cursor (source badge) -->
<span class="badge badge--default">static source badge</span>
```

Class map:
- `.badge` — required root, on `<a>` (clickable) or `<span>` (static). Carries an `::after` overlay layer for hover.
- Color/variant class — required in practice (`--default` for basic grey).
- `.badge__icon` — optional 12px flex slot (the harvested 20px base was dead code; 12px is the rendered truth).

## Variants
- `.badge--default` — basic grey: `--badge-bg` / `--badge-text`, regular weight. `span.badge.badge--default` is the non-clickable source badge (overlay display:none).
- `.badge--editorial` — `--lime-support` bg, bold; hover = lime-core alpha via `color-mix`.
- `.badge--weekly-summary` — same recipe as editorial (`--lime-support`, lime alpha hover).
- `.badge--signal` — `--support` bg, bold; hover = the base `--overlay-hover` (its own hover rule was deleted 2026-07-03 as a byte-identical duplicate).
- `.badge--location` — `--magenta-support` bg, bold; hover = magenta alpha via `color-mix` (corrected from a stale pre-rebrand purple `#B148C6`, operator 2026-07-03, GM exception).
- `.badge--promo` — `--secondary-support` bg, bold, `cursor: default`; colored but static BY DESIGN — its anchor hover is `display: none`.
- Legacy surface remaps, in CSS but with no verified surface context (rationalization queue, not showcased): `.badge--inverse` (`--deep`/`--paper`), `.badge--special` (`--black`/`--paper`, bold), `.badge--special-inverse` (`--bg-alt`/`--text`, bold).

## States
- **Hover — anchors only** (STATIC BADGE LAW): `a.badge:hover::after` fills with `--overlay-hover` (dark overlay on light, flipped to the light overlay by dark/brand/support scopes). Colored badges use the accent-overlay idiom instead: `color-mix(in srgb, var(--accent) 15%, transparent)` (no alpha entries in the palette — the overlay follows its accent). `--inverse`/`--special` hover with `--overlay-light`.
- **Static** — `span.badge` gets `cursor: default` and no hover of any kind; the harvested "keep the same bg" hover hack on the source badge was DELETED (it flipped the badge light on dark/brand).
- No focus/active/disabled styling exists.

## Responsive
Nothing. No media queries; badges are `white-space: nowrap` at every width.

## Tokens referenced
`--spacing-6`, `--spacing-8`, `--button-small-font`, `--button-small-size`, `--button-small-leading`, `--transition-fast`, `--overlay-hover`, `--overlay-light`, `--badge-bg`, `--badge-text`, `--weight-regular`, `--weight-bold`, `--deep`, `--paper`, `--black`, `--bg-alt`, `--text`, `--lime-support`, `--lime`, `--secondary-support`, `--support`, `--magenta-support`, `--magenta`

## Surfaces
`--badge-bg`/`--badge-text`/`--overlay-hover` are surface roles: light = grey-300/black/dark-overlay; dark scopes = dark-600/paper/light-overlay; brand + support scopes = black/gray-300/light-overlay — so a default badge is visible everywhere with no extra classes. The article hero (`.article-hero__badges`, a brand scope) is the harvested colored-badge surface context: badges flip to dark cards and colored badges keep their category accent as TEXT color (`.badge--editorial`/`--weekly-summary` → `--badge-bg` fill + `--lime` text; hover falls back to `--overlay-hover` = light overlay). The default-badge override there was deleted (2026-07-03 audit) because the brand scope's roles already resolve it — the gate ruled the scope's gray-300 beats the harvested paper text.

## Known friction
- **Hover on a `<span>` never works by design** — do not "fix" a static badge by adding hover; make it an `<a>` if it is genuinely clickable.
- The flat variant list is legacy: the axis model is the intent; axis-based classes are queued but not shipped, so new consumers still pick from the harvested variants.
- `--inverse`/`--special`/`--special-inverse` have no verified surface context — avoid in new work until rationalized.
- The colored-accent-as-text remap on the article hero has no semantic role yet (raw `--lime` palette read; "no semantic role for the colored axis yet" per code comment).
- Accent hover overlays use `color-mix` alpha because the palette has no alpha tints — adding an alpha role needs operator sign-off (DECISIONS.md 2026-07-03).

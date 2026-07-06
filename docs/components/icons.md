# Icons

**Type:** system · **Status:** shipped · **git_path:** `inc/icons.php` + `assets/css/components.css#icon-utilities` · **Specimen:** `/?aigds_styleguide=1&item=icons` (catalog) · `/?aigds_styleguide=1&item=icon-system` (tokens)

## Intent
The icon system is a single PHP helper, `aigds_icon()`, returning inline SVG from a fixed catalog (the UNION of both themes' sets — 36 harvested icons + 15 operator-approved Lucide imports, batch 2) plus the CSS utilities that size and stroke them. Reach for it for ANY pictogram in DS or theme markup — buttons, badges, fields, nav — always by slug through the helper, never by hand-rolled SVG (the Lucide batch exists precisely to retire the inline SVGs both live sites papered over gaps with). Do NOT invent new icons (hard rule in the file header: "DO NOT INVENT NEW ICONS") and do not bake colors in: the **COLOR LAW** (operator 2026-07-02) says icons are strictly color-agnostic — every icon defaults to `currentColor` and recolors from CSS context; only `colored`-type icons carry baked accent fills. What breaks: recoloring by targeting SVG internals fights the taxonomy; scaling the `colored` smart-button (it is SIZE-LOCKED and needs per-size art variants); and overriding stroke-width inline (the CSS stepped-stroke rules intentionally beat the inline attributes).

## Anatomy
```php
// PHP contract
aigds_icon( 'arrow-right' );                                  // 20px, currentColor
aigds_icon( 'menu', array( 'size' => 24, 'class' => 'x' ) );  // size in px, extra class
aigds_icon( 'check', array( 'color' => 'var(--brand)' ) );    // explicit override only when context can't recolor
```
- Args: `class` (appended to the base `icon` class), `size` (int px, default 20 — sets width/height attributes), `color` (default `null` = `currentColor`).
- Unknown slug returns `''`. `aigds_icon_choices()` returns slug→label pairs for ACF selects; `aigds_icon_slugs()` returns slugs for validation.
- Themes alias at adoption: `aif_icon()` / `aiguild_icon()` → `aigds_icon()`.
- The helper auto-attaches the stroke classes by taxonomy AND size: `outline` → `.icon--stroked` (+ `.icon--stroked-fine` when `size < 16`, `.icon--stroked-heavy` when `size > 32`); `outline-fixed` (check-bold) → `.icon--stroked .icon--stroked-bold`; `shape`/`colored` get neither.

## Variants
**Taxonomy** (operator 2026-07-02):
- `outline` (default) — stroke-based line icons; the stepped-stroke law applies.
- `shape` — solid fill icons (linkedin, x, instagram, bluesky, lightbulb-filled): `fill: currentColor`, no stroke.
- `colored` — multi-color art with accent fills (smart-button, reading `--icon-smart-accent`): untouched by stroke rules and **SIZE-LOCKED** (doesn't scale; per-size art variants required).
- `outline-fixed` — `check-bold`: deliberately thick, exempt from the stepping (always `--stroke-3`).

Catalog (51 slugs): course, arrow-right, calendar, chat, check, circle-check-big, mail-check, arrow-down, download, close, edit, signal, editorial, map-pin, calendar-check, source, rss, user-round, lightbulb, trash-2, lightbulb-filled, share, code-xml, pen-tool, flask-conical, badge-euro, map-pin-check, chevron-down, send, menu, chevron-left, chevron-right, copy, chevron-up, clock, users, external-link, mail, briefcase, circle-alert, info, play, plus, minus, smart-button, check-bold, pin, skills, linkedin, x, instagram, bluesky, web. Geometry swaps kept slugs stable (course → Lucide graduation-cap, skills → Lucide hammer).

## States
None of their own — icons inherit state color from their host (e.g. `.btn--secondary-inverted:hover .icon path` maps the icon to the hover TEXT role).

## Responsive
None — sizes are explicit. Sizing utilities: `.icon--sm` (`--icon-size-sm`), `.icon--md` (`--icon-size-md`), `.icon--default` (`--icon-size-default`), `.icon--xl` (`--icon-size-xl`). Inside buttons, icons are `--icon-size-md` (16px raw inside `.btn--sm` — no token for it); inside badges the `__icon` slot is 12px; inside fields `.form-icon` sizes to `--field-font-size`.

## Tokens referenced
`--stroke-1`, `--stroke-1_5`, `--stroke-3`, `--icon-size-sm`, `--icon-size-md`, `--icon-size-default`, `--icon-size-xl`, `--icon-smart-accent`, `--brand` (arrow defaults resolve via the brand role per `[data-theme]` — no hardcoded brand fallback).

## Surfaces
Fully surface-safe by construction: `currentColor` means the surface's `--text` (or the host component's role) drives the icon; the recoloring-proof specimen shows one outline icon recolored purely by context. Exception: `lightbulb-filled` bakes the AIG yellow/dark pair (the bulb is yellow always, by design), and `smart-button` reads its accent from `--icon-smart-accent`.

## Known friction
- **THE STROKE LAW** (operator 2026-07-04, supersedes constant-1.5): outline icons render a **STEPPED visual stroke by rendered size** — **<16px → `--stroke-1`** (fine) · **16–32px → `--stroke-1_5`** (default) · **>32px → `--stroke-3`** (heavy). Boundary ruled explicitly: 16px itself is 1.5 — only below 16 is fine. `vector-effect: non-scaling-stroke` keeps the width in screen px (no viewBox math); `aigds_icon()` picks the step from its `size` arg (`.icon--stroked-fine/-heavy`), and the `.icon--sm` utility pairs with the fine step for CSS-sized icons. The inline `stroke-width` attributes in the SVG strings are only fallbacks the CSS overrides. Any *scaled* stroked icon elsewhere must use the svg-icon-hover-scale recipe (host scale k + stroke-width calc(S/k)) — the law stands system-wide.
- Some legacy icons carry explicit color notes because the old `.section-dark *` wildcard used to break button-internal colors — that wildcard is dead; rely on context now.
- The chatbot bubble is NOT a DS component (operator 2026-07-03) — its icons live in the themes.
- 20×20, 24×24 and one 18×18 viewBox families coexist (harvest reality); `non-scaling-stroke` is what keeps them visually uniform within a step.
- If an icon is sized by CSS (not the helper's `size` arg), the helper can't pick the step — pair the sizing class with the right stroke class (`.icon--sm` does this automatically).

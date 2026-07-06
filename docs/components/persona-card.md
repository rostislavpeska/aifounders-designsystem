# Persona card

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#persona-card` · **Specimen:** `/?aigds_styleguide=1&item=persona-card`

## Intent

The person card — instructors, editors, authors — a square photo over a
name/role header, bio, and bottom-pinned meta. Reach for it for team/lecturer
grids (production: AIF homepage personas, AIG course lecturers) and for the
article author box. Do NOT reach for it for testimonials (reference card) or
for a bare identity chip (avatar + text inline). The contract in one breath —
and the operator's ruling — is ONE CANONICAL, SURFACE-RIDING CARD:
`.persona-card` was REMOVED from the dark-3 scope map; the card reads roles
(`--raised` fill, text roles) and takes its appearance from the background.
Place it in a dark section for the production dark look; the article author
card is the SAME card on light (the old `--light` override block collapsed
into roles). The avatar stays SQUARE — harvested spec, "no hallucinated
rounding".

## Anatomy

```html
<div class="persona-card">
  <div class="persona-card__avatar"><img src="…" alt=""></div>
  <div class="persona-card__content">
    <div class="persona-card__header">
      <h4 class="persona-card__name">Petr Novák</h4>
      <p class="persona-card__role">AI Engineer, AI Guild</p>
    </div>
    <div class="persona-card__bio"><p>…</p></div>
    <div class="persona-card__socials">
      <a class="persona-card__social-link" href="…"><?php echo aigds_icon( 'linkedin' ); ?></a>
    </div>
    <p class="persona-card__meta">Prague · CZ/EN</p>
  </div>
</div>
```

- `__avatar` — full-width, `aspect-ratio: 1/1`, square, `object-fit: cover`.
- `__content` — `--spacing-24` padding + column gap; `flex: 1` so cards in a
  grid equalize.
- `__header` — NO divider (operator 2026-07-04: AIG's clean version wins —
  AIF's border + padding under the position were space-wasters); name =
  **heading-sm** bundle, role = **description** bundle in `--text-tertiary`.
- `__bio` — **body-md** bundle.
- `__meta` — **meta** bundle in `--text-disabled` at **opacity .5 — the
  WATERMARK voice** (operator 2026-07-05: the location line is a very quiet
  watermark, not a dignified label); `margin: auto 0 0` pins it (bottom
  block).
- `__socials` / `__social-link` — quiet icon links (`--text-tertiary`,
  opacity .5). Hover is DECENT by ruling — a quiet opacity step to .8, no
  color jump (socials are not a primary action). Excluded from the global
  link chain.

## Linked vs unlinked (two versions, operator 2026-07-05)

When the persona HAS a detail page, the **photo and the name become links**;
otherwise nothing is clickable except socials. Pure composition — no new
CSS: the name wraps in `a.card-title-link` (inherits the card voice, no
underline at rest, decent 2px hover) and the photo in `a.card-image-link`
(block-filling wrapper). Both are AIG-canonical utilities already excluded
from the global link chain, so no link-blue ever leaks into the card.

```html
<h4 class="persona-card__name"><a class="card-title-link" href="/persona/...">Petr Novák</a></h4>
<div class="persona-card__avatar"><a class="card-image-link" href="/persona/..." aria-label="Petr Novák"><img …></a></div>
```

## Variants & orientation (container-based — the 2026-07-04 rebuild)

Orientation is NOT a variant — it is CONTAINER-DERIVED
(`docs/proposals/PERSONA-CARD-HORIZONTAL.md`, operator-approved; replaces
the production geometry whose constraints could not all hold):

- Wrap each card in **`.persona-card-slot`** (the query container — a
  container cannot query itself; in a grid, the slot is the cell content).
- **Slot < 560px → vertical** (the only state). **Slot ≥ 560px →
  horizontal**: a grid with a photo track of `clamp(200px, 40cqi, 320px)`.
- **THE PORTRAIT IS FULL HEIGHT — ALWAYS** (operator ruling, final): the
  `img` is ABSOLUTELY positioned inside the photo cell (`position: absolute;
  inset: 0; object-fit: cover`), so it contributes ZERO intrinsic size — the
  content column alone sizes the row and the portrait cover-crops ("zooms")
  to fill it. This kills the circular-sizing trap behind every past failure
  (an in-flow img participates in sizing the very row it should fill).
  `min-height: clamp(200px, 40cqi, 320px)` guards the short-bio case — the
  card is never shorter than the photo column is wide (no letterboxed
  sliver).
- **The bottom block**: the location meta pins to the card bottom
  (`margin-top: auto`); `__socials` sit BELOW the location — the lowest
  element; either one alone still pins (a `~` rule prevents double-auto
  space splitting). Markup order: bio → meta → socials.
- `.persona-card--vertical` — escape hatch: pins vertical at any width.
  (Deliberately NO horizontal pin — a too-narrow horizontal card is never
  correct.)
- `.persona-card--horizontal` — legacy no-op alias (auto handles it);
  themes drop it at adoption along with all `card-row` orientation
  overrides.
- `--persona-photo-col` — consumer layout knob for the photo track.

## States

Only the social-link hover. The card itself is non-interactive.

## Responsive

Fully container-based — ZERO viewport queries in the component. The 560px
cut is a CONTAINER threshold (photo floor + ~360px readable text), so the
card is correct in any context: a 350px grid cell at a wide viewport stays
vertical; the author-profile edit widget (~200px) self-heals to vertical.
Browser floor: container queries are Baseline 2023; without support the
card simply stays vertical (safe degradation by construction). The vertical
card is fluid — width belongs to the consumer/grid.

## Tokens referenced

`--raised` `--border` `--stroke-1` `--spacing-4` `--spacing-12` `--spacing-16`
`--spacing-24` `--spacing-32` `--heading-sm-font/size/weight/leading`
`--description-font/size/weight/leading` `--body-md-font/size/weight/leading`
`--meta-font/size/weight/leading` `--text` `--text-tertiary` `--text-disabled`

## Surfaces

Fully surface-riding by construction (the ruling): `--raised` + text roles
re-resolve wherever the card sits. Gate-asserted: the same markup paints
differently inside `.section-dark` vs on light, and `tokens.css` no longer
contains a `.persona-card` scope selector.

## Known friction

- **The scope-map change is load-bearing**: `.persona-card` used to BE a
  dark-3 scope selector (`build/build.mjs`). Re-adding it would double-paint
  the card and break the light author card. The gate asserts its absence.
- The old `--light` variant class is theme drift — retire at adoption (the
  light look is free now).
- `heading-sm` is brand-diverged (22px AIF / 20px AIG name size) — intended;
  divergences are data.
- The bundles used here (`description`, `meta`, parts of `heading-sm`) were
  previously "reserved API" — this card is their first DS consumer.

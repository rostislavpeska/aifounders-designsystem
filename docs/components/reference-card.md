# Reference card (testimonial / case study)

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#reference-card` · **Specimen:** `/?aifds_styleguide=1&item=reference-card`

## Intent

Social proof as a card — a person or company vouching for the product. Reach
for it for testimonials (avatar + quote) and case studies (logo + rich
outcome content), rendered by the `[testimonial]` shortcode in production
(both themes). Do NOT reach for it for the author-of-this-content box (that
is the persona card) or for editorial pull-quotes inside prose (that is the
blockquote voice). The contract in one breath — and the operator's ruling —
is ONE CANONICAL CARD whose appearance comes from the BACKGROUND: the dark
testimonial is literally `reference-card section-dark` (the scope class ON
the card, production's own markup pattern), the light case study is the same
card on the page surface; every role re-resolves, zero variant CSS. Identity
block (64px avatar OR logo) + name/subtitle, then an optional quote mark and
the QUOTE voice content (accent font, reading size).

## Anatomy

```html
<div class="reference-card section-dark">   <!-- drop section-dark for the light card -->
  <span class="reference-card__quote" aria-hidden="true"></span>  <!-- optional quote mark -->
  <div class="reference-card__header">
    <div class="reference-card__avatar"><img src="…" alt=""></div>
    <!-- OR: <div class="reference-card__logo-wrapper"><img class="reference-card__logo" …></div> -->
    <div class="reference-card__title-group">
      <h4 class="reference-card__name">Jana Kovářová</h4>
      <p class="reference-card__subtitle">Head of Product, Raiffeisen</p>
    </div>
  </div>
  <div class="reference-card__body">
    <div class="reference-card__content"><p>…quote or rich text…</p></div>
  </div>
</div>
```

- `.reference-card` — the card: `--raised` fill, NO border (operator
  2026-07-05: AIG had a stroke, AIF didn’t — ruled borderless), `--spacing-40`
  padding + column gap.
- `__header` — identity row (gap 40): `__avatar` (64px, `--radius-full`) OR
  `__logo-wrapper` (64px, contain) + `__title-group` (name = heading-xs voice;
  subtitle = 14px accent, `--text-tertiary`).
- `__body` — content only (the old `__icon` slot is RETIRED).
- `__quote` — THE QUOTE MARK (operator 2026-07-05, final): an empty
  `<span class="reference-card__quote" aria-hidden="true">` anywhere inside
  the card, absolutely positioned TOP-RIGHT as the avatar’s MIRROR — same
  row, same 64px box, mirrored inset (the card padding). Renders a Lazzer
  `“` via `::before` in `--brand-tint` (very muted brand, alpha — holds on
  light AND dark). Never disturbs layout; holds position at every
  breakpoint. Replaced the harvested SVG brackets (the `quote-brackets`
  icon stays archived in the catalog).
- `__content` — **THE QUOTE VOICE** (operator 2026-07-05 FINAL — AIG’s
  already-fixed grammar; the generic `.quote-voice` hook was WITHDRAWN: the
  Blue-protocol abstraction was ruled not possible now, the protocol
  distills on its own later). THE RULE: everything speaks `--font-accent`
  at ONE size (`--size-18`) — `p`, `li` (same `--text-secondary` color as
  the text), `strong` (same size, bold, `--text`); **headlines h1–h6 =
  strong as a block** — the SAME size as the text, bold, identical across
  levels; list markers (`ul` arrow masks + `ol` counters) share ONE muted
  color (`--text-disabled`) and counters INHERIT the text’s font/size/
  weight — never bold, never a different face; harvested `--spacing-40`
  indent.

## Variants

None — surfaces replace variants (the ruling). Slots compose: avatar vs
logo, quote mark vs empty slot, plain quote vs rich content. The three
canonical compositions shown in the specimen: **dark testimonial**
(`section-dark` on the card, avatar + quote), **classic personal
testimonial — light** (same markup, no scope class), **case study** (light,
logo slot, rich quote-voice content).

## States

None — non-interactive. Content links are owned by the global link chain.

## Responsive

Harvested (AIG ≤1023, ported without the `!important` armor): the card
STACKS — header and body go column (`--spacing-24` body gap); the quote
mark holds its top-right mirror position (absolute — layout-independent).
1023 is the canonical cut. The 1/2/3+ **testimonials-carousel** wrapper (1 = centered,
2 = grid, 3+ = Apple-swipe carousel under 1024) is a separate composition —
OUT of this row, tracked as a sweep candidate.

## Tokens referenced

`--raised` `--spacing-4` `--spacing-8` `--spacing-16` `--spacing-24`
`--spacing-32` `--spacing-40` `--radius-full` `--heading-xs-font/size/weight`
`--font-accent` `--font-display` `--weight-black` `--size-14` `--size-18`
`--lead-size` `--lead-leading` `--weight-regular` `--weight-bold`
`--leading-relaxed` `--text` `--text-secondary` `--text-tertiary` `--bullet`
`--brand-tint`

## Surfaces

The whole point: fill `--raised`, text roles, quote mark `--brand-tint`
(alpha — muted brand on any fill) — the same markup paints dark inside
`.section-dark` (on the card or an ancestor) and light on the page surface. Gate-asserted: dark and light renders of the
same markup differ in paint, with zero variant classes.

## Known friction

- **The old variant system is GONE** (operator ruling 2026-07-04): production's
  `--case-study` override block (~10 rules) and the AIF newsletter
  `.testimonial-card` skin are theme drift — retire at adoption; header/body
  gaps unified to the testimonial's 40 (case-study's 24 died — GM exception).
- **Fill = `--raised`** (role over value): live fills sat one palette step off
  per surface (#f2f2f2 / #161616) — GM exception, the role wins.
- **Quote voice weight**: production declares Light/300 — the outlawed
  fiction (renders 400); DS encodes `--weight-regular`.
- The case-study link hardcode `#8a6a00` (= `--deep`) died — the global link
  chain owns content links (GM exception).
- `quote-brackets` is HARVESTED art registered into the icon catalog
  (shape type, currentColor) — not an invention.

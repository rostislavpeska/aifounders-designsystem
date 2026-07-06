# Course card

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#course-card` · **Specimen:** `/?aigds_styleguide=1&item=course-card`

## Intent

THE course listing/promo card — the editorial "half image, half content"
unit behind every course placement on AI Guild: homepage course lists,
course archive, lecturer profile, skill detail, and the article "Naše
kurzy" section. Reach for it when a course (or course-like product) is
pitched with an image, display-voice title, and one CTA. Do NOT reach for
it for article/event/signal feeds (preview card) or in-article ads on AIF
(the native promo replaced it there, 2026-06). ONE canonical card;
production's `--editorial` class is a legacy alias that dies at adoption.
**Orientation comes from THE SLOT, not a variant**: wrap the card in
`.course-card-slot` — a slot ≥720px renders horizontal (image left on a
fixed 420px track, centered content), a narrower slot renders vertical
(16/9 image on top, top-aligned content, CTA pinned). Production's
viewport breakpoint AND its count-3/count-4 grid overrides both collapse
into this one rule.

## Anatomy

```html
<div class="course-card-slot">                     <!-- the container the card queries -->
  <article class="course-info-card [course-info-card--inactive]">
    <!-- IMAGE (optional): <a> = linked (hover zoom) · <div> = plain (inactive) -->
    <a class="course-info-card__illustration-lg card-image-link" href="…"><img src="…" alt=""></a>
    <div class="course-info-card__content">
      <p class="course-info-card__eyebrow course-accent--tertiary">AI Academy</p>
      <h3 class="course-info-card__title"><a class="card-title-link" href="…">Course title</a></h3>
      <p class="course-info-card__subtitle">100 hours | 6 weeks</p>
      <p class="course-info-card__description">The pitch, ~30 words.</p>
      <a class="btn btn--md btn--primary" href="…"><?php echo aigds_icon( 'course', array( 'size' => 20 ) ); ?> Course CTA</a>
    </div>
  </article>
</div>
```

- `.course-card-slot` — the container-query wrapper (`container:
  course-card / inline-size`; a container cannot query itself — persona
  precedent). A grid-stretched slot stretches the card (`min-height: 100%`)
  for equal-height rows.
- `.course-info-card` — fill `--bg-base` (pops to the page surface), 1px
  `--border` box, overflow hidden. Vertical (column) by default; **≥720px
  container → horizontal** (live-measured production minimum: a 720px card
  at 768 viewport).
- `__illustration-lg` — vertical: full-bleed 16/9; horizontal: **fixed
  420px track** (operator-ruled, harvested Figma constant) with an
  absolute-fill cover image (the crop zooms, the box never grows). As an
  `<a>`: hover/focus 1.02 zoom; inactive: grayscale + 0.6 opacity, no zoom.
- `__eyebrow` — the mono-label recipe (`--font-mono`, `--size-12`, bold,
  uppercase, `--tracking-label`); default `--magenta`; per-course accent
  classes `course-accent--primary` (`--brand-strong` — production's
  on-white #c9a101 special case IS this token) / `--secondary` /
  `--tertiary` / `--quaternary` (`--lime`). Negative bottom margin nets the
  harvested ~8px eyebrow→title rhythm against the content gap of 12.
- `__title` — **heading-lg voice + the display treatment**:
  `--leading-snug` (1.05) and `--tracking-display` (-0.022em) — the two
  typography rungs minted for this card (operator 2026-07-05). Links use
  `card-title-link`.
- `__subtitle` — description voice, `--text-tertiary` (meta facts:
  hours | weeks).
- `__description` — body voice, ~30 words.
- CTA — the `.btn--md` ladder rung (52px; production's "bigger CTA"
  harvested pad 24 / gap 12 snapped to the rung's 16/8, GM). Active =
  primary, inactive = tertiary (production opens the contact modal).
  Bottom-pinned in vertical mode (`margin-top: auto`); full-width ≤767
  viewport (touch affordance — a device concern, so it stays a media
  query).

## Variants

None. Orientation is the slot's decision; active/inactive is a STATE
(`--inactive`: grayscaled image, `--text-disabled` title/subtitle/eyebrow —
the state outranks the accent class by source order — `--text-tertiary`
description, tertiary CTA, no zoom).

## States

- Linked image hover/focus-visible — 1.02 zoom (`--transition-normal`).
- Linked title hover — the card-title-link idiom.
- Inactive — see above; the image `<a>` becomes a `<div>`.

## Responsive

**THE ORIENTATION CONTRACT**: `@container course-card (min-width: 720px)`
→ horizontal; below → vertical. Consumers never set orientation: the
homepage's full-width slots are horizontal, a 3-up grid's ~360px slots are
vertical, phones are vertical — all from the same rule. Alignment ties to
orientation: horizontal = `justify-content: center` (the homepage editorial
look), vertical = top-aligned + pinned CTA (the kurz grid's equal-height
patches, now canon). Equal-height grid rows: grid stretch + the slot's
`min-height: 100%` card + the pinned CTA align bottoms and CTA rows
(gate-asserted on the specimen's 3-up demo).

## Tokens referenced

`--bg-base` `--border` `--stroke-1` `--spacing-8` `--spacing-12`
`--spacing-24` `--spacing-32` `--transition-normal`
`--heading-lg-font/size/weight` `--leading-snug` `--tracking-display`
`--font-mono` `--size-12` `--weight-bold` `--leading-none` `--case-upper`
`--tracking-label` `--description-font/size/weight/leading`
`--body-md-font/size/weight/leading` `--text` `--text-tertiary`
`--text-disabled` `--magenta` `--brand-strong` `--secondary` `--lime`

## Surfaces

Fill = `--bg-base`: white on EVERY light scope (the article section's
white-on-grey hand-patch is now the canon), black on dark + brand scopes.
Text reads roles. **Eyebrow accents are Tier-1 palette reads** (the accent
mechanism, like the accordion's `--deep`) — unharvested territory on dark;
magenta/lime/brand-strong hold, `--secondary` is the weakest on black.

## Known friction

- **Dead code census (2026-07-05)**: the base 56px-icon card, `--vertical`,
  `--inverted`, `--no-illustration`, `--product` have ZERO markup — CSS-only
  ghosts, not ported. Both AIF CSS blocks are dead (the `[add]` shortcode
  emits `.aif-native-promo` since 2026-06); the AIF yellow `!important` CTA
  override and the `.article-layout__ad-inline` centering rules die with
  them (AIG's blog.css copies of those rules are equally dead).
- At adoption: `--editorial` becomes a no-op alias; the
  `kurz-more-courses--count-*` orientation overrides, the white-fill
  override, and the viewport MQ are deleted — consumers add
  `.course-card-slot` wrappers instead. The homepage/template inline
  `style="align-self: flex-start"` on CTAs is replaced by the card's own
  rule.
- GM exceptions: eyebrow tracking 0.1em → `--tracking-label` (0.08em,
  0.24px delta); CTA snapped to the `.btn--md` rung (pad 24→16, gap 12→8);
  harvested `::selection` grey hardcode DROPPED; card self `margin-bottom`
  DROPPED (consumer spacing); the dead `__body max-width: 620` rule
  DROPPED.
- The 420px track + 720px cut are CALIBRATED constants (operator-ruled,
  live-measured provenance in the section banner).
- Inactive `opacity: 0.6` / `grayscale(100%)` are harvested state constants.

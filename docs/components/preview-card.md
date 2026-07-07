# Preview card

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#preview-card` · **Specimen:** `/?aifds_styleguide=1&item=preview-card`

## Intent

THE listing card — the one component behind every "item in a feed" across
both sites: articles (AIF homepage + archive, AIG archive), events, signals,
job positions (AIG), and the My-Articles management list (AIF author
portal). Reach for it whenever a grid/list previews an entity and links
onward. Do NOT reach for it for self-contained records with aligned labelled
columns (record list), dense comparable values (data table), or social proof
(reference card). The contract — the operator's model — is TWO AXES:
**size** (default = heading-md headline; `--condensed` = heading-sm, the
production `text-h4` override used by compact/events/signals/positions) ×
**slots** (photo · headline · meta · badges · skills · text · actions —
only the headline is required). Production's six variant classes collapse
into these two axes; content differences are composition, not CSS.

## Anatomy

```html
<article class="preview-card [preview-card--condensed]">
  <!-- PHOTO (optional): <a> = linked (hover zoom) · <div> = plain (possible, unused) -->
  <a class="preview-card__photo" href="…"><img src="…" alt=""></a>
  <div class="preview-card__content">
    <h3 class="preview-card__headline"><a class="card-title-link" href="…">Title</a></h3>
    <div class="preview-card__meta">5 Jul 2026<span class="preview-card__meta-separator">|</span>Author: …</div>
    <div class="preview-card__badges"><span class="badge badge--editorial">Editorial</span>…</div>
    <div class="preview-card__skills"><?php echo aifds_icon( 'skills', array( 'size' => 16 ) ); ?> <span><a href="…">Python</a>, …</span></div>
    <p class="preview-card__text">Excerpt…</p>   <!-- signals: a <div> with <p> paragraphs -->
    <div class="preview-card__actions">
      <a class="btn btn--sm btn--link" href="…"><?php echo aifds_icon( 'arrow-right', array( 'size' => 16 ) ); ?> Read more</a>
    </div>
  </div>
</article>
```

- `.preview-card` — flex column, `--spacing-24` gap + bottom padding, bottom
  hairline (`--border-strong`). **No photo → a top hairline + top padding
  appear automatically** (`:not(:has(> .preview-card__photo))` — the
  harvested no-image compensation, zero extra classes).
- `__photo` — `546/306` calibrated ratio (harvested), cover-cropped. As an
  `<a>`: **hover zoom** `scale(1.02)` over `--transition-normal` (harvested;
  focus-visible included). As a `<div>`: plain (possible but unused in
  production).
- `__headline` — the **card-heading** bundle (operator 2026-07-05):
  heading-md SIZE but **Inter on BOTH brands** — heading-md's AIG font is
  Lazzer, which renders terribly at 28px in cards; constant per MECHANISM
  LAW (card/UI element). Condensed = heading-sm (Inter everywhere already). Linked headlines use **`card-title-link`** (chain-excluded, no
  rest underline, decent hover) or stay plain (positions: the CTA is the
  only link).
- `__meta` — the caption voice (Space Grotesk 14, `--text-tertiary`)
  between top+bottom hairlines; **1..n facts — the count is unbounded**
  (operator 2026-07-05: "A / B — can be also C / D — basically infinite");
  **separators are content**
  (`<span class="preview-card__meta-separator">|</span>`). Carries whatever
  the type needs: date/author (articles), date/source (signals),
  company/salary (positions), datetime (events).
- `__badges` — the DS badge row (gap 12): editorial/signal/location/default
  variants per type.
- `__skills` — positions only: the DS `skills` icon (16px, stepped-stroke
  law applies) + comma-separated skill names, linked or plain (links
  inherit color, idiom underline).
- `__text` — description voice (16). **Recommended length: ~30 words
  (articles), ~20 (events); SIGNALS are the ruled exception** — a `<div>`
  carrying full `<p>` paragraphs.
- `__actions` — **Action 1..n** `.btn--sm.btn--link` buttons, bottom-pinned
  (`margin-top: auto`) so action rows align across a grid.
  **DESTRUCTIVE IS ALWAYS LAST** (operator 2026-07-05) — enforced
  MECHANICALLY: `.preview-card__actions .btn--destructive { order: 99 }`,
  so even mis-authored markup renders it last (gate-asserted with an
  out-of-order specimen). **ICON GRAMMAR** (from production):
  `arrow-right` = internal navigate/read · `source` = external
  (`target="_blank"`) · `edit` = edit · `.btn--destructive` =
  delete/unpublish (the management card composes edit + view + delete).

## Variants

- `--condensed` — the smaller headline. That's the whole variant axis;
  everything else is slots.

## States

- Linked photo hover/focus-visible — 1.02 zoom.
- Linked headline hover — card-title-link underline.
- Action buttons carry the btn--link states; destructive reads
  `--status-error`.

## Responsive

None on the card itself — it is fluid; columns are the CONSUMER's grids
(production: 4/2/1 homepage, 2→3@1440 archive, 4/2/1 signals, 3/2/1
positions). The grids' single-line-between-rows `nth-child` divider games
also stay consumer patterns — the card ships its own top/bottom hairline
grammar only.

**THE STACKING CONTRACT** (where past friction lived): in any CSS grid row,
cards stretch to equal height (grid default `align-items: stretch` + the
card's flex column) and `__actions` is bottom-pinned via `margin-top: auto`
— so **every bottom hairline AND every action row aligns across the row**
regardless of content length (one/two-line headlines, 1..n meta facts,
long/short text, photo/no-photo). Gate-asserted on the specimen's 3×3
simulation (`#preview-stack-demo`): per row, all card bottoms equal and all
action-row tops equal. Consumers must NOT set `align-items: start/center`
on preview-card grids — that breaks the contract.

**THE SINGLE SEPARATOR LAW** (harvested from the production signal
archive): stacked rows must NEVER show a double line (row-above bottom +
no-photo row-below top). Production's mechanism — the CONSUMER grid strips
`border-bottom` from every card that is not in the last row, per
breakpoint:

```css
/* production: page.css — .articles-grid--signals (4 → 2 → 1 columns) */
@media (min-width: 1025px) { .grid .preview-card:nth-last-child(n+5) { border-bottom: none; } }
@media (min-width: 641px) and (max-width: 1024px) { .grid .preview-card:nth-last-child(n+3) { border-bottom: none; } }
@media (max-width: 640px) { .grid .preview-card:nth-last-child(n+2) { border-bottom: none; } }
```

The pattern generalizes as `:nth-last-child(n+cols+1)`. Result: a no-photo
row contributes the ONLY line via its own top hairline; a photo row meets
the row above with no line at all (production behavior on the homepage
grids); the final row closes the stack with its bottom hairline. This stays
CONSUMER CSS (the selector math depends on the consumer's column count per
breakpoint — a DS class cannot know it); the specimen's 3×3 demo implements
it as the reference consumer and the gate asserts it.

## Tokens referenced

`--spacing-4` `--spacing-8` `--spacing-12` `--spacing-16` `--spacing-24`
`--stroke-1` `--border-strong` `--card-heading-font/size/weight/leading`
`--heading-sm-font/size/weight/leading` `--caption-font/size/weight/leading`
`--body-md-font` `--description-size` `--description-leading` `--size-14`
`--weight-regular` `--text` `--text-secondary` `--text-tertiary`
`--transition-normal`

## Surfaces

Hairlines read `--border-strong`, text reads roles — the card re-resolves on
scope surfaces (production only shows it on light; unharvested on dark, the
roles hold).

## Known friction

- **The production variant sprawl is DEAD**: `.article`
  `--archive/--editorial/--compact/--events/--signal/--position` +
  `--has-image/--no-image` all alias onto size × slots at adoption (the
  no-image class is replaced by the `:has()` rule).
- The JS-rendered cards (`archive-lazy-load.js`, `signal-archive-lazy-load.js`)
  drift from their PHP twins (h4 vs h3, `--outline` vs `--link` buttons,
  `<p>` vs `<div>` text) — unify to this canon at adoption.
- Headline hover was unified to the card-title-link idiom (production's
  `!important` per-variant link rules die — GM exception).
- Skills line: harvested `line-height: 1.9` → `--leading-relaxed` and
  `margin: 20px` → `--spacing-16` (off-scale snaps, GM exceptions).
- Card `max-width` 608/589 dropped — width belongs to the consumer's grid.
- `preview-card__text` recommended lengths are editorial guidance the DS
  cannot enforce — review surface.

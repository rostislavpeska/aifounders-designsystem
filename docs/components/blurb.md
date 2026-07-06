# Blurb + stack grid

**Type:** component + layout primitive · **Status:** shipped · **git_path:** `assets/css/components.css#blurb` · **Specimen:** `/?aigds_styleguide=1&item=blurb`

## Intent

THE BENEFITS FAMILY, resolved (operator architecture 2026-07-06,
sandbox-judged on the aif-home clone): **one content component + one
universal stacking layout — and NO container component**. The `.blurb` is
every "eyebrow + title + text in a row of N" on both sites; the
`.stack-grid` is how rows of anything stack; the box around them (fill +
crust + bleed) is a **consumer wrapper**, a recipe not a component. This
row retires: the AIG editorial cert-card and its nl-benefits skin, AIF's
old centered icon cert-card, the dark-blurb (hero mini-benefits +
front-page), the footer-blurb, and the lp-what "Šest oblastí" cells.
Info-bar is NOT this component (shipped separately — the
blockquote-grammar statement stripe). Census:
[BENEFITS-FAMILY-MAP](../proposals/BENEFITS-FAMILY-MAP.md).

## Anatomy — the blurb (every slot on/off)

```html
<div class="blurb">
  <div class="blurb__icon">…64px illustration…</div>          <!-- optional -->
  <p class="blurb__eyebrow">Label</p>  <!-- optional; EMPTY = auto-number in a numbered grid -->
  <h3 class="blurb__headline [blurb__headline--md|--lg]">Headline</h3>  <!-- optional -->
  <p class="blurb__text [blurb__text--lg|--sm]">Body copy.</p>
  <div class="blurb__action">…any buttons/links/meta…</div>   <!-- optional, bottom-pinned -->
</div>
```

**CLOSED SETS (operator: "no more")**:
- Headline — three rungs: default **sm** = heading-xs (18) · `--md` =
  heading-sm (22) · `--lg` = **benefit-display** (the approved fluid
  bundle: Inter `clamp(24px,2.4vw,32px)` / extrabold / `--leading-tight`,
  Inter on BOTH brands per the census) + `--tracking-display`.
- Text — **the info-box ladder**: default 16 (`--body-md-size`) · `--lg`
  18 (`--body-lg-size`, 38ch measure) · `--sm` 14 (`--body-sm-size`). One
  shared ladder across info box and blurb; the census's two-font body
  split (SG vs Inter) dies — bodies are Inter.
- Eyebrow — THE MONO ATOM (mono/12/bold/`--tracking-label`/uppercase,
  `--text-tertiary`); content is a label or, when EMPTY inside
  `.stack-grid--numbered`, the auto `01/02/03` counter.
- **NO rule axes on the blurb at all** (operator veto 2026-07-06, "the
  pizza pattern everywhere"): separators are exclusively the STACK GRID's
  duty and are interior-only — the harvested cert rule-tops and the
  lp-what grid border DIE at adoption; an edge line reads as broken.
  Rule-LEFT stays blockquote-family territory (perex, info-bar).
- No alignment axis — left only; AIF's old centered look retires.

## Anatomy — the stack grid

```html
<div class="stack-grid [stack-grid--divided] [stack-grid--numbered]" style="--stack-cols: 3;">
  <div class="blurb">…</div> …
</div>
```

- `.stack-grid` — N-up grid (`--stack-cols` knob, default 3), open gaps
  (40/32); stacks to one column ≤767. Universal — any content, not
  blurb-only.
- `--numbered` — the `01/02/03` counter is a **row duty**: the grid
  resets/increments; empty blurb eyebrows auto-fill.
- `--divided` — **THE PIZZA LAW, everywhere** (operator 2026-07-06): this
  is THE separator grammar of the whole family — benefits rows, the
  newsletter landing, the homepage box, all of it. Separators belong to
  the grid, are **INTERIOR-ONLY** (between objects — never above the
  first row, never on any outer edge; an edge line is crust territory and
  reads as broken), and span only the grid's content extent, never the
  crust. Mechanism: the grid paints `--border` (normal, not strong) and
  its `--stroke-1` gaps SHOW the line; cells paint `--stack-fill` (knob,
  default `--bg` — surface-riding: black cells on a dark section, white
  on light; set `--stack-fill: var(--raised)` inside a raised box) over
  it. Middle cells get full-length lines, edge cells' lines end at their
  edges, multi-row crossings just work. **SINGLE-OWNERSHIP PADDING**
  (operator: doubled edge padding "reads cheap"): the crust (or host
  container) owns the edge inset; cells pad INTERIOR sides only — every
  boundary, edge or separator, gets exactly one 24px breath per side and
  the lines stop at the content extent. Column math = the default 3;
  other counts override the nth pair; full rows assumed (a partial last
  row needs consumer care — preview-grid precedent).

## The box recipe (the "container" that isn't a component)

```html
<div style="background: var(--raised); padding: var(--spacing-24);
            position: relative; z-index: 1;
            margin-bottom: calc(-1 * var(--spacing-80));"> <!-- THE BLEED -->
  <div class="stack-grid stack-grid--divided" style="--stack-fill: var(--raised);">…</div>
</div>
<!-- the NEXT section compensates: padding-top: calc(var(--spacing-80) + …) -->
```

Fill = `--raised` (surface-riding), crust = the wrapper padding (the
pizza's crust — separators never reach it), bleed = production's −80px +
`z-index: 1` with the next section compensating (harvested from
`.aif-dark-section__blurbs`). Consumer CSS by design.

## Variants

None beyond the closed rungs and the two grid modes. Everything else is
slots.

## Responsive

Grid stacks to one column ≤767 (divided separators turn horizontal
automatically — the gap mechanism is orientation-free). The `--lg`
headline is fluid by construction (display class, MECHANISM LAW).

## Tokens referenced

`--spacing-12` `--spacing-24` `--spacing-32` `--spacing-40` `--stroke-1`
`--border` `--bg`
`--benefit-display-font/size/weight/leading` `--heading-xs-*`
`--heading-sm-*` `--body-md-font/size` `--body-lg-size` `--body-sm-size`
`--leading-body` `--leading-none` `--font-mono` `--meta-size`
`--weight-bold` `--tracking-label` `--tracking-display` `--case-upper`
`--text` `--text-secondary` `--text-tertiary` `--illustration-size-64`

## Surfaces

Fully surface-riding: voices read roles, the divided grid's line layer
reads `--border`, cell fill defaults to `--bg` with the `--stack-fill`
knob for raised boxes. Renders on light and dark in the specimen.

## Known friction

- **Adoption census** (all die into this): `.cert-card` (both themes) +
  `.card-row--three` counter wiring + the `nl-benefits` override skin;
  `.dark-blurb` (+ `--secondary`) and the hero-blurbs conjoined row (→
  the divided grid + box recipe); `.footer-blurb`; the lp-what cell CSS
  (the /newsletter `landing_html` DB markup needs the operator's edit,
  same as the sticky bar's). AIG's `.hero-card` course box qualifies for
  the box recipe later — it is currently a dark-1 SCOPE selector, so
  adopting it means removing it from the scope map (persona precedent),
  a separate step.
- GM exceptions: display leading 1.15 → `--leading-tight` (1.1); display
  tracking −0.01em → `--tracking-display` (−0.022 — one display rung of
  the tracking dimension); cert body 1.55 → `--leading-body`; the SG
  bodies (dark-blurb 14, old cert 16) unify onto the Inter ladder;
  footer-blurb's loose 1.6 eyebrow leading normalizes to the mono atom;
  centering dies.
- The `.reveal` scroll stagger stays UN-homed by design — tracked as its
  own behavior-primitive candidate; blurb and grid ship animation-free.
- 38ch on `__text--lg` is the harvested reading measure (calibrated).

# Accordion

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#accordion` · **Specimen:** `/?aigds_styleguide=1&item=accordion`

## Intent

The disclosure card for question-and-answer content — reach for it when a page
carries a list of self-contained questions (FAQ) where the reader scans titles
and expands only what they care about. Two production consumers define it:
the AIF `/newsletter` FAQ and the AIG course-detail FAQ (near-twin CSS in both
themes; canonical markup documented in both themes'
`docs/landing-page-primitives.md`). Do NOT reach for it for content the reader
must see (prose belongs in the article flow), for navigation (links), or for
progressive form sections (forms stay visible). The contract in one breath:
a bordered white card, an 18px extrabold question with a `--deep` arrow that
points right closed / up open, and a measured-height animation that stays
smooth regardless of answer length. Two modes: **independent** (default, the
AIG behavior — several items can be open) and **exclusive** (the AIF
newsletter behavior — opening one closes its siblings), chosen by a wrapper
attribute, not by JS edits.

## Anatomy

The canonical markup (harvested verbatim from both themes' primitives doc):

```html
<!-- independent (default) -->
<div class="accordion">
  <button class="accordion__header" aria-expanded="false">
    <h3 class="accordion__title">Question?</h3>
    <div class="accordion__icon"><?php echo aigds_icon( 'arrow-right', array( 'size' => 24 ) ); ?></div>
  </button>
  <div class="accordion__content">
    <div class="accordion__inner"><p>Answer.</p></div>
  </div>
</div>

<!-- exclusive group (the AIF newsletter FAQ behavior) -->
<div data-accordion="exclusive">
  <div class="accordion">…</div>
  <div class="accordion">…</div>
</div>
```

Class map:
- `.accordion` — the card: `--bg` fill, 1px `--border`, border-box.
- `.accordion__header` — a real `<button>` (required for a11y): full-width
  flex row, `--spacing-24` padding, toggled `aria-expanded`.
- `.accordion__title` — the question; the `heading-xs` bundle verbatim
  (harvested 18/Inter/extrabold/1.35); specificity-bumped `margin: 0` beats
  host heading margins.
- `.accordion__icon` — 24px (`--icon-size-default`) box, `--deep` color,
  arrow-right; rotates −90° (points up) when open.
- `.accordion__content` — the collapsible region: `height: 0` + hidden
  overflow; height animated inline by the JS.
- `.accordion__inner` — the answer body: `body-lg` voice (18/1.7), rich text
  (`<p>` margins handled), `--spacing-24` bottom padding.

Behavior: `js/components/accordion.js` — the AIG engine ported verbatim
(measure `scrollHeight`, animate, clear to `auto` on `transitionend`; close =
snapshot → reflow → 0). The exclusive mode generalizes the AIF newsletter
inline script from its page-specific `.lp-faq` scoping to the
`[data-accordion="exclusive"]` wrapper.

## Variants

None in CSS. The one behavioral axis is the wrapper attribute:
- (no wrapper) — independent toggles (AIG course detail).
- `[data-accordion="exclusive"]` — single-open group (AIF newsletter FAQ).

## States

- **closed** (default) — content at height 0, `aria-expanded="false"`, arrow
  points right.
- **open** (`.accordion--open`, set by JS) — content animates to measured
  height then `auto`, `aria-expanded="true"`, arrow rotated −90°.
- No hover/focus styling in the harvested CSS (the header is a native button;
  focus comes from the global treatment).

## Responsive

Nothing — no media queries in either theme's accordion; the card is fluid
(`width: 100%`). Width constraints belong to the consumer (the Figma-era
`max-width: 844px` was deliberately dropped; the newsletter overrode it to
100% anyway — GM exception).

## Tokens referenced

`--bg` `--border` `--stroke-1` `--spacing-8` `--spacing-16` `--spacing-24`
`--heading-xs-font/size/weight/leading` `--body-lg-font/size/weight/leading`
`--text` `--deep` `--icon-size-default` `--transition-smooth`

## Surfaces

The card reads `--bg`/`--border`/`--text`, so it re-resolves on scope
surfaces. CAUTION: the icon reads `--deep` — a Tier-1 palette color that
scopes do NOT remap (production only ever shows accordions on light
sections; AIF's sits on a tinted section, AIG's on white). Dark-surface use
is unharvested territory — verify contrast before shipping one there.

## Known friction

- **Unifications vs live (GM exceptions, cohort-tile precedent):** AIG's 1px
  card border wins on both brands (AIF live is borderless — its card read
  against a tinted section); AIG's synced icon transition
  (`--transition-smooth`) wins over AIF's `--transition-rotate`; the 844px
  Figma max-width is dropped.
- **AIF ships no accordion JS file** — its newsletter toggle lives in an
  inline page script (exclusive variant). At adoption, point both themes at
  `js/components/accordion.js`.
- The height engine sets inline `height` — do not add CSS `height`/
  `max-height` rules to `.accordion__content`; they fight the animation.
- The header is a `<button>` — keep it one (the a11y contract `aria-expanded`
  is gate-asserted).
- AIG live bumps title specificity to beat `.content-section h3` margins; the
  DS keeps the same `.accordion .accordion__title` bump.

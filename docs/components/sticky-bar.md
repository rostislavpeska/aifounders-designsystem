# Sticky bar

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#sticky-bar` · **Specimen:** `/?aigds_styleguide=1&item=sticky-bar`

## Intent

THE fixed-bottom bar — one primitive behind all six production instances
(census: [STICKY-BAR-MAP](../proposals/STICKY-BAR-MAP.md)): the /newsletter
landing bar, both article newsletter bars, the AIG positions bars, the AIG
course CTA bar, and the AIF preferences save bar (whose `.sticky-bar` name
production itself extracted — the DS keeps it). Reach for it for a
persistent bottom-docked action: email capture, a course CTA, a form-save
control. Do NOT reach for it for interrupts (modal) or inline feedback
(engagement toast). THE DEFINITION is three orthogonal facts: **1 · THE
SHELL** (fixed bottom, surface-riding, quiet edge, slide + visible-only
shadow; surface = a scope class, static or sampled) · **2 · THE BAR RUNG**
(the bar is a CONTROL-SCALE SCOPE, the scale=scope precedent: EVERY
control inside — field, submit, mobile button, CTA — speaks ONE voice,
40px at caption-size; markup carries NO ladder rung on bar buttons) ·
**3 · TYPE = COMPOSITION** (--email / --button / free compositions) with
the --chatbot-clear axis.

## Anatomy

```html
<!-- EMAIL type (article/landing newsletter bars) -->
<div class="sticky-bar sticky-bar--email sticky-bar--chatbot-clear"
     data-sticky-cta aria-hidden="true"
     data-show-anchor=".aif-article-body" data-hide-anchor=".kurz-more-courses"
     data-suppress-key="aif-subscribed" data-sticky-sample>
  <div class="sticky-bar__inner">
    <div class="sticky-bar__pitch"><p class="sticky-bar__consent">By clicking … <a href="…">privacy policy</a>.</p></div>
    <div class="sticky-bar__form form-scale-small">
      <div class="input-pair">
        <div class="form-control-wrapper"><input type="email" class="form-control" placeholder="Your e-mail"></div>
        <button type="submit" class="btn btn--primary">Subscribe</button> <!-- NO rung: the bar supplies it -->
      </div>
    </div>
    <a href="#newsletter-signup" class="btn btn--primary sticky-bar__btn-mobile">Subscribe</a>
  </div>
</div>

<!-- BUTTON type (the course CTA bar) -->
<div class="sticky-bar sticky-bar--button sticky-bar--chatbot-clear" data-sticky-cta aria-hidden="true"
     data-show-anchor=".hero-card__actions" data-show-gate="top" data-hide-anchor="#terminy" data-sticky-sample>
  <div class="sticky-bar__inner">
    <span class="sticky-bar__meta">Next cohort 12. 8. 2026</span>
    <a href="#terminy" class="btn btn--primary">Reserve</a>
  </div>
</div>
```

- `.sticky-bar` — fixed bottom, `--bg` fill + `--text` (SURFACE-RIDING),
  quiet `--stroke-1` `--border` top edge (production ruling: "quiet edge,
  not a banner stripe"), slide via `translateY(100%)` →
  `--transition-smooth`; **shadow (`--shadow-up`, the token minted for
  bottom-docked chrome) only while `--visible`** — the harvested
  phantom-glow fix. z-index 40, always under the modal's 9999.
- `__inner` — `--container-max` row, `--spacing-6` vertical rhythm (slim,
  operator-ruled), nowrap (the pitch shrinks via `min-width: 0` — the
  tablet double-height patch, collapsed).
- **THE BAR RUNG** — `.sticky-bar .btn` and `.form-control-wrapper`
  read the bar's component knobs (`--bar-control-height` 40px CALIBRATED,
  `--bar-control-size` = `--caption-size`): one voice for every control
  (production forced 14px on the desktop submit AND the AIG mobile button;
  AIF's 16px mobile default and btn--sm's 38px CTA were strays — died,
  GM). Buttons carry NO ladder rung; standalone ones sit at the rung via
  min-height, paired ones stretch to the field (relaxing together on
  touch).
- **`--email` type** — `__pitch` › `__consent` (caption-size secondary,
  links ride the global chain) + `__form` (**composes
  `form-scale-small`** — the slim field floors at 40px on desktop pointers
  and relaxes for touch per the forms law) + `__btn-mobile` (hidden ≥1024).
- **`--button` type** — `__meta` (accent bold body text, `nowrap`) + one
  `.btn` (the rung makes it 40px — not btn--sm's 38).
- **Save composition** — the primitive + a status span + a submit button
  (the preferences page); form-state driven, theme JS toggles `--visible`.
- **`--chatbot-clear`** — 100px right clearance for the bubble at EVERY
  viewport (harvested: "same as desktop"); on mobile it also reshapes the
  email button — natural width instead of full (see Responsive).

## Behavior (`js/components/sticky-bar.js`)

The AIG data-anchor engine (the unit-testable pure `shouldShow()`),
extended so every production trigger expresses as data attributes:
`data-show-anchor` (50%-passed gate; EMPTY = always-on) with
`data-show-gate="top"` (the course hero's fully-above gate),
`data-hide-anchor` (fallback: the show-anchor's bottom),
`data-show-fraction`/`data-hide-fraction` (the landing's page-geometry —
its inline DB script dies), `data-suppress-key` (localStorage,
production's `aif-subscribed`). One bar per page; rAF-throttled passive
listeners; `aria-hidden` mirrors visibility.

**THE SAMPLER (`data-sticky-sample`)** — the generalized course-detail
behavior (operator 2026-07-06): at the bar's resting position it reads the
RENDERED background (`elementsFromPoint` → first opaque ancestor →
luminance > 127) and **toggles the `.section-dark` scope class on the
bar** — the bar reads roles, so the 3-layer system re-skins everything.
Sections need NO registration and NO component status: any opaque
background works (scope sections, hardcoded landing DB content, anything).
Re-samples only while visible (the slide-out never color-flashes); any
failure → light (the DS default skin).

## Variants

`--email` · `--button` (types are composition); `--chatbot-clear` (axis).
Surface is never a variant — the sampler or a static scope class.

## Responsive

≤1023 (GM: harvested 900 snapped): the email bar collapses to its anchor
button (pitch + form hide); the button bar drops its meta (harvested
≤1279, snapped into the same cut); inner padding tightens.
**THE CHATBOT AXIS CHANGES THE MOBILE SHAPE** (harvested, AIF vs AIG):

- **no chatbot** → the button goes FULL WIDTH (the AIF article bar);
- **`--chatbot-clear`** → the 100px clearance SURVIVES ("right: chatbot
  clearance, same as desktop") and the button stays natural width,
  left-aligned at the 14px submit size ("NOT full width — the chatbot
  bubble lives bottom-right") — the bubble owns its corner on every
  viewport.

## Tokens referenced

`--bg` `--text` `--text-secondary` `--border` `--stroke-1` `--shadow-up`
`--container-max` `--spacing-6` `--spacing-12` `--spacing-16`
`--spacing-32` `--body-md-font` `--body-md-size` `--caption-size`
`--font-accent` `--weight-bold` `--transition-smooth` `--transition-normal`

## Surfaces

Fully surface-riding — that IS the feature: light (default roles) = the
slim white article skin; `.section-dark` (sampled or static) = the dark
course/landing skin. The specimen's live bar demonstrates the flip over a
dark band.

## Known friction

- **Adoption census** (all six die into this): `.lp-sticky` (landing +
  both article bars) and `.sticky-cta` (course + AIG landings) alias to
  `.sticky-bar`; both themes' `article-sticky.css`/`article-sticky.js`,
  the course bar's inline script + `components.css:3017` block, and the
  landing's inline visibility script are deleted. **The /newsletter
  landing needs a DB edit** (`landing_html`) — operator action.
- GM exceptions: mobile cut 900 → 1023; button-meta cut 1279 → 1023; the
  901–1150 consent-shrink patch → base nowrap + `min-width: 0` (consent
  may wrap to two lines); inner 1280 → `--container-max` (1200); slide
  .35s/.45s → `--transition-smooth`; z-index 90 (course) → 40; the
  landing's tall dark skin + 2px accent stripe die (the slim + quiet-edge
  operator rulings recorded in production comments); consent-link
  inherit-armor dies (global chain); pitch gap 14 → `--spacing-12`;
  button-type meta left-cluster → `space-between`.
- Calibrated constants: 40px slim control floor, 480px form slot, 100px
  chatbot clearance.
- The sampler needs opaque section backgrounds (all production sections
  qualify); a bar over a transparent-to-body stack falls back to light.
- Guests-only rendering stays a PHP include guard in the themes (the DS
  ships the component, not the audience rule).

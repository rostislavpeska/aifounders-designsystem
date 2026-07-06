# Modal

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#modal` · **Specimen:** `/?aigds_styleguide=1&item=modal`

## Intent

THE FORM MODAL — a centered box on a dark scrim carrying a form, with the
page locked behind it. This is how production ships every real modal:
AI Guild's reservation modal (Fluent Forms 4/5/7) and inactive-course
contact modal, AIF's registration modal — **Fluent Forms inside**, which
the themes map onto the DS form system (fluent-forms-override). Reach for
it for any interrupt that captures a form. Do NOT reach for it for
non-blocking feedback (the engagement toast) or persistent CTAs (sticky
bar, mapped). One shape: title, form body, close button — **the close
button is always present**; there is no close-less variant. The AIF
newsletter-modal and author-consent popup are page one-offs, not this
component, not ported.

## Anatomy

```html
<button data-modal-open="my-modal" data-modal-title="Optional per-event title">Open</button>

<div id="my-modal" class="modal" aria-hidden="true">
  <div class="modal__overlay" data-close-modal></div>
  <div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="my-modal-title">
    <button class="modal__close" data-close-modal aria-label="Close"><?php echo aigds_icon( 'close', array( 'size' => 24 ) ); ?></button>
    <div class="modal__content">
      <h2 id="my-modal-title" class="modal__title">Reservation</h2>
      <!-- THE FORM — the DS form system (Fluent Forms markup maps onto it): -->
      <div class="form-stack">
        <div class="form-group">
          <div class="form-label-row"><label class="form-label" for="…">Name</label><span class="form-mandatory">*</span></div>
          <div class="form-control-wrapper"><input type="text" class="form-control"></div>
        </div>
        <label class="selection-item selection-item--checkbox selection-item--consent">…</label>
        <button type="submit" class="btn btn--primary btn--md">Submit</button>
      </div>
    </div>
  </div>
</div>
```

- `.modal` — fixed full-viewport flex center; hidden via
  `opacity/visibility` (animatable, accessible), shown at
  `[aria-hidden="false"]`; fade = `--transition-normal`.
- `__overlay` — the scrim: `color-mix(in srgb, var(--black) 70%,
  transparent)` — the harvested 70% via the no-alpha-in-palette overlay
  ruling (2026-07-03).
- `__container` — the box: `--bg` fill, sharp corners, **656px total**
  (production renders a 560px CONTENT-BOX + 48px padding, live-measured on
  aiguild.cz — converted to border-box; the first cut squeezed the form
  96px narrower). `90vh` + `overflow-y: auto` = the production scroll
  engine — a form taller than the viewport scrolls INSIDE the box;
  `overscroll-behavior: contain` keeps the wheel off the locked page
  (polish over production, which contains only on mobile). No outer inset
  on `.modal` (production has none).
- `__close` — REQUIRED: 40px ghost hit at top/right `--spacing-16`,
  `close` icon 24, `--text-secondary` → `--text` hover.
- `__title` — the **heading-md** bundle (brand fonts, its own 28→22 mobile
  step); openers may override per event via `data-modal-title` (the
  registration modal's harvested behavior).
- `__text` — optional intro paragraph between title and form (body voice,
  `--text-secondary`). **Any DS info box composes into the flow**
  (`.modal__content .info-box` gets the form rhythm) — deadlines, funding
  notes, prerequisites. **Use the condensed `--small` variant in modals**
  (operator ruling — fits the form density).
- Form body — **the DS form system**: `.form-stack` owns the rhythm
  (never hand-rolled gaps), `.form-group` + `.form-label-row` +
  `.form-control-wrapper` fields, `.selection-item--consent`, and a
  **sized** submit (`.btn--primary.btn--md` — the base `.btn` has no
  dimensions; a ladder rung is required). Fluent Forms markup maps onto
  these classes at adoption (the themes' fluent-forms-override already
  does this).
- `body.modal-open` — scroll lock while open.

## Behavior (`js/components/modal.js`)

The AIF registration-modal engine (the only production copy with the full
accessibility contract), generalized: `data-modal-open="<id>"` openers
(+ `data-modal-title`), `data-close-modal` closers + overlay click +
**ESC** (closes the most recently opened), body scroll lock (released only
when no modal remains open), focus moves to the first field (harvested
100ms delay), Fluent-Forms `submission_success` → auto-close after the
harvested 2s. Public API: `window.aigdsModal.open(id, {title}) /
.close(id)`. Per-modal wiring (hidden-field population, AIG's 409-line
4/5/7 form-switching) stays THEME JS on top of this engine.

## Variants

None.

## Responsive

≤599px (GM: harvested 640 snapped — 600–639 viewports still fit the 560
box): **full-screen sheet** — `100dvh` (the iOS visible-viewport fix; vh
fallback) + `overscroll-behavior: contain` (no scroll chaining to the page
behind) — the reservation-modal's harvested stronger-twin treatment —
`--spacing-24` padding with 64px top clearance for the close button.

## Tokens referenced

`--spacing-16` `--spacing-24` `--spacing-48` `--bg` `--black` `--text`
`--text-secondary` `--heading-md-font/size/weight/leading`
`--body-md-font/size/leading` `--transition-fast` `--transition-normal`

## Surfaces

The box reads `--bg`, the scrim reads `--black` (Tier-1, like the
accordion's `--deep` — flagged). Production only opens modals over light
pages; dark scopes are unharvested territory.

## Known friction

- **REBUILD NOTE (operator 2026-07-05)**: the first cut invented a
  close-less `--narrow` "consent dialog" variant — WITHDRAWN, no such
  component exists. The modal is the form modal, full stop.
- **Position-fixed trap**: a modal inside an ancestor with
  `transform`/`filter`/`will-change` is trapped. Canon: modal markup
  mounts at `<body>` level (footer template-part, like production's
  reservation modal). The specimen exploits the trap deliberately for its
  static render.
- GM exceptions: scrim unified to the dedicated modals' 70% (the inline
  contact modal's .6 dies); title 32/700 (+ AIG's Space-Grotesk hardcode
  drift) → the heading-md bundle; fade 0.3s → `--transition-normal`;
  breakpoint 640 → 599.
- Calibrated constants: 656px box (= production's 560 content + 48
  padding, live-measured), 40px close hit, 64px mobile top clearance,
  z-index 9999 (unanimous across the harvest).
- Adoption aliases: `registration-modal__*` / `reservation-modal__*` →
  `modal__*` (`__container` is the box; the inline contact modal's
  `__content`-as-box renames); `data-open-registration` /
  `data-open-contact-form` → `data-modal-open`;
  `window.aifRegistrationModal` → `window.aigdsModal`.

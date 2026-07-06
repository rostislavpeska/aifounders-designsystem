# Datepicker

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#select-dropdown-datepicker` · **Specimen:** `/?aigds_styleguide=1&item=datepicker`

## Intent
Datepicker is the pick-a-date control: a text field with a calendar icon whose click opens a floating month grid. Reach for it whenever the answer is a calendar date — deadlines, birth dates, event dates. Do NOT use a plain Input for dates (no affordance, free-form parsing pain) and do NOT use Select for them (a date set is too large to enumerate). It **shares the popover-panel mechanic with Select** (`shares_pattern: popover-panel` — same absolute panel, same light-surface treatment, same open-class convention) but stays a separate component because the intent differs. Contract in one breath: the trigger field scales with the field scale like any input, but the calendar keeps ONE density at all scales (styleguide ruling: "floating overlay, touch targets"); on the grid, today is a quiet outline, the selected day is a control-accent fill, and outside-month days are muted; `.datepicker--open` shows the panel (showcase JS in `js/components/datepicker.js`).

## Anatomy
Class map:

- `.datepicker` — required root, `position: relative; width: 100%`. Modifier: `.datepicker--open` (shows the calendar). Combine with `.form-group` for label/helper.
- `.form-control-wrapper` › `.form-control` + `.form-icon` — the standard field trigger (calendar icon in the icon slot).
- `.datepicker-calendar` — the popover panel: absolute below the trigger (`top: calc(100% + 4px)`), `--bg` fill, `--border-strong` 2px border, `--shadow-xl`, `z-index: 100`, `padding: var(--spacing-16)`, `min-width: 280px`; hidden until `.datepicker--open`.
- `.calendar-header` — month/nav row; accent font, space-between layout.
- `.calendar-grid` — `grid-template-columns: repeat(7, 1fr)`, `gap: var(--spacing-4)`.
- `.calendar-day-label` — weekday header cell; meta voice, uppercase, `--text-tertiary`.
- `.calendar-day` — one day cell, `aspect-ratio: 1`, centered. Modifiers: `--today`, `--selected`, `--outside`.

Minimal example (from the specimen, `inc/styleguide.php` → `aigds_sg_item_datepicker()`):

```html
<div class="form-group datepicker datepicker--open">
  <div class="form-control-wrapper">
    <input type="text" class="form-control" placeholder="07/03/2026">
    <svg class="form-icon">…calendar…</svg>
  </div>
  <div class="datepicker-calendar">
    <div class="calendar-header"><span>&larr;</span><span>July 2026</span><span>&rarr;</span></div>
    <div class="calendar-grid">
      <div class="calendar-day-label">Mo</div> <!-- ×7 -->
      <div class="calendar-day calendar-day--outside">29</div>
      <div class="calendar-day">1</div>
      <div class="calendar-day calendar-day--today">6</div>
      <div class="calendar-day calendar-day--selected">7</div>
    </div>
  </div>
</div>
```

## Variants
- **Scale** — the trigger field follows `.form-scale-small` like any input; the calendar deliberately does NOT scale (one density, styleguide ruling). No other variants.

## States
- **Closed / open** — `.datepicker-calendar` is `display: none`; `.datepicker--open .datepicker-calendar` shows it.
- **Trigger states** — inherited from the field wrapper (hover / focus-within / error / disabled — see input.md).
- **Day hover** — `.calendar-day:hover` → `--bg-alt`.
- **Today** — `.calendar-day--today`: quiet OUTLINE (`inset 0 0 0 2px var(--border-strong)` box-shadow), `--text`, bold. Deliberately not brand-colored: "Brand-colored text failed contrast on white (yellow especially)" (CSS comment).
- **Selected** — `.calendar-day--selected`: `--control-accent` FILL with `--control-accent-ink` text, bold (Apple/Carbon today-outline/selected-fill pattern).
- **Outside month** — `.calendar-day--outside`: `--text-disabled`, `opacity: 0.5`.

## Responsive
No media query targets the Datepicker. The trigger scales via field tokens (SMALL relaxes at `max-width: 767px` or `(pointer: coarse)`); the calendar panel keeps one density everywhere (`min-width: 280px`, square touch-target cells).

## Tokens referenced
`--field-bg`, `--field-bg-focus`, `--field-border`, `--field-border-strong`, `--field-placeholder`, `--field-pad-y`, `--field-pad-x`, `--field-font-size`, `--stroke-2`, `--stroke-style-solid`, `--transition-fast`, `--bg`, `--bg-alt`, `--border-strong`, `--shadow-xl`, `--spacing-16`, `--spacing-4`, `--spacing-8`, `--font-accent`, `--body-md-size`, `--body-sm-size`, `--weight-medium`, `--weight-bold`, `--meta-font`, `--meta-size`, `--text`, `--text-tertiary`, `--text-disabled`, `--control-accent`, `--control-accent-ink`

## Surfaces
The trigger field adapts per surface via the `--field-*` roles. The calendar panel is a light panel floating above any surface (same rule block as the select menu — "popovers are light panels"); the `--today` cell's `--text` color is annotated in the CSS as safe because "the panel is a light-1 surface". `--control-accent` / `--control-accent-ink` are roles: brand fill with ink on light/dark, remapped by the brand scope so the selected day never vanishes on a brand background.

## Known friction
- `js/components/datepicker.js` marks its selection logic "Basic Selection logic (Showcase only)" — it hardcodes the month when writing the value. Real month navigation/data binding is an adoption concern, not shipped.
- Where the harvested themes diverged, AIF's medium calendar header weight won (CSS section comment).
- The calendar intentionally ignores the field scale — do not wrap expectations of a smaller grid in `.form-scale-small`.

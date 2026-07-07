# Record list

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#record-list` · **Specimen:** `/?aifds_styleguide=1&item=record-list`

## Intent
The record list renders a LIST OF ENTITIES — course cohorts, events, offers — as self-contained `<article>` cards whose labelled fields align into columns across the whole list, so it *reads* like a table while staying semantically a list. Reach for it when each item is a rich record: it carries a CTA button, a status field, optionally a title + rich-text description head. Do NOT reach for it for dense homogeneous values you compare cell-by-cell — that is the **data table** (`.data-table`, a real `<table>` with `th`/`td`); the ratified decision rule (DATA-TABLES.md addendum) is: simple comparable values → data table, self-contained cards with CTA/media/expandable content → record list. The model is ABSTRACT: no baked "cohort" or "capacity" variants — the consumer owns the column layout by setting `--record-columns` (a full `grid-template-columns` value) on the list, the action is just another column, and status is data (a field color modifier), not a variant. Alignment is real table behavior via CSS subgrid: every record shares the list's tracks, so a long value (e.g. a wide button) widens its column for ALL records instead of breaking one. On mobile the grid collapses to a stacked card: label left, value flush-right, action full-width. 0 new design tokens (`--record-columns` is a layout var, not a design token).

## Anatomy
```html
<div class="record-list" style="--record-columns: 1.5fr 1fr 0.9fr 0.8fr 1fr auto;">
  <article class="record">
    <!-- optional head: title + rich-text description, spans the whole card -->
    <div class="record__head">
      <div class="record__title">AI Founders Meetup</div>
      <div class="record__description"><p>An evening of lightning talks… <a href="#">See the agenda</a>.</p></div>
    </div>
    <div class="record__fields">
      <div class="record__field record__field--strong" data-label="Date">4 May - 20 Jun</div>
      <div class="record__field" data-label="Location">Prague</div>
      <div class="record__field record__field--strong record__field--nowrap" data-label="Price">45,500 CZK</div>
      <div class="record__field" data-label="Language">English</div>
      <div class="record__field record__field--success" data-label="Capacity">Filling fast</div>
      <div class="record__field record__field--action"><a href="#" class="btn btn--sm btn--primary">Reserve</a></div>
    </div>
  </article>
</div>
```

Class map:
- `.record-list` — required container; owns `--record-columns` (consumer sets it; fluid default `repeat(auto-fit, minmax(9rem, 1fr))` when unset).
- `.record` — required, one per item, an `<article>`; spans all columns and subgrids the list's tracks; card look = 1px inset-shadow border on `--bg`.
- `.record__head` — optional; full-width strip with a bottom divider.
- `.record__title` — optional inside head; the mono/uppercase label recipe.
- `.record__description` — optional inside head; rich-text body (`<p>`, links allowed), 14px.
- `.record__fields` — required; the N-column field row, subgrids the card's (= the list's) tracks.
- `.record__field` — one per column; the label lives INSIDE the card, above the value, rendered from `data-label` via `::before` (a label-less field — e.g. the action — gets no label).

## Variants
No component-level variants — composition + field modifiers only:
- `.record__field--strong` — bold value
- `.record__field--nowrap` — value never wraps
- `.record__field--success` / `--warning` / `--error` — status field colors (bold, `--status-*`; same convention as the data-table's `.cell--*`)
- `.record__field--action` — holds a CTA; on desktop pinned to the end of its shared column and vertically centered; on mobile a full-width button

Column count/widths are per-consumer via `--record-columns` (styleguide proves it with a 6-column cohort list and a 4-column event list on the same component).

## States
None styled on the component itself — records are not hoverable/selectable. Interactive states belong to the composed `.btn` inside the action field.

## Responsive
At `max-width: 767px` the aligned grid collapses to a plain vertical stack (the AIG production pattern):
- `.record-list` becomes a flex column; `.record` becomes a plain block.
- `.record__fields` stacks with `--spacing-12` gaps; each field becomes one flex row — the `data-label` moves inline (left, nowrap, `margin-right: auto`) and the value is pushed flush to the right edge.
- `.record__field--action` stretches full-width with a top margin; its `.btn` gets `box-sizing: border-box; width: 100%` so the button's own padding stays inside the card.

## Tokens referenced
`--spacing-8`, `--spacing-12`, `--spacing-16`, `--spacing-24`, `--spacing-32`, `--stroke-1`, `--border`, `--bg`, `--font-mono`, `--size-12`, `--size-14`, `--weight-bold`, `--weight-regular`, `--tracking-label`, `--case-upper`, `--body-md-font`, `--text`, `--text-secondary`, `--text-tertiary`, `--status-success`, `--status-warning`, `--status-error`

## Surfaces
Everything reads surface-aware roles: the card border is `--border`, the card fill is `--bg`, labels are `--text-tertiary`, description is `--text-secondary`. Inside dark scopes (`.section-dark` etc.) the status field modifiers re-resolve because the dark scopes remap `--status-*` to the bright variants (`success/warning/error-bright`) — the styleguide renders the cohort list on dark with zero extra classes. The brand scope does not remap status.

## Known friction
- **Subgrid is load-bearing** (RECORD-LIST.md correction, 2026-07-04): round 1 wrongly claimed `fr` tracks align without it. Because the action is an `auto` column, without subgrid a wide button in one record shifts only that record's tracks. Any refactor that drops `grid-template-columns: subgrid` breaks the table-like alignment.
- **The `.record__description p` selector is REQUIRED** (code comment): rich-text descriptions render `<p>`, and a host theme's `main p` rule (18px) would otherwise beat the div's inherited 14px.
- **Mobile action button needs `box-sizing: border-box`** (code comment + fix commits): a content-box `width: 100%` button overflows the card's right padding.
- The occupancy/progress **meter was removed from the core** (operator ruling 3) — it becomes a separate composable component; a record that needs "how full" shows it as a status field or composes the future meter component.
- `--record-columns` is the only new name and it is a layout variable, not a design token — don't move it into the token files.

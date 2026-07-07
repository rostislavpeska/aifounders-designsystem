# Data table

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#data-table` · **Specimen:** `/?aifds_styleguide=1&item=data-tables`

## Intent
The data table renders dense, homogeneous tabular DATA — rows of simple values you scan, compare and (eventually) sort across columns, on a semantic `<table>` with real `th`/`td` relationships. Reach for it for pricing grids, comparison tables, article tables, anything where every row has the same simple cells. Do NOT reach for it when each row is a self-contained entity with rich content — a CTA button, a rich-text description, an expandable body — even if the fields visually line up into columns; that is the **record list** (`.record-list`), which shares the same mono-label vocabulary through tokens but keeps semantics honest. The contract in one breath: mono/uppercase/bold/tracked header, reading-font cells, a 1px full grid on every cell including the header, and a conservative grammar where everything reads through borders + text + status roles — no background fills except the one sanctioned brand-tint signifier — so the same markup holds on light, dark and brand surfaces with zero extra classes. Three sizes (condensed/standard/large), three grid modes (full/plain/banded), 0 new tokens.

## Anatomy
Semantic `<table>` is the canonical markup (the harvested div-grid comparison table was folded in as a variant of this, not kept separate).

```html
<table class="data-table">
  <thead>
    <tr><th>Cohort</th><th>Starts</th><th>Status</th><th class="cell--num">Price</th></tr>
  </thead>
  <tbody>
    <tr><td>Spring — Praha</td><td>4 May</td><td class="cell--success">Open</td><td class="cell--num">12 000</td></tr>
    <tr><td>Autumn — online</td><td>1 Oct</td><td class="cell--error">Full</td><td class="cell--num">9 000</td></tr>
  </tbody>
</table>
```

Class map:
- `.data-table` — required root on the `<table>`. Drives 4 internal size vars (`--dt-pad-y`, `--dt-pad-x`, `--dt-cell-size`, `--dt-head-size`).
- `thead th` — the header recipe is automatic: mono, uppercase, bold, letter-spaced, nowrap. No fill (type + grid carry it).
- `th`/`td` — every cell gets padding + a 1px border on all sides (full grid, header included), left-aligned, middle-vertical-aligned.
- Cell modifiers (optional, per `td`/`th`): `.cell--num`, `.cell--success`, `.cell--warning`, `.cell--error`, `.is-emphasized` (also valid on `tr`).
- `.table-scroll` — optional wrapper `<div>` around the table; makes a wide table scroll horizontally instead of overflowing (see Responsive).

## Variants
**Size axis** (each step only repoints the 4 driving vars):
- default (standard) — 16px cells / 12px header / spacing-12·16 padding
- `.data-table--condensed` — 14px cells / 12px header / spacing-6·8 padding, line-height 1.35 (the article-table density)
- `.data-table--large` — 18px cells / 14px header / spacing-16·24 padding

**Grid axis**:
- default — full 1px grid, every cell including header
- `.data-table--plain` — horizontal row rules only (verticals + outer sides dropped)
- `.data-table--banded` — the AIG cohort "invisible grid": `border-collapse: separate` with row gaps (`border-spacing`), each body row an outlined box; header cells lose their borders and become bare labels

**Signifiers**:
- `tr.is-emphasized` / `th.is-emphasized` / `td.is-emphasized` — a translucent brand FILL (`--brand-tint`, brand at 22% alpha, not mixed with `--bg`), never a line transform; same brand-fill idea as the info box's info variant
- `.cell--success` / `.cell--warning` / `.cell--error` — status cells, text color only + bold, reusing the `--status-*` roles
- `.cell--num` — right-aligned with `tabular-nums`

## States
None. The table is non-interactive in the shipped CSS — no hover, no selected-row state, no sort affordances.

## Responsive
Wide tables scroll horizontally inside the **`.table-scroll` wrapper** (shipped 2026-07-04, HARVESTED from AIF production — `page.css` `.table-scroll` + the `single.php` auto-wrap): `overflow-x: auto` + `-webkit-overflow-scrolling: touch` + `margin: --spacing-24 0` + `max-width: 100%`; the wrapper owns the vertical rhythm (`> table` margins zeroed). Hand-authored tables ship the wrapper in markup; CMS/article tables get it auto-wrapped by `js/components/table-scroll.js` (idempotent, ported verbatim from the theme). The AIF wide-grid breakout (±120px at ≥1024px inside `.aif-article-body`) is theme-grid-specific and stays theme-side. Scroll is the DEFAULT behavior for wide tables; a mobile stacking transform (same table, stacked on mobile) is a tracked FUTURE special override — never the default. The gate asserts the wrapper scrolls at 390px while the page body does not.

## Tokens referenced
`--spacing-6`, `--spacing-8`, `--spacing-12`, `--spacing-16`, `--spacing-24`, `--size-12`, `--size-14`, `--size-16`, `--size-18`, `--body-md-font`, `--font-mono`, `--weight-bold`, `--case-upper`, `--tracking-label`, `--stroke-1`, `--border`, `--text`, `--brand-tint`, `--status-success`, `--status-warning`, `--status-error`

## Surfaces
Fully surface-riding by construction: borders read `--border` (dark-600 on dark scopes, black on `.section-brand`), text reads `--text`, and the emphasis fill is `--brand-tint` — an alpha fill that holds on every surface including brand. Status cells read `--status-*`, which the dark scopes (`.section-dark`, `.content-section--dark*`, `.footer`, `.hero-card`, `.persona-card`, …) remap to the bright variants (`success-bright`/`warning-bright`/`error-bright`); the brand scope does NOT remap status. The styleguide renders the same condensed table inside `.section-dark` and `.section-brand` with zero extra classes.

## Known friction
- **No-fills law** (operator ruling in the CSS banner): a background fill would need `--raised`, which resolves black on the brand surface / white on light-2, so any fill is fragile — everything must read through borders + text + status. The single exception is the alpha-based `--brand-tint` signifier.
- The mobile **card-collapse** (`data-label` stacking) is deliberately NOT a table feature — if a table reads well as stacked cards, it should be a record list (the decision rule). A future `.data-table` mobile-transform override is tracked for landing-page cases, opt-in only.
- The harvested tables used raw 10/20px paddings that are not on the spacing scale; canon normalized them to `--spacing-8/16/24` (DATA-TABLES.md §2 — "the same trap as the retired `--spacing-20`").
- Zebra striping is intentionally absent — the harvest grammar is borders/bands, never zebra.
- The boundary with `.record-list` is a ratified decision rule (DATA-TABLES.md addendum): homogeneous simple values → data table; self-contained cards with CTA/media/expandable content → record list. Conflating them "would force interactive rich cards into `<td>`s and mislead screen readers."

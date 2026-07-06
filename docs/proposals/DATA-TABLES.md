# PROPOSAL — data tables: research + plan

Status: **research + plan for operator sign-off.** No implementation this round.
The operator asked to explore how the themes handle tables (articles, the
aifounders newsletter, the AIG course detail) and then craft the plan around a
model of: skeleton (mono header + cell font), grid/no-grid, three sizes
(condensed/standard/large), signifiers (header/row/column), and surface reuse.

## What's live today — three harvested archetypes

### A · Article table — CONDENSED (identical on both brands)
`aiguild/blog.css:2109` == `aifounders/page.css:1058` — byte-identical, already unified.
- Semantic `<table>`, `border-collapse: collapse`, **2px full grid** (outer +
  every cell: border-bottom + border-right, last-col/last-row stripped).
- **Header** (`thead th`): `--font-mono`, **11px, weight 700, UPPERCASE,
  letter-spacing 0.1em**, `text-align:left`, bg = `bg-secondary` tint,
  padding `6px 10px`.
- **Cells**: article font, `text-small` (**14px**), line-height 1.35, padding `6px 10px`.
- No hover (not clickable). JS injects a `.table-scroll` (overflow-x) wrapper for mobile.

### B · Newsletter comparison table — LARGE (aifounders, landing/newsletter.css:326)
Live confirmed on aifounders.cz/newsletter: 3-col grid (row-label + 2 compare
columns), 6 rows (Jazyk, Focus, Zdroje, Reklama, Formát, Cena).
- **CSS grid** (div-based, not `<table>`): `grid-template-columns: 1.2fr 1fr 1.2fr`.
- **1px** border box + **column dividers** (`border-left` between head cells) — a
  lighter "no full-grid" look.
- **Header**: `--lp-mono`, **13px, weight 700, UPPERCASE, letter-spacing .08em**,
  accent color, padding `20px 24px` (large).
- **Signified column**: the "AI Founders" (ours) column carries a 10px dot marker
  (`::before`) + accent — the emphasized column.
- **Surface variants**: the SAME table renders on a **light** band (`.lp-diff`,
  white) and a **dark** band (`.lp-why`, ink bg) with color overrides only.

### C · Cohort / course-detail table — STANDARD, "exotic" (aiguild/components.css:3802)
- Semantic `<table>` but `border-collapse: separate; border-spacing: 0 12px` —
  **rows float as bordered bands** with a gap between them (no continuous grid).
- **Header**: `'Spline Sans Mono'`, **12px, weight 700, UPPERCASE**, muted ("same
  eyebrow recipe as footer-blurb__title").
- **Cells**: primary font, `text-small` (**14px**), padding `24px 20px` (roomy).
- **Signified row**: `--data` rows get a `bg-secondary` fill + 2px border box.
- **Signified cell by status**: `--status-error / -warning / -success` color the
  status cell; price cells have currency/original-strikethrough formatting.
- **Responsive**: below the cut the `<table>` hides and `.cohorts-cards` shows —
  each row becomes a stacked card with `label (uppercase) : value` rows
  (the `data-label` pattern).

## The through-line (harvest-confirmed)

Every one of the three uses the SAME header signature: **monospace · uppercase ·
bold · letter-spaced**, on a tint/accent, left-aligned. Cells are the **reading
font at ~14px**. That is the DS table skeleton — the operator's "mono header +
cell font" is confirmed across all three, unprompted. What varies is only:
density (padding/size), how much grid is drawn, and which signifier is lit.

## Cross-check — Carbon DataTable

Carbon offers five row sizes — xs 24 · sm 32 · md 40 · lg 48 · xl 64px — the
header row always matches the row size; xl is only for two-line cells. Carbon
leans on zebra striping; our themes use **borders/bands instead of zebra** (no
zebra anywhere in the harvest). So we take Carbon's *size ladder* idea but keep
our *border/band* grammar and mono header. Three sizes (not five) matches the
operator's ask and the three real densities we actually have.

## The plan (for sign-off)

### 1 · One skeleton, semantic `<table>`
Canonical markup is a real `<table>` (accessible, sortable-ready). The
comparison layout (B) becomes a **variant** of the same component, not a
separate div-grid. Base skeleton:
- header = `--font-mono`, uppercase, weight-bold, letter-spacing, left-aligned,
  tint background;
- cells = `--body-sm/md` reading font, top/middle aligned;
- borders = `--stroke-2` + the surface-aware `--border` color.

### 2 · Size axis (density) — 3 steps, harvest-anchored
| size | header | cell | padding | from |
|---|---|---|---|---|
| `--condensed` | mono 11 | 14 | 6 / 10 → **spacing-6 / spacing-8** | article A |
| `--standard` (default) | mono 12 | 14–16 | **spacing-12 / spacing-16** | interpolated |
| `--large` | mono 13 | 16–18 | 20 / 24 → **spacing-16 / spacing-24** | newsletter B / cohort C |
Note: harvest uses raw 10/20px, which aren't on our spacing scale (the same trap
as the retired `--spacing-20`) — normalize to real tokens (8/16/24).

### 3 · Grid axis — visible grid vs no-grid
- `--grid` = full 2px cell borders (article A).
- default / `--plain` = header underline + row separators only (lighter).
- `--banded` = separated bordered rows (cohort C) — a distinct row-band mode.
(Zebra intentionally omitted — not in the harvest; our grammar is borders/bands.)

### 4 · Signifiers — reuse the existing vocabulary
- **Header** — always on (the mono/tint recipe). No new token.
- **Row** (`tr.is-emphasized`) — `bg-alt` fill + accent left border (reuses the
  4px `--stroke-4` signifier + `--brand`, exactly like perex/blockquote).
- **Column** (`col.is-emphasized`) — accent tint + the dot marker (newsletter's
  "ours"); reuses `--brand` / `--support`.
- **Status cell** (bonus, from cohort) — `--status-error/-warning/-success`,
  which already exist (used by the info box).

### 5 · Surfaces + transforms — reuse, add nothing if possible
Tables should ride the surface scopes like every other component: the border,
header tint, and row fill read **surface-aware semantic roles** (`--border`,
`--bg-alt`, `--text`) so a table re-resolves on dark/brand bands for free — this
is exactly how the newsletter table already does light↔dark with color
overrides only. Goal: **0 new palette values.** Likely need **2–3 new semantic
roles** at most (`table-header-bg`, `table-border`, `table-row-emphasis`), each a
single-hop ref — operator sign-off per the growth law. First choice: reuse
`bg-alt` / `border` / `stroke-*` directly and add no names.

### 6 · Responsive
Adopt the cohort `data-label` pattern: below the phone cut, a table can collapse
to stacked `label : value` cards (opt-in class), and long tables get the article
`.table-scroll` overflow wrapper. One mechanism, chosen per table.

**RESOLVED 2026-07-04 (operator):** `.table-scroll` SHIPPED — harvested 1:1
from AIF production (`page.css` wrapper + `single.php` auto-wrap →
`js/components/table-scroll.js`); horizontal scroll is the DEFAULT for wide
tables. The `data-label` card collapse was NOT adopted for tables (a table
that reads well as cards should be a record list); a mobile-transform
**special override** (same table, stacks on mobile, for landing-page cases)
is tracked in `IMPLEMENTATION_STATUS.md` — opt-in only, never the default.

## Open questions for the operator

1. **3 sizes vs Carbon's 5** — recommend 3 (condensed/standard/large); confirm.
2. **Comparison table** — fold into the table component as a `--comparison`
   variant (semantic `<table>` with an emphasized column), or keep the marketing
   grid-div as its own thing? Recommend folding in.
3. **New semantic tokens** — OK to add up to ~3 table roles if reuse doesn't
   cover it, or hold to strict reuse only?
4. **Default grid** — plain (light) or visible-grid as the default look?
5. **Header size** — one constant mono size (e.g. 12px) or scale 11/12/13 with
   the density step?

## If approved — implementation sketch (next round)
`.data-table` base + `--condensed/-standard/-large`, `--grid/-banded`,
`tr.is-emphasized` / `col.is-emphasized`, status-cell classes; token additions
(if any) through `base.tokens.json`/`semantic.json`; a "Data tables" styleguide
tab showing the 3 sizes × grid modes × signifiers on light/dark/brand; gate
assertions (mono header, border widths, status colors, surface re-resolve).
Ritual as always.

---

# ADDENDUM — the cohort "tiles" are NOT a table (research, 2026-07-04)

The operator flagged the course-cohort block (aiguild.cz/kurz/ai-designer/,
"Termíny") as looking table-like but built from divs, and asked whether it
belongs in the table component at all. **Verdict: no — it's a record/card list
that only borrows a table's alignment. Give it its own component; share the
tokens, not the structure.**

## What the markup actually is (harvest: single-kurz.php + components.css)
- Container is a `<div class="cohort-list">`, NOT a `<table>`.
- The column-title strip (`.cohort-list__head`: Termín · Místo · Cena · Jazyk)
  is **`aria-hidden="true"`** — a decorative alignment header, deliberately NOT
  a real `<th>` row.
- Each cohort is an **`<article class="cohort-row">`**, not a `<tr>` — a
  self-contained unit carrying a **CTA button**, a **progress bar**
  (`--progress`), status, price with sale/original, and an **expandable "how it
  works"** block. In `--cards` mode every cell prints its OWN label via
  `[data-label]::before`.

So the theme author already decided this is not a table: `<article>` rows +
aria-hidden header + rich per-row interactive content. Each tile is its own
key-value record — exactly the operator's read.

## Best practice (Carbon · WAI/MDN)
- **`<table>`** is for a 2-D grid of homogeneous rows you scan/sort/compare, with
  real `<th>`/`<td>` relationships. "Tables should not be used for lists."
- **Carbon** splits this explicitly: **Data table** = complex, sortable,
  selectable, 25+ rows; **Structured list** = simple grouped/key-value content.
  Rich content, per-row CTAs, row selection, or few items → NOT a data table.
- **Cards / records**: no `<card>` element — use `<article>` with an internal
  `<dl>` (name/value groups) for the fields. The responsive "table→cards with
  duplicated labels" pattern is the same shape, but the cohort block is
  **cards-first** (each row is already an `<article>` with a CTA + progress).

By every one of these tests the cohort block is a **record/card list**, not a
table: `<article>` units, decorative header, a primary CTA + progress bar + an
expandable section per item, and only a handful of items.

## Decision rule for the DS (so this doesn't recur)
> Use the **data-table** component when rows are homogeneous simple values you
> compare across columns (real `<th>`/`<td>`, sortable/scannable).
> Use a **record list** component when each item is a self-contained card with
> rich content — a CTA, media, a progress/'status, or an expandable body — even
> if the fields visually line up into columns.

## Recommendation
1. **Do NOT fold the cohort tiles into `.data-table`.** Conflating them would
   force interactive rich cards into `<td>`s and mislead screen readers.
2. **Add a separate `.record-list` component** (a.k.a. cohort/comparison cards):
   `<article>` rows + `<dl>` fields, an optional aria-hidden alignment header on
   desktop, per-item CTA/progress/expandable slots, and the responsive
   label-per-field collapse.
3. **Share the visual language through TOKENS, not markup** — the mono/uppercase
   column-label recipe, `--border`/`--stroke-*`, the `--brand-tint` signifier,
   and `--status-*` are already tokens; the record list consumes the same ones,
   so it reads as a sibling of the table without being one. That is how the two
   stay visually consistent yet semantically honest.

This is research + a boundary ruling only — no component built. If you approve,
`.record-list` is its own scoped batch (harvest the full cohort CSS: progress
bar, sale price, expandable "how it works", the desktop grid ↔ mobile cards).

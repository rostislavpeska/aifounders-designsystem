# PROPOSAL — record list, the ABSTRACT model

Status: **RESOLVED + SHIPPED (2026-07-04).** Operator rulings on the round-1
questions:
1. Columns — `--record-columns` (consumer writes the grid template). ✓
2. Action — **just another column** (a `.record__field--action`), not a
   dedicated slot.
3. Meter — the occupancy/progress bar is **removed from the core** and becomes a
   SEPARATE component (usable in any card/popup); tracked as its own task.
4. Demo — **two field sets** (cohort 6-col + mock AIF event 4-col) on the styleguide.

**Correction (2026-07-04):** round 1 said `subgrid` was only needed for
content-sized columns. It IS needed — the action is an `auto` column, so without
subgrid a wide button in one record shifts only that record's tracks (columns
don't line up). The build uses **subgrid**: the list owns `--record-columns`,
every `.record`/`.record__fields` subgrids those tracks, so columns align across
all records and a long value widens its column for the whole list (table
behaviour). Verified: the status column shares one x across records.

Built accordingly: `.record-list` (owns `--record-columns`) / `.record` /
`.record__head` (title + description) / `.record__fields` (N-col grid) /
`.record__field` (+ `--strong`/`--nowrap`/`--success`/`--warning`/`--error`/
`--action`). Status is a field colour (reuses `--status-*`), not a variant. 0 new
design tokens (`--record-columns` is a layout var). The original round-1 reasoning
is kept below for the record.

---

Reasoning below; questions were at the end (now answered above).

## The abstraction: a list of records, each a stack of optional SLOTS

A **record list** is a vertical list of **records**. A record is a self-contained
card built from slots — all optional except the fields:

| slot | role | notes |
|---|---|---|
| `record__head` | title + description body | optional; spans full width, divided below |
| `record__fields` | **N** labelled fields (label + value) | the core; aligned columns on desktop, stacked (label-inline) on mobile |
| `record__action` | CTA(s) | optional; pinned to the row end, independent of N |
| `record__meter` | a progress/occupancy bar + marker | optional; a generic "how full / how far" indicator |

Nothing here says "cohort", "Termín", "price", or "capacity". A cohort is one
consumer (Date · Location · Price · Language · +meter); an AIF event is another
(Date · Venue · Type · +action, no meter). Same component.

## The key decision — how N columns align

Columns must line up across every record (so it still reads like a grid), for
**any** N. Reasoning about the options:

- **Shared template variable (recommended).** The list owns one custom property
  `--record-columns` (a `grid-template-columns` value); every `record__fields`
  grid uses it. Because the tracks are `fr`/fixed and every record is the same
  width, identical templates line up automatically — no subgrid needed. The
  consumer writes it once: cohorts `--record-columns: 1.5fr 1fr .9fr .8fr`,
  events `--record-columns: 1.5fr 1fr auto`. The component ships a fluid default
  (`repeat(auto-fit, minmax(9rem, 1fr))`) so it degrades if unset.
- **`subgrid`.** Only needed if we want *content-sized* (`max-content`) columns
  to align across records. `fr` tracks don't need it. Keep in reserve; don't
  require it.
- **Auto-fit only.** Adapts to N but does NOT align records to each other
  (each record packs independently) — fails the "reads like a grid" goal. No.

So: **`--record-columns` on the list, applied to each record's field grid.** That
is the single N-column knob. The action is NOT one of these columns (next point).

## Status is DATA, not a variant

Today's "bar vs `--capacity` column" are two hard-coded variants. Abstractly they
are the same thing — *a status* — shown two ways:
- as a **field** with a status colour (just another `record__fields` cell), or
- as the **meter** slot (the bar).
So there is no `--capacity` variant. A record simply *has a meter slot or not*,
and *may include a status field or not*. Both read `--status-*` (+ the new
`--text-on-status` for the marker). This collapses the variants into composition.

## What stays (already abstract / correct)

- Type: labels = the data-table mono recipe; values + description = 14px.
- Labels inside the card, above each value; mobile stacks with label inline.
- Card = 1px inset-shadow border; meter sits on the bottom edge (border omits it).
- Tokens: `--status-*`, `--text-on-status`, `--border`, `--stroke-*`,
  `--brand-tint`, mono/size — reused. Likely **0 new design tokens**; the only
  new name is the *layout* var `--record-columns` (not a colour).

## Naming

`.record-list` / `.record` / `.record__head` / `.record__fields` /
`.record__field` (was `__cell`) / `.record__action` / `.record__meter`
(was `__bar`) / `.record__meter-marker`. Generic, consumer-agnostic. The cohort
and events markup both map onto these.

## Open questions (before any code)

1. **Column knob** — `--record-columns` (consumer writes the full template, max
   flexibility) as proposed, or would you rather a simpler `--record-column-count`
   (N, evenly distributed)? Recommend the template — real tables want unequal
   widths.
2. **Action** — a dedicated end slot pinned right (independent of N), as
   proposed; confirm you don't want it to be one of the `--record-columns`.
3. **Meter** — keep the occupancy bar as a generic optional `record__meter`
   slot in the core, or is any bar too specific and it should live as an
   add-on/extension? Recommend keeping it generic (progress bars are common).
4. **Prove-it demo** — the styleguide should show **two different field sets**
   (a cohort and a mock AIF event) on the same component to make the abstraction
   visible. OK?

Once you rule on these, round 2 rebuilds it against this model (from scratch,
per the new rule) with the two demos + gate.

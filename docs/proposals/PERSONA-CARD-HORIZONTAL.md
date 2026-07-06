# PROPOSAL — persona card horizontal: kill the patches, one architecture

Status: **RESOLVED + SHIPPED 2026-07-04** (amended same day by operator
ruling: **the portrait is FULL HEIGHT — ALWAYS**). Final architecture:
`.persona-card-slot` query container (a container cannot query itself), ONE
container cut at 560px, photo track widened to `clamp(200px, 40cqi, 320px)`,
and the ABSOLUTE-FILL portrait: the img is absolutely positioned inside the
photo cell (`inset: 0; object-fit: cover`) so it contributes ZERO intrinsic
size — the content column alone sizes the row, the portrait zooms its crop
to fill it (kills the circular-sizing trap behind every past failure). The
§4.1 top-anchored-square default and the separate `--flush` modifier were
REPLACED by this single full-height mode (operator rejected whitespace under
the photo). `min-height: clamp(...)` guards short bios (no sliver).
`--vertical` escape hatch; `--horizontal` = legacy no-op. Gate asserts:
portrait height == card height EXACTLY at long AND short bios; clamp
floor/cap; container-based flip at one viewport. Open follow-up:
`.author-hero` unification (tracked).

Operator brief: "breaks constantly, all current variants are patches — fetch
all instances, propose different sizing, clean horizontal→vertical rules,
research how other design systems handle this, propose a solution."

## 1 · WHY it breaks — the measured root cause (not a bug, a contradiction)

The horizontal card declares an over-constrained system:

```
width: 40% + min-width: 320px + max-width: 480px      (the photo column)
aspect-ratio: 1/1                                      (the photo box)
align-items: stretch                                   (the row)
flex: 1                                                (the content column)
```

These cannot all hold. Per spec, `align-items: stretch` gives the photo a
DEFINITE height (= content height), and `aspect-ratio` then derives the
other axis — so **the bio's length sizes the photo**. Which constraint the
browser breaks depends on container width and content length:

- Measured in a 638px container: `min-width: 320` beat the 40% (photo took
  50%), stretch beat the aspect (rendered ratio 0.93), content starved to
  318px → word-per-line text.
- Long bio → photo balloons (or crops arbitrarily); short bio → photo
  dictates card height, content floats in space.
- The orientation flip is VIEWPORT-based (`@media 768`), but the card's
  CONTAINER (grid cell, article column, sidebar) is what actually matters —
  a 350px grid cell at a 1200px viewport still goes horizontal.

Every production fix patched one symptom of this contradiction. The
component cannot be fixed by another patch; the constraint system must
change.

## 2 · ALL instances (full inventory — agent sweep, file:line in the report)

| Where | Variant combo | Container | Notes |
|---|---|---|---|
| AIF homepage | vertical, dark grid | `.personas-grid` | works |
| AIF author-profile edit | `--light --horizontal` | form widget (~200px avatar ctx) | horizontal in a NARROW context — exactly the viewport-query misfire |
| AIF shortcodes (archive/newsletter) | `--light` (+`--horizontal` if filtered) | varies | orientation decided by data, not layout |
| AIG homepage | dark grid + reveal stagger | flex grid | works |
| AIG course detail (lecturers) | `--light --horizontal` in `.card-row--single/--two/--grid` | varies by count | THE breakage hot spot |
| AIG article author box | `--horizontal --light` | full article column | works only at wide viewports |
| AIG persona detail | **`.author-hero`** — a SECOND, incompatible person component (circular 200px avatar) | hero band | same data, parallel system |

**Patch archaeology (the "million fixes"):** AIF `page.css:410-448` FORCES
`flex-direction: row` on 3-up grids at ≤1023 then re-verticalizes at ≤599
(two flips, three states); no rule ever returns `--horizontal` itself to
column on mobile (the crushed-phone bug); `.section-dark` specificity war
(`(0,3,0)` overrides + SVG-path exceptions); `card-row--single/--two/--grid`
each re-size the card differently; AIG already reached for **container
queries** (`container-name: persona`, name scales at 400px) — the right
instinct, bolted onto the wrong geometry.

## 3 · What mature systems do (full cited research in the agent report)

- **Nobody lets content height size the media.** Media is extrinsically
  sized: fixed width (Tailwind `md:w-48 shrink-0`), clamped track
  (web.dev `minmax(50px, min(20%, 500px))`), or fixed-ratio crop box —
  always `object-fit: cover`.
- **Container queries are the settled orientation mechanism** (Baseline
  2023: Chrome 105+/Safari 16+/Firefox 110+): vertical is the BASE state,
  horizontal is the enhancement at a container-width cut. Viewport queries
  are the named anti-pattern (Shadeed, web.dev); TNA's `horizontalOnSmall`
  escape flag is a DS admitting viewport heuristics misfire.
- Carbon core avoids side-by-side media cards entirely; Polaris MediaCard
  ships 40/60 percentages but only inside a width-controlled admin shell;
  M3 makes orientation a screen-level decision, not a card knob.
- `aspect-ratio` + `align-items: stretch` is a documented anti-pattern —
  our exact bug, spec-working-as-designed.

## 4 · PROPOSED ARCHITECTURE (one system, three pieces)

### 4.1 The geometry — extrinsic photo, one authoritative axis

Horizontal mode becomes a GRID with a **fixed-by-design photo track** that
never negotiates with content:

```css
/* the card is its own query container */
.persona-card { container: persona / inline-size; }

/* BASE = vertical. The ONLY state below the cut. No exceptions. */

/* HORIZONTAL — earned by CONTAINER width, not viewport */
@container persona (min-width: 560px) {
    .persona-card--auto,             /* default: orientation is automatic */
    .persona-card--horizontal {      /* legacy alias during adoption */
        display: grid;
        grid-template-columns: var(--persona-photo-col, clamp(160px, 33cqi, 262px)) 1fr;
        align-items: start;          /* KILLS the stretch/aspect coupling */
    }
    .persona-card__avatar { aspect-ratio: 1/1; }  /* square, top-anchored */
}
```

- Photo column = `clamp(160px, 33cqi, 262px)`: floors at a readable
  thumbnail, scales gently with the CONTAINER (`cqi`), caps at 262 — the
  harvested vertical card width, so the photo never outgrows the identity
  it has in the grid. `--persona-photo-col` is a component-local layout
  knob for consumers (the ds-distill layout-knob law).
- **Long bio ⇒ card grows, photo stays a square, whitespace below the
  photo.** Deterministic at every width. (The web.dev / Shadeed pattern.)
- Opt-in modifier `.persona-card--flush`: photo cell `height: 100%;
  aspect-ratio: auto` + `object-fit: cover` — the edge-to-edge "list row"
  look (Tailwind/Polaris model) where the crop absorbs bio length instead
  of whitespace. Two honest modes; never square+stretch simultaneously.

### 4.2 The orientation contract — clean flip rules

- ONE cut: container ≥ 560px (photo 160 min + ~360px readable text + gaps).
  Below it the card is ALWAYS vertical — including every `--horizontal`
  legacy instance. No viewport queries anywhere in the component.
- All `card-row--*` per-count re-sizing of the CARD dies: card-rows lay out
  CELLS; the card decides its own orientation from the cell width it
  receives. (The tablet force-row patch and the 599 re-flip die with it.)
- Escape hatch (the Polaris/TNA prop): `.persona-card--vertical` pins
  vertical regardless of width. No pin for horizontal — a too-narrow
  horizontal card is never correct.

### 4.3 The sizing proposal (operator asked for different sizing)

| Context | Photo | Mechanism |
|---|---|---|
| Vertical (grid) | full card width, square | unchanged (works today) |
| Horizontal, container 560–~800 | 160–~215px square | `33cqi` inside the clamp |
| Horizontal, container ≥ ~800 | 262px cap | clamp ceiling = the vertical card's width |
| `--flush` rows | fixed WIDTH track, full-height cover crop | opt-in |
| Author-profile edit widget | stays vertical (200px context < 560 cut) | automatic — today's misfire self-heals |

### 4.4 Cleanup ledger (dies with this)

AIF `page.css` 410–448 + 451–462 (the double-flip), all `card-row--* >
.persona-card` orientation/size overrides, the `--horizontal` viewport
block in both themes, the 40%/320/480 photo math. AIG's container-query
name scaling (400px cut) is KEPT — it was correct — re-cut to the same
560 system if desired.

## 5 · Open questions (operator verdict)

- [ ] Architecture 4.1 (container query + fixed clamped square, top-anchored
      default + `--flush` opt-in)? This is the intersection of what Polaris/
      Tailwind/web.dev/Shadeed all do right.
- [ ] Container cut 560px — or rule a different readable-content threshold?
- [ ] `.author-hero` (the second person system, circular): unify into
      persona-card later (tracked), or keep as the hero-context component?
- [ ] Browser floor: container queries are Baseline 2023 — OK as a hard
      dependency (no fallback), or ship a `@supports not` vertical-only
      fallback line?

Until ruled: the DS keeps the quarantined harvest; nothing else changes.

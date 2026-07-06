# Numbered headings

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#numbered-headings` · **Specimen:** `/?aigds_styleguide=1&item=prose`

## Intent
`.numbered-headings` is an auto-numbered section-heading mechanism harvested from both themes' `.course-syllabus` (live on the course-detail page): each `<h3>` inside the wrapper auto-increments a number rendered in a **brand-colored tile** before its text, and all following content indents to align under the heading text. Reach for it when a page section is a scannable ordered sequence — course modules, syllabus blocks, step-by-step program outlines — and you want reorder-safe numbering with zero markup (numbers are pure CSS counters; move a block and the numbers re-flow). Do NOT reach for it for ordinary prose headings (use the prose defaults), for inline ordered lists (use `<ol>`), or when the numbers carry no meaning. The contract: ONE mechanism, no brand scoping — the tile fill is `--brand` (yellow AIG / blue AIF, resolves per brand automatically) with the digit in `--text-on-brand`; the heading text is the page-context heading-md style; content indent = tile width + gap. What breaks: putting the number in markup (defeats reorder safety), or changing the tile geometry ad hoc — the 44/32px tile and 22/18px digit are component calibration, like the bullet-arrow raw px.

## Anatomy
```html
<div class="numbered-headings">
  <h3>First module — the number tile leads</h3>
  <p>Body copy, indented to sit under the heading text.</p>
  <ul><li>A supporting point</li></ul>
  <h3>Second module — counter auto-increments</h3>
  <p>Renders "2" with zero markup change.</p>
</div>
```
- Wrapper: `counter-reset: numbered-heading`.
- Each `h3`: `counter-increment`, `display: flex; align-items: flex-start`, heading-md bundle type, `margin: var(--spacing-56) 0 var(--spacing-12)` (FLOW LAW: generous gap before each numbered block, tight gap to content). First heading resets its top margin to 0 (both clean markup and chat-pasted wrapper markup are handled).
- `h3::before` = the tile: `counter(numbered-heading)`, 44×44px, digit 22px at `--weight-extrabold`, `line-height: 44px` (= height, vertically centers the digit), `background-color: var(--brand)`, `color: var(--text-on-brand)`, `margin-right: var(--spacing-12)`, `margin-top: -5px` (optical — tile cap aligns to the heading cap height).
- Content alignment: `p, ul, ol, blockquote, h4` get `margin-left: var(--spacing-56)` — 56 = tile 44 + gap 12, so content aligns under the heading TEXT.
- `li p { margin: 0 }` — markdown loose lists render `<li><p>` and the p-indent must not cascade in.

## Variants
None. One mechanism, both brands — the tile fill resolves per brand via `--brand`, never via scoping.

## States
None — static content.

## Responsive
At `max-width: 599px` (the `bp-sm` cut, AIG mobile pattern): the heading becomes a column — the number tile sits **above** the headline (so long headings get full width); tile shrinks to 32×32px with an 18px digit and `margin: 0 0 var(--spacing-8) 0`; heading top margin tightens to `--spacing-32`; content drops its left indent entirely (`margin-left: 0`) and runs full width beneath.

## Tokens referenced
`--brand`, `--text-on-brand`, `--text`, `--heading-md-font/-size/-weight/-leading`, `--weight-extrabold`, `--spacing-56`, `--spacing-32`, `--spacing-24`, `--spacing-16`, `--spacing-12`, `--spacing-8`. (Tile 44/32px and digit 22/18px are calibrated component constants, deliberately not on any token scale.)

## Surfaces
The tile reads the `--brand` / `--text-on-brand` roles and heading text reads `--text`, so the component adapts wherever those roles resolve. Harvested/live context is light sections (course detail).

## Known friction
- **UNIFIED to AIG's tile on both brands** (operator's "check aig course detail" reference — the tile IS the good version). AIF live renders a plain "N." instead: intentional delta, GM exception, and a flagged **operator veto point** in `docs/DECISIONS.md` (keep tile-on-both vs revert AIF to plain numbers).
- Adoption alias: theme class `course-syllabus` → `numbered-headings` at adoption. This alias is NOT in `rename-map.json` (that map is tokens only).
- The `-5px` optical tile shift and the 56px indent equation (tile + gap) are coupled — change one, retune the other.

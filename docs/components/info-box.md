# Info box

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#info-box` · **Specimen:** `/?aigds_styleguide=1&item=info-box`

## Intent
The info box is the DS skeleton note box: a plain tinted box with a thick 4px accent left border — NO icon, SQUARE corners, nothing else. Reach for it to call out a note, tip, success/warning/error message, or neutral aside inside content flow, at any of three reading sizes. Do NOT reach for it to emphasize a table row/column (that's the data-table `is-emphasized` brand-tint signifier), for intro emphasis in an article (that's the perex/blockquote voice, which shares the 4px-accent-border grammar), or for status text inside tables/records (`.cell--*` / `.record__field--*`). The whole contract is one custom-property knob: each color variant only sets `--info-accent`; the base derives the background tint from it via `color-mix(… 22%, var(--bg))` — zero new tokens and surface-aware by construction, since the mix re-resolves against the local `--bg`. Content sits directly inside the box (no inner wrapper); first/last child margins are stripped so padding stays even.

## Anatomy
```html
<div class="info-box info-box--success">
  <strong>Success</strong> — body copy at the default 16px reading size.
</div>
```

Class map:
- `.info-box` — required root. Defaults to the info accent (`--brand`). Tinted background + `--stroke-4` solid left border in the accent. Left padding is `calc(var(--flow-indent) - var(--stroke-4))` so text lands at flow-indent, like perex/blockquote; right inset is symmetric (`--flow-indent`).
- Color variant class — optional (`--info` is the explicit form of the default).
- Size variant class — optional, stacks with any color variant.
- Content — any flow content directly inside; `> :first-child` / `> :last-child` margins are zeroed. There is no `.info-box__icon` (the gate asserts its count is 0).

## Variants
**Color** (each sets only `--info-accent`):
- `.info-box--info` — `--brand` (the primary color; yellow AIG / blue AIF)
- `.info-box--success` — `--status-success`
- `.info-box--warning` — `--status-warning`
- `.info-box--error` — `--status-error`
- `.info-box--neutral` — `--border-strong` (true greyscale — a neutral grey, deliberately not the cold text hue)

**Size** (independent of color; default is 16px `--body-md-size`):
- `.info-box--small` — `--body-sm-size` (14px; compact notes and captions)
- `.info-box--article` — `--body-lg-size` (18px; matches article body copy)

## States
None. The info box is static — no hover, focus, or interactive states in the CSS.

## Responsive
Nothing. No media queries touch `.info-box`; sizes and padding are constant across the 599/767/1023 cuts.

## Tokens referenced
`--brand`, `--status-success`, `--status-warning`, `--status-error`, `--border-strong`, `--bg`, `--stroke-4`, `--spacing-16`, `--flow-indent`, `--text`, `--body-md-font`, `--body-md-size`, `--body-sm-size`, `--body-lg-size`, `--weight-regular`, `--leading-body`

## Surfaces
Nothing special on the box itself — it re-resolves automatically. The tint mixes the accent with the local `--bg`, so on `.section-dark` the background darkens with the surface; text reads `--text`. Status variants invert on dark because the dark scopes remap `--status-success/-warning/-error` to the bright variants (`success-bright`/`warning-bright`/`error-bright`) — drop the same markup inside `.section-dark` and accents, tint and text all re-resolve. The info variant follows `--brand` per brand; neutral stays greyscale everywhere. The brand scope does not remap status.

## Known friction
- The 4px border is part of the left padding math — changing `--stroke-4` or the padding independently breaks the flow-indent alignment with perex/blockquote.
- The 22% `color-mix` tint is a component recipe, not a token; there are no alpha entries in the palette (the same operator idiom as badge hover overlays), so don't promote the tint to a palette value.
- `--status-warning` was UNIFIED across brands (DECISIONS.md 2026-07-03): the previous AIF value read as red; the yellowish AIG value won (GM exception on AIF).
- Neutral deliberately reads `--border-strong`, not a text color — the gate checks the neutral accent, so don't "simplify" it to `--text-secondary`.

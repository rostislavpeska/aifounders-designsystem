# Avatar

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#avatar` · **Specimen:** `/?aifds_styleguide=1&item=avatars`

## Intent
The avatar is the person-image container, identical in both themes: a fixed-size box that crops its `<img>` with `object-fit: cover` on an `--bg-alt` placeholder background. Reach for it wherever a person is shown — author chips (sm), persona cards (md), team grids (lg). Do NOT reach for it for arbitrary thumbnails, logos or icon slots (use the icon utilities / `.icon-placeholder` / `.logo-placeholder` instead). The contract in one breath: three CALIBRATED sizes harvested from production — 64 / 160 / 260px, component constants deliberately NOT on a token scale — where sm and md are round (`--radius-full`) and lg is square (`border-radius: 0`). A size class is required; the base class alone has no dimensions.

## Anatomy
```html
<span class="avatar avatar--sm">
  <img src="…" alt="Author name">
</span>
```

Class map:
- `.avatar` — required root: `inline-block`, `overflow: hidden`, `--bg-alt` background (shows while/if the image is missing).
- `.avatar--sm` / `.avatar--md` / `.avatar--lg` — required, exactly one; supplies width/height/radius.
- `img` — the content; fills the box (`width/height: 100%`, `object-fit: cover`, `display: block`). The styleguide substitutes an `.icon-placeholder` when no image exists.

## Variants
- `.avatar--xs` — 48×48, round (`--radius-full`). Production source: comment
  author. Comments shrink it to 40px ≤599 inside `.article-comments`.
- `.avatar--nav` — 36×36, round. The header ACCOUNT avatar (brand circle +
  initial). SHRINKS to 30×30 under `.main-header--scrolled` (harvested nav-avatar
  behavior; the shrink rule lives in the header block). Smaller initial than
  `--xs` (`--size-14`, → `--size-12` scrolled); has a `--transition-normal` on
  size for the header shrink. Combine with `.avatar--initials`.
- `.avatar--sm` — 64×64, round (`--radius-full`). Production source: author chip.
- `.avatar--md` — 160×160, round (`--radius-full`). Production source: persona card.
- `.avatar--lg` — 260×260, SQUARE (`border-radius: 0`). Production source: team grid.
- `.avatar--initials` — MODE, combines with a size: no `<img>`, a single
  initial as content; `--brand` fill + `--text-on-brand` ink
  (numbered-headings precedent; production hardcoded #fff), accent bold
  `--size-18`. Production source: comments initials fallback + header
  nav-avatar.

## States
None. Purely presentational — no hover, focus, or loading states in the CSS.
(Consumers may add their own — comments dim a LINKED avatar to 0.85 on hover.)

## Responsive
Nothing on the component itself. Sizes are fixed pixel constants at every
viewport; context overrides (comments' 40px mobile step) belong to consumers.

## Tokens referenced
`--bg-alt`, `--radius-full`, `--brand`, `--text-on-brand`, `--font-accent`,
`--weight-bold`, `--size-18`

## Surfaces
Only the placeholder background is surface-aware: `--bg-alt` re-resolves per scope, so an empty avatar tints correctly on dark/brand sections. The image content itself is unaffected by surface.

## Known friction
- The 64/160/260 sizes are calibrated component constants, intentionally off the token scale (same policy as the button size ladder) — do not "normalize" them onto spacing/size tokens.
- The lg square vs sm/md round split is harvested production truth, not an oversight — don't add `--radius-full` to lg.

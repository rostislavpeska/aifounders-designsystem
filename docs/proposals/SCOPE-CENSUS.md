# PROPOSAL — surface-scope census + collapse recommendation

Status: **MEASUREMENT DONE 2026-07-04 — awaiting operator verdict. No scope
was changed.** This executes roadmap item #1 (HANDOFF.md): "surface-usage
measurement on the live sites → possible scope collapse. Measure first,
never collapse blind."

## Method

Counted `class="…"` markup usages of every DS scope selector across BOTH
production theme clones (`_harvest`, all PHP/HTML templates), then computed
the token-value deltas between candidate scopes from `tokens/scopes/*.json`.

**Caveats (honest limits):**
- DB-stored content (WYSIWYG landing pages, e.g. the AIF newsletter's `lp-*`
  sections) is not counted — those sections style their own backgrounds and
  do not use DS scope classes anyway.
- `dark-3`'s count is dominated by `persona-card`, `dark-1`'s includes
  `hero-card`/`footer` — component-surface mappings, not per-page section
  choices. The counts measure how often the SCOPE PAINTS, not how often an
  author picks it.

## The census

| Scope | Selectors (tokens.css layer 3) | AIF | AIG | Total | Delta vs its parent |
|---|---|---|---|---|---|
| light-1 | `:root` (default) | — | — | everywhere | — |
| light-2 | `.content-section--secondary` | 5 | 7 | **12** | 2 props vs light-1 (bg, perex-border) |
| light-3 | `.content-section--tertiary` | 4 | 0 | **4 (AIF-only)** | 4 props vs light-2 (bg gray-150, raised→white, perex-border, tertiary-hover) |
| dark-1 | `.section-dark`, `.content-section--dark`, `.footer`, `.hero-card` | 27 | 61 | **88** | (the dark baseline) |
| dark-2 | `.content-section--dark-secondary`, `.dark-blurb--secondary`, `.footer__newsletter-section` | 1 | 1 | **2** | **1 prop vs dark-1** (bg: dark-900 vs black) |
| dark-3 | `.content-section--dark-tertiary`, `.persona-card` | 53 | 23 | **76** | 3 props vs dark-1 (bg dark-850, raised→black, tertiary-hover→black — the gate-asserted "raised wraps" behavior) |
| brand | `.section-brand`, `.page-hero` | 19 | 18 | **37** | (own family) |
| support | `.surface-support`, `.smart-btn` | 0 | 1 | **1** | (own family — but see below) |

## Findings

1. **`dark-2` does not earn its tier.** Two markup usages across both sites,
   and its entire identity is ONE background step (dark-900 instead of
   black). Recommendation: **RETIRE the dark-2 scope**; remap its two
   consumers to dark-1 at adoption (GM exception: their bg shifts one step
   darker) — or, if the dark-900 panel look must survive, it's a component
   concern for those two blocks, not a system surface.
2. **`light-3` is borderline.** AIF-only, 4 usages, but a REAL role (the
   gray-150 "band" surface with the raised→white wrap). Recommendation:
   **KEEP, watchlist** — revisit after Batch-2 section harvests; folding it
   now would force `--raised` fills onto a same-color background.
3. **`support` is not a page surface.** Its one real consumer is the
   smart-button itself (`.smart-btn` IS the scope). Recommendation:
   **RECLASSIFY in docs as a component scope** (owned by smart-btn), keep the
   mechanics unchanged. `.surface-support` as an authorable section class has
   zero production usage — drop it from the authoring vocabulary at adoption.
4. **`light-2`, `dark-1`, `dark-3`, `brand` all earn their tiers** — real
   usage, real deltas, gate-asserted behaviors.

## Net result if ruled as recommended

8 scopes → **6 page surfaces** (light-1/2/3, dark-1/3, brand) + 1 component
scope (support/smart-btn). The scope FILES shrink by one (dark-2.json); the
authoring vocabulary shrinks by two (`--dark-secondary`, `.surface-support`).

## Operator verdict needed (per the never-collapse-blind ruling)

- [ ] Retire `dark-2` (remap 2 consumers → dark-1, GM exception)?
- [ ] Keep `light-3` on watchlist (no change now)?
- [ ] Reclassify `support` as a component scope (docs only, no CSS change)?

Until ruled: nothing changes — the census is the deliverable.

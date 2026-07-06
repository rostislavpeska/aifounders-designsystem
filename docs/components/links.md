# Links (global link system + card link utilities)

**Type:** system · **Status:** shipped · **git_path:** `assets/css/components.css#global-link-system` · **Specimen:** `/?aigds_styleguide=1&item=surfaces`

## Intent
The global link system styles every `<a>` inside `main` / the section scopes **by default** — you never opt a prose link in; components that are not text links opt OUT via the exclusion chain. Reach for it by doing nothing: an inline link in editor content, a footnote, a "read more" in running text all get the LINK IDIOM automatically. Do NOT restyle links per component; if a component's anchor must not look like a text link (buttons, badges, nav items, card title/image links), it must appear in the `:not()` exclusion chain, otherwise the global recipe wins and the component's look breaks. The idiom (operator 2026-07-02): resting state = a **1px hairline underline lowered 4px** (Apple-grade) on every surface; hover feedback = color shift on light/dark; on brand the underline **thickens to 2px** because color can't shift there (links are already black). Feedback by subtraction (removing the underline on hover) is outlawed. Colors are roles: `--link` / `--link-hover` re-resolve per surface, so the same rule renders brand-ink on light, brand-bright on dark, black on brand.

## Anatomy
```css
/* the default recipe (exclusion chain elided) */
main a:not(.btn):not(.badge):not(.nav-item)…​ {
    color: var(--link);
    text-decoration: underline;
    text-decoration-thickness: 1px;   /* longhands AFTER the shorthand — the shorthand resets them */
    text-underline-offset: 4px;
    transition: color var(--transition-button), text-decoration-thickness var(--transition-fast);
}
…:hover, …:active { color: var(--link-hover) !important; }
```
The full exclusion chain (canonical = AIG's superset, additive for AIF): `.btn`, `.badge`, `.nav-item`, `.nav-dropdown-item`, `.mobile-nav-item`, `.mobile-lang-item`, `.card-title-link`, `.card-image-link`, `.persona-card__social-link`. The `.section-dark` rule additionally excludes `.footer__subtle-link`, `.footer__logo-link`, `.footer__social-link`.

Scope rules, in cascade order:
1. `main a` / `.section-light a` — the base recipe above.
2. `.section-dark a` — same drawing; comes AFTER the base rule (specificity/order), roles supply brand-bright/support.
3. `.section-brand a` / `.article-hero a` — `color: var(--link) !important` (the brand scope maps LINK to black); hover = underline **stays and thickens to 2px** with `!important` longhands (a shorthand would reset thickness). `.article-hero` is included because it is a brand scope in tokens.css that never got the brand idiom live (audit 2026-07-03). The harvested brand hover dropped the underline — off-idiom, corrected as an intentional delta vs live sites (GM exception).

## Variants
- **`.card-title-link`** (+ `.card-image-link` as its image sibling in the chain) — CARD LINK UTILITIES, AIG canonical, **excluded from the global chain**: `color: inherit; text-decoration: none;` resting, and on hover a 2px underline at 4px offset in `--text` (`transition: text-decoration-color 0.22s var(--ease-apple)`). Use for a card whose whole title is the link — the title must read as text until hovered.
- `.btn--link` (documented with buttons) deliberately re-implements the same drawing: resting 1px/4px, hover 2px.

## States
- Default: `--link` color, 1px underline, 4px offset.
- `:hover` / `:active`: `--link-hover` (per surface: light → tint-dark, dark → support, brand → black) — on brand additionally `text-decoration-thickness: 2px`.
- No styled `:visited` (except `.btn--link:visited` keeping `--text`).

## Responsive
None — the idiom is identical at every width.

## Tokens referenced
`--link`, `--link-hover`, `--transition-button`, `--transition-fast`, `--text`, `--ease-apple`.

## Surfaces
Entirely role-driven: the light/dark/brand rules exist for cascade/exclusion differences, but colors come from the surface scope's `--link`/`--link-hover` remaps (tier 3 re-resolution). The comment in code notes the dark/brand rules become redundant as surfaces adopt — the rationalization queue plans to replace the exclusion chain with **prose-scoped links**.

## Known friction
- **The exclusion chain is the contract's weak point**: a new non-text-link component inside `main` WILL be painted as a text link until it is added to every copy of the chain (base, dark, brand ×2 hover variants). Symptom: unwanted hairline underline + `!important` hover color that beats your component hover.
- The `!important` on hover color is load-bearing (it must beat scope-frozen colors) — component hovers inside prose can't out-specificity it; opt out via the chain instead.
- Longhand-after-shorthand ordering matters: `text-decoration` shorthand resets thickness/offset, so every rule re-states the longhands after it. Copy the pattern exactly.
- `:not(.badge)` on the brand rule was added 2026-07-02 (no badge existed on `.section-brand` live); required by the operator's badge-inversion ruling on brand surfaces.

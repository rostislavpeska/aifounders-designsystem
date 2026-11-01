# THE THEME.CSS CONTRACT — composition rules + override protocol

**Operator verdict 2026-07-16:** page templates are *inspiration* (reference
anatomy in the styleguide Templates group + docs/templates/*.md). What is
**contractual** is this file: what a theme's CSS may be, and how overrides
happen when they must. Every law below was paid for on the AIF adoption
branch — sources cited as (Lx) map to the lessons ledger in
docs/proposals/AIG-ADOPTION-PLAN.md §2.

Applies to every DS-adopted theme (aifounders, aiguild). The theme ships
**exactly one stylesheet**: `assets/css/theme.css`, enqueued with a dependency
on `aifds-components`.

---

## 1. Ownership split

| Layer | Owner | Files |
|---|---|---|
| normalize, fonts, tokens, components, icons, component JS, vendor overrides, section/surface primitives | **DS plugin** | `aifds-*` handles |
| page composition: layout grids, section rhythm, template-specific arrangement, theme-only components not yet distilled | **theme** | `theme.css` (one file) |
| content | WP/ACF | — |

## 2. What theme.css MAY contain

1. **Page composition**: layout grids (`.articles-grid`, `.news-columns`),
   section wrappers/rhythm, template-specific arrangement — built FROM DS
   tokens and DS layout primitives.
2. **Theme-only components** the DS does not yet own — every value from DS
   tokens, colors only via surface roles (`--bg`, `--text`, `--link`, …),
   and carrying a dated comment naming them as **sweep candidates**.
3. **Documented overrides** per the protocol in §4 — nothing else.

## 3. What theme.css MUST NEVER contain

1. **Token definitions or redefinitions** — no `--spacing-*`, `--size-*`,
   palette values, no "backfill" shims mapping foreign token names (L12;
   the 38-dupe purge, the blog.css AIF-shim).
2. **Selector collisions with the DS** — no rule whose selector also exists
   in components.css (L-dup: 19 collisions on AIF are debt, not precedent).
   If the DS renders it wrong, fix the DS or file an override (§4).
3. **Prose/context rules above (0,1,0)** — element-scoped prose may ride a
   context class ONLY when guarded so it cannot match component-classed
   elements: `.content-section h3:not([class])` (L5, the preview-card leak).
4. **Hardcoded colors** — every color resolves from the surface scope. If a
   surface renders the wrong color, the scope mapping is wrong — fix it in
   the DS (L9, section-deep).
5. **`!important` against DS rules** and specificity stunts (`:not(#_)` etc.).
6. **`apply_filters('the_content')` styling workarounds** — component copy is
   formatted by `aif_component_content()` at the PHP layer (L4).
7. **Legacy token names** — the compat shim exists for migration windows, not
   as a permanent dialect (L6).

## 4. THE OVERRIDE PROTOCOL (when the DS output is wrong for a theme)

Escalate in order; stop at the first step that resolves:

1. **Surface first.** Wrong color/contrast on a section? The fix is the
   surface scope (use the right scope class, or fix the scope's role mapping
   in the DS). Never repaint the element (L9).
2. **DS bug?** If the DS renders wrong on BOTH brands, it's a DS bug — fix
   components.css/tokens in the DS repo. The theme never patches DS bugs.
3. **DS gap?** If the component doesn't exist in the DS, it's a theme-only
   component (§2.2) flagged as a sweep candidate — NOT an override of
   something DS-owned.
4. **Genuine theme override** (rare: brand-legitimate divergence from a
   DS-owned component). Then ALL of:
   - a dated operator-verdict comment at the rule:
     `/* OVERRIDE (operator YYYY-MM-DD): <what> diverges because <why>.
        DS default: <what the DS renders>. */`
   - an entry in the theme's `OVERRIDES.md` ledger (selector, reason, date,
     DS default) — silent divergence is debt, recorded divergence is design;
   - specificity: exactly one class above the DS rule it overrides — never
     `!important`, never ID tricks;
   - a Playwright assertion pinning the overridden value, so the override is
     load-order-proof and visible in tests (L8).
5. **Sunset rule.** Every override is re-examined when its component next
   changes in the DS; the ledger is the checklist.

## 5. Enqueue & dependency laws

1. One theme stylesheet, one handle, dep on `aifds-components` — the DS chain
   (normalize → fonts → tokens → components → theme) is the only cascade
   order (theme loads LAST; ties at equal specificity go to the theme —
   which is why §3.2 bans selector collisions outright).
2. Retiring/renaming any handle ⇒ repo-wide grep for dependents first (L2 —
   WP silently drops styles with missing deps; it killed every landing page
   once).
3. Per-page CSS (landing per-slug files) depends on the theme handle and
   keys on body classes — scoped, additive, never redefining DS or theme
   rules.
4. JS: engines come from the DS (`aifds-engines` theme support); the theme
   dequeues its dying twins in the same commit it flips the support flag.

## 6. Enforcement (what makes this a contract, not a wish)

- **Static gate** (build-time, both repos): selector-collision check
  (theme.css ∩ components.css = ∅), token-redefinition check, `!important`
  / hardcoded-hex scan, prose-specificity guard check. Runs with the DS
  `lint-css` step.
- **Rendered gate** (Playwright): page specs assert computed styles on real
  pages, both brands; every override ledger entry has a pinning assertion;
  every fix commit carries a render assertion (L8).
- Production verification = page specs with `BASE_URL`; the styleguide is
  local/admin-only by design.

## 7. Relation to templates

The Templates styleguide group + docs/templates/*.md are **inspiration**:
canonical anatomies, surface rhythm, component vocabulary, named gaps. Themes
compose freely against them. The moment a theme's composition needs to fight
the DS's CSS, it has left composition and entered §4 — that line, not the
template, is the contract.

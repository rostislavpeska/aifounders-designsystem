# Homepage (AIF front page)

**Type:** page template · **Status:** adopted (AIF theme, 2026-07-08) · **Source:**
`aifounders_web/wp-content/themes/aifounders/front-page.php` · **Live:** `http://localhost:8090/`

> First page-template captured during the theme→DS adoption. NOT yet a DS
> artifact — this is the harvest record so the template can be distilled into
> the DS later (operator: "add page templates to the DS after we're done").
> Documents the ORDERED section stack, the DS scope/component each section
> rides, what stays theme composition, and the residual leaks still to clean.

## The section stack (top → bottom)

Every section is a full-width `<section>`; the world it renders in is set by its
**surface scope class** (the DS's `--bg`/`--text`/`--raised`/`--brand` re-resolve
per scope). Data-driven sections self-hide when their query/field is empty.

| # | Section | Scope | DS components | Composition (stays theme) |
|---|---------|-------|---------------|---------------------------|
| 1 | **Hero** | `section-brand` | `.hero-aif__form` (newsletter capture + CONJOIN LAW), `.btn--lg btn--primary-inverted`, `.lead` subheadline, consent note | `.hero-aif`, `.hero-aif__wrapper`, `.highlight`/`.hero-glitch` |
| 2 | **Info bar** | host `section-dark` → child `.info-bar` | `.info-bar` / `__wrapper` / `__item` (rides `--raised` = lighter dark) | ACF `hp_info_quotes` loop |
| 3 | **Dark quote + blurbs** | `section-dark` | `.blurb` / `__headline--md` / `__text` / `__action` + `.btn--sm btn--secondary` | `.aif-dark-section` + `__quote` + `__blurbs` **bleeding container** (operator keeps the bleed) |
| 4 | **Editorial (Články)** | `content-section--tertiary` (light-3) | `.article-card` (variant `editorial`) → `.preview-card`, `.btn--md btn--tertiary` | `.content-section__wrap`, `.articles-grid--editorial` (3/2 responsive), section header |
| 5 | **Signály** | `content-section--secondary` (light-2) | `.signal-card` → `.preview-card--condensed`, `.btn--md btn--tertiary` | `.articles-grid`, section header |
| 6 | **Události (Events)** | `content-section--tertiary` | `.event-card` → `.preview-card--condensed`, `.btn--md btn--tertiary` | `.articles-grid`; **future-events query** → hides locally when none |
| 7 | **Logos** | `content-section` (light-1) | — | `.logos-section`, `.logos-grid`, ACF partners |
| 8 | **Osobnosti (Personas)** | `section-dark` | `.persona-card` (border-left divider), `.btn--md btn--secondary-inverted` | `.personas-grid` / `--centered`; **inline `background-color` override (leak)** |
| 9 | **Newsletter band** | `section-brand` | `.hero-aif__form` (same capture as hero + CONJOIN LAW) | `.newsletter-cta` + `__wrapper` / `__headline` / `__subtitle` |
| — | **Footer** | `section-dark` / footer scopes | `.footer` family (see `footer.md`) | — |

## Migration decisions (what changed during adoption)

- **Info bar → canonical anatomy** ([info-bar.md](../components/info-bar.md)): scope
  moved onto a host `<section class="section-dark">` with `.info-bar` as a child
  `<div>` so the band rides `--raised` (`#1f2126`) natively — a distinct lighter
  band above the black dark-section, **no theme override**. The earlier
  `.info-bar section-dark` (both classes on one element) flattened it to `--bg`.
- **Newsletter band form → `.hero-aif__form`** (bucket-2): the band's form gained
  the DS brand-hero context; the theme's `.newsletter-cta__form .form-control*`
  reimplementation (legacy tokens) was deleted. Verified pixel-identical.
- **Signal + Event cards → `.preview-card--condensed`**: 22px headline (was the
  full 30px+ preview headline).
- **Hero subheadline → `.lead`**: DS text style; theme font declarations stripped.
- **Card dates → `date_i18n('j. F Y', get_the_time('U'))`**: Czech month names.
- **Editorial 3/2**: `.articles-grid--editorial` shows 2 cards <1440px (3rd hidden),
  3 at ≥1440px; `posts_per_page = 3`.

## Residual theme leaks (open — for the page-template distill, NOT yet fixed)

These render acceptably but are NOT DS-sourced; capture them so the future
template resolves them:

1. **Section-header perex** (editorial §4, signals §5, events §6): inline-styled
   `<blockquote style="border-left: 4px solid var(--color-*-support)">` with
   legacy tokens (`--color-bg-inverse-tertiary`, `--font-weight-bold`). Should map
   to a DS section-header + perex/blockquote voice.
2. **Section-header + button wrappers**: inline `style="margin-bottom:…"` and
   `style="display:flex; justify-content:center; margin-top:…"`. Layout that
   belongs to a DS section-header / section-cta primitive.
3. **Personas section**: inline `style="background-color: var(--color-bg-inverse-secondary)"`
   on the `<section>`, inline `<h2 style="text-align:center; color:…">`, and
   `.persona-card section-dark style="max-width:none"` per card. Persona grid +
   dark-2 surface want a real DS scope, not inline overrides.
4. **`.logos-section` / `.logos-grid`**: entirely theme composition — a candidate
   DS "logo wall" primitive if it recurs on ≥2 templates (one-offs law).

## Data conditions (local vs production)

- **Events hidden locally**: the query filters `event_start_date >= now`; no future
  events in the local DB → section correctly absent (data, not a defect).
- **Editorial** requires posts WITH a `_thumbnail_id`; **cross-language injection**
  (`aif_inject_cross_lang`) can surface all-language articles here.

## Verification

All sections verified in real Chrome (operator confirmation loop, 2026-07-08):
info-bar lighter-dark distinct from section below; newsletter forms pixel-identical
top/bottom; condensed card headlines 22px; editorial 3-up at desktop; tertiary
buttons DS-canonical (the `--raised` pop attempt was reverted — see
[ADOPTION-FEEDBACK](../proposals/ADOPTION-FEEDBACK.md), still parked).

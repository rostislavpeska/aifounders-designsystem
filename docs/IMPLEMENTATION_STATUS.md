# Implementation Status — component ledger

One row per shipped unit. **Row granularity follows decision space** (see
`docs/proposals/VECTOR-DS.md` §4) — this ledger is the master list for the
future vector-DS rows. Per-component reference docs live in
`docs/components/`. Statuses: **shipped** (in `assets/css/components.css`,
gated, specimen live) · **reserved** (token API waiting for theme adoption) ·
**tracked** (approved, not built).

Last regenerated: 2026-07-04 (deep-hygiene run; gate 52/52 both brands).

## Foundations (token layers — not vectorized, shipped whole)

| Unit | Source | Status | Notes |
|---|---|---|---|
| Color system (3 layers) | `tokens/palette.*.json` · `semantic.json` · `scopes/*.json` | shipped | 62 palette names/brand (perfect parity) · 48 semantic · 7 scopes. Laws gated by `build/lint-css.mjs` + Playwright. |
| Typography system | `tokens/typography.json` · `type-styles.json` | shipped | sizes/leading/flow/fonts/weights + 18 style bundles; mobile via `mobile-size` in-bundle. No 300/Light (law). |
| Spacing / containers / strokes | `tokens/base.tokens.json` | shipped | spacing scale 2–120 · containers · stroke-1/1_5/2/3/4/6 · shadows (reserved). |
| Breakpoints (closed set) | `tokens/breakpoints.json` | shipped | 600/768/1024/1440; BOUNDARY LAW max = value−1; lint LAW 4. |
| Icon system | `inc/icons.php` + `icons/` | shipped | outline/shape/colored taxonomy; currentColor law; STEPPED stroke (<16 → 1px · 16–32 → 1.5px · >32 → 3px) via `non-scaling-stroke` (operator 2026-07-04). |

## Components & patterns

| Row | Classes (entry points) | Specimen | Doc | Status |
|---|---|---|---|---|
| Text styles | `.heading-xl/lg/md/sm/xs` `.lead` `.body-lg/md/sm` `.caption` `.meta` | `text-classes` | [text-styles.md](components/text-styles.md) | shipped |
| Text elements | `.text--perex`, blockquote, ul/ol (indent-signifier) | `text-elements` | [text-elements.md](components/text-elements.md) | shipped |
| Numbered headings | `.numbered-headings` | `text-elements` | [numbered-headings.md](components/numbered-headings.md) | shipped |
| Prose / article layout | `.article-layout__content`, flow rhythm | `prose` | [prose-layout.md](components/prose-layout.md) | shipped |
| Global link system | link recipe + exclusion chain | — | [links.md](components/links.md) | shipped |
| Card link utilities | `.card-title-link` | — | [links.md](components/links.md) | shipped |
| Section contexts (surfaces) | `.section-light/dark/brand`, `.surface-support` | `surfaces` | [section-contexts.md](components/section-contexts.md) | shipped |
| Button | `.btn` + `--primary/secondary/tertiary/link/destructive` + `--lg/md/sm`, `.button-group` | `buttons` | [button.md](components/button.md) | shipped |
| Newsletter capture | `.hero-aif__form`, `.aif-ecomail-form*` | `form-composition` | [newsletter-capture.md](components/newsletter-capture.md) | shipped |
| Badge | `.badge` + variants, `.badge__icon` | `badges` | [badge.md](components/badge.md) | shipped |
| Info box | `.info-box` + `--info/success/warning/error/neutral` + `--small/--article` | `info-box` | [info-box.md](components/info-box.md) | shipped |
| Data table | `.data-table` + `--condensed/--large/--plain/--banded`, `.table-scroll` wrapper | `data-tables` | [data-table.md](components/data-table.md) | shipped |
| Record list | `.record-list` / `.record` / `.record__*` (N-column abstract) | `record-list` | [record-list.md](components/record-list.md) | shipped |
| Preview card (article / event / signal / position / management) | `.preview-card` / `__photo/__headline/__meta/__badges/__skills/__text/__actions` + `--condensed` | `preview-card` | [preview-card.md](components/preview-card.md) | shipped (distilled 2026-07-05; size x slots — production's 6 variants collapsed) |
| Accordion | `.accordion` / `.accordion__*` + `[data-accordion="exclusive"]` | `accordion` | [accordion.md](components/accordion.md) | shipped (harvested 2026-07-04) |
| Breadcrumb | `.breadcrumbs` / `.breadcrumb__*` | `breadcrumb` | [breadcrumb.md](components/breadcrumb.md) | shipped (distilled 2026-07-04) |
| Pagination | `.archive-pagination` (+ WP `.page-numbers`) | `pagination` | [pagination.md](components/pagination.md) | shipped (distilled 2026-07-04) |
| Nav tabs | `.nav-tabs` / `.nav-tabs__tab(--active)` | `nav-tabs` | [nav-tabs.md](components/nav-tabs.md) | shipped (distilled 2026-07-04; theme alias `author-tabs`) |
| Reference card (testimonial / case study) | `.reference-card` / `__avatar/__logo-wrapper/__title-group/__body/__icon/__content` | `reference-card` | [reference-card.md](components/reference-card.md) | shipped (distilled 2026-07-04; ONE canonical — surfaces replace variants) |
| Persona card | `.persona-card` (+ `.persona-card-slot` container) / `__avatar/__content/__header/__bio/__meta/__socials` + `--flush` / `--vertical` | `persona-card` | [persona-card.md](components/persona-card.md) | shipped (distilled + container-rebuilt 2026-07-04; surface-riding; orientation is CONTAINER-derived at slot ≥ 560 — [PERSONA-CARD-HORIZONTAL](proposals/PERSONA-CARD-HORIZONTAL.md) RESOLVED) |
| Course card | `.course-info-card` (+ `.course-card-slot` container) / `__illustration-lg/__content/__eyebrow/__title/__subtitle/__description` + `--inactive` + `course-accent--*` | `course-card` | [course-card.md](components/course-card.md) | shipped (distilled 2026-07-05; AIG-only living code — AIF blocks dead since the native-promo redesign; orientation CONTAINER-derived at slot ≥ 720; 3 new tokens: leading-snug, tracking-display, bg-base) |
| Engagement (Aha! + share + toast) | `.aif-engagement` / `.aif-aha(--clicked)` / `.aif-share` / `.aif-engagement-toast(--open)` | `engagement` | [engagement.md](components/engagement.md) | shipped (distilled 2026-07-05; byte-identical cross-brand twins; engine ported to `js/components/engagement.js`, AddToAny boundary documented; 0 new tokens) |
| Comments (threaded discussion) | `.article-comments` / `.comment-author/__meta` / `.comment-body__bubble` / `.children` connectors / `.aif-tombstone` / `.comment-respond` | `comments` | [comments.md](components/comments.md) | shipped (distilled 2026-07-05; AIF-only — the canon AIG adopts; avatar gains `--xs` + `--initials`; edit/delete engine stays plugin territory; 0 new tokens) |
| Modal | `.modal` / `__overlay/__container/__close/__title` + `data-modal-open` + `js/components/modal.js` | `modal` | [modal.md](components/modal.md) | shipped (distilled 2026-07-05, REBUILT same day per operator veto — THE form modal, DS form system inside, no variants; a11y engine + dvh mobile sheet; 0 new tokens) |
| Sticky bar | `.sticky-bar(--email/--button/--chatbot-clear)` / `__inner/__pitch/__consent/__form/__btn-mobile/__meta` + `data-sticky-*` + `js/components/sticky-bar.js` | `sticky-bar` | [sticky-bar.md](components/sticky-bar.md) | shipped (2026-07-06; SIX instances collapsed; THE SAMPLER generalized — surface via .section-dark flip; 1 new token: shadow-up) |
| Header | `.main-header(--scrolled/--overlay)` / `__inner` · `.site-logo` · `.nav-item(--active/--has-dropdown)` + `__trigger` · `.nav-dropdown(-item)` · `.burger-toggle` · `.mobile-menu-overlay(--open)` / `.mobile-nav-item` / `.mobile-lang-row` · `.reading-progress` + `js/components/menu.js` | `header` | [header.md](components/header.md) | shipped (2026-07-06 — byte-identical twins distilled; surfaces replace --light/--dark; 2 new roles: nav-active + progress-fill; a11y GM: focus-within dropdowns, aria, scroll-lock canon) |
| Footer | `.footer(--chatbot-clear)` / `__inner` · `__newsletter-section/row/headline/form-wrap` (dark-2) · `__partners/label/partners-grid` · columns = `.stack-grid`+`.blurb` · `__subtle-link` · `__bottom-bar/logo-link/social-icons/social-link/social-icon` · `__legal-section/divider/legal/legal-link` | `footer` | [footer.md](components/footer.md) | shipped (2026-07-06 — byte-identical twins; band stack, all optional but legal; dark-2 lives as the band; dark-1 `.footer` alias dropped; canon columns; 0 new tokens) |
| Blurb + stack grid (THE BENEFITS FAMILY) | `.blurb` / `__icon/__eyebrow/__headline(--md/--lg)/__text(--lg/--sm)/__action` · `.stack-grid(--divided/--numbered)` + `--stack-cols/--stack-fill` | `blurb` | [blurb.md](components/blurb.md) | shipped (2026-07-06 — the family resolved: 6 dialects retired; PIZZA-LAW dividers; closed rungs; 1 new bundle: benefit-display) |
| Info bar | `.info-bar` / `__wrapper/__item` | `info-bar` | [info-bar.md](components/info-bar.md) | shipped (2026-07-06 — first benefits-family member; operator V2: 4px bold + perex indent; surface-riding --raised band resolves the AIG light experiment; 1..n statements; 0 new tokens) |
| Avatar | `.avatar--xs/sm/md/lg` + `.avatar--initials` | `avatars` | [avatar.md](components/avatar.md) | shipped (+ `--xs` 48px & `--initials` mode 2026-07-05, harvested from comments + the header nav-avatar) |
| Input (+ textarea variant) | `.form-group` `.form-control` | `input` | [input.md](components/input.md) | shipped |
| Select | `.form-select-*`, `.dropdown` | `select` | [select.md](components/select.md) | shipped |
| Datepicker | `.datepicker`, `.calendar-*` | `datepicker` | [datepicker.md](components/datepicker.md) | shipped |
| Checkbox | `.selection-item--checkbox` | `checkbox` | [checkbox.md](components/checkbox.md) | shipped |
| Radio | `.selection-item--radio` | `radio` | [radio.md](components/radio.md) | shipped |
| Consent (GDPR) | `.selection-item--consent` | `consent` | [consent.md](components/consent.md) | shipped |
| Segmented control | `.segmented` / `.segmented-option` | `segmented` | [segmented-control.md](components/segmented-control.md) | shipped (promoted 2026-07-04) |
| File dropzone | `.dropzone` / `.dropzone-*` | `file-upload` | [file-dropzone.md](components/file-dropzone.md) | shipped (promoted 2026-07-04) |
| Forms composition (pattern) | `.form-stack`, `.form-scale-small`, `.input-pair`, conventions | `form-composition` | [forms-composition.md](components/forms-composition.md) | shipped (+ `.form-stack` 2026-07-05 — the named form rhythm; submits require a ladder rung) |
| Icons | `aigds_icon()` + `.icon--*` | `icons`, `icon-system` | [icons.md](components/icons.md) | shipped |

## Tracked (approved, not built)

| Unit | Ruling | Notes |
|---|---|---|
| Progress bar / meter | extracted from record-list (operator 2026-07-04) | standalone component usable in any card/popup; `--text-on-status` token is reserved for its marker. |
| Data-table mobile transform | operator 2026-07-04 | a SPECIAL OVERRIDE class (same table, stacks on mobile) for landing-page cases — opt-in only, never the default; `.table-scroll` horizontal scroll stays the default for wide tables. |
| Heading ramp for pages | `docs/proposals/HEADING-RAMP.md` | awaiting operator verdict. |
| Vector DS (Supabase index) | `docs/proposals/VECTOR-DS.md` | architecture agreed; `docs/components/` files are the future row sources; `.ds.yaml` schema undecided. |

### Distillation queue (operator 2026-07-04 — run each through `ds-distill`)

Usage pointers are the harvest starting points, not rulings; scope lines get
set per run.

| Unit | Known usage pointers (starting points for the harvest) |
|---|---|
| Benefits FAMILY (benefits + dark blurbs + hero mini-benefits + nl-benefits + Šest oblastí + info-bar + footer-blurb) | EXPANDED by operator 2026-07-06 — 'semantically possibly one component', extreme inconsistency. **CENSUS + SANDBOX PROPOSALS DELIVERED**: [BENEFITS-FAMILY-MAP](proposals/BENEFITS-FAMILY-MAP.md) (7 dialects, 5 title voices, 5 body voices, 3 accent grammars) + three competing architectures rendered as full replica pages in the NEW SANDBOX (`/?aigds_sandbox=1`: benefits-a ONE primitive · benefits-b TWO rows · benefits-c conservative). **Build blocked on operator verdicts** (architecture pick + the map §4 sub-verdicts: display clamp, info-bar light experiment, icon fate, .reveal row, footer-blurb membership). |
| Benefits (numbered eyebrow-line cards) — ORIGINAL POINTER ROW | operator 2026-07-04: "one of the more complex." REAL selector: `.cert-card` (historical name — ABSORBS the cert-card sweep candidate). AIG components.css ~2792: editorial numbered card — 2px `border-top` hairline, CSS-counter `01/02/03` eyebrow (mono/tracked/inverse-link), clamp(24→32) extrabold title (MECHANISM LAW check: fluid = display class), 18px text, arrow link; icon hidden, `--with-icon` re-enables; `reveal`/`--reveal-delay` scroll animation (possible separate behavior primitive). Usages: AIG homepage "Jak fungujeme" (dark), AIG course detail `kurz-benefits` "Co si odneseš" (`card-row--three`), AIG newsletter landing `nl-benefits` (override skin — UNIFY per operator). AIF has the OLD icon cert-card (~7 rules) — unification ruling needed. |

### Road to deployment (operator 2026-07-06 — order matters; DS is NOT ready to install yet)

1. **Full VECTOR-DS system audit** — completeness pass (nothing missed) + refactor ([VECTOR-DS](proposals/VECTOR-DS.md) is the base).
2. **Clean Figma import** — ✅ UNBLOCKED 2026-07-06: Figma MCP verified authenticated (whoami = operator, Full seat on "My Projects") + `figma@claude-plugins-official` installed (npx route; no bare `claude` CLI on device).
3. **GitHub repo cleanup** — PLANNED ([PUBLIC-REPO-PLAN](proposals/PUBLIC-REPO-PLAN.md)): new repo **`aifounders-designsystem`** (operator ruling: AIF = the open-source platform, AIG = a commercial project built on it), fresh history, operator creates it in the UI → bootstrap push ready.
4. **Vectorization process** — n8n vectorization workflow + a NEW Supabase table.
5. THEN: plugin installation on BOTH websites + theme-code refactoring — possibly two parallel Fable agents (one AIF, one AIG).

### Sweep candidates (full both-theme sweep 2026-07-04 — found, NOT approved; operator picks)

Everything below exists in production and is NOT covered by the DS or the
queue above. Listed by decision space; noise (plugin chrome, one-off page
layout, `lp-*` landing primitives ruled out of scope earlier) excluded.

| Candidate | Pointers | Notes |
|---|---|---|
| Dark blurb | `.dark-blurb` both themes (~69 rules); `.dark-blurb--secondary` is a **dark-2 scope selector** | dark card: illustration + title + description + link; SCOPE-CENSUS dark-2 verdict affects it |
| Ad card / native promo | `.aif-native-promo` (+ color variants), `[add]` shortcode, AIF | editorial inline ad: kicker + title + meta + arrow CTA, category-colored border |
| Protocol card | `.protocol-card`, `[protocol]` shortcode, AIF (aifounders.cz/o-nas) | a derivative of the reference card, but the shared-voice abstraction was WITHDRAWN (operator 2026-07-05: "forget about the Blue protocol… this level of abstraction is simply not possible now") — distills standalone later |
| Newsletter-preference card | `.nl-card`, AIF preference center + landing | checkbox styled as a colorful topic card — relates to the selection family |
| Archive header (+ event/signal variants) | `.archive-header`, both themes (`components/archive-header.php`); AIF adds event/signal extensions | brand band: title + subheadline + `.archive-filter__link` row |
| Sticky CTA bar | AIG course sticky (`.sticky-cta`) + `.lp-sticky` newsletter bar (both) | one "sticky bottom bar" decision space, two intents (promo CTA vs capture); reveal/hide on scroll |
| Register banner | `.register-banner`, AIF | unexamined |
| Toast | `.aif-engagement-toast`, BOTH themes | generic feedback primitive (currently only engagement uses it) |
| Video embed (+ lightbox) | `.lp-video`/`.article-hero__video-wrapper`/`.aif-video` + AIG `video-embed.css`; `article-image-lightbox.js` AIF | 16:9 wrapper; zoom-on-click behavior |
| Podcast / audio player | `.article-audio`, both themes | article audio embed |
| Logo grid / partners | `.logo-grid`/`.logos-grid` + `[partners]` shortcode, both themes | logo grid + optional section title; borderline layout-utility |
| Hero family | `.page-hero` (brand scope selector) / `.article-hero` / `.author-hero` / `.homepage-hero` / `.hero-aif` | SECTION-level batch (HANDOFF Batch-2); author-hero is the persona-detail hero |
| Testimonials carousel | `.testimonials-carousel` (AIG single-kurz renderer: 1 = centered, 2 = grid, 3+ = Apple-swipe carousel <1024, scroll-snap) | composition wrapper around reference-cards — discovered during the testimonial distill, out of that row's scope |
| Application row | `[applications]` shortcode rows, AIF course pages | date/time/location/price/CTA — **likely a record-list CONSUMER, not a new component** — verify at harvest |

## Deprecated aliases (kept for markup compat, rationalization queue)

| Alias | Replacement | Ruling |
|---|---|---|
| `.btn--outline` | `.btn--secondary` | duplicate; queue for removal at adoption. |
| `.btn--primary-inverted` | newsletter/nav CTA classes (conversion-kit batch) | reclassified 2026-07-02: not a hierarchy member. |
| badge `--inverse` variants | surface-aware badges | on rationalization queue. |

## Reserved token API (defined, unconsumed until theme adoption)

Full color-family ladders (`lime-*`, `magenta-*`, `secondary-*`, `tint-*`),
type bundles (`hero`, `code`, parts of `description`), `shadow-sm/md/lg`,
`illustration-size-*`, `transition-*`, spare spacing steps, `text-on-status`
(progress-bar marker), `raised`. These are the harvested public API for
Stage-2 theme adoption — do NOT retire as "unused" (audited 2026-07-04).

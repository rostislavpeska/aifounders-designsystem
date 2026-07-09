# Implementation Status — component ledger

One row per shipped unit. **Row granularity follows decision space** (see
`docs/proposals/VECTOR-DS.md` §4) — this ledger is the master list for the
future vector-DS rows. Per-component reference docs live in
`docs/components/`. Statuses: **shipped** (in `assets/css/components.css`,
gated, specimen live) · **reserved** (token API waiting for theme adoption) ·
**tracked** (approved, not built).

Last regenerated: 2026-07-04 (deep-hygiene run; gate 52/52 both brands).

**Adoption (live):** AIF (Stage 2) is underway — additive rebuild landed
2026-07-08 (the theme retired its own `components.css` wholesale; the DS plugin
owns every component, the theme ships one thin composition `theme.css`), forms
moved onto DS markup 2026-07-09 (zero-drift law). A build/compat-shim.mjs token
shim bridges the theme's 141 legacy names during the migration. AIG (Stage 3)
next. Method + lessons: `docs/proposals/ADOPTION-PLAYBOOK.md`.

**Figma projection:** session-1 import is live (`assets/figma-map.json`) —
variable collections + modes + palette variables + Auto Layout components in
file `HHYhpNh2JoxtKSKm7Ecpli`; manual component refinement pending
(`docs/proposals/FIGMA-IMPORT.md`).

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
| Icons | `aifds_icon()` + `.icon--*` | `icons`, `icon-system` | [icons.md](components/icons.md) | shipped |

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
| Benefits FAMILY (benefits + dark blurbs + hero mini-benefits + nl-benefits + Šest oblastí + info-bar + footer-blurb) | EXPANDED by operator 2026-07-06 — 'semantically possibly one component', extreme inconsistency. **CENSUS + SANDBOX PROPOSALS DELIVERED**: [BENEFITS-FAMILY-MAP](proposals/BENEFITS-FAMILY-MAP.md) (7 dialects, 5 title voices, 5 body voices, 3 accent grammars) + three competing architectures rendered as full replica pages in the NEW SANDBOX (`/?aifds_sandbox=1`: benefits-a ONE primitive · benefits-b TWO rows · benefits-c conservative). **Build blocked on operator verdicts** (architecture pick + the map §4 sub-verdicts: display clamp, info-bar light experiment, icon fate, .reveal row, footer-blurb membership). |
| Benefits (numbered eyebrow-line cards) — ORIGINAL POINTER ROW | operator 2026-07-04: "one of the more complex." REAL selector: `.cert-card` (historical name — ABSORBS the cert-card sweep candidate). AIG components.css ~2792: editorial numbered card — 2px `border-top` hairline, CSS-counter `01/02/03` eyebrow (mono/tracked/inverse-link), clamp(24→32) extrabold title (MECHANISM LAW check: fluid = display class), 18px text, arrow link; icon hidden, `--with-icon` re-enables; `reveal`/`--reveal-delay` scroll animation (possible separate behavior primitive). Usages: AIG homepage "Jak fungujeme" (dark), AIG course detail `kurz-benefits` "Co si odneseš" (`card-row--three`), AIG newsletter landing `nl-benefits` (override skin — UNIFY per operator). AIF has the OLD icon cert-card (~7 rules) — unification ruling needed. |

### Road to deployment (operator 2026-07-06 — order matters; DS is NOT ready to install yet)

1. **Full VECTOR-DS system audit** — ✅ **COMPLETE 2026-07-06** ([AUDIT-2026-07](proposals/AUDIT-2026-07.md)): inventories/tokens/docs verified clean; 5 real bugs fixed; the gate grew L0→L4 (92 checks: layout sweep + a11y baseline + behavior contracts); rows GENERATED (build/rows.mjs, 40/40, .ds.yaml dead). Out-tray: HEADING-RAMP, sweep picks, Phase-C retirements, a11y ratchet, repo §6 verdicts.
2. **Clean Figma import** — ✅ **ARCHITECTURE RESOLVED 2026-07-07** ([FIGMA-IMPORT](proposals/FIGMA-IMPORT.md), 4 operator verdicts): Palette (AIF·AIG) ← Semantic (all 8 scope modes; Pro plan = 10 since Oct 2025) · Type Primitives (Desktop·Mobile — the clamp answer) · staged tiers · [`ds-figma-import` skill](../.claude/skills/ds-figma-import/SKILL.md) ratified. MCP authenticated; plugin installed. **NEXT: the import session** (fresh session so the bundled figma skills activate; session 1 = variables + styles + Foundations + icons + Tier-0 atoms → operator review in Figma). Code Connect is plan-gated (Org/Ent) — node-id backfill via `assets/figma-map.json`, `code_connect` deferred.
3. **GitHub repo cleanup** — ✅ **EXECUTED 2026-07-07** ([REPO-CLEANUP-SPEC](proposals/REPO-CLEANUP-SPEC.md) R0–R6 done): **AIFDS rename** landed (`eb445a0`, 464 replacements/71 files + plugin file/slug/text-domain, gate 92/92 before AND after, plugin reactivated in the container, residue clean); vector store rebuilt at the rename ref; **public repo synced** ([`aifounders-designsystem`](https://github.com/rostislavpeska/aifounders-designsystem) `d219848` = 2.0.0-rc.1, LICENSE → AI Founders, proposals all-in, `.claude/skills` agentic layer ships, parity diff 0). **Remaining = R7, operator:** flip repo public · optionally rename the compose mount target to `aifounders-designsystem` (cosmetic slug; current `aig-design-system` dir works) + reactivate · flip the n8n ingest `Repo Params` default at cutover · archive the factory repo.
4. **Vectorization process** — ✅ **BUILT + LIVE-TESTED 2026-07-07** (house-pattern conformant, mandated pattern check done): Supabase `ds_component_vectors` + `match_ds_components` RPC (mirrors `match_positions`, adds `_class_like`/`_token_like`); n8n **PULL-model** ingest `AIF Sub: DS Vector Ingest (components)` (key-gated webhook kick → n8n fetches `assets/ds-rows.json` itself via the existing "GitHub account" credential, Main-Harvester-2 pattern → guard-if-0 → scoped wipe → LangChain insert, `text-embedding-3-small`; source repo = one `Repo Params` default: factory now → `aifounders-designsystem` at flip) + `AIF DS Components RAG Search (MCP)` (same shape as the positions RAG search); repo side = [`ds-lookup` skill](../.claude/skills/ds-lookup/SKILL.md) only (zero-config via the n8n MCP; key self-served from the workflow; no env vars, no push script — operator veto). **✅ COMPLETE 2026-07-07: full store LIVE — 40/40 components (80 chunks) ingested from GitHub in 8s** after the operator added `aig-desigsystem` to the n8n fine-grained PAT (the "n8n" token; 404-on-private caught live, guard refused the wipe until then). Smoke-tested ×3: "dark footer email capture" → Newsletter capture 0.518 · "sticky bottom bar that must not cover the chatbot" → Sticky bar 0.564 · "threaded discussion with replies, avatars, edit window" → Comments 0.62 (+`_min_similarity` floor verified). Left for later: add `aifounders-designsystem` to the same PAT at the public flip; optional `availableInMCP` flip of the search workflow.
5. THEN: plugin installation on BOTH websites + theme-code refactoring — possibly two parallel Fable agents (one AIF, one AIG).

### Sweep candidates (2026-07-04 sweep; statuses refreshed by the audit 2026-07-06 — 2 resolved, 2 partial, 10 open; operator picks)

Everything below exists in production and is NOT covered by the DS or the
queue above. Listed by decision space; noise (plugin chrome, one-off page
layout, `lp-*` landing primitives ruled out of scope earlier) excluded.

| Candidate | Pointers | Notes |
|---|---|---|
| ~~Dark blurb~~ | — | **RESOLVED 2026-07-06**: retired into `.blurb` + the divided stack grid (the benefits verdict); `--secondary` stays a dark-2 selector until theme adoption |
| Ad card / native promo | `.aif-native-promo` (+ color variants), `[add]` shortcode, AIF | editorial inline ad: kicker + title + meta + arrow CTA, category-colored border |
| Protocol card | `.protocol-card`, `[protocol]` shortcode, AIF (aifounders.cz/o-nas) | a derivative of the reference card, but the shared-voice abstraction was WITHDRAWN (operator 2026-07-05: "forget about the Blue protocol… this level of abstraction is simply not possible now") — distills standalone later |
| Newsletter-preference card | `.nl-card`, AIF preference center + landing | checkbox styled as a colorful topic card — relates to the selection family |
| Archive header (+ event/signal variants) | `.archive-header`, both themes (`components/archive-header.php`); AIF adds event/signal extensions | brand band: title + subheadline + `.archive-filter__link` row |
| ~~Sticky CTA bar~~ | — | **RESOLVED 2026-07-06**: shipped as `.sticky-bar` (six instances collapsed; email/button types + THE SAMPLER) |
| Register banner | `.register-banner`, AIF | unexamined |
| Toast | `.aif-engagement-toast`, BOTH themes | SHIPPED inside engagement 2026-07-05; still TRACKED as a generic feedback-primitive promotion (rename + generic API) — operator pick |
| Video embed (+ lightbox) | `.lp-video`/`.article-hero__video-wrapper`/`.aif-video` + AIG `video-embed.css`; `article-image-lightbox.js` AIF | 16:9 wrapper; zoom-on-click behavior |
| Podcast / audio player | `.article-audio`, both themes | article audio embed |
| Logo grid / partners | `.logo-grid`/`.logos-grid` + `[partners]` shortcode, both themes | PARTIALLY resolved: the footer partners band shipped 2026-07-06 (`.footer__partners`); the PAGE-level logo grid + shortcode remain — operator pick |
| Hero family | `.page-hero` (brand scope selector) / `.article-hero` / `.author-hero` / `.homepage-hero` / `.hero-aif` | SECTION-level batch (HANDOFF Batch-2); author-hero is the persona-detail hero |
| Testimonials carousel | `.testimonials-carousel` (AIG single-kurz renderer) | still open (composition wrapper around shipped reference-cards; 1/2/3+ display modes) — operator pick |
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

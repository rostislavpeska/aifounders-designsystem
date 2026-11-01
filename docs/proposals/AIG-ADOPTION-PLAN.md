# AIG Adoption Plan v2 — Stage 3: full DS migration of aiguild.cz

**Status: DETAILED EXECUTION PLAN (2026-07-16) — supersedes v1 in place.
Awaiting the §10 operator verdicts before any code moves.**

Built from four empirical probes (wf_dce25f31): the AIG unique-elements
deep-dive, live computed ground truth from localhost:8080 (stack booted,
HTTP 200), the full DS method corpus (playbook / refactor-spec /
css-architecture / adoption-feedback / 39 component docs), and the L1
selector-ownership pre-audit (class lists persisted in `/tmp/aig_audit/`).

**End state (the operator's charter):** ONE source of truth — the DS plugin
owns every component, token, icon, and engine; each theme ships exactly one
thin `theme.css` composition layer (THEME-CSS-CONTRACT.md is the binding
contract) plus its own page templates, data model, and brand-specific JS.
Two themes, zero duplicated component CSS, brands switch on
`[data-theme]` alone.

---

## 1. Strategy: hard cut at the beginning (unchanged, now safer)

AIF ran surgical sweeps with deletion LAST → ~40% fix-commits, 5 regressions
from one late deletion, 19 residual selector collisions. AIG inverts: **one
cut commit early**, made safe by the ownership audit that now already exists
(this research). The 12 laws (v1 §2) remain binding; the machinery that
enforces them is spelled out in §5–§7.

The AIF method's operating protocol carries over verbatim:
- **F1–F4 delta protocol** (ADOPTION-FEEDBACK.md): F1 theme fix (default) ·
  F2 DS bug (specimen-first, gate must GROW, mirror-sync) · F3 DS gap —
  **never the agent's call**: file a queue row, PARK the family, operator
  rules at the checkpoint · F4 deliberate delta → DELTAS.md line.
- **Checkpoints** C0 baselines · C1 post-cut sanity (highest risk) ·
  C2 batched F3 rulings · C3 behavior click-through · C4 full inventory.
- **Zero-drift law**: never re-draw a DS component in theme CSS; third-party
  engines (Fluent Forms) get a token-only ENGINE-ADAPTER gated by
  `form-adapter-lint.mjs` + a computed-style Playwright layer.
- **Commit discipline**: conventional commits, one per phase gate/family;
  gates never weaken; CRLF theme files handled with
  `git -c core.autocrlf=false add` (a 1-line fix must diff +1/−1).
- **Verification recipe** (operator's own): disable theme CSS in DevTools →
  the DS must render every component correctly → add back ONLY composition.
  Hard pages for real-Chrome checks: kurz detail + a long article.

## 2. Ground truth (what we're migrating — live @8080)

- Fonts rendering: Lazzer 900 all headings · Space Grotesk = lead + ALL
  buttons (700) · Inter = body · Spline Sans Mono minor. Palette: brand
  #f5c400, ink rgb(7,7,8), off-white #fffdf6, dark-alt #2a2a2a, light-band
  #e4e4e4. `data-theme="aiguild"` already stamped on `<html>`.
- **The DS chain is already double-loading**: theme tokens.css +
  fluent-forms-override.css each appear TWICE (theme copy + mounted DS
  plugin copy) — evidence the mount/enqueue state needs the pre-flight pass.
- Content: kurz 3 · lektor 3 · skill 18 · job_position 25 · kohort 4 ·
  post 15 · page 12 · faq 2 · prinos 3 · reference 3. **No published landing
  CPT** — the landing parity target is the `landing_html` newsletter page.
- **/kurz/ archive is an UNSTYLED index fallback** (English "Courses",
  light header, WP-block junk) — a pre-existing gap, NOT a parity target;
  flagged for a post-migration decision (real archive template or redirect).
- Zero console errors / zero 404 assets on every sampled page — the parity
  baseline starts clean; any new error is a regression.

## 3. AIG unique elements — the verdict table

| Element | Verdict | Notes |
|---|---|---|
| Course hero (`hero-card` + `course-headline` + image-wipe) | **DISTILL → DS hero family** | The kurz parity pass's main distillation; fold page-hero, author-hero, homepage hero shell into ONE hero-family pass (§10.5) |
| Cohort table (`cohort-list/row`, Termíny engine) | **MAPS-TO record-list + data-table** | DS record-list was built FROM it. Port deltas: `--cards` "Jak to probíhá" mode (in-flight git work!), A/B column-set switching |
| Occupancy bar + pennant + booking nudge (cohort-bar.js) | **THEME-ONLY** | Conversion theatre; DS keeps status as data |
| `sticky-cta` (+ luminance sampler) | **MAPS-TO sticky-bar** | Sampler = DS `data-sticky-sample` axis; rename adoption |
| Testimonials renderer 1/2/3+ + carousel | **DISTILL → DS** (wrapper) | Card = reference-card as-is; the carousel composition is the new piece |
| Position filter pills (server-side facets, zero JS) | **DISTILL → DS** | Genuinely new cross-brand pattern (AIF archives could facet) |
| Position card | **MAPS-TO preview-card `--position`** | Already one of preview-card's six variants incl. `__skills` |
| `course-info-card` | **MAPS-TO DS course-card** | DS harvested exactly this; check `--product`, deep-yellow-on-white accent, inactive override. **Copy-pasted at 5 render sites → ONE `components/course-card.php` part at adoption** |
| Benefits family (`cert-card`, `dark-blurb`, `homepage-infobar`) | **MAPS-TO blurb + stack-grid + info-bar** | ⚠ Doc drift: BENEFITS-FAMILY-MAP + blurb.md say RESOLVED 2026-07-06; plan v1 said blocked on A/B/C — reconcile (§10.3) |
| `author-hero` (lektor band) | **MAPS-TO hero family** | 1:1 mirror of AIF author archive — don't invent a component |
| `logo-grid` + `persona-logo-grid` | **DISTILL → DS** | One surface-riding component + the clamp-height sizing trick |
| `kurz-funding-box` | **MAPS-TO info-box** | Accent variant (3px vs 4px border, support tint) |
| Accordion, reservation/contact modal, engagement, pagination, breadcrumbs, badges, persona/reference cards, prose | **MAPS-TO shipped DS** | Accordion/engagement/sticky-bar engines ARE the ported AIG code |
| `smart-button` (chatbot trigger) | **DISTILL → DS (small)** | Visual block only; payload contract stays theme/mu-plugin |
| Course accent system (`course-accent--*`) | **DISTILL → DS accent axis** | Small token map, big cross-template payoff |
| `reveal` scroll utility | **DISTILL → DS motion utility** | Brand-agnostic, zero tokens, used everywhere |
| Homepage typewriter (TypeIt) | **THEME-ONLY** | unpkg CDN = supply-chain flag → self-host during homepage pass |
| Video embed `[aig_video]` | **MAPS-TO AIF twin** | Distill during its page pass |

Consolidations the audit demands: course-card render block ×5 → one part;
inline contact-modal ×4 → one part; `hp_intro_quote` feeds two sections
(vestigial — flag); many inline `style=""` layout attrs → absorbed by the
theme.css layout contract.

## 4. ⚠ NEW STRUCTURAL FINDING — the token-language gap (revises v1)

v1 assumed "AIG may need no compat shim at all." **Wrong.** The audit found
theme CSS consumes **166 distinct custom-property names; 134 are not defined
by DS tokens.css** — 118 exist only in the AIG theme's legacy namespace
(`--color-text-primary`, `--color-bg-*`, `--font-family-*`, `--font-weight-*`…).
The surviving composition files are *written in the legacy language*. Worse,
the blog.css 8-name backfill shim has **already leaked** (L6 live): page.css,
persona.css and components.css consume shim names **with no fallback** on
pages where the shim never loads — a latent production bug today.

**Resolution (chosen for the plan): RETOKENIZE, don't bridge.** Since every
surviving THEME block is being rewritten into theme.css v1 anyway, translate
to DS token names as each block is ported — the audit's
`/tmp/aig_audit/consumed_tokens.txt` + `dangling.txt` are the enumerable
checklist. `build/compat-shim.mjs` + rename-map.json remain the fallback if
a bridge window becomes unavoidable, but the target ships **zero legacy
names and zero shim** (L12). The theme-parity gate catches any missed name
(invalid-at-computed-value → visual diff).

## 5. Pre-flight (small independent commits, before the branch)

### 5.1 DS repo
1. `--lead-leading-tight` → `brand.aiguild.tokens.json` (+ AIF counterpart
   decision); next `build.mjs` run currently deletes it (L10 landmine).
2. **`aifds-dropdown` is registered but `js/components/dropdown.js` does not
   exist** → guaranteed 404 on every page the moment `aifds-engines` flips.
   Fix before P1 (ship the engine or unregister the handle).
3. **DS menu engine lacks the `.mobile-menu-close` binding** AIG's mobile
   menu uses (DS closes via Escape/backdrop only) → add the binding to the
   DS engine (both brands benefit) or AIG keeps a 5-line theme shim.
4. Version sync (`AIFDS_VERSION` rc.1 vs header rc.2) + plugin-header doc
   pointer (`DESIGN_SYSTEM.md` → `DESIGN_SYSTEM_GUIDE.md`).
5. Verify `theme-parity.mjs` auth support — the canonical repo's runner has
   NONE though the AIF inventory declares auth pages (the factory repo may
   hold a newer runner); AIG's author-portal baselines depend on it.

### 5.2 AIG stack + repo
1. **Git reconcile** (operator, §10.1): local main is 1 ahead (ecomail
   IS_SEZNAM) / 1 behind (Zone.Identifier hygiene) + 11 dirty files.
   Sequence: land/absorb the **in-flight single-kurz cohort-cards work**
   (it edits exactly the files the cut deletes — starting the branch now
   would conflict or silently drop it) → commit/stash the rest →
   `git pull --rebase` → push. Branch `refactor/ds-adoption` starts from
   clean, reconciled main only.
2. **DS mount**: diff `/mnt/c/Users/TIGO/Desktop/WORKSPACE/aiguild-design-system`
   (aig-design-system.php v2.0.0-dev.0) against the canonical WSL repo for
   unmerged work → remount via `docker-compose.override.yml` (AIF template
   042b3ba) → **verify the plugin is genuinely ACTIVE** (DB `active_plugins`
   may point at the old main file; if inactive, the whole cut is a silent
   no-op — no `aifds-*` handles exist).
3. Enable `WP_DEBUG_LOG` (parity runner reads the container's debug.log).

## 6. Phase 0 — Baseline & audit formalization (no visual change)

1. **Parity BEFORE capture**: create `~/projects/aiguild/theme-parity/inventory.json`
   — site `http://localhost:8080`, container `aiguildcz-wp`,
   pixelThreshold 0.001, sentinels (.main-header, .btn--primary, footer,
   .course-info-card, .article/.preview-card, .sticky-cta, .accordion,
   .cohort-list), pages: `/`, `/clanky/`, article, kurz detail (**per A/B/C
   variant via `?v=`**), taxonomy archive, lektor, skill, `/pozice/` +
   one virtual `/pozice/<slug>/`, `/newsletter/`, `/kontakt/`,
   `/jak-pracujeme/`, auth pages, 404. Run
   `node build/theme-parity.mjs before --inventory …` from the DS repo.
2. **Commit the audit**: promote `/tmp/aig_audit/` into
   `~/projects/aiguild/adoption/` (ownership lists, rename table, dangling
   tokens, handle map) — the cut worklist is version-controlled. Re-run the
   **DB emitter sweep** at cut time (landing_html + post_content move).
3. Playwright `tests/aig-*.spec.js` skeletons pinning baseline computed
   voices (hero h1, buttons, cohort row, card grids) — flip to DS-expected
   values as P2 lands.
4. Observability day one: ds-xray v2 with an AIG `component-map.json`
   (DS ∪ theme ∪ FF selectors), `adoption-coverage.mjs` → COVERAGE.md,
   the two NocoDB boards (Migration_Component_Status, Migration_Page_QA).
5. Baseline **authenticated pages before any forms/button/modal sweep**
   (they ARE the author-portal surfaces).

## 7. Phase 1 — THE HARD CUT (one commit)

**Enqueue switch** (handle repoint table from the audit):
- DIE: `aiguild_normalize`, `aiguild_tokens`, `aiguild_components`, both FF
  hyphen handles (`aiguild-tokens`, `aiguild-fluent-forms` — the
  double-load offenders in fluent-forms-integration.php).
- REPOINT: `aiguild_page` deps → `aifds-components`; `aiguild_fluent_forms`
  deps → `aifds-components`; keep `aiguild_google_fonts` ALIVE and re-parent
  (aifds-fonts self-hosts **only Lazzer** — killing the Google handle drops
  Inter/Space Grotesk site-wide).
- Downstream chain (`aiguild_blog` ×3 call sites, positions, article-sticky
  ×2, persona, landing_base, per-slug, modal-reservation) survives by parent
  handle — verify each with the L2 grep anyway.
- `add_theme_support('aifds-engines')` + dequeue dying JS twins **in the
  same commit**: menu.js, reading-progress.js, engagement.js, accordion.js,
  article-sticky.js (⚠ NOT a pure dequeue — see sticky merge below),
  main.js (a jQuery console.log stub — delete outright).
  KEEP: modal-reservation.js (re-wired onto `window.aifdsModal`), cohort-bar,
  course/contact/reading-trackers, testimonials-carousel, persona.js,
  lightbox, video-zoom, smooth-scroll, scroll-reveal, fluent-forms-checkbox,
  landing-base.

**DELETE ≈11,600 lines**: normalize 398 · tokens.css 553 (L12) ·
components.css 5,173 (169 of 348 classes are DS-owned; ~70 confirmed DEAD
incl. the entire cohort-card family, styleguide relics, sidebar-nav) ·
page.css 1,379 · main.css 302 · blog.css 3,339 (the leaked shim dies here —
retokenize its 9 external consumers in the same commit) · FF
.compact/.backup 898 (unenqueued) · n8n-chat-override 15 (orphaned).

**theme.css v1** = the audit's THEME blocks only, retokenized to DS names
(§4), banner-sectioned, prose ≤(0,1,0) guarded (L5), zero `!important`
(the legacy files carry ~802 — most die with their rules). Conditional
files (positions, persona, landing-base, article-sticky, lightbox,
video-embed, modal-reservation) fold in as sections; positions.css shrinks
drastically — most of it exists to COUNTER legacy `!important` border rules
that die with the cut.

**The sticky triple-merge** (highest-touch behavioral change): three engines
(article-sticky.js `lp-sticky`, single-kurz inline sampler `sticky-cta`,
landing-base.js `sticky-cta`) converge on ONE `aifds-sticky-bar` toggling
`sticky-bar--visible` — coordinated renames in templates,
components/newsletter-sticky.php, AND the inline script. The **8 templates
with inline `<script>`** binding old class names are invisible to any
enqueue-level cut — each needs the L3 three-grep treatment.

**Fluent Forms**: do NOT port AIG's 620-line skin (48 hex literals, blanket
`!important`, brand-frozen yellow). Adopt **AIF's zero-drift adapter**
(0 hex, 138 var(), brand-neutral by construction) + wire
`form-adapter-lint.mjs` into the AIG build. One open question: the adapter
normalizes group rhythm 12px→16px — pixel-diff the **reservation modal (the
revenue path)** and rule on a scoped modal exception (§10.4).

**DB-driven markup**: class renames (lp-sticky→sticky-bar, .article→
preview-card, dark-blurb→blurb…) must sweep `wp_posts.post_content` +
`landing_html`/`landing_html_b` postmeta — templates alone aren't enough.

Gate (C1): stack boots · styleguide renders · zero 404'd assets · zero new
console errors · smoke screenshots. Ugly allowed; broken enqueue is not.

## 8. Phase 2 — Parity fix loop (page by page)

Order and per-page briefs:
1. **Homepage** — benefits family lands (blurb + stack-grid + info-bar per
   the resolved map; `homepage-infobar__*` → `info-bar__*`, light-flip
   override dies); typewriter stays theme (self-host TypeIt); course-card
   part consolidation (×2 render sites here).
2. **Article** — engagement/prose/badges/hero; `lp-sticky` → sticky-bar.
3. **Archive family** — /clanky/ (preview-card renames per the table),
   pagination override tokens already shipped 2026-07-16.
4. **Standard page + /jak-pracujeme/ + /kontakt/**.
5. **Kurz detail** (the crown jewel) — hero-family distillation (§10.5),
   cohort→record-list adoption (port `--cards` mode + A/B column sets),
   sticky-cta→sticky-bar with sampler axis, syllabus→numbered-headings
   alias, funding-box→info-box variant, testimonials carousel distillation,
   reservation modal onto DS modal + aifdsModal. **Preserve every
   `aiguild_kurz_flag` read point and DONOTCACHEPAGE** — the A/B overlay is
   field-level, invisible to CSS-only thinking. Parity per variant (?v=).
6. **Lektor + skill** — author-hero via hero family; persona-articles
   2-col lock; course-card part reuse; smart-button distillation.
7. **Pozice board + virtual landings** — preview-card `--position`, filter
   pills distillation, badge mapping; test `/pozice/<slug>/` rewrites
   (stateful flush on prod!).
8. **Landings** (`landing_html` newsletter) — DB markup sweep, per-slug
   autoload deps re-check (the AIF landing-CSS kill was exactly this class).
9. **Auth/404** — nav-tabs, form-stack; FF adapter verification.

Every delta classified F1–F4; DS gaps close **in the DS** (cross-brand) and
re-run against AIF's suite too — single source of truth means a gap fix must
stay green on 8090 and 8080 simultaneously (the existing 117-spec suite is
the AIF regression net).

Header/footer specifics from the component docs: scrolled bg = `--raised`
(#1c1c1c exact); `--overlay` variant for kurz/landing heroes; the 3
light-header templates look accidental — flag; menus bind by NAME bypassing
the registered location — theme fix; footer newsletter submit
`btn--lg --primary-inverted` → `btn--md --tertiary`; Spline Sans Mono 700
must enter the font enqueue (currently faux-bold).

## 9. Phases 3–4 — Templates, verify, deploy, seal

- **Templates = live embeds** (operator verdict + the zero-hallucination
  rebuild): the styleguide Templates group already embeds REAL pages via
  install-agnostic resolvers — on the AIG install it shows AIG's real pages.
  Extend the group with AIG-specific items (kurz detail, lektor, pozice)
  resolved by post type; contracts stay THEME-CSS-CONTRACT.md; AIG template
  anatomies documented in the aiguild repo.
- **Verify**: full DS suite (both brands) + `tests/aig-*.spec.js` green;
  parity AFTER runs clean at the full 5-viewport matrix; C3 behavior
  click-through (reservation modal end-to-end, cohort nudge, filters,
  load-more, chat trigger).
- **Deploy**: AIG-parametrized deploy skill (same phases; artifacts + delta
  manifest from the branch-point tag; operator performs ALL production
  actions). ⚠ WP Rocket concatenates by handle set — the rename changes
  batches: full purge + preload mandatory; rewrite flush for /pozice/.
  Prod verification = `BASE_URL=https://aiguild.cz` page specs only.
- **Seal**: commits + `deploy-YYYY-MM-DD-N` tags in both repos; rebuild the
  deploy artifact so the next delta manifest has a baseline (L11).

## 10. OPERATOR VERDICTS (nothing moves until answered)

1. **Git reconcile & in-flight kurz work** — approve the §5.2 sequence
   (absorb cohort-cards work → commit dirty → rebase → push)?
2. **DS mount** — approve diff-then-remount + WP-Admin reactivation?
3. **Benefits family** — reconcile the doc drift: blurb.md says the family
   RESOLVED 2026-07-06 (blurb + stack-grid shipped, cert-card retired);
   plan v1 still held A/B/C open. Confirm the resolved architecture stands
   (then nothing blocks) or reopen the sandbox verdict.
4. **Fluent Forms** — confirm: adopt AIF's zero-drift adapter, drop AIG's
   hex skin; and rule on the reservation-modal 12px→16px rhythm (keep DS
   16px vs scoped compact exception).
5. **Hero family** — distill into the DS during this migration (strongly
   recommended: kurz/lektor/skill/pozice have no AIF twin and lean on it
   hardest) or theme-side interim?
6. **Mobile-menu close button** — add the binding to the DS menu engine
   (recommended, both brands) or AIG theme shim?
7. **/kurz/ archive fallback** — out of migration scope; decide separately
   (real archive template vs redirect to taxonomy pages).

## 11. Estimated shape

Pre-flight ≈ half a session (mechanical, but §5.2.1 depends on verdict 1).
P0 ≈ one session (audit exists; formalization + baselines + boards).
P1 = one focused day incl. the C1 gate. P2 = the bulk: 9 page groups,
several sessions — kurz detail is the longest (hero + cohort + modal +
carousel in one pass); front-load verdicts 3/4/5. P3 ≈ one session
(live-embed extension + docs). P4 = existing machinery, AIG-parametrized.

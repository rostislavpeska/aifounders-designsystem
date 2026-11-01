# Page templates as a DS layer — research synthesis (2026-07-16)

Three-modality exploration commissioned before building Phase 3 of
AIG-ADOPTION-PLAN.md: (1) the AIF vector store (the operator's own published
DS doctrine), (2) the open web (systems that ship page templates), (3) the
WordPress ecosystem grounded against our actual code. ~15 corpus articles +
case studies mined, 20+ primary web sources, 5 repo files read.

> **OPERATOR VERDICT (2026-07-16, supersedes the enforcement framing below):**
> templates are **inspiration** — reference anatomy, not contracts. Shipped as
> the styleguide **Templates** group (live surface-scoped anatomies, both
> brands) + docs/templates/*.md. What IS contractual is the **theme.css
> composition + override protocol** — now codified in
> `docs/THEME-CSS-CONTRACT.md`. Consequence for the artifacts below: the
> contract-shaped machinery (region permissiveness, versioned exception path,
> drift-detecting specs) applies to the THEME-CSS contract, not to template
> section stacks; template renders and .md files stay descriptive.

## Verdict

**The proposed mechanism — contract docs + live reference renders + Playwright
specs, NO shipped PHP — survives all three modalities and is the
industry-median pattern.** No surveyed system (USWDS, GOV.UK, Carbon, Polaris,
Atlassian, W3C, HPE) ships installable page composition to consumers; the
operator's own gov.cz federation doctrine and the rtCamp multi-brand WP
precedent draw the ownership line exactly where our charter does. The
WordPress-mechanism alternatives fail on the ground: `theme_page_templates`
covers only 2 of our 6 template types and would force one brand's ACF/Polylang
content model into a brand-blind plugin; a WooCommerce-style template-part
library inherits the override-drift burden WooCommerce itself built version
headers and admin panels to survive.

**But the research upgrades two artifacts from supporting to LOAD-BEARING:**

1. **The reference render is what agents actually copy.** The operator's own
   constrained test found agents cannot reliably assemble stable page layouts
   from atomic parts + prose — they succeed only with executable reference
   surfaces. The .md is for decisions; the render is the artifact.
2. **The Playwright spec is the forcing function, not an accessory.** Agents
   (and theme authors) demonstrably skip layers that documentation merely
   describes; GOV.UK's copy-paste consumers get zero update propagation, and
   WooCommerce needed drift-detection machinery. A contract without a failing
   test is aspirational.

## Evidence highlights per modality

### 1. Operator corpus (vector store)
- The operator's published four-layer model **terminates in page templates**
  ("layout chassis") — Loono + MIKE/ÖBB case studies. The layer is doctrine,
  not invention.
- gov.cz: documentation-as-enforcement, hard contractual boundaries, teams
  compose freely — federation for consumers you never meet, which now
  includes agents. Agents got the DS only when it was "a documented, local,
  parseable surface."
- Constrained test: page-level structure is the top agent-drift zone;
  **synthetic reference structures built only in the workshop proved brittle**
  — reference renders must be composed from the exact CSS/components themes
  consume.
- DESIGN.md doctrine: contracts carry decision context — non-negotiables with
  rationale, flexible-with-conditions, named anti-patterns already observed;
  link to component docs, never duplicate them.
- Maturity model: markdown contract tree now (Stage 1.5); serve the same
  files later via the aif MCP as `get_template_contract`/`list_templates`
  (Stage 2). Stage-3 machinery today would be over-engineering.

### 2. Industry (web)
- Atomic design: templates specify content STRUCTURE (char budgets, image
  ratios); pages are stress-test instances (longest headline, empty state).
- GOV.UK ships exactly ONE thing as code — the outer page shell with ~20
  named regions; page TYPES are patterns: written guidance + live coded
  example. The strongest direct precedent for our mechanism.
- USWDS templates are explicitly "a starting point, not a mandate" — contracts
  must mark normative vs variable per region or the render becomes pixel law.
- Salesforce FlexiPage: template = machine-readable named-region contract
  with placement rules, zero content. Nathan Curtis: every slot needs an
  explicit permissiveness grade or templates fragment.
- Polaris: every layout pattern carries the WHY (reading/conversion role) —
  the rationale is what stops consumers "improving" the template.
- Template-level testing: structural/computed-style assertions are the robust
  tier; full-page pixel diffs are the brittle tier (secondary, sandbox-only).
- 2026 agentic practice: llms.txt-style structured markdown + MCP endpoints;
  agents consume structured metadata + examples, not prose.

### 3. WordPress ecosystem (grounded in our repos)
- front-page.php is woven from hp_* ACF fields, Polylang, custom query flags,
  shortcodes — the non-component parts of a template are precisely what a
  brand-blind plugin cannot own. Ground truth that kills shipped-PHP.
- **Real drift already observed**: page.php copy-pasted the personas section
  from front-page.php and diverged (h4 vs p, socials call, duplicated inline
  styles). The theme has get_template_part discipline for cards but not
  sections.
- The sandbox already renders full homepages from canon components
  (aif-home-v2) but its registry declares "nothing here is canon" — the
  missing piece is a canon tier, not new machinery.
- Block patterns are a non-mechanism today (they feed block-editor
  post_content, which our templates never render) — recorded as the on-ramp
  if a hybrid path ever opens.
- FSE's template model (declarative ordered stack of parts) is the same shape
  as our contract table — a doc contract ports to a future FSE migration
  mechanically; a plugin PHP template would be a throwaway second
  implementation.

## The refined mechanism (supersedes Phase 3 §3 of AIG-ADOPTION-PLAN.md)

Each of the six templates ships as a **trio**:

### A. Contract — `docs/templates/{name}.md`
Dual-audience file:
- **YAML front-matter (machine-readable core)** — one source of truth driving
  agent parsing AND spec generation:
  ```yaml
  ---
  template: article
  version: 1
  brands: [aifounders, aiguild]
  regions:
    - id: article-hero
      scope: section-brand
      components: [article-hero, badge]
      permissiveness: locked        # locked | recommended | free
      slots: [featured_image|youtube, title, categories, personas, reading_time]
      hides_when: never
    - id: body
      scope: content-section
      components: [article-layout, prose, engagement, persona-card]
      permissiveness: recommended
      slots: [the_content, ad_pool, author_personas]
      hides_when: never
    # ... ordered, flat — no nested slot grammar (complexity breaks agents)
  recurring_regions: [newsletter-band]   # must be theme partials, see rule R1
  anti_patterns: [double-hero, section-headline-at-hero-scale, share-row-inside-card]
  ---
  ```
- **Prose (decision context, DESIGN.md discipline):** one-paragraph purpose;
  per-region one-line rationale (the WHY that deters "improvements");
  flexibility conditions per region (what a theme MAY vary and when);
  content-structure constraints (headline char budgets, image ratios);
  non-happy-path states (empty archive, logged-in/out auth, missing hero
  image); links to component docs — never duplicated content.
- An `llms.txt`-style index file enumerates the six contracts.

### B. Reference render — sandbox **canon tier**
- Split `inc/sandbox.php` registry: `experiments` (existing, judged-then-
  deleted) vs `templates` (canon): six stable slugs,
  `?aifds_sandbox=1&page=template-{name}&theme={brand}`, both brands.
- Composed ONLY from shipped canon components + the exact DS CSS themes
  consume — zero experiment CSS, zero synthetic markup (proven brittle).
- Each template renders TWO variants: the structural render (placeholder-
  shaped content) and ≥1 stress page (longest realistic headline, missing
  image, empty state) per atomic-design pages-tier practice.
- Seed from the best shipped production pages (gov.cz lesson: extracted
  references carry more authority than invented exemplars).

### C. Contract spec — `tests/template-{name}.spec.js`
- Generated/driven from the front-matter: regions present, in DOM order,
  correct surface scopes, DS component classes present, measures within
  token values. Structural + computed-style assertions = the contract test.
- Runs against **both** the sandbox canon render AND the real theme URLs, on
  **both** brands. This is the drift detector the ecosystem otherwise builds
  with version headers.
- Pixel screenshots: secondary layer, sandbox-only (full-page diffs cry wolf).
- Specs assert the contract `version` a theme page implements.

### Normative rules the contracts impose on THEMES (contract text, not plugin code)
- **R1 — recurring-section rule:** any region appearing on ≥2 template
  contracts (newsletter band, personas, logos) MUST be a theme partial
  (`components/section-*.php`) invoked via get_template_part; the spec
  asserts identical rendering across including templates. (Fixes the
  observed front-page/page.php personas drift.)
- **R2 — ACF composition policy:** region ORDER is fixed by the contract
  (never editor-reorderable); regions self-hide on empty data (existing
  front-page.php pattern, now canonical); flexible-content reserved for
  genuinely free-form zones (landing page body only).
- **R3 — exception path:** a theme MAY diverge from a `recommended` region by
  recording the divergence in the contract's per-theme exceptions list; the
  spec then asserts the exception, not the default. Silent divergence = red.
- **R4 — chrome vocabulary:** the outer shell (skip link, header, main,
  footer, body-end hooks) uses one rigorously named region vocabulary shared
  by both themes (GOV.UK's one code-worthy exception).

### Acceptance test for the layer itself
After the six contracts ship: ask an agent to build a **seventh, uncontracted
page type** and check it composes from the contracted section vocabulary
instead of inventing structure (the operator's "novel component test").

### Later (named upgrade path, not now)
Serve the contract files verbatim through the existing aif MCP as
`list_templates` / `get_template_contract` — Stage 2 of the operator's own
maturity model; the front-matter schema is designed to be servable as-is.

## What changed vs the original Phase-3 proposal
| Original | Refined |
|---|---|
| Contract = prose section stack | + YAML front-matter region contract (ids, permissiveness, slots, versions) |
| Sandbox render = illustrative | Load-bearing canon tier, exact theme CSS, + stress variants |
| Spec = supporting artifact | THE forcing function; runs vs sandbox AND live theme pages, both brands; version-pinned |
| — | R1 recurring-section theme-partial rule (fixes observed drift) |
| — | R2 ACF composition policy made canonical |
| — | R3 versioning + documented exception path |
| — | R4 shared chrome region vocabulary |
| — | Anti-patterns named per template; rationale per region |
| — | llms.txt index + MCP-servable design |

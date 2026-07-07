# Prose layout (article context + flow rhythm)

**Type:** pattern · **Status:** shipped · **git_path:** `assets/css/components.css#prose-defaults` (+ `#heading-contexts`) · **Specimen:** `/?aifds_styleguide=1&item=prose`

## Intent
Prose layout is what bare editor content gets for free inside `<main>`: the heading ramp, paragraph voice, and the FLOW LAW rhythm — plus the **article context transform**, where `.article-layout__content` steps every heading slot ONE full style down. Reach for it by writing plain semantic markup (`h2`, `h3`, `p`, lists, blockquote) in a content column; reach for `.article-layout__content` when the content is a reading column (an article body) that needs the calmer, one-step-down ramp. Do NOT add margins to type classes or hand-space prose blocks — rhythm belongs exclusively to these flow rules and their `flow-*` tokens. The article transform is a **style step, not a size tweak**: the harvested size-only overrides created a ramp orphan on AIG (a 22px Lazzer article h3 matching no ramp style, since heading-sm is Inter); now every article heading IS an existing ramp style by construction, brand divergence included (intentional delta vs live AIG article h3, GM exception). What breaks: overriding only `font-size` in a context re-creates the orphan bug; forgetting the first-heading reset doubles the top gap of a container.

## Anatomy
```html
<main>
  <div class="article-layout__content">   <!-- article context: ramp steps down -->
    <h2>Renders the heading-md style</h2>
    <p>Body voice: body-lg (18px / 1.7).</p>
    <h3>Renders heading-sm</h3>
    <h4>Renders heading-xs</h4>
  </div>
</main>
```
- Page ramp (`main h1…h4`): h1 → heading-xl bundle, h2 → heading-lg, h3 → heading-md, h4 → heading-sm; each sets `color: var(--text)` + its flow margins.
- Article ramp (`.article-layout__content h2/h3/h4`): the FULL bundle of the next style down — h2 → heading-md, h3 → heading-sm, h4 → heading-xs (family, size, weight, leading all step).
- Paragraphs (`main p`): body-lg bundle, `margin: 0 0 var(--spacing-24)`. Empty WYSIWYG paragraphs are hidden (`main p:empty`; article safety net adds `p:has(> br:only-child)`).

## Variants
Two contexts only: standard page prose and article prose (`.article-layout__content`). `docs/proposals/HEADING-RAMP.md` (status: awaiting operator sign-off) proposes collapsing the page ramp onto the article ramp — if approved, the article override is deleted and one ramp remains; until then both exist.

## States
None — static prose.

## Responsive
- Heading sizes follow their bundles' MECHANISM LAW: heading-xl/lg fluid (clamp), heading-md/sm (and lead) take one step at the 768 cut, heading-xs/body constant. The flow rhythm itself is width-independent.
- Article blockquote margin steps at `max-width: 767px` (`--flow-quote` → `--flow-quote-mobile`).

## Tokens referenced
`--heading-xl-*`, `--heading-lg-*`, `--heading-md-*`, `--heading-sm-*`, `--heading-xs-*`, `--body-lg-*`, `--text`, and the flow set: `--flow-space` (paragraph + after h2/h3), `--flow-tight` (lists + after h4), `--flow-before-h2`, `--flow-before-h3`, `--flow-before-h4`, `--flow-quote`, `--flow-quote-mobile`, `--spacing-24`.

## Surfaces
Headings and paragraphs read `--text` only — the surface scope inverts them (TEXT INVERSION LAW). No prose rule names a palette color.

## Known friction
- **FLOW LAW** (operator 2026-07-03): *a heading belongs to what FOLLOWS* — space before ≈ 2× space after: h2 = `flow-before-h2`/`flow-space` (48/24), h3 = `flow-before-h3`/`flow-space` (40/24), h4 = `flow-before-h4`/`flow-tight` (32/16); paragraph 24 below; lists `flow-tight`; figures `flow-figure`; article pull-quotes `flow-quote`/`flow-quote-mobile` (unified at AIG's deliberate value). Margins **collapse** with the previous element's bottom margin — the visible gap is the larger of the two, which is how "the heading belongs to this text" reads.
- **First-heading reset**: `main h2/h3/h4:first-child { margin-top: 0 }` — the first heading in a container must not carry its before-gap. The numbered-headings component repeats the same reset for its own wrapper.
- These rules are scoped to bare tags under `main` — the rationalization queue plans narrowing to prose containers; until then any component markup inside `main` using bare `h*`/`p`/`a` inherits prose styling unless it overrides it (this is the root cause of several "why is my component text 18px" surprises, e.g. the record-list description needed an explicit `p` selector to beat `main p`).
- Type classes stay margin-free by law — do not "fix" spacing by adding margins to `.heading-*`.

# Component reference — the future vector-DS rows

One file per **row** (see `docs/proposals/VECTOR-DS.md`). Granularity follows
**decision space**, not stylesheet classes — checkbox / radio / consent share
one `.selection-*` CSS system but get three files because their *when-to-use*
diverges. The master list (with statuses and specimen slugs) is
[`docs/IMPLEMENTATION_STATUS.md`](../IMPLEMENTATION_STATUS.md).

Every file follows the same template, designed to map 1:1 onto the VECTOR-DS
§7 row shape:

| Doc section | Vector row field |
|---|---|
| **Intent** | `content` (the embedded prose — what/when/when-not/what-breaks) |
| header line | `metadata.name`, `metadata.git_path`, `metadata.type` |
| **Variants** / **States** | `metadata.variants`, `metadata.states` |
| **Tokens referenced** | `metadata.token_refs` (names only — values ship whole via `tokens/*.json`) |
| **Anatomy** / **Surfaces** / **Known friction** | verbatim payload |

Rules for editing:
- **Harvest, don't invent** — every claim must be verifiable in
  `assets/css/components.css`, `inc/styleguide.php`, `tokens/`, or
  `docs/DECISIONS.md`. Verified 2026-07-04: all class + token references in
  these files exist in code.
- Token references are **names only**, never values.
- A new component ships with its doc file + a ledger row
  (`REPOSITORY_RULES.md` §5).

## Files

**Foundations & text** — [text-styles](text-styles.md) ·
[text-elements](text-elements.md) · [numbered-headings](numbered-headings.md) ·
[prose-layout](prose-layout.md) · [links](links.md) ·
[section-contexts](section-contexts.md) · [icons](icons.md)

**Navigation** — [breadcrumb](breadcrumb.md) · [pagination](pagination.md) ·
[nav-tabs](nav-tabs.md)

**Actions & feedback** — [button](button.md) · [badge](badge.md) ·
[info-box](info-box.md) · [accordion](accordion.md) ·
[newsletter-capture](newsletter-capture.md)

**Data display & people** — [data-table](data-table.md) ·
[record-list](record-list.md) · [preview-card](preview-card.md) ·
[reference-card](reference-card.md) · [persona-card](persona-card.md) ·
[avatar](avatar.md)

**Forms** — [input](input.md) · [select](select.md) · [datepicker](datepicker.md) ·
[checkbox](checkbox.md) · [radio](radio.md) · [consent](consent.md) ·
[segmented-control](segmented-control.md) · [file-dropzone](file-dropzone.md) ·
[forms-composition](forms-composition.md)

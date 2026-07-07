# Breadcrumb

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#breadcrumb` · **Specimen:** `/?aifds_styleguide=1&item=breadcrumb`

## Intent

The wayfinding trail — Home → archive → current page — shown above archive
and detail content so the reader always knows where they are and can step
back up one level. Reach for it on any page that sits inside a content
hierarchy (article, signal, event, category). Do NOT reach for it for
sibling navigation (that is nav-tabs) or for filtering (archive-header's
filter row). The contract in one breath: a caption-sized accent-font line
where past levels are text-colored idiom links, the current page is plain
text, and the arrow separator is CONTENT rendered by the PHP helper — never
CSS. Harvested 1:1 from AIF (`inc/breadcrumbs.php` + `page.css:2619`);
AIF-only in production (no AIG twin).

## Anatomy

```html
<nav class="breadcrumbs" aria-label="Breadcrumb"><span class="breadcrumb__list">
  <a href="/" class="breadcrumb__link">Home</a>
  <span class="breadcrumb__separator">&rarr;</span>
  <a href="/articles/" class="breadcrumb__link">Articles</a>
  <span class="breadcrumb__separator">&rarr;</span>
  <span class="breadcrumb__current">AI in Practice</span>
</span></nav>
```

- `.breadcrumbs` — the `<nav>` (keep `aria-label`); sets the voice.
- `.breadcrumb__link` — a past level; text-colored, link-idiom underline.
- `.breadcrumb__separator` — the arrow; content comes from the renderer
  (AIF's helper passes `→` as a configurable `separator` arg).
- `.breadcrumb__current` — the current page, plain text, never a link.

## Variants

None. Depth is data (the AIF helper builds Home→archive→term→single chains
per context — posts, signals, events).

## States

- Link hover/focus — underline thickens 1px → 2px (link idiom).
- No active/current styling beyond "not a link".

## Responsive

None — a single wrapping text line at every viewport.

## Tokens referenced

`--font-accent` `--caption-size` `--caption-leading` `--weight-bold` `--text`
`--spacing-8` `--transition-fast`

## Surfaces

Everything reads `--text`, so the trail re-resolves on scope surfaces (the
specimen shows it on `.section-dark`).

## Known friction

- **Link idiom alignment (GM exception, btn--link precedent):** live AIF
  hover REMOVED the underline (feedback by subtraction — outlawed); the DS
  version rests at 1px/4px and thickens to 2px on hover.
- Links are TEXT-colored, not link-colored — `.breadcrumb__link` is in the
  global link chain's exclusion list; removing it from the chain would leak
  `--link` blue/yellow into the trail.
- The v1 `!important` armor (old-wildcard era) was dropped.
- The separator is content: changing it means changing the renderer, not CSS.

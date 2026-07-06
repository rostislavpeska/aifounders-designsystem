# Comments

**Type:** component · **Status:** shipped · **git_path:** `assets/css/components.css#article-comments` · **Specimen:** `/?aigds_styleguide=1&item=comments`

## Intent

Threaded article discussion — **AIF-only today** (AI Guild has no comments
yet; this row IS the canon it adopts when it turns them on). Medium-style:
no bubbles — hairlines separate top-level comments, and depth-2 replies
draw 1px L-shaped **thread connectors** whose geometry derives from the
avatar (48px avatar → corner at its center y=24; 40px mobile → y=20; 72px
reply indent = avatar + gap). Reach for it under long-read content with
registered-user discussion. Do NOT reach for it for the engagement row
(own component) or the guest registration banner (separate candidate).
Harvested from AIF `components.css:5014-5554` + `comments.php` + the
aif-publisher renderer (`aif_publisher_render_comment`, read from the live
container).

## Anatomy

```html
<section class="article-comments" aria-label="Comments">
  <h2 class="article-comments__heading">Comments: 4</h2>
  <ol class="article-comments__list comment-list">
    <li class="comment [aif-can-edit] [aif-can-delete] [aif-tombstone]">
      <article class="comment-body">
        <header class="comment-author vcard">
          <!-- avatar: photo or initials; linked when the author has a public profile -->
          <a class="card-image-link" href="…"><span class="aif-comment__avatar avatar avatar--xs"><img src="…" alt=""></span></a>
          <!-- or: <span class="aif-comment__avatar avatar avatar--xs avatar--initials">P</span> -->
          <div class="comment-author__meta">
            <b class="fn"><a class="card-title-link" href="…">Author name</a></b>
            <div class="comment-metadata">
              <time datetime="…">June 12, 2026 · 9:34 am</time>
              <span class="comment-edited-badge" title="…">· (edited)</span>
              · <button class="aif-comment-delete-link" title="Delete comment">…trash-2 16…</button>
            </div>
          </div>
        </header>
        <div class="comment-body__bubble">
          <div class="comment-content"><p>…</p></div>
          <div class="comment-actions">
            <a href="…">Reply</a><span class="comment-actions__sep" aria-hidden="true">·</span><button class="aif-comment-edit-link">Edit</button>
          </div>
        </div>
      </article>
      <ol class="children"> <!-- depth 2 only --> </ol>
    </li>
  </ol>
  <nav class="article-comments__pagination">…</nav>
  <div class="comment-respond">
    <h3 class="comment-reply-title">Add a comment <small>Cancel reply</small></h3>
    <form class="form-stack comment-form">
      <p class="comment-form-comment"><textarea class="form-control" …></textarea></p>
      <p class="form-submit"><button class="btn btn--primary btn--sm">Post comment</button></p>
    </form>
  </div>
</section>
```

- Heading = **heading-sm** voice ("Comments: N" or the zero-state
  invitation); form title = **heading-xs**, one notch smaller.
- Avatar composes the **DS avatar**: new `--xs` (48px circle) + new
  `--initials` mode (`--brand` fill, `--text-on-brand` ink, accent bold
  `--size-18`) — minted here from the comments harvest + the header
  nav-avatar pattern. Photo mode is a plain `avatar--xs` with `<img>`.
  Linked avatar = `card-image-link` (0.85 opacity hover); linked name =
  `card-title-link`.
- Author name = **heading-xs**; metadata = **caption** voice +
  `--text-secondary`, `·`-separated: time, (edited) badge (`--meta-size`
  italic, `cursor: help`), admin edit link, delete trash icon (16px,
  0.6→1 opacity).
- Body = **body-md** voice; the "bubble" is bubble-less (Medium ruling —
  plain flow, `--spacing-12` rhythm).
- Actions — Reply (`<a>`, the global link chain) and Edit (`<button>`
  styled to the same `--link`/`--link-hover` idiom); the `·` separator is
  a real sibling so underlines never bleed.
- **Thread connectors** (depth 2): spine at x=0 (the parent avatar's left
  edge), L-corner `60×36px` landing at the reply avatar's center, spine
  `::after` continues through every non-last reply and stops at the last.
  All lines `--stroke-1` `--border`, sharp corners.
- Tombstone (`.aif-tombstone`) — author-deleted, row preserved so replies
  keep nesting: same weights, `--text-tertiary` color only; the initials
  avatar drops to `--raised`; no action row.
- Inline edit (open state) — `.aif-comment-edit-form.form-stack` wraps a DS
  `textarea.form-control` + Save (`.btn--primary.btn--sm`) + a quiet
  underlined Cancel; disabled = 0.5 opacity + `cursor: wait`; errors read
  `--status-error`.
- Terminal states: awaiting-moderation note (`--meta-size` italic),
  pagination (space-between, `--meta-size`), closed message (centered
  italic).

## THE BOUNDARY

DS owns every **visual** state (thread, connectors, all item states, the
open edit form, the form skin). Plugin/theme territory: the renderer
markup (`aif_publisher_render_comment`), the AJAX edit/delete endpoints,
the 15-min self-edit window, GDPR tombstoning, `comment-edit.js` (content
↔ form swap + countdown). The guest state (`.register-banner--subtle`)
is a separate sweep candidate.

## Variants

None. Item states: linked/unlinked author · own comment (edit + delete) ·
edited · awaiting moderation · tombstone. Section states: zero · closed ·
paginated.

## Responsive

≤599px (GM: harvested 600): avatar 40px (`--size-16` initials), reply
indent 56px, connector L 46×32 (corner at y=20), header gap tightens.

## Tokens referenced

`--spacing-4` `--spacing-8` `--spacing-12` `--spacing-16` `--spacing-24`
`--spacing-32` `--spacing-40` `--spacing-48` `--stroke-1` `--border`
`--heading-sm-font/size/weight/leading` `--heading-xs-font/size/weight/leading`
`--caption-font/size/weight` `--body-md-font/size/leading` `--meta-size`
`--size-16` `--size-18` `--font-accent` `--weight-regular` `--weight-bold`
`--leading-none` `--leading-heading` `--text` `--text-secondary`
`--text-tertiary` `--link` `--link-hover` `--brand` `--text-on-brand`
`--raised` `--status-error` `--transition-fast` `--transition-button`

## Surfaces

Everything reads roles, but production renders comments only on the light
article column — **unharvested on dark**. The initials avatar reads
`--brand`/`--text-on-brand` (Tier-2 brand roles; brand-sane on both).

## Known friction

- **The list armor stays**: `!important` resets against the article prose
  counters (`main ol li::before` numbering) — the only context this
  component lives in. The connector's `content: "" !important` deliberately
  out-specifies the marker killer.
- GM exceptions: action links/buttons ALIGNED TO THE LINK IDIOM
  (production's hover-removes-underline outlawed — breadcrumb precedent;
  the forced blue+underline `!important` armor replaced by the `--link`
  roles); metadata links join the global chain (inherit-armor dropped);
  tombstone avatar `#ebeef3` → `--raised`; edit-cancel semibold(600) →
  `--weight-bold`; delete button's 2px optical pad dropped (the -1px
  optical top nudge kept); breakpoint 600 → 599.
- Adoption aliases: `nav-avatar__circle nav-avatar__circle--lg` →
  `avatar avatar--xs [avatar--initials]`; `aif-comment__author-link` →
  `card-title-link`; `aif-comment__avatar-link` → `card-image-link`.
- The comment form composes the DS form system (`.form-control`,
  `.btn--primary.btn--sm`) — production's `.aif-form` skin dies at
  adoption.

# File dropzone

**Type:** component · **Status:** shipped (promoted 2026-07-04, branch `forms-split`) · **git_path:** `assets/css/components.css#dropzone` · **Specimen:** `/?aifds_styleguide=1&item=file-upload`

## Intent
The File dropzone is the drag-and-drop / click-to-browse file input: a dashed rectangle that IS the label for a visually hidden native `<input type="file">`, with a prompt line, a formats hint, and a filled state showing the chosen filename plus a remove button. Reach for it whenever the user must supply a file — images, audio, documents; the harvested original (`.aif-publish__image-dropzone`, author-publish write-article page) served BOTH the image and the audio input on one template, which is what proved it reusable and earned the promotion. Do NOT use it for typed answers (Input) or URL-based media (a plain Input with validation — the video URL case in the media-switch disclosure). Contract in one breath: the zone is a field-colored dashed surface; hover and drag-over are deliberately ONE shared accented state (border → `--brand`, faint bg lift) with the `.is-dragover` class toggled by consumer JS; the filled state swaps the prompt for `.dropzone-preview` (ellipsized filename + `.dropzone-remove`); everything scales with `.form-scale-small`.

## Anatomy
Class map:

- `.dropzone` — required container, ideally a `<label>` so clicks reach the hidden input; centered column, `min-height: 120px`, `padding: var(--spacing-24)`, `--field-bg` fill, 2px DASHED `--field-border` border (`--stroke-style-dashed`), `--text-secondary` text, pointer cursor. State class: `.is-dragover` (toggled by JS on drag events).
- `.dropzone-input` — the native `<input type="file">`, visually hidden (absolute, zero size, opacity 0).
- `.dropzone-prompt` — the main line ("Drag & drop … or click to browse"), body font at `--field-font-size`.
- `.dropzone-formats` — the constraints hint ("JPG, PNG or WEBP · max 5 MB"), meta voice, `--text-tertiary`.
- `.dropzone-preview` — the filled state row: full-width flex, `gap: var(--spacing-12)`.
- `.dropzone-filename` — grows (`flex: 1`), ellipsizes overflow, `--text`.
- `.dropzone-remove` — chromeless icon button at the end, `--text-tertiary`, hover → `--status-error`.

From the specimen (`inc/styleguide.php` → `aifds_sg_item_file_upload()`):

```html
<!-- rest -->
<label class="dropzone">
  <input type="file" class="dropzone-input" accept="image/jpeg,image/png,image/webp">
  <span class="dropzone-prompt">Drag &amp; drop an image here or click to browse</span>
  <span class="dropzone-formats">JPG, PNG or WEBP · max 5 MB</span>
</label>

<!-- filled -->
<div class="dropzone">
  <div class="dropzone-preview">
    <span class="dropzone-filename">episode-04-final-mix.mp3</span>
    <button type="button" class="dropzone-remove" aria-label="Remove file">…close icon…</button>
  </div>
</div>
```

## Variants
- No drawing variants. Rest vs filled is a content swap (prompt/formats vs preview), not a class variant.
- **Scale** — `.dropzone-prompt` and `.dropzone-filename` read `--field-font-size`, so the zone scales with `.form-scale-small` (CSS comment: "Scales with `.form-scale-small`").

## States
- **Rest** — dashed `--field-border`, `--field-bg`, `--text-secondary`.
- **Hover / drag-over** — ONE shared accented state (harvested behavior): `.dropzone:hover` and `.dropzone.is-dragover` both set border → `--brand`, bg → `--field-bg-focus`, text → `--text`. `.is-dragover` is toggled by the consumer's JS on dragenter/dragleave — the DS keeps the hook, ships no JS.
- **Filled** — `.dropzone-preview` present; filename ellipsizes, remove button at the end.
- **Remove hover** — `.dropzone-remove:hover` → `--status-error`.
- No styled disabled or error state exists in the CSS.

## Responsive
No media query targets the dropzone. Text sizes ride `--field-font-size`, so a `.form-scale-small` instance relaxes back to LARGE at `max-width: 767px` or `(pointer: coarse)`.

## Tokens referenced
`--spacing-8`, `--spacing-12`, `--spacing-4`, `--spacing-24`, `--field-bg`, `--field-bg-focus`, `--field-border`, `--field-font-size`, `--stroke-2`, `--stroke-style-dashed`, `--brand`, `--text`, `--text-secondary`, `--text-tertiary`, `--body-md-font`, `--meta-font`, `--meta-size`, `--transition-fast`, `--status-error`

## Surfaces
The zone reads field surface roles (`--field-bg`, `--field-border`, `--field-bg-focus`), so it adapts inside dark scopes like any field. Note the accent state uses raw `--brand` for the border (harvested mapping `primary-brand→brand`), not the surface-transforming `--link`/`--control-accent` roles the selection controls use.

## Known friction
- Promotion mapping (CSS comment): `border-default→field-border`, `primary-brand→brand`, and the harvested hard-coded `rgba(0,0,0,.02)` → `--field-bg-focus`. The theme-local `.aif-publish__image-dropzone` is drift to retire; "adoption = rename the container class only" — the theme JS already toggles `.is-dragover`.
- Class-name discrepancy: VECTOR-DS §6 lists the drag state as `.dropzone--dragover`, but the shipped class is `.is-dragover` (kept to match the existing theme JS hook). The CSS is canonical.
- All upload/validation/preview-thumbnail behavior is consumer JS; the DS ships only the drawing and the `.is-dragover` hook.

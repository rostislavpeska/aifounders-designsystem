# WordPress Integration - Potential Friction Analysis

**Version:** 1.0  
**Last Updated:** 2026-01-25  
**Status:** Pre-integration audit complete

---

## 🔴 Critical Friction Points

### 1. Generic Class Name Conflicts

**Issue:** Several class names may conflict with WordPress core, themes, or plugins.

| Our Class | Potential Conflict With |
|-----------|------------------------|
| `.btn` | Bootstrap, Elementor, many themes |
| `.footer` | Most themes define `.footer` |
| `.container` | Bootstrap, theme containers |
| `.card` | Bootstrap, Gutenberg |
| `.badge` | Bootstrap, WooCommerce |
| `.accordion` | Bootstrap, jQuery UI |

**Solution Options:**

**Option A: Namespace all classes (Recommended)**
```css
/* Prefix all classes with 'aig-' */
.aig-btn { }
.aig-footer { }
.aig-container { }
```

**Option B: Higher specificity wrapper**
```css
/* Wrap all content in .aiguild-ds */
.aiguild-ds .btn { }
.aiguild-ds .footer { }
```

**Option C: Use @layer (Modern CSS)**
```css
@layer aiguild {
    .btn { }
    .footer { }
}
```

**Recommendation:** Option A with automated find/replace before deployment.

---

### 2. `!important` Overuse (35 instances)

**Issue:** 35 `!important` declarations may cause cascading issues with theme styles.

**Locations:**
```
- Link hover states (4 instances) - NECESSARY for section context
- Reference card responsive styles (12 instances) - REFACTORABLE
- Calendar selected state (2 instances) - NECESSARY
- Quote icon SVG fill (2 instances) - NECESSARY for currentColor fix
- Footer legal links (2 instances) - NECESSARY for override
```

**High-Risk `!important` (may cause conflicts):**
```css
/* These override global styles aggressively */
.reference-card { padding: 40px !important; }
.reference-card { width: 100% !important; }
```

**Solution:** Refactor responsive reference-card styles to use higher specificity instead of `!important`:
```css
/* Instead of */
.reference-card { padding: 40px !important; }

/* Use */
.content-section .reference-card { padding: 40px; }
```

---

### 3. Inline Styles in Templates (45 instances)

**Issue:** Templates contain inline styles that won't work in Gutenberg/WP editor.

**Problematic patterns found:**
```html
<!-- In template-3.html -->
<div style="display: flex; flex-wrap: wrap; gap: var(--spacing-24);">
<div style="flex: 1 1 calc(50% - var(--spacing-12)); min-width: 300px;">
<div style="margin: 0 auto; padding: 0 var(--spacing-24);">
```

**Solution:** Create utility classes for these patterns:
```css
/* Add to components.css */
.card-row--two {
    display: flex;
    flex-wrap: wrap;
    gap: var(--spacing-24);
}

.card-row--two > * {
    flex: 1 1 calc(50% - var(--spacing-12));
    min-width: 300px;
}

.container--centered {
    margin: 0 auto;
    padding: 0 var(--spacing-24);
}
```

---

### 4. `main` Element Styling (Global Reach)

**Issue:** Styles targeting `main` element affect ALL content in WordPress main area.

**Affected rules:**
```css
main h1 { }           /* All H1s in main */
main h2 { }           /* All H2s in main */
main p { }            /* All paragraphs in main */
main a:not(.btn) { }  /* All links in main */
main ul { }           /* All lists in main */
main ol { }           /* All lists in main */
main blockquote { }   /* All blockquotes in main */
```

**Potential Issues:**
- WordPress admin bar renders inside `<main>` on some themes
- Plugin content may be affected (forms, tables, etc.)
- Gutenberg block styles may conflict

**Solution A: Scope to content area**
```css
/* Instead of */
main h1 { }

/* Use */
.entry-content h1 { }
/* or */
.wp-block-post-content h1 { }
```

**Solution B: Create explicit opt-in class**
```css
.aiguild-content h1 { }
.aiguild-content p { }
```

---

### 5. CSS Custom Properties Browser Support

**Issue:** All styling relies on CSS variables. IE11 has no support.

**Current usage:** 100% of colors, spacing, typography use variables.

**Solution:** If IE11 support needed, generate fallback CSS:
```css
/* Fallback pattern */
.btn {
    background-color: #F5C400; /* Fallback */
    background-color: var(--color-primary-brand);
}
```

**Recommendation:** IE11 is dead (2022). No action needed for modern WP sites.

---

## 🟡 Medium Friction Points

### 6. SVG Icon Color Inheritance

**Issue:** SVG icons using `currentColor` may not inherit correctly in all contexts.

**Known problem areas:**
```css
.reference-card__icon--quote path {
    fill: var(--color-border-inverse-strong) !important;
}
```

**Why `!important` is needed:** WordPress themes often set `svg path { fill: currentColor; }` which overrides our explicit fills.

**Solution:** Keep the `!important` but document it:
```css
/* FRICTION FIX: Override theme SVG resets */
.reference-card__icon--quote path {
    fill: var(--color-border-inverse-strong) !important;
}
```

---

### 7. Link Styling Complexity

**Issue:** Complex `:not()` selectors for link styling may miss edge cases.

**Current selector:**
```css
main a:not(.btn):not(.badge):not(.nav-item):not(.nav-dropdown-item):not(.mobile-nav-item):not(.mobile-lang-item)
```

**Potential missed cases:**
- WooCommerce buttons (`.woocommerce-button`)
- Elementor buttons (`.elementor-button`)
- Contact Form 7 buttons (`.wpcf7-submit`)
- Gravity Forms buttons (`.gform_button`)

**Solution:** Add exclusions as needed:
```css
main a:not(.btn):not(.badge):not([class*="button"]):not([class*="submit"])
```

---

### 8. Z-Index Stack Conflicts

**Issue:** Fixed/absolute positioned elements may conflict with WP admin bar or theme elements.

**Our z-index values:**
```css
.main-header { z-index: 100; }
.mobile-menu-overlay { z-index: 9999; }
.nav-dropdown { z-index: 1000; }
```

**WordPress defaults:**
```css
#wpadminbar { z-index: 99999; }
```

**Solution:** Ensure admin bar clearance:
```css
/* Add to page.css */
body.admin-bar .main-header {
    top: 32px; /* WP admin bar height */
}

@media (max-width: 782px) {
    body.admin-bar .main-header {
        top: 46px; /* Mobile admin bar */
    }
}
```

---

### 9. Font Loading Race Condition

**Issue:** Custom fonts (Inter, Space Grotesk) may cause FOUT (Flash of Unstyled Text).

**Current implementation:**
```html
<link href="https://fonts.googleapis.com/css2?family=Inter..." rel="stylesheet">
```

**Solution:** Use `font-display: swap` (already in Google Fonts URL) and preconnect:
```php
// In functions.php
add_action('wp_head', function() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
}, 1);
```

---

### 10. Responsive Image Handling

**Issue:** Logo images use `zoom: 0.5` for Retina scaling, which is non-standard.

**Current:**
```css
.logo-grid__img {
    zoom: 0.5; /* Retina scaling */
}
```

**`zoom` browser support:** Works in Chrome, Safari, Edge. Firefox uses transform.

**Solution:** Use transform for cross-browser consistency:
```css
.logo-grid__img {
    transform: scale(0.5);
    transform-origin: center;
}
```

---

## 🟢 Low Friction Points

### 11. Gutenberg Block Compatibility

**Issue:** Our components don't map 1:1 to Gutenberg blocks.

**Solution:** Create custom blocks or use ACF Blocks:
```php
// Example ACF Block registration
acf_register_block_type([
    'name' => 'persona-card',
    'title' => 'Persona Card',
    'render_template' => 'template-parts/blocks/persona-card.php',
]);
```

---

### 12. WooCommerce Integration

**Issue:** If site uses WooCommerce, button styles may conflict.

**Solution:** Add WooCommerce-specific exclusions:
```css
.woocommerce .btn,
.woocommerce-page .btn {
    /* Reset our styles for WooCommerce */
    all: unset;
}
```

---

## ✅ Pre-Integration Checklist

Before deploying to WordPress:

- [ ] **Namespace Decision:** Choose prefix strategy (e.g., `aig-`)
- [ ] **Run find/replace:** Update all class names if namespacing
- [ ] **Scope `main` rules:** Change to `.entry-content` or custom class
- [ ] **Admin bar fix:** Add `body.admin-bar` offset styles
- [ ] **Test with theme:** Check conflicts with target theme
- [ ] **Test with plugins:** Verify no conflicts with active plugins
- [ ] **Refactor inline styles:** Convert to utility classes
- [ ] **Remove unnecessary `!important`:** Refactor reference-card responsive styles

---

## 📋 Recommended Namespace Conversion

If choosing to namespace (Option A), here's the conversion map:

| Current | Namespaced |
|---------|------------|
| `.btn` | `.aig-btn` |
| `.btn--primary` | `.aig-btn--primary` |
| `.footer` | `.aig-footer` |
| `.container` | `.aig-container` |
| `.card` | `.aig-card` |
| `.badge` | `.aig-badge` |
| `.accordion` | `.aig-accordion` |
| `.nav-item` | `.aig-nav-item` |
| `.section-dark` | `.aig-section-dark` |
| `.section-light` | `.aig-section-light` |
| `.section-brand` | `.aig-section-brand` |

**Automated conversion command:**
```bash
# Linux/Mac
sed -i 's/\.btn/\.aig-btn/g' css/components.css
sed -i 's/\.footer/\.aig-footer/g' css/components.css
# etc.

# Or use a build tool like PostCSS with prefixer plugin
```

---

## 🔧 Quick Fixes Available Now

These can be implemented immediately without major refactoring:

### Fix 1: Admin Bar Offset
```css
/* Add to page.css */
body.admin-bar .main-header {
    top: 32px;
}
body.admin-bar .mobile-menu-overlay {
    top: 32px;
    height: calc(100vh - 32px);
}
@media (max-width: 782px) {
    body.admin-bar .main-header { top: 46px; }
    body.admin-bar .mobile-menu-overlay { 
        top: 46px;
        height: calc(100vh - 46px);
    }
}
```

### Fix 2: Font Preconnect
```php
// functions.php
add_action('wp_head', function() {
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
}, 1);
```

### Fix 3: Cross-browser Logo Scaling
```css
/* Replace zoom with transform */
.logo-grid__img {
    transform: scale(0.5);
    transform-origin: center center;
}
```

---

## 📞 Support Notes

When encountering styling issues in WordPress:

1. **Check browser dev tools** for which rule is winning
2. **Look for `!important`** conflicts from theme/plugins
3. **Verify load order** (tokens → components → page)
4. **Check z-index** if elements are hidden/overlapping
5. **Test without plugins** to isolate conflicts

**Document any new friction points discovered during implementation.**




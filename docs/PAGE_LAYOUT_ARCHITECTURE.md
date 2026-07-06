# Page Layout Architecture — AI Guild Design System

**Last Updated:** 2026-01-24
**Provenance (2026-07-04):** documents the **v1 static-template generation**
(archived in `dev/v1-archive/`). Kept as layout pattern knowledge for theme
adoption; file paths/class names may not match the v2 canon
(`assets/css/components.css`).
**Purpose:** Documentation for bots and developers integrating templates with WordPress

---

## 1. Core Principle: CSS Grid Overlay

All page templates use **CSS Grid Overlay** for cross-browser consistency. This eliminates negative margins which render inconsistently across browsers.

### Why Not Negative Margins?

| Issue | Negative Margins | CSS Grid Overlay |
|-------|------------------|------------------|
| Margin collapse | ❌ Unpredictable | ✅ No collapse |
| Browser consistency | ❌ Varies by engine | ✅ Identical everywhere |
| Subpixel rounding | ❌ Different per browser | ✅ Consistent |
| Debugging | ❌ Hard to trace | ✅ Explicit structure |

---

## 2. Hero Section Pattern

### HTML Structure

```html
<section class="course-hero-section">
    <!-- Layer 1: Background decorator -->
    <div class="hero-yellow-bg"></div>
    
    <!-- Layer 2: Content (overlays Layer 1) -->
    <div class="hero-content">
        <div class="hero-card">
            <!-- Card content -->
        </div>
        <div class="hero-illustration">
            <div class="hero-illustration-placeholder"></div>
        </div>
    </div>
</section>
```

### CSS Grid Overlay Explained

```css
.course-hero-section {
    display: grid;
    grid-template-columns: 1fr;
    grid-template-rows: auto;
}

/* Background and content occupy THE SAME GRID CELL */
.hero-yellow-bg {
    grid-row: 1;
    grid-column: 1;
    height: 600px;           /* Fixed height */
    align-self: start;       /* Stick to top */
}

.hero-content {
    grid-row: 1;             /* SAME cell */
    grid-column: 1;          /* Overlays background */
    padding-top: 32px;       /* Gap controlled by padding */
    z-index: 1;              /* Above background */
}
```

### Visual Explanation

```
┌─────────────────────────────────────────────┐
│ HEADER (80px)                               │
├─────────────────────────────────────────────┤
│ ┌─ hero-yellow-bg (600px) ────────────────┐ │
│ │                                         │ │
│ │  32px padding-top                       │ │
│ │  ┌─────────────────┐ ┌────────────────┐ │ │
│ │  │                 │ │                │ │ │
│ │  │   HERO CARD     │ │  ILLUSTRATION  │ │ │
│ │  │                 │ │                │ │ │
│ │  │                 │ │                │ │ │
│ │  │                 │ │                │ │ │
│ └──┼─────────────────┼─┼────────────────┼─┘ │
│    │                 │ └────────────────┘   │
│    │  Card extends   │  (24px overlap)      │
│    │  below yellow   │                      │
│    └─────────────────┘                      │
│ WHITE BACKGROUND (visible at bottom)        │
└─────────────────────────────────────────────┘
```

---

## 3. Responsive Breakpoints

| Breakpoint | Description | Key Changes |
|------------|-------------|-------------|
| `≥1024px` | Desktop | Side-by-side layout, 600px yellow, illustration visible |
| `768-1023px` | Tablet | Same as desktop but illustration may scale |
| `<768px` | Mobile | Stacked layout, 450px yellow, illustration hidden, burger menu |

### Critical: Consistent Breakpoint Usage

**All hero section elements use `1024px` breakpoint:**

```css
/* CORRECT - All use same breakpoint */
@media (min-width: 1024px) {
    .hero-yellow-bg { height: 600px; }
    .hero-content { flex-direction: row; }
    .hero-illustration { display: flex; }
}
```

**Navigation uses `768px` breakpoint:**

```css
@media (max-width: 767px) {
    .site-nav--desktop { display: none; }
    .site-nav--mobile { display: flex; }
}
```

---

## 4. WordPress Integration Notes

### 4.1 Header Structure

The template uses **two separate navigation objects** for WordPress compatibility:

```html
<header class="main-header main-header--dark">
    <div class="logo-placeholder">AI Guild</div>
    
    <!-- Desktop Navigation - visible ≥768px -->
    <nav class="site-nav site-nav--desktop">
        <?php wp_nav_menu(['theme_location' => 'primary']); ?>
    </nav>

    <!-- Mobile Navigation - visible <768px -->
    <nav class="site-nav site-nav--mobile">
        <button class="burger-toggle" aria-label="Menu">
            <!-- SVG burger icon -->
        </button>
    </nav>
</header>
```

### 4.2 Dynamic Content Considerations

**Card Min-Height:** The hero card has `min-height: 592px` on desktop to ensure consistent overlap regardless of content length.

```css
@media (min-width: 1024px) {
    .hero-content .hero-card {
        min-height: 592px; /* Ensures 24px overlap below 600px yellow */
    }
}
```

**For WordPress:** If content is dynamic and might be shorter than expected, this min-height prevents layout breaking.

### 4.3 Illustration Replacement

The `.hero-illustration-placeholder` is a development placeholder. In WordPress:

```html
<!-- Replace placeholder with actual image -->
<div class="hero-illustration">
    <img src="<?php echo get_template_directory_uri(); ?>/img/course-illustration.svg" 
         alt="Course illustration"
         class="hero-illustration-img">
</div>
```

```css
.hero-illustration-img {
    width: 100%;
    max-width: 550px;
    height: auto;
    aspect-ratio: 1 / 1;
    object-fit: contain;
}
```

---

## 5. CSS Files Structure

| File | Purpose |
|------|---------|
| `css/tokens.css` | Design tokens (colors, spacing, typography) |
| `css/components.css` | Reusable UI components |
| `css/page.css` | Page-level layout (hero sections, containers) |

### Load Order (Critical)

```html
<!-- 1. Tokens first -->
<link rel="stylesheet" href="css/tokens.css">

<!-- 2. Components second -->
<link rel="stylesheet" href="css/components.css">

<!-- 3. Page layout last (can override) -->
<link rel="stylesheet" href="css/page.css">
```

---

## 6. Key CSS Variables Used

```css
/* Spacing */
--spacing-24: 24px;  /* Mobile padding */
--spacing-32: 32px;  /* Top gap */
--spacing-40: 40px;  /* Desktop padding */

/* Colors */
--color-bg-primary: #ffffff;
--color-primary-inverse-link: #FFD233; /* Yellow background */
--color-primary: #E5BD2D; /* Illustration placeholder */

/* Heights (hardcoded for precision) */
/* Mobile yellow: 450px */
/* Desktop yellow: 600px */
/* Card min-height: 592px (ensures 24px overlap) */
```

---

## 7. Testing Checklist for WordPress Integration

- [ ] Header displays correctly at all breakpoints
- [ ] Mobile menu toggle works
- [ ] Hero card maintains 32px top gap
- [ ] Hero card maintains 24px bottom overlap (extends below yellow)
- [ ] Illustration hidden on mobile, visible on desktop
- [ ] Long course titles wrap correctly
- [ ] Short course titles don't break layout (min-height)
- [ ] All fonts loaded (Inter, Space Grotesk)
- [ ] Colors match design tokens

---

## 8. Troubleshooting

### Card not overlapping yellow at bottom?
- Check `min-height: 592px` is applied on desktop
- Verify yellow height is `600px` on desktop
- Ensure content fills the card

### Illustration overflowing?
- Check `max-width: 550px` and `aspect-ratio: 1/1`
- Verify parent has `overflow: hidden` if needed

### Inconsistent gap at top?
- Use `padding-top: 32px` on `.hero-content`
- NEVER use negative margins for positioning

### Mobile menu not showing?
- Check `@media (max-width: 767px)` is applied
- Verify `.site-nav--mobile` has `display: flex !important`

---

## 9. File Backups

Before making changes, backups are stored at:
- `templates/template-1.backup.html`
- `css/page.backup-grid.css`
- `css/page_backup.css`

---

**Author:** AI Guild Design System  
**For:** WordPress Integration Bots & Developers




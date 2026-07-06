# Hero Section Pattern Documentation

> **Provenance (2026-07-04):** documents the **v1 static-template generation**
> (archived in `dev/v1-archive/`). Kept as pattern knowledge for the theme
> adoption / Batch 2 hero work — the friction analysis still applies; file
> paths and class names may not. The v2 canon is `assets/css/components.css`.

> **Purpose**: Browser-friction-resistant hero section implementation for overlapping/bleeding content layouts.

---

## Table of Contents

1. [Problem Statement](#problem-statement)
2. [The Solution Pattern](#the-solution-pattern)
3. [Implementation Details](#implementation-details)
4. [Course Detail Hero](#course-detail-hero)
5. [Homepage Hero](#homepage-hero)
6. [Common Pitfalls](#common-pitfalls)
7. [Reusable Components](#reusable-components)

---

## Problem Statement

### What Causes Browser Friction

When creating hero sections where content visually "bleeds" or "overlaps" beyond its background container, several CSS approaches cause cross-browser inconsistencies:

| Approach | Issue |
|----------|-------|
| CSS Grid overlay | Inconsistent stacking in Safari/Firefox |
| Absolute positioning for background | Content height calculation fails |
| `transform: translateY()` | Affects layout flow unpredictably |
| Complex nested grids | Debugging nightmare, browser-specific bugs |

### Design Requirements

- Yellow background section (`#FFD84D` / `--color-primary-inverse-link`)
- Content that extends below the yellow background
- Consistent rendering across Chrome, Firefox, Safari, Edge
- Mobile-responsive with graceful degradation

---

## The Solution Pattern

### Core Principles

1. **Simple Flexbox** - universally supported, predictable behavior
2. **Negative margin** for bleed effect (not transforms)
3. **`position: relative` + `z-index`** for proper stacking
4. **NO CSS Grid overlay** - this caused the most friction
5. **Mobile-first responsive breakpoints**

### Base CSS Structure

```css
/* 
 * HERO SECTION - FRICTION-RESISTANT PATTERN
 * Tested across: Chrome, Firefox, Safari, Edge
 * Mobile: iOS Safari, Chrome Android
 */

.hero-section {
    background-color: var(--color-primary-inverse-link); /* Yellow */
    padding: var(--spacing-32) 0 0 0; /* Top padding only */
}

.hero-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--spacing-24);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-32);
}

/* Desktop: side-by-side layout */
@media (min-width: 1024px) {
    .hero-wrapper {
        flex-direction: row;
        align-items: flex-start;
    }
}

/* Content that bleeds below yellow background */
.hero-section .hero-card {
    flex: 1;
    max-width: 588px;
    margin-bottom: calc(-1 * var(--spacing-24)); /* BLEED EFFECT */
    position: relative;
    z-index: 1;
}
```

### Why This Works

```
┌─────────────────────────────────────────────┐
│  YELLOW BACKGROUND (.hero-section)          │
│  padding-top: 32px                          │
│  padding-bottom: 0                          │
│                                             │
│  ┌─────────────────┐  ┌─────────────────┐   │
│  │  HERO CARD      │  │  ILLUSTRATION   │   │
│  │                 │  │                 │   │
│  │                 │  │                 │   │
│  │                 │  └─────────────────┘   │
│  │                 │                        │
└──│─────────────────│────────────────────────┘
   │  margin-bottom: │ ← Card bleeds below
   │  -24px          │   yellow background
   └─────────────────┘
   
┌─────────────────────────────────────────────┐
│  WHITE BACKGROUND (next section)            │
│                                             │
```

---

## Implementation Details

### File Locations

| File | Purpose |
|------|---------|
| `css/page.css` | Hero section layout styles |
| `css/components.css` | Hero card component styles |
| `css/tokens.css` | Design tokens (colors, spacing) |

### Required Tokens

```css
/* From tokens.css */
--color-primary-inverse-link: #ffd84d;  /* Yellow background */
--spacing-24: 24px;
--spacing-32: 32px;
--spacing-40: 40px;
```

### Responsive Breakpoints

| Breakpoint | Layout |
|------------|--------|
| < 768px | Single column, stacked |
| 768px - 1023px | Single column, larger spacing |
| ≥ 1024px | Two columns, side-by-side |

---

## Course Detail Hero

### Current Implementation (template-1.html, template-2.html)

```html
<!-- HERO SECTION -->
<section class="hero-section">
    <div class="hero-wrapper">
        <!-- Hero Card - bleeds below yellow -->
        <div class="hero-card section-dark">
            <span class="hero-card__badge">Akreditovaný kurz</span>
            <h1 class="hero-card__title">Webdesigner: Interakční designer</h1>
            <p class="hero-card__description">
                Intenzivní program interakčního designu...
            </p>
            <div class="hero-card__benefits">
                <ul>
                    <li>100 hodin výuky</li>
                    <li>Certifikát MŠMT</li>
                </ul>
            </div>
            <div class="hero-card__actions">
                <a href="#terminy" class="btn btn--lg btn--primary">
                    Chci místo v kurzu
                </a>
            </div>
        </div>
        
        <!-- Illustration - desktop only -->
        <div class="hero-illustration">
            <img src="assets/illustration.svg" alt="">
        </div>
    </div>
</section>

<!-- Content flows normally below -->
<section class="content-section">
    ...
</section>
```

### CSS (page.css)

```css
/* Hero Section - Simple Approach */
.hero-section {
    background-color: var(--color-primary-inverse-link);
    padding: var(--spacing-32) 0 0 0;
}

.hero-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 var(--spacing-24);
    display: flex;
    flex-direction: column;
    gap: var(--spacing-32);
}

@media (min-width: 768px) {
    .hero-wrapper {
        padding: 0 var(--spacing-40);
    }
}

@media (min-width: 1024px) {
    .hero-wrapper {
        flex-direction: row;
        align-items: flex-start;
    }
}

/* Hero card - extends below yellow background */
.hero-section .hero-card {
    flex: 1;
    max-width: 588px;
    margin-bottom: calc(-1 * var(--spacing-24));
    position: relative;
    z-index: 1;
}

/* Hero illustration - desktop only */
.hero-illustration {
    display: none;
}

@media (min-width: 1024px) {
    .hero-illustration {
        display: block;
        flex: 1;
        max-width: 550px;
    }
    
    .hero-illustration img {
        width: 100%;
        height: auto;
    }
}
```

---

## Homepage Hero

### Design Differences

| Aspect | Course Detail | Homepage |
|--------|--------------|----------|
| Content | Card + Illustration | Headline + 2 Dark Blurbs |
| Width | 1200px | 980px (narrower) |
| Background | Solid yellow | Yellow + grid pattern |
| Bleed | Card bleeds significantly | Minimal/no bleed |

### Proposed Implementation

```html
<!-- HOMEPAGE HERO -->
<section class="homepage-hero">
    <div class="homepage-hero__wrapper">
        <!-- Headline Block -->
        <div class="homepage-hero__headline">
            <h1>Vzdělávací programy pro AI-native designéry a vývojáře</h1>
            <p class="text--perex">
                UX/UI <span class="bullet-dot">•</span> 
                prototypování s AI <span class="bullet-dot">•</span> 
                automatizace workflow
            </p>
        </div>
        
        <!-- Two Dark Blurbs Side by Side -->
        <div class="homepage-hero__blurbs">
            <div class="dark-blurb section-dark">
                <div class="dark-blurb__icon">
                    <img src="assets/icon-certificate.svg" alt="">
                </div>
                <div class="dark-blurb__content">
                    <h5>První akreditovaný kurz pro interakční designery v ČR</h5>
                    <p>Systematický základ pro praxi. Možnost financované rekvalifikace.</p>
                    <a href="#" class="btn btn--sm btn--secondary-inverse">
                        Akreditované programy
                    </a>
                </div>
            </div>
            
            <div class="dark-blurb section-dark">
                <div class="dark-blurb__icon">
                    <img src="assets/icon-brain.svg" alt="">
                </div>
                <div class="dark-blurb__content">
                    <h5>Specializované kurzy pro designery a produktové teamy</h5>
                    <p>Krátké intenzivní bloky: UX/UI, prototypování, automatizace.</p>
                    <a href="#" class="btn btn--sm btn--secondary-inverse">
                        Specializované kurzy
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
```

### CSS for Homepage Hero

```css
/* ==========================================================================
   HOMEPAGE HERO
   ========================================================================== */

.homepage-hero {
    background-color: var(--color-primary-inverse-link);
    padding: var(--spacing-80) 0;
    position: relative;
}

/* Optional: Background grid pattern overlay */
.homepage-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image: url('../assets/background-grid.svg');
    background-repeat: no-repeat;
    background-position: center;
    background-size: cover;
    pointer-events: none;
    opacity: 0.3;
}

.homepage-hero__wrapper {
    max-width: 980px;
    margin: 0 auto;
    padding: 0 var(--spacing-24);
    position: relative;
    z-index: 1;
}

@media (min-width: 768px) {
    .homepage-hero__wrapper {
        padding: 0 var(--spacing-40);
    }
}

/* Headline Block */
.homepage-hero__headline {
    text-align: center;
    margin-bottom: var(--spacing-40);
}

.homepage-hero__headline h1 {
    color: var(--color-text-primary);
    margin-bottom: var(--spacing-24);
}

.homepage-hero__headline .text--perex {
    color: var(--color-text-primary);
}

.homepage-hero__headline .bullet-dot {
    color: var(--color-primary-dark);
    margin: 0 var(--spacing-8);
}

/* Dark Blurbs Row */
.homepage-hero__blurbs {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-24);
}

@media (min-width: 768px) {
    .homepage-hero__blurbs {
        flex-direction: row;
    }
    
    .homepage-hero__blurbs > .dark-blurb {
        flex: 1;
    }
}
```

---

## Common Pitfalls

### ❌ DON'T: Use CSS Grid for Overlay Effects

```css
/* WRONG - causes cross-browser issues */
.hero-section {
    display: grid;
    grid-template-rows: 576px auto;
}

.hero-background {
    grid-row: 1;
    grid-column: 1;
}

.hero-content {
    grid-row: 1 / 3; /* Spanning rows causes issues */
    grid-column: 1;
}
```

### ❌ DON'T: Use Absolute Positioning for Main Content

```css
/* WRONG - breaks content flow */
.hero-section {
    position: relative;
    height: 576px;
}

.hero-content {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
}
```

### ❌ DON'T: Use transform for Bleed Effect

```css
/* WRONG - unpredictable across browsers */
.hero-card {
    transform: translateY(24px);
}
```

### ✅ DO: Use Negative Margin

```css
/* CORRECT - predictable, well-supported */
.hero-card {
    margin-bottom: calc(-1 * var(--spacing-24));
    position: relative;
    z-index: 1;
}
```

### ✅ DO: Use Simple Flexbox

```css
/* CORRECT - universally supported */
.hero-wrapper {
    display: flex;
    flex-direction: column;
    gap: var(--spacing-32);
}

@media (min-width: 1024px) {
    .hero-wrapper {
        flex-direction: row;
    }
}
```

---

## Reusable Components

### Components Available in Design System

All these components are already implemented and can be used on the homepage:

| Component | Class | Description |
|-----------|-------|-------------|
| Dark Header | `.main-header` | Navigation bar with logo |
| Mobile Menu | `.mobile-menu-overlay` | Responsive hamburger menu |
| Hero Card | `.hero-card` | Dark card with badge, title, benefits |
| Dark Blurb | `.dark-blurb` | Icon + text + button card |
| Quote Block | `blockquote` | Left-bordered quote text |
| Persona Card | `.persona-card` | Lecturer/team member card |
| Reference Card | `.reference-card` | Testimonial with avatar |
| Logo Grid | `.logo-grid` | Partner/client logos |
| Accordion | `.accordion` | Expandable FAQ items |
| Cohort Card | `.cohort-card` | Course date/price card |
| Footer | `.footer` | Dark footer with blurbs & logos |

### Section Context Classes

```css
/* Apply to sections for automatic text/link coloring */
.section-light   /* Light background - primary text */
.section-dark    /* Dark background - inverse text */
.section-brand   /* Yellow background - primary text */
```

### Content Containers

```css
.content-container           /* 628px - narrow content */
.content-container--wide     /* 800px - wide content */
.content-container--wider    /* 980px - wider content */
```

### Content Section Variants

```css
.content-section                    /* Light background */
.content-section--secondary         /* Light gray background */
.content-section--dark              /* Dark primary background */
.content-section--dark-secondary    /* Dark secondary background */
.content-section--dark-tertiary     /* Dark tertiary background */
```

---

## Quick Reference

### Hero Section Checklist

- [ ] Use `.hero-section` for yellow background
- [ ] Use `.hero-wrapper` for flex container
- [ ] Apply `margin-bottom: calc(-1 * var(--spacing-24))` for bleed
- [ ] Add `position: relative; z-index: 1;` to bleeding content
- [ ] Test on Chrome, Firefox, Safari, Edge
- [ ] Test on mobile (iOS Safari, Chrome Android)

### File Structure

```
aiguild-design-system/
├── css/
│   ├── tokens.css          # Design tokens
│   ├── components.css      # Component styles
│   └── page.css            # Page layout (hero section here)
├── templates/
│   ├── template-1.html     # Course detail (Interaction Design)
│   └── template-2.html     # Course detail (Vibe Coding)
├── components.html         # Component showcase
└── docs/
    └── HERO_PATTERN.md     # This document
```

---

## Version History

| Date | Change |
|------|--------|
| 2026-01-24 | Initial documentation of friction-resistant hero pattern |
| 2026-01-24 | Added homepage hero specifications |

---

*This documentation prevents future friction by establishing a single source of truth for hero section implementation.*




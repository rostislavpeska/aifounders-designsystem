# WordPress Implementation Guide

**Version:** 1.0  
**Last Updated:** 2026-01-25  
**Status:** Ready for WordPress Integration

---

## 📋 Overview

This document provides complete instructions for implementing the AI Founders Design System in WordPress. All components are production-ready, tested, and follow Apple-grade quality standards.

---

## 📁 Files to Copy to WordPress

| File | WordPress Path | Purpose |
|------|----------------|---------|
| `css/tokens.css` | `/assets/css/tokens.css` | Design tokens (colors, spacing, typography) |
| `css/components.css` | `/assets/css/components.css` | All component styles |
| `css/page.css` | `/assets/css/page.css` | Page layout styles |
| `js/mobile-menu.js` | `/assets/js/mobile-menu.js` | Mobile menu toggle |
| `js/components/accordion.js` | `/assets/js/components/accordion.js` | Accordion functionality |
| `assets/img/logos/*` | `/assets/img/logos/*` | Logo images |
| `assets/img/logos/footer/*` | `/assets/img/logos/footer/*` | Footer partner logos |

---

## 🎨 Theme Setup

### 1. Enqueue Styles & Scripts

```php
function aiguild_enqueue_assets() {
    // Core CSS (order matters!)
    wp_enqueue_style('aiguild-tokens', get_template_directory_uri() . '/assets/css/tokens.css', [], '1.0');
    wp_enqueue_style('aiguild-components', get_template_directory_uri() . '/assets/css/components.css', ['aiguild-tokens'], '1.0');
    wp_enqueue_style('aiguild-page', get_template_directory_uri() . '/assets/css/page.css', ['aiguild-components'], '1.0');
    
    // Google Fonts
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700;800;900&family=Space+Grotesk:wght@300;400;500;700&family=Spline+Sans+Mono:wght@400;500&display=swap');
    
    // JavaScript
    wp_enqueue_script('aiguild-mobile-menu', get_template_directory_uri() . '/assets/js/mobile-menu.js', [], '1.0', true);
    wp_enqueue_script('aiguild-accordion', get_template_directory_uri() . '/assets/js/components/accordion.js', [], '1.0', true);
}
add_action('wp_enqueue_scripts', 'aiguild_enqueue_assets');
```

### 2. Set Theme Attribute

```php
// In header.php
<html <?php language_attributes(); ?> data-theme="aiguild">
```

---

## 🔘 Button Shortcodes

### Standard Button

```php
// Shortcode: [button text="Label" url="#" size="md" style="primary" icon="arrow-right"]

function aiguild_button_shortcode($atts) {
    $atts = shortcode_atts([
        'text' => 'Click Here',
        'url' => '#',
        'size' => 'md',      // sm, md, lg
        'style' => 'primary', // primary, secondary, secondary-inverted, primary-inverted
        'icon' => '',
    ], $atts);
    
    $icon_html = $atts['icon'] ? '<span class="icon-placeholder icon--' . esc_attr($atts['size']) . '"></span>' : '';
    
    return sprintf(
        '<div class="shortcode-btn"><a href="%s" class="btn btn--%s btn--%s">%s%s</a></div>',
        esc_url($atts['url']),
        esc_attr($atts['size']),
        esc_attr($atts['style']),
        $icon_html,
        esc_html($atts['text'])
    );
}
add_shortcode('button', 'aiguild_button_shortcode');
```

**Usage:**
```
[button text="Zpět ke kurzu" url="/kurzy" size="md" style="primary" icon="arrow-left"]
```

**Output:**
```html
<div class="shortcode-btn">
    <a href="/kurzy" class="btn btn--md btn--primary">
        <span class="icon-placeholder icon--md"></span>
        Zpět ke kurzu
    </a>
</div>
```

### Smart Button (AI-Driven CTA)

```php
// Shortcode: [smart_button text="Label" url="#" icon="ai-chat"]

function aiguild_smart_button_shortcode($atts) {
    $atts = shortcode_atts([
        'text' => 'Start Chat',
        'url' => '#',
        'icon' => 'sparkle',
    ], $atts);
    
    return sprintf(
        '<div class="shortcode-btn">
            <div class="smart-btn">
                <div class="illustration-placeholder illustration--32"></div>
                <a href="%s" class="btn btn--sm btn--secondary">
                    <span class="icon-placeholder icon--sm"></span>
                    %s
                </a>
            </div>
        </div>',
        esc_url($atts['url']),
        esc_html($atts['text'])
    );
}
add_shortcode('smart_button', 'aiguild_smart_button_shortcode');
```

**Usage:**
```
[smart_button text="Zvýšit šanci na schválení" url="/ai-assistant"]
```

---

## 📄 Page Sections

### Section Classes

| Class | Background | Text Color |
|-------|------------|------------|
| `section-light` | White | Dark text |
| `section-dark` | Dark (#1F1C12) | Light text |
| `section-brand` | Yellow (#F5C400) | Dark text |
| `content-section--dark-secondary` | Dark secondary | Light text |

### Container Widths

| Class | Max Width | Use Case |
|-------|-----------|----------|
| `content-container` | 628px | Article text |
| `content-container--wide` | 800px | Cards, forms |
| `content-container--wider` | 980px | Logo grids, wide layouts |

### Section Template

```html
<section class="content-section section-light">
    <div class="content-container">
        <!-- Content here -->
    </div>
</section>
```

---

## 🧭 Navigation

### Desktop Header (Dark)

```html
<header class="main-header main-header--dark">
    <a href="/" class="logo">
        <img src="/assets/img/logo.svg" alt="AI Guild">
    </a>
    
    <nav class="site-nav site-nav--desktop">
        <a href="/kurzy" class="nav-item">Akreditované Kurzy</a>
        <a href="/specializovane" class="nav-item">Specializované kurzy</a>
        <a href="/jak-pracujeme" class="nav-item">Jak pracujeme</a>
        <a href="/kontakt" class="nav-item">Kontakt</a>
        <div class="nav-item nav-item--has-dropdown">
            <span>CZ</span>
            <span class="nav-item-icon"></span>
            <div class="nav-dropdown">
                <a href="?lang=cs" class="nav-dropdown-item">Čeština (CZ)</a>
                <a href="?lang=en" class="nav-dropdown-item">English (EN)</a>
            </div>
        </div>
    </nav>

    <nav class="site-nav site-nav--mobile">
        <button class="burger-toggle" aria-label="Menu">
            <svg viewBox="0 0 24 24">
                <path class="line-top" d="M4 5h16"/>
                <path class="line-mid" d="M4 12h16"/>
                <path class="line-bot" d="M4 19h16"/>
            </svg>
        </button>
    </nav>
</header>
```

### Mobile Menu Overlay

```html
<div class="mobile-menu-overlay mobile-menu-overlay--dark">
    <div class="mobile-menu-content">
        <a href="/kurzy" class="mobile-nav-item">Akreditované Kurzy</a>
        <a href="/specializovane" class="mobile-nav-item">Specializované kurzy</a>
        <a href="/jak-pracujeme" class="mobile-nav-item">Jak pracujeme</a>
        <a href="/kontakt" class="mobile-nav-item">Kontakt</a>
        <div class="mobile-lang-row">
            <a href="?lang=cs" class="mobile-lang-item mobile-lang-item--active">CZ</a>
            <a href="?lang=en" class="mobile-lang-item">EN</a>
        </div>
    </div>
</div>
```

---

## 📝 Typography (Semantic)

Typography is automatically applied within `<main>`:

| Element | Font | Size | Weight |
|---------|------|------|--------|
| `h1` | Inter | 48px (desktop) / 32px (mobile) | 900 |
| `h2` | Inter | 32px (desktop) / 24px (mobile) | 800 |
| `h3` | Inter | 26px | 800 |
| `h4` | Inter | 22px | 800 |
| `p` | Inter | 18px | 400 |

### Perex/Subheadline

```html
<p class="text--perex">Large intro text for hero sections</p>
```

---

## 📦 Components

### Reference Card (Testimonial)

```html
<div class="reference-card section-dark">
    <div class="reference-card__header">
        <div class="reference-card__avatar">
            <img src="/path/to/avatar.jpg" alt="Name">
        </div>
        <div class="reference-card__title-group">
            <h4 class="reference-card__name">Petr Krátký</h4>
            <p class="reference-card__subtitle">UX Designer | Česká spořitelna</p>
        </div>
    </div>
    <div class="reference-card__body">
        <div class="reference-card__icon reference-card__icon--quote">
            <!-- Quote SVG icon -->
        </div>
        <div class="reference-card__content">
            <p>"Testimonial text here..."</p>
        </div>
    </div>
</div>
```

### Dark Blurb (Footer Cards)

```html
<div class="dark-blurb dark-blurb--secondary section-dark">
    <div class="dark-blurb__illustration">
        <div class="illustration-placeholder illustration--64"></div>
    </div>
    <div class="dark-blurb__content">
        <div class="dark-blurb__text-group">
            <h5 class="dark-blurb__title">Title here</h5>
            <p class="dark-blurb__description">Description text</p>
        </div>
        <a href="#" class="btn btn--sm btn--secondary-inverted">
            <span class="icon-placeholder icon--sm"></span>
            Button Label
        </a>
    </div>
</div>
```

### Logo Grid

```html
<p class="logo-grid__label">Máme zkušenosti z mnoha projektů</p>
<div class="logo-grid">
    <div class="logo-grid__item">
        <img src="/assets/img/logos/logo-name.png" alt="Company" class="logo-grid__img">
    </div>
    <!-- Repeat for each logo (10 total) -->
</div>
```

### Accordion

```html
<div class="accordion-group">
    <div class="accordion">
        <button class="accordion__header">
            <span class="accordion__title">Question here?</span>
            <span class="accordion__icon"></span>
        </button>
        <div class="accordion__content">
            <p>Answer content here...</p>
        </div>
    </div>
</div>
```

### Persona Card

```html
<div class="persona-card section-dark">
    <div class="persona-card__image">
        <img src="/path/to/photo.jpg" alt="Name">
    </div>
    <div class="persona-card__content">
        <div class="persona-card__header">
            <h3 class="persona-card__name">Rostislav Peška</h3>
            <p class="persona-card__role">Senior UX Designer</p>
        </div>
        <div class="persona-card__bio">
            <p>Bio text here...</p>
        </div>
        <div class="persona-card__links">
            <a href="#" class="persona-card__link">LinkedIn</a>
            <a href="#" class="persona-card__link">Portfolio</a>
        </div>
    </div>
</div>
```

---

## 🦶 Footer

```html
<footer class="footer section-dark">
    <div class="container">
        <!-- Partners -->
        <div class="footer__partners">
            <p class="footer__label">Spolupracujeme:</p>
            <div class="footer__partners-grid">
                <img src="/assets/img/logos/footer/logo-mvcr.png" alt="MV ČR">
                <img src="/assets/img/logos/footer/logo-msmt.png" alt="MŠMT">
                <!-- More partner logos -->
            </div>
        </div>

        <!-- More Courses -->
        <div class="footer__section">
            <div class="footer__divider"></div>
            <h3 class="footer__section-title">Další z našich kurzů</h3>
            <div class="footer__row">
                <!-- Dark blurb cards here -->
            </div>
        </div>

        <!-- Contact -->
        <div class="footer__section">
            <div class="footer__row">
                <!-- Contact dark blurb cards -->
            </div>
        </div>

        <!-- Legal -->
        <div class="footer__section" style="gap: var(--spacing-12);">
            <div class="footer__divider"></div>
            <p class="footer__legal">
                @aiguild.cz | 
                <a href="/pristupnost" class="footer__legal-link">Prohlášení o přístupnosti</a> | 
                <a href="/podminky" class="footer__legal-link">Obchodní podmínky</a> | 
                <a href="/gdpr" class="footer__legal-link">Prohlášení o ochraně uživatelských dat</a> | 
                <a href="/cookies" class="footer__legal-link">Používání Cookies</a>
            </p>
        </div>
    </div>
</footer>
```

---

## 🔗 Link Styling

Links are automatically styled based on section:

| Section | Default Color | Hover Color |
|---------|---------------|-------------|
| `section-light` / `main` | Gold (#B8860B) | Dark (#1F1C12) |
| `section-dark` | Yellow (#FFD84D) | Light Yellow (#FFF3B0) |
| `section-brand` | Black | Black (no underline) |
| `.footer__legal-link` | Grey | Grey (no underline) |

---

## 📱 Responsive Breakpoints

| Breakpoint | Target |
|------------|--------|
| `1024px` | Tablet landscape |
| `768px` | Tablet portrait |
| `600px` | Mobile large |
| `375px` | Mobile small |

---

## ✅ Implementation Checklist

- [ ] Copy CSS files to WordPress theme
- [ ] Copy JS files to WordPress theme
- [ ] Copy logo assets
- [ ] Enqueue styles and scripts in `functions.php`
- [ ] Set `data-theme="aiguild"` on `<html>`
- [ ] Implement button shortcodes
- [ ] Implement smart_button shortcode
- [ ] Set up header template part
- [ ] Set up footer template part
- [ ] Test mobile menu functionality
- [ ] Test accordion functionality
- [ ] Verify responsive behavior at all breakpoints

---

## 🎯 Reference Templates

See these HTML files for complete implementation examples:

| Template | Purpose |
|----------|---------|
| `templates/template-1.html` | Course detail page (multiple lecturers) |
| `templates/template-2.html` | Course detail page (single lecturer) |
| `templates/template-3.html` | Homepage |
| `templates/template-4.html` | Standard content page (subsidies info) |

---

## ⚠️ Important Notes

1. **Never hardcode colors** - Always use CSS variables from `tokens.css`
2. **Section classes are required** - Use `section-light`, `section-dark`, or `section-brand` for proper text/link colors
3. **Shortcode wrapper** - All buttons inserted via shortcode must use `.shortcode-btn` wrapper for consistent spacing
4. **Logo sizing** - Footer logos use `zoom: 0.5` for Retina (2x) assets
5. **SVG icons** - Use `currentColor` for fill/stroke to inherit text color from parent

---

## 📞 Support

For questions or issues, refer to:
- `docs/DESIGN_SYSTEM_GUIDE.md` - Architecture details
- `REPOSITORY_RULES.md` - Strict implementation rules
- `components.html` - Live component showcase




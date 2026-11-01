<?php
/**
 * Styleguide — TEMPLATES group: LIVE page showcases (operator verdict 2026-07-16).
 *
 * LESSON (operator: "basically all pages are 100% hallucinated"): a
 * hand-composed replica of a page IS a hallucination — invented geometry,
 * invented content, and the wrong CSS chain (the styleguide shell loads only
 * the DS chain, while real pages are mostly THEME-owned composition). The
 * only showcase that cannot lie is the page itself.
 *
 * Each Templates item therefore embeds the REAL page of this install in a
 * same-origin <iframe>: real markup, real content, the full production CSS
 * chain (DS handles → theme tokens → compat shim → theme.css → per-page CSS)
 * and the real JS engines (fixed header shrink, sticky bar, hero glitch).
 * The iframe IS the viewport, so media queries, vw-clamps and fixed/sticky
 * semantics are honest — root-cause analysis (wf_d9a8a4f4) showed inline
 * embedding is STRUCTURALLY unfaithful regardless of which CSS is loaded.
 *
 * Brand toggle: choosing aiguild re-stamps data-theme on the EMBEDDED
 * document's <html> (same-origin script) — a live Stage-3 preview: AIG
 * tokens over the real composition. Labeled as such in the strip.
 *
 * URL resolution is install-agnostic — template-file queries, never
 * hardcoded slugs — so the same group works on any install (incl. the
 * production styleguide, admin-gated). A missing page type renders an
 * honest empty state instead of an invented page.
 *
 * The anatomy notes ("described in markdown if necessary") are a collapsed
 * footnote under the embed — never the artifact.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* ── URL resolvers (install-agnostic) ────────────────────────────────────── */

/** @return array{0:string,1:string}|null [url, how-it-was-resolved] */
function aifds_tpl_resolve( $type ) {
	switch ( $type ) {
		case 'homepage':
			return array( home_url( '/' ), 'front page' );

		case 'archive':
			$posts_page = (int) get_option( 'page_for_posts' );
			if ( $posts_page ) {
				return array( get_permalink( $posts_page ), 'posts page' );
			}
			$link = get_post_type_archive_link( 'post' );
			return $link ? array( $link, 'post archive' ) : null;

		case 'article':
			// Prefer a production-shape article (featured image) over bare test posts.
			$posts = get_posts( array( 'numberposts' => 1, 'post_status' => 'publish', 'meta_key' => '_thumbnail_id' ) );
			if ( ! $posts ) {
				$posts = get_posts( array( 'numberposts' => 1, 'post_status' => 'publish' ) );
			}
			return $posts ? array( get_permalink( $posts[0] ), 'newest article with a featured image' ) : null;

		case 'page':
			foreach ( array( 'o-nas', 'about' ) as $slug ) {
				$p = get_page_by_path( $slug );
				if ( $p && 'publish' === $p->post_status ) {
					return array( get_permalink( $p ), 'page “' . $slug . '”' );
				}
			}
			foreach ( get_posts( array( 'post_type' => 'page', 'numberposts' => 20, 'post_status' => 'publish' ) ) as $p ) {
				$tpl = get_page_template_slug( $p );
				if ( '' === $tpl || 'default' === $tpl ) {
					return array( get_permalink( $p ), 'newest default-template page' );
				}
			}
			return null;

		case 'landing':
			return aifds_tpl_page_by_template( 'page-landing.php', 'newest “Landing Page”-template page' );

		case 'auth':
			return aifds_tpl_page_by_template( 'page-author-login.php', 'the author-login-template page' );
	}
	return null;
}

function aifds_tpl_page_by_template( $file, $label ) {
	$pages = get_posts( array(
		'post_type'   => 'page',
		'numberposts' => 1,
		'post_status' => 'publish',
		'meta_key'    => '_wp_page_template',
		'meta_value'  => $file,
	) );
	return $pages ? array( get_permalink( $pages[0] ), $label ) : null;
}

/* ── shared renderer ─────────────────────────────────────────────────────── */

function aifds_tpl_embed_css() {
	?>
	<style>
	/* showcase chrome only (sgt-) — never part of the DS */
	.sgt-strip { display: flex; gap: 16px; flex-wrap: wrap; align-items: center; padding: 8px 24px; background: var(--bg-alt); border-bottom: 1px solid var(--border); font: 11px/1.6 var(--font-mono); color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.08em; }
	.sgt-strip a { color: inherit; }
	.sgt-strip .sgt-live { background: var(--status-success, #1a7f37); color: #fff; padding: 3px 8px; font-weight: 700; }
	.sgt-strip .sgt-preview { background: var(--brand); color: var(--text-on-brand); padding: 3px 8px; font-weight: 700; }
	.sgt-frame { display: block; width: 100%; height: calc(100vh - 46px); border: 0; background: #fff; }
	.sgt-footnote { margin: 0; padding: 16px 24px 24px; background: var(--bg-alt); border-top: 1px solid var(--border); font-size: 13px; }
	.sgt-footnote summary { cursor: pointer; font: 700 12px/1.6 var(--font-mono); text-transform: uppercase; letter-spacing: 0.08em; }
	.sgt-footnote ul { margin: 12px 0 0; }
	.sgt-missing { padding: 48px 32px; max-width: 720px; }
	</style>
	<?php
}

function aifds_sg_render_template_embed( $type, $md, $notes ) {
	$brand = isset( $GLOBALS['aifds_sg_brand'] ) ? $GLOBALS['aifds_sg_brand'] : aifds_styleguide_brand();
	// The install's NATIVE brand: the embedded real page already carries it.
	// The token-preview overlay (re-stamp + badge) applies only when the
	// operator toggles the OTHER brand — comparing against a hardcoded
	// 'aifounders' wrongly badged the AIG install's own pages as previews.
	$native = ( false !== strpos( get_stylesheet(), 'aifounders' ) ) ? 'aifounders' : 'aiguild';
	aifds_tpl_embed_css();

	$resolved = aifds_tpl_resolve( $type );
	if ( ! $resolved ) {
		// Honest empty state — never an invented page.
		echo '<div class="sgt-missing"><h2 class="sg-section-h">No live page of this type on this install</h2>'
			. '<p>The showcase embeds the REAL page (zero-hallucination law) — this install has no published '
			. esc_html( $type ) . ' page to embed. Anatomy reference: <code>' . esc_html( $md ) . '</code></p></div>';
		return;
	}
	list( $url, $how ) = $resolved;
	?>
	<div class="sgt-strip">
		<span class="sgt-live">LIVE PAGE</span>
		<span><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $url ); ?></a> (<?php echo esc_html( $how ); ?>) · open ↗ for full viewport</span>
		<?php if ( $native !== $brand ) : ?>
		<span class="sgt-preview">AIGUILD TOKEN PREVIEW</span>
		<span>real composition, <?php echo esc_html( $brand ); ?> tokens (Stage-3 preview)</span>
		<?php endif; ?>
		<span style="margin-left:auto;">inspiration, not contract · contract: <code>docs/THEME-CSS-CONTRACT.md</code></span>
	</div>
	<iframe id="sgt-frame" class="sgt-frame" src="<?php echo esc_url( $url ); ?>" title="<?php echo esc_attr( $type ); ?> — live page"></iframe>
	<?php if ( $native !== $brand ) : ?>
	<script>
	// Same-origin brand re-stamp: flips every token inside the embedded REAL
	// page — the composition stays byte-identical to production. Stamped
	// eagerly + retried (the doc is swapped mid-load) + on every load
	// (covers navigation inside the frame).
	(function () {
		var f = document.getElementById('sgt-frame');
		var brand = <?php echo wp_json_encode( $brand ); ?>;
		function stamp() {
			try { f.contentDocument.documentElement.setAttribute('data-theme', brand); } catch (e) {}
		}
		f.addEventListener('load', stamp);
		var n = 0;
		var t = setInterval(function () { stamp(); if ( ++n > 75 ) { clearInterval( t ); } }, 200);
	}());
	</script>
	<?php endif; ?>
	<details class="sgt-footnote"><summary>Anatomy notes — <?php echo esc_html( $md ); ?> (markdown, if necessary)</summary><ul>
		<?php foreach ( $notes as $n ) : ?><li><?php echo wp_kses_post( $n ); ?></li><?php endforeach; ?>
	</ul></details>
	<?php
}

/* ═════════════════════════════ THE SIX ITEMS ═════════════════════════════ */

function aifds_sg_item_template_homepage() {
	aifds_sg_render_template_embed( 'homepage', 'docs/templates/homepage.md', array(
		'Section stack: hero-aif (section-brand, hero-glitch highlight + capture) → info-bar (section-dark host) → aif-dark-section (quote + stack-grid--divided blurbs) → editorial (content-section--tertiary, articles-grid--editorial) → signals (--secondary) → events (--tertiary) → logos-section → personas (section-dark, dark-secondary paint) → newsletter-cta (section-brand).',
		'DS-owned: info-bar, stack-grid + blurb, preview-card, persona-card, badges, buttons, newsletter capture, surface scopes. Theme-owned: hero-aif, aif-dark-section bleed, articles-grid, logos-grid, personas-grid, newsletter-cta, hero-glitch (theme.css EXPERIMENTAL block).',
		'DS gaps (sweep candidates): the hero family, page-level logo grid.',
	) );
}

function aifds_sg_item_template_page() {
	aifds_sg_render_template_embed( 'page', 'docs/templates/standard-page.md', array(
		'Section stack: page-hero (brand, h1 + text--perex) → content-section section-light with main.content > .content-container prose measure (628px) → optional personas / partners sections (shared with homepage — MUST be one theme partial, rule R1) → newsletter-cta.',
		'DS-owned: prose flow, text--perex, persona-card, surface scopes. Theme-owned: page-hero, content-container, personas/logos grids.',
	) );
}

function aifds_sg_item_template_article() {
	aifds_sg_render_template_embed( 'article', 'docs/templates/article.md', array(
		'Section stack: article-hero (image/video + h1 + badges + meta) → content-section > article-layout (prose + engagement + native promo [add] + author persona-card + comments) → more-news (--secondary, news-columns: compact + external RSS cards) → newsletter-cta → sticky bar (guests, 50% scroll; theme lp-sticky port — DS .sticky-bar is the convergence target).',
		'DS-owned: prose, badges, engagement (+toast), persona-card, comments thread + form, preview-card. Theme-owned: article-hero, article-layout sidecar, news-columns, ad cascade, reading-time. Component copy formats via aif_component_content(), never the_content.',
		'body.single-post gates the reading-progress bar in the fixed header.',
	) );
}

function aifds_sg_item_template_archive() {
	aifds_sg_render_template_embed( 'archive', 'docs/templates/archive.md', array(
		'Section stack: archive-header (section-brand: breadcrumbs + h1 + category filter row) → content-section--secondary > articles-grid--archive of preview-cards + archive-pagination → newsletter-cta.',
		'DS-owned: breadcrumb, preview-card, archive-pagination. Theme-owned: archive-header composition, articles-grid--archive, filter row, empty state.',
		'Variants on the same skeleton: signals archive (--secondary, rich-text cards) · events archive (--tertiary, future-events query, paginate_links()).',
	) );
}

function aifds_sg_item_template_landing() {
	aifds_sg_render_template_embed( 'landing', 'docs/templates/landing-page.md', array(
		'Mechanism: body = raw ACF landing_html through do_shortcode(); body.aif-landing kills the 80px header offset; per-slug CSS/JS autoload (assets/css/landing/{slug}.css, dep aif-theme so LP overrides win); body.aif-landing--{slug} scopes it.',
		'Newsletter LP stack: hero-aif → bare info-bar → blurb stack-grid → data-table compare (dark) → sample-issue picker (per-slug one-off) → accordion FAQ (--tertiary) → [testimonial] on dark → newsletter band → DS sticky-bar (the canonical usage).',
	) );
}

function aifds_sg_item_template_auth() {
	aifds_sg_render_template_embed( 'auth', 'docs/templates/auth.md', array(
		'One shell, four pages (login / registration / password / profile): page-hero (brand, ZERO bottom padding — nav-tabs dock flush on the hero bottom edge, active white chip lands on the white section below) → content-section section-light > .aif-form column (max 420px).',
		'DS-owned: nav-tabs, form-stack/form-group/form-control, checkbox, buttons. Theme/mu-plugin-owned: .aif-form/.aif-login wrapper + status messages (aif_publisher_login_styles()), FluentForms + social-login skins.',
		'Non-happy paths belong to the page: activated / password-reset / login-error messages.',
	) );
}

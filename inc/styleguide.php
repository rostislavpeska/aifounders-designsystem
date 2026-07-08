<?php
/**
 * /design-system/ — the living styleguide ("WordPressbook" shell).
 *
 * Storybook-like layout: persistent sidebar (one item = one component or
 * token category), single-item content view, brand toggle. Renders through
 * the real plugin CSS + PHP in a standalone shell (no theme CSS).
 * Every showcase = REAL markup (styleguide-is-data law); chrome selectors
 * are scoped so they can never leak into demos.
 *
 * Routes: /design-system/ (first item) · /design-system/{item}/
 * Fallback (no rewrites needed): ?aifds_styleguide=1&item={slug}
 *
 * Access: admins, or any visitor when WP_DEBUG is on (= local stacks).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aifds_styleguide_add_rewrite() {
	add_rewrite_rule( '^design-system/([a-z0-9-]+)/?$', 'index.php?aifds_styleguide=1&aifds_item=$matches[1]', 'top' );
	add_rewrite_rule( '^design-system/?$', 'index.php?aifds_styleguide=1', 'top' );
}
add_action( 'init', 'aifds_styleguide_add_rewrite' );

function aifds_styleguide_query_vars( $vars ) {
	$vars[] = 'aifds_styleguide';
	$vars[] = 'aifds_item';
	return $vars;
}
add_filter( 'query_vars', 'aifds_styleguide_query_vars' );

/**
 * The item registry — sidebar structure. One item = one token category or
 * one component. Prefigures the component-folder + Storybook-story layout.
 */
function aifds_styleguide_items() {
	return array(
		'Tokens'     => array(
			'colors'         => 'Colors',
			'surfaces'       => 'Surfaces',
			'typography'     => 'Typography',
			'spacing'        => 'Spacing',
			'containers'     => 'Containers',
			'breakpoints'    => 'Breakpoints',
			'radius-shadows' => 'Radius, shadows & strokes',
			'icon-system'    => 'Icon system',
		),
		'Components' => array(
			'text-classes' => 'Typography classes',
			'text-elements' => 'Text elements',
			'buttons'      => 'Buttons',
			// form elements ARE components
			'input'            => 'Input',
			'select'           => 'Select',
			'datepicker'       => 'Datepicker',
			'checkbox'         => 'Checkbox',
			'radio'            => 'Radio',
			'consent'          => 'Consent',
			'segmented'        => 'Segmented control',
			'file-upload'      => 'File upload',
			'form-composition' => 'Form composition',
			'badges'       => 'Badges',
			'info-box'     => 'Info boxes',
			'data-tables'  => 'Data tables',
			'record-list'  => 'Record list',
			'preview-card' => 'Preview card',
			'course-card'  => 'Course card',
			'engagement'   => 'Engagement',
			'comments'     => 'Comments',
			'modal'        => 'Modal',
			'header'       => 'Header',
			'footer'       => 'Footer',
			'sticky-bar'   => 'Sticky bar',
			'info-bar'     => 'Info bar',
			'blurb'        => 'Blurb + stack grid',
			'accordion'    => 'Accordion',
			'breadcrumb'   => 'Breadcrumb',
			'pagination'   => 'Pagination',
			'nav-tabs'     => 'Nav tabs',
			'reference-card' => 'Testimonial',
			'persona-card' => 'Persona card',
			'avatars'      => 'Avatars',
			'icons'        => 'Icons',
			'prose'        => 'Prose contexts',
		),
	);
}

function aifds_styleguide_maybe_render() {
	$requested = get_query_var( 'aifds_styleguide' );
	if ( ! $requested && isset( $_GET['aifds_styleguide'] ) ) {
		$requested = '1';
	}
	if ( ! $requested ) {
		return;
	}

	$allowed = current_user_can( 'manage_options' ) || ( defined( 'WP_DEBUG' ) && WP_DEBUG );
	if ( ! $allowed ) {
		status_header( 404 );
		nocache_headers();
		exit;
	}

	$item = get_query_var( 'aifds_item' );
	if ( ! $item && isset( $_GET['item'] ) ) {
		$item = sanitize_key( $_GET['item'] );
	}

	$valid = array();
	foreach ( aifds_styleguide_items() as $group => $items ) {
		$valid = array_merge( $valid, array_keys( $items ) );
	}
	if ( ! in_array( $item, $valid, true ) ) {
		$item = $valid[0]; // default: colors
	}

	nocache_headers();
	aifds_render_styleguide( $item );
	exit;
}
add_action( 'template_redirect', 'aifds_styleguide_maybe_render' );

function aifds_styleguide_brand() {
	$req = isset( $_GET['theme'] ) ? sanitize_key( $_GET['theme'] ) : '';
	if ( in_array( $req, array( 'aiguild', 'aifounders' ), true ) ) {
		return $req;
	}
	return ( false !== strpos( get_stylesheet(), 'aifounders' ) ) ? 'aifounders' : 'aiguild';
}

function aifds_styleguide_url( $brand, $item = 'colors' ) {
	return add_query_arg(
		array( 'aifds_styleguide' => '1', 'item' => $item, 'theme' => $brand ),
		home_url( '/' )
	);
}

function aifds_render_styleguide( $item = 'colors' ) {
	$brand = aifds_styleguide_brand();
	$GLOBALS['aifds_sg_brand'] = $brand;
	$css_url = AIFDS_URL . 'assets/css/';
	$css_dir = AIFDS_DIR . 'assets/css/';

	$items  = aifds_styleguide_items();
	$titles = array();
	foreach ( $items as $group => $group_items ) {
		$titles = array_merge( $titles, $group_items );
	}

	header( 'Content-Type: text/html; charset=utf-8' );
	?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo esc_attr( $brand ); ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?php echo esc_html( $titles[ $item ] . ' — ' . $brand ); ?> — AIG DS v<?php echo esc_html( AIFDS_VERSION ); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="<?php echo esc_url( aifds_google_fonts_url() ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( $css_url . 'normalize.css?v=' . (string) @filemtime( $css_dir . 'normalize.css' ) ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( $css_url . 'fonts.css?v=' . (string) @filemtime( $css_dir . 'fonts.css' ) ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( $css_url . 'tokens.css?v=' . (string) @filemtime( $css_dir . 'tokens.css' ) ); ?>">
<link rel="stylesheet" href="<?php echo esc_url( $css_url . 'components.css?v=' . (string) @filemtime( $css_dir . 'components.css' ) ); ?>">
<style>
/* ── styleguide chrome only — NOT part of the design system. All selectors
   are sg-prefixed or direct-child scoped: chrome must NEVER leak into demos. */
body { margin:0; font-family: var(--font-primary); color: var(--text); background: var(--bg); }
.sg-app { display:grid; grid-template-columns: 250px 1fr; min-height:100vh; }
.sg-sidebar { background: var(--bg-alt); border-right:1px solid var(--border); padding:16px 0; position:sticky; top:0; height:100vh; overflow-y:auto; box-sizing:border-box; }
.sg-logo { padding:4px 20px 16px; }
.sg-logo b { font-size:14px; display:block; }
.sg-logo span { font-size:11px; color: var(--text-tertiary); }
.sg-group { margin-top:16px; }
.sg-group-label { padding:0 20px; font:700 10px/1 var(--font-mono); letter-spacing:.12em; text-transform:uppercase; color: var(--text-tertiary); }
.sg-nav { list-style:none; margin:8px 0 0; padding:0; }
.sg-nav a { display:block; padding:7px 20px; font-size:13px; text-decoration:none; color: var(--text-secondary); border-left:3px solid transparent; }
.sg-nav a:hover { background: var(--bg-band); color: var(--text); }
.sg-nav a.on { border-left-color: var(--brand); background: var(--bg); color: var(--text); font-weight:700; }
.sg-main { min-width:0; }
@media (max-width:767px){
  .sg-app { grid-template-columns:1fr; }
  .sg-sidebar { position:static; height:auto; border-right:0; border-bottom:1px solid var(--border); }
}
.sg-topbar { position:sticky; top:0; z-index:10; display:flex; align-items:center; gap:16px; padding:10px 32px; background: var(--black); color: var(--paper); }
.sg-topbar h1 { font-size:15px; margin:0; font-weight:700; }
.sg-brand { margin-left:auto; display:flex; gap:8px; }
.sg-brand a { padding:5px 12px; border-radius:6px; font-size:12px; text-decoration:none; color: var(--paper); border:1px solid var(--dark-600); }
.sg-brand a.on { background: var(--brand); color: var(--text-on-brand); border-color:transparent; font-weight:700; }
.sg-content { padding:32px 32px 120px; max-width:1100px; }
.sg-content > h2:first-child { margin-top:0; }
/* Wide tables (transforms matrix) escape the 1100px column and take the full
   viewport width minus sidebar; scroll only if even that is too narrow. */
.sg-fullbleed { width: calc(100vw - 250px - 64px - 12px); max-width: none; overflow-x: auto; }
/* Chrome tables never break the page — they scroll within themselves
   (display:block keeps row rendering; L2 sweep finding, audit 2026-07-06) */
.sg-table, .sg-props-table { display: block; overflow-x: auto; max-width: 100%; }
/* headings carry file paths / long code tokens — never let them push the page */
.sg-section-h, .sg-h3 { overflow-wrap: anywhere; }
.sg-section-h { font:900 22px/1.2 var(--font-primary); border-bottom:2px solid var(--border-strong); padding-bottom:8px; margin:40px 0 8px; }
.sg-h3 { font:800 15px/1.2 var(--font-primary); margin:28px 0 8px; }
.sg-note { background: var(--support); border-left:4px solid var(--brand); padding:12px 16px; font-size:14px; margin:16px 0; }
.sg-warn { background: color-mix(in srgb, var(--status-error) 12%, var(--bg)); border-left:4px solid var(--status-error); padding:12px 16px; font-size:14px; margin:16px 0; }
.sg-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:12px; margin-top:16px; }
.sg-swatch { border:1px solid var(--border); border-radius:8px; overflow:hidden; font-size:11px; }
.sg-swatch .chip { height:56px; }
.sg-swatch .meta { padding:8px; word-break:break-all; }
.sg-swatch .meta b { display:block; font-size:11px; }
.sg-swatch .hex { color: var(--text-tertiary); font-family: var(--font-mono); }
.sg-dark { background: var(--black); color: var(--paper); border-radius:12px; padding:24px; margin-top:16px; }
.sg-dark .sg-swatch { border-color: var(--dark-700); }
.sg-dark .sg-h3 { color: var(--dark-200); }
.sg-type-row { display:flex; align-items:baseline; gap:24px; border-bottom:1px dashed var(--border); padding:16px 0; }
.sg-type-label { flex:0 0 320px; font-size:12px; color: var(--text-tertiary); font-family: var(--font-mono); }
.sg-type-sample { margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.sg-space-row { display:flex; align-items:center; gap:16px; padding:4px 0; font-size:12px; font-family: var(--font-mono); }
.sg-space-row .lbl { flex:0 0 200px; }
.sg-space-row .bar { height:16px; background: var(--brand); }
.sg-cont-row { padding:4px 0; font-size:12px; font-family: var(--font-mono); }
.sg-cont-row .bar { height:12px; background: var(--secondary); margin-top:2px; }
.sg-cards { display:flex; gap:24px; flex-wrap:wrap; margin-top:16px; }
.sg-shadow-card, .sg-radius-card { width:140px; height:90px; background: var(--bg); display:flex; align-items:center; justify-content:center; font-size:12px; font-family: var(--font-mono); border:1px solid var(--border); }
.sg-row { display:flex; gap:16px; align-items:center; margin:12px 0; flex-wrap:wrap; }
table.sg-table { border-collapse:collapse; font-size:13px; margin-top:16px; }
table.sg-table th, table.sg-table td { border:1px solid var(--border); padding:6px 12px; text-align:left; }
table.sg-table th { background: var(--bg-alt); }
/* MODAL static preview: the box caps at 90vh (VIEWPORT-relative) + overflow-y:auto
   — the production scroll engine. A static, embedded frame can't stand in for the
   viewport, so 90vh resolved TALLER than the frame and the box was clipped, never
   scrolling. Here the frame IS the stand-in short viewport: bind the box's cap to
   the frame (max-height:100%) so the internal-scroll engine is shown honestly. */
#modal-static-default .modal__container { max-height: 100%; }
</style>
</head>
<body>
<div class="sg-app">
	<aside class="sg-sidebar">
		<div class="sg-logo"><b>AI Founders Design System</b><span>v<?php echo esc_html( AIFDS_VERSION ); ?> · WordPressbook</span></div>
		<?php foreach ( $items as $group => $group_items ) : ?>
		<div class="sg-group">
			<div class="sg-group-label"><?php echo esc_html( $group ); ?></div>
			<ul class="sg-nav">
				<?php foreach ( $group_items as $slug => $title ) : ?>
				<li><a href="<?php echo esc_url( aifds_styleguide_url( $brand, $slug ) ); ?>" class="<?php echo $slug === $item ? 'on' : ''; ?>"><?php echo esc_html( $title ); ?></a></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endforeach; ?>
	</aside>
	<div class="sg-main">
		<header class="sg-topbar">
			<h1><?php echo esc_html( $titles[ $item ] ); ?></h1>
			<nav class="sg-brand">
				<a href="<?php echo esc_url( aifds_styleguide_url( 'aiguild', $item ) ); ?>" class="<?php echo 'aiguild' === $brand ? 'on' : ''; ?>">aiguild (yellow)</a>
				<a href="<?php echo esc_url( aifds_styleguide_url( 'aifounders', $item ) ); ?>" class="<?php echo 'aifounders' === $brand ? 'on' : ''; ?>">aifounders (blue)</a>
			</nav>
		</header>
		<main class="sg-content">
			<?php call_user_func( 'aifds_sg_item_' . str_replace( '-', '_', $item ) ); ?>
		</main>
	</div>
</div>
<script>
(function () {
	var cs = getComputedStyle(document.documentElement);
	document.querySelectorAll('[data-token]').forEach(function (el) {
		var v = cs.getPropertyValue(el.getAttribute('data-token')).trim();
		el.textContent = ' ' + v;
	});
}());
</script>
<script src="<?php echo esc_url( AIFDS_URL . 'js/components/accordion.js' ); ?>?v=<?php echo esc_attr( AIFDS_VERSION ); ?>"></script>
<script src="<?php echo esc_url( AIFDS_URL . 'js/components/engagement.js' ); ?>?v=<?php echo esc_attr( AIFDS_VERSION ); ?>"></script>
<script src="<?php echo esc_url( AIFDS_URL . 'js/components/modal.js' ); ?>?v=<?php echo esc_attr( AIFDS_VERSION ); ?>"></script>
<script src="<?php echo esc_url( AIFDS_URL . 'js/components/sticky-bar.js' ); ?>?v=<?php echo esc_attr( AIFDS_VERSION ); ?>"></script>
<script src="<?php echo esc_url( AIFDS_URL . 'js/components/menu.js' ); ?>?v=<?php echo esc_attr( AIFDS_VERSION ); ?>"></script>
<script src="<?php echo esc_url( AIFDS_URL . 'js/components/nav-tabs.js' ); ?>?v=<?php echo esc_attr( AIFDS_VERSION ); ?>"></script>
</body>
</html>
	<?php
}

/* ═══════════════════════════ TOKEN ITEMS ═════════════════════════════════ */

/* ── TOKEN MANIFEST (single source, operator law 2026-07-03) ────────────────
   Token descriptions live ONLY in tokens/*.json ($description); the build
   projects them into assets/tokens-manifest.json and the styleguide renders
   FROM it. No hand-maintained token facts in PHP — grouping/order below is
   the only curation. */
function aifds_tokens_manifest() {
	static $m = null;
	if ( null === $m ) {
		$m = json_decode( (string) file_get_contents( AIFDS_DIR . 'assets/tokens-manifest.json' ), true );
		if ( ! is_array( $m ) ) {
			$m = array( 'semantic' => array(), 'component' => array() );
		}
	}
	return $m;
}

function aifds_color_chip( $val, $size = 28 ) {
	if ( null === $val || '' === $val ) {
		return '<em style="color: var(--status-error);">MISSING</em>';
	}
	return '<span style="display:inline-block; width:' . (int) $size . 'px; height:' . (int) $size . 'px; border:1px solid var(--border); vertical-align:middle; margin-right:8px; background: '
		. esc_attr( $val ) . ';"></span>' . esc_html( $val );
}

function aifds_palette_intent( $name ) {
	if ( 0 === strpos( $name, 'overlay-' ) ) {
		return 'Hover overlays';
	}
	if ( 0 === strpos( $name, 'highlight' ) ) {
		return 'Text selection';
	}
	if ( 0 === strpos( $name, 'secondary' ) ) {
		return 'Sibling-brand accents (eyebrows, promo badges)';
	}
	if ( 0 === strpos( $name, 'magenta' ) ) {
		return 'Category accents (locations)';
	}
	if ( 0 === strpos( $name, 'lime' ) ) {
		return 'Category accents (editorial)';
	}
	if ( 0 === strpos( $name, 'support' ) ) {
		return 'Section accents, tinted panels';
	}
	if ( 0 === strpos( $name, 'deep' ) ) {
		return 'Deep accents (badges, borders on dark)';
	}
	if ( 0 === strpos( $name, 'tint-' ) ) {
		return 'Brand-tinted neutrals';
	}
	if ( 0 === strpos( $name, 'brand' ) ) {
		return 'Brand elements, important actions';
	}
	if ( 'paper' === $name || 0 === strpos( $name, 'dark-' ) ) {
		return 'Dark neutrals: text, backgrounds, borders';
	}
	if ( in_array( $name, array( 'white', 'black' ), true ) || 0 === strpos( $name, 'gray-' ) ) {
		return 'Light neutrals: text, backgrounds, borders';
	}
	return 'Status & feedback';
}

function aifds_token_intent( $name ) {
	$map = array(
		'button'    => 'Buttons',
		'field'     => 'Form fields',
		'badge'     => 'Badges',
		'disabled'  => 'Disabled states',
		'link'      => 'Links',
		'text'      => 'Text blocks',
		'bg'        => 'Backgrounds',
		'border'    => 'UI elements (borders, dividers)',
		'raised'    => 'Raised elements (cards, hovers)',
		'bullet'    => 'List bullets',
		'overlay'   => 'Hover overlays',
		'selection' => 'Text selection',
		'status'    => 'Status & feedback',
		'perex'     => 'Perex & quote accent',
		'icon'      => 'Character icon accents',
	);
	foreach ( $map as $prefix => $intent ) {
		if ( 0 === strpos( $name, $prefix ) ) {
			return $intent;
		}
	}
	return 'Accent';
}

function aifds_sg_item_colors() {
	$man = aifds_tokens_manifest();

	/* ONE TAB, THREE LAYERS (operator architecture, 2026-07-03):
	   1 PALETTE — named unique values, the ONLY place colors are defined
	   2 TOKENS  — one semantic vocabulary, each a single-hop palette ref
	   3 TRANSFORMS — per-background deltas re-declaring the SAME names
	   Everything generated from tokens/*.json. */

	// Layer 1 groups derive from the palette NAME
	$pgroup_of = function ( $name ) {
		if ( 0 === strpos( $name, 'secondary' ) ) {
			return 'Secondary';
		}
		if ( 0 === strpos( $name, 'magenta' ) ) {
			return 'Tertiary';
		}
		if ( 0 === strpos( $name, 'lime' ) ) {
			return 'Quaternary';
		}
		foreach ( array( 'brand', 'deep', 'support', 'tint-' ) as $p ) {
			if ( 0 === strpos( $name, $p ) ) {
				return 'Primary';
			}
		}
		if ( 'paper' === $name || 0 === strpos( $name, 'dark-' ) ) {
			return 'Dark theme';
		}
		if ( in_array( $name, array( 'white', 'black' ), true ) || 0 === strpos( $name, 'gray-' ) ) {
			return 'Light theme';
		}
		if ( 0 === strpos( $name, 'overlay-' ) || 0 === strpos( $name, 'highlight' ) ) {
			return 'Special';
		}
		return 'Status';
	};
	$pgroups = array_fill_keys( array( 'Primary', 'Secondary', 'Tertiary', 'Quaternary', 'Light theme', 'Dark theme', 'Status', 'Special' ), array() );
	foreach ( $man['palette'] as $name => $v ) {
		$pgroups[ $pgroup_of( $name ) ][ $name ] = $v;
	}
	?>
	<div class="sg-note"><b>THE COLOR SYSTEM — three layers, one page.</b><br>
		<b>1 · PALETTE</b>: named unique colors — the ONLY place values are defined. Change a value here and
		everything using it follows.<br>
		<b>2 · TOKENS</b>: the one semantic vocabulary components use — every token is a single-hop reference
		to a palette name. Repoint a token and only that role changes.<br>
		<b>3 · ON-BACKGROUND TRANSFORMS</b>: dark / brand / support (and band) sections re-declare the SAME
		token names with different palette refs — a component only ever knows <code>--text</code>; the
		background decides its value. Reading a scope table below IS the complete answer to “what changes
		there”.<br>
		LAWS: values only in the palette · tokens reference palette names, never siblings · scopes add no new
		names. GROWTH: new element → 0 tokens · new background → 1 scope file · new token/palette name →
		operator sign-off.</div>

	<h2 class="sg-section-h">1 · Palette</h2>
	<?php foreach ( $pgroups as $glabel => $entries ) : ?>
	<h3 class="sg-h3"><?php echo esc_html( $glabel ); ?> — <?php echo (int) count( $entries ); ?> colors</h3>
	<table class="sg-table">
		<tr><th>name</th><th>AIG</th><th>AIF</th><th>Intent</th></tr>
		<?php foreach ( $entries as $name => $v ) : ?>
		<tr>
			<td style="font-family:var(--font-mono); font-size:12px;">--<?php echo esc_html( $name ); ?></td>
			<td style="font-family:var(--font-mono); font-size:12px; white-space:nowrap;"><?php echo aifds_color_chip( $v['aig'] ); ?></td>
			<td style="font-family:var(--font-mono); font-size:12px; white-space:nowrap;"><?php echo aifds_color_chip( $v['aif'] ); ?></td>
			<td style="font-size:12px; color: var(--text-secondary);"><?php echo esc_html( aifds_palette_intent( $name ) ); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php endforeach; ?>

	<h2 class="sg-section-h">2 · Tokens — the semantic vocabulary (<?php echo (int) count( $man['semantic'] ); ?> names)</h2>
	<div class="sg-note">There are NO <code>-dark</code> token names by design: the dark value of
		<code>--field-bg</code> is <code>--field-bg</code> itself, re-declared by the dark background —
		one vocabulary, backgrounds swap the values. The “transforms on” column says WHERE a token changes;
		the exact values are in the matrix below (§3).</div>
	<table class="sg-table">
		<tr><th>token</th><th>→ palette</th><th>AIG</th><th>AIF</th><th>transforms on</th><th>Intent</th></tr>
		<?php
		$scope_short = array( 'light-2' => 'Soft band', 'light-3' => 'Neutral band', 'dark-1' => 'Dark', 'dark-2' => 'Dark layered', 'dark-3' => 'Dark raised', 'brand' => 'Brand', 'support' => 'Support' );
		foreach ( $man['semantic'] as $name => $v ) :
			$where = array();
			foreach ( $scope_short as $sc => $lbl ) {
				if ( isset( $man['scopes'][ $sc ][ $name ] ) ) {
					$where[] = $lbl;
				}
			}
			?>
		<tr>
			<td style="font-family:var(--font-mono); font-size:12px;">--<?php echo esc_html( $name ); ?></td>
			<td style="font-family:var(--font-mono); font-size:12px; color: var(--text-tertiary);">→ <?php echo esc_html( $v['ref'] ); ?></td>
			<td style="font-family:var(--font-mono); font-size:12px; white-space:nowrap;"><?php echo aifds_color_chip( $v['aig'] ); ?></td>
			<td style="font-family:var(--font-mono); font-size:12px; white-space:nowrap;"><?php echo aifds_color_chip( $v['aif'] ); ?></td>
			<td style="font-size:11px; color: var(--text-secondary);"><?php echo $where ? esc_html( implode( ' · ', $where ) ) . ' <a href="#" onclick="document.querySelector(\'[data-test=transforms-matrix]\').scrollIntoView();return false;" style="font-size:10px;">↓ matrix</a>' : '<span style="opacity:.5;">constant</span>'; ?></td>
			<td style="font-size:12px; color: var(--text-secondary);"><?php echo esc_html( aifds_token_intent( $name ) ); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>

	<h2 class="sg-section-h">3 · On-background transforms — one matrix, same names, columns = backgrounds</h2>
	<?php
	// Current theme only (brand toggle above): transforms are BEHAVIOR, the
	// same structure on both brands. Dot = inherits the Default.
	$b          = ( 'aifounders' === $GLOBALS['aifds_sg_brand'] ) ? 'aif' : 'aig';
	$scope_cols = array(
		'light-2' => array( 'Soft band', '.content-section--secondary' ),
		'light-3' => array( 'Neutral band', '.content-section--tertiary' ),
		'dark-1'  => array( 'Dark', '.section-dark, .footer, .hero-card' ),
		'dark-2'  => array( 'Dark layered', '.content-section--dark-secondary' ),
		'dark-3'  => array( 'Dark raised', '.persona-card' ),
		'brand'   => array( 'Brand', '.section-brand, .article-hero' ),
		'support' => array( 'Support', '.surface-support, .smart-btn' ),
	);
	// rows = only the tokens that change somewhere, in vocabulary order
	$changing = array();
	foreach ( $man['semantic'] as $name => $v ) {
		foreach ( $scope_cols as $scope => $_l ) {
			if ( isset( $man['scopes'][ $scope ][ $name ] ) ) {
				$changing[ $name ] = $v;
				break;
			}
		}
	}
	?>
	<div class="sg-note">Rows: the <?php echo (int) count( $changing ); ?> tokens (of <?php echo (int) count( $man['semantic'] ); ?>)
		that change on at least one background — the rest are constants. <b>·</b> = inherits the Default.
		A new background = one new scope file = one new column here, ZERO new names.</div>
	<div class="sg-fullbleed">
	<table class="sg-table" data-test="transforms-matrix">
		<tr>
			<th>token</th>
			<th>Default</th>
			<?php foreach ( $scope_cols as $scope => $l ) : ?>
			<th><?php echo esc_html( $l[0] ); ?><span style="display:block; font-weight:400; font-size:10px; color: var(--text-tertiary);"><?php echo esc_html( $l[1] ); ?></span></th>
			<?php endforeach; ?>
			<th>Intent</th>
		</tr>
		<?php foreach ( $changing as $name => $v ) : ?>
		<tr>
			<td style="font-family:var(--font-mono); font-size:12px;">--<?php echo esc_html( $name ); ?></td>
			<td style="font-family:var(--font-mono); font-size:11px; white-space:nowrap;"><?php echo aifds_color_chip( $v[ $b ], 14 ); ?><span style="display:block; color: var(--text-tertiary);"><?php echo esc_html( $v['ref'] ); ?></span></td>
			<?php foreach ( $scope_cols as $scope => $_l ) : ?>
			<td style="font-family:var(--font-mono); font-size:11px; white-space:nowrap;">
				<?php
				if ( isset( $man['scopes'][ $scope ][ $name ] ) ) {
					$sv = $man['scopes'][ $scope ][ $name ];
					echo aifds_color_chip( $sv[ $b ], 14 ) . '<span style="display:block; color: var(--text-tertiary);">' . esc_html( $sv['ref'] ) . '</span>';
				} else {
					echo '<span style="color: var(--text-tertiary); opacity:.5;">&middot;</span>';
				}
				?>
			</td>
			<?php endforeach; ?>
			<td style="font-size:12px; color: var(--text-secondary);"><?php echo esc_html( aifds_token_intent( $name ) ); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	</div>
	<?php
}

function aifds_sg_item_typography() {
	$man    = aifds_tokens_manifest();
	$prim   = isset( $man['typePrimitives'] ) ? $man['typePrimitives'] : array();
	$styles = isset( $man['typeStyles'] ) ? $man['typeStyles'] : array();
	$sample = 'The quick brown fox jumps over the lazy dog 0123456789';
	$pick   = function ( $prefix ) use ( $prim ) {
		$out = array();
		foreach ( $prim as $k => $v ) {
			if ( 0 === strpos( $k, $prefix ) ) {
				$out[ $k ] = $v;
			}
		}
		return $out;
	};
	?>
	<div class="sg-note"><b>TYPOGRAPHY — the operator's six layers.</b>
		<b>1 SIZES</b> (pure, line-height-free) · <b>2 SPACING</b> (leading + paragraph flow) ·
		<b>3 FONTS &amp; WEIGHTS</b> (no 300 — the Light law) · <b>4 MOBILE</b> (a style's own size,
		re-declared in one media block) · <b>5 STYLES</b> (bundles: size + font + weight + leading +
		mobile) · <b>6 TRANSFORMS</b> (article context, brand divergence, decorations, emphasis).
		Generated from <code>tokens/typography.json</code> + <code>tokens/type-styles.json</code>.</div>

	<h2 class="sg-section-h">1 · Sizes</h2>
	<table class="sg-table">
		<tr><th>token</th><th>value</th><th>sample (line-height 1)</th></tr>
		<?php foreach ( $pick( 'size-' ) as $k => $v ) : ?>
		<tr>
			<td style="font-family:var(--font-mono); font-size:12px;">--<?php echo esc_html( $k ); ?></td>
			<td style="font-family:var(--font-mono); font-size:12px;"><?php echo esc_html( $v['ref'] ); ?></td>
			<td><span style="font-size: var(--<?php echo esc_attr( $k ); ?>); line-height:1; font-family: var(--font-primary);">Aa</span></td>
		</tr>
		<?php endforeach; ?>
	</table>

	<h2 class="sg-section-h">2 · Spacing — leading + paragraph flow</h2>
	<table class="sg-table">
		<tr><th>leading token</th><th>value</th><th>flow token</th><th>value</th></tr>
		<?php
		$leads = array_values( array_map( null, array_keys( $pick( 'leading-' ) ), array_values( $pick( 'leading-' ) ) ) );
		$flows = array_values( array_map( null, array_keys( $pick( 'flow-' ) ), array_values( $pick( 'flow-' ) ) ) );
		$rows  = max( count( $leads ), count( $flows ) );
		for ( $i = 0; $i < $rows; $i++ ) :
			?>
		<tr>
			<td style="font-family:var(--font-mono); font-size:12px;"><?php echo isset( $leads[ $i ] ) ? '--' . esc_html( $leads[ $i ][0] ) : ''; ?></td>
			<td style="font-family:var(--font-mono); font-size:12px;"><?php echo isset( $leads[ $i ] ) ? esc_html( $leads[ $i ][1]['ref'] ) : ''; ?></td>
			<td style="font-family:var(--font-mono); font-size:12px;"><?php echo isset( $flows[ $i ] ) ? '--' . esc_html( $flows[ $i ][0] ) : ''; ?></td>
			<td style="font-family:var(--font-mono); font-size:12px;"><?php echo isset( $flows[ $i ] ) ? esc_html( $flows[ $i ][1]['ref'] . ( $flows[ $i ][1]['description'] ? ' — ' . $flows[ $i ][1]['description'] : '' ) ) : ''; ?></td>
		</tr>
		<?php endfor; ?>
	</table>

	<h2 class="sg-section-h">3 · Fonts, weights &amp; text transforms</h2>
	<table class="sg-table">
		<tr><th>token</th><th>value</th><th>sample</th></tr>
		<?php foreach ( $pick( 'font-' ) as $k => $v ) : ?>
		<tr>
			<td style="font-family:var(--font-mono); font-size:12px;">--<?php echo esc_html( $k ); ?></td>
			<td style="font-family:var(--font-mono); font-size:11px;"><?php echo esc_html( $v['ref'] ); ?></td>
			<td><span style="font-family: var(--<?php echo esc_attr( $k ); ?>); font-size:18px;"><?php echo esc_html( $sample ); ?></span></td>
		</tr>
		<?php endforeach; ?>
		<?php foreach ( $pick( 'weight-' ) as $k => $v ) : ?>
		<tr>
			<td style="font-family:var(--font-mono); font-size:12px;">--<?php echo esc_html( $k ); ?></td>
			<td style="font-family:var(--font-mono); font-size:12px;"><?php echo esc_html( $v['ref'] ); ?></td>
			<td><span style="font-weight: var(--<?php echo esc_attr( $k ); ?>); font-size:18px; font-family: var(--font-primary);">Weight <?php echo esc_html( $v['ref'] ); ?></span></td>
		</tr>
		<?php endforeach; ?>
		<?php foreach ( $pick( 'case-' ) as $k => $v ) : ?>
		<tr>
			<td style="font-family:var(--font-mono); font-size:12px;">--<?php echo esc_html( $k ); ?></td>
			<td style="font-family:var(--font-mono); font-size:12px;"><?php echo esc_html( $v['ref'] ); ?></td>
			<td><span style="text-transform: var(--<?php echo esc_attr( $k ); ?>); font-size:14px; font-family: var(--font-mono); font-weight: var(--weight-bold);">the eyebrow voice in caps</span></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<div class="sg-note">NO 300/Light anywhere — the Light claims in the old tokens were fiction
		(subheadline renders BOLD, lead quote MEDIUM); this migration encodes the rendered reality.</div>

	<h2 class="sg-section-h">5 · Styles — the bundles (4 · mobile lives inside each) — REAL sizes, live evidence</h2>
	<div class="sg-fullbleed">
	<table class="sg-table">
		<tr><th>style</th><th>size</th><th>font</th><th>weight</th><th>leading</th><th>behavior</th><th>specimen — REAL size</th><th>live evidence (7-page harvest)</th></tr>
		<?php
		$bt = isset( $man['brandType'] ) ? $man['brandType'] : array();
		// a size is FLUID when its primitive resolves to a clamp() — shrinking is dynamic
		$is_fluid = function ( $ref ) use ( $prim ) {
			return $ref && isset( $prim[ $ref ] ) && false !== strpos( $prim[ $ref ]['ref'], 'clamp' );
		};
		// brand-diverged prop cell: show the ACTUAL per-brand refs, not just 'brand'
		$brand_cell = function ( $key ) use ( $bt ) {
			if ( ! isset( $bt[ $key ] ) ) {
				return '<em>brand?</em>';
			}
			return '<em>AIG</em> ' . esc_html( (string) $bt[ $key ]['aig'] ) . '<br><em>AIF</em> ' . esc_html( (string) $bt[ $key ]['aif'] );
		};
		foreach ( $styles as $name => $props ) :
			/* BEHAVIOR cell — MECHANISM LAW (Carbon): behavior comes from the
			   style's CLASS: display = FLUID (clamp) · content ramp = ONE STEP ·
			   reading/UI = CONSTANT. Mechanisms never diverge per brand. */
			$size_ref = isset( $props['size'] ) ? $props['size'] : null;
			$brand_sz = isset( $bt[ $name . '-size' ] ) ? $bt[ $name . '-size' ] : null;
			if ( isset( $props['mobile-size'] ) ) {
				$mobile = 'step → ' . esc_html( $props['mobile-size'] );
			} elseif ( $is_fluid( $size_ref ) || ( $brand_sz && ( false !== strpos( (string) $brand_sz['aig'], 'clamp' ) || false !== strpos( (string) $brand_sz['aif'], 'clamp' ) ) ) ) {
				$mobile = 'fluid (clamp)';
			} else {
				$mobile = '<span style="opacity:.5;">constant</span>';
			}
			?>
		<tr>
			<td style="font-family:var(--font-mono); font-size:12px;">--<?php echo esc_html( $name ); ?>-*</td>
			<td style="font-family:var(--font-mono); font-size:11px;"><?php echo isset( $props['size'] ) ? esc_html( $props['size'] ) : $brand_cell( $name . '-size' ); ?></td>
			<td style="font-family:var(--font-mono); font-size:11px;"><?php echo isset( $props['font'] ) ? esc_html( $props['font'] ) : $brand_cell( $name . '-font' ); ?></td>
			<td style="font-family:var(--font-mono); font-size:11px;"><?php echo isset( $props['weight'] ) ? esc_html( $props['weight'] ) : $brand_cell( $name . '-weight' ); ?></td>
			<td style="font-family:var(--font-mono); font-size:11px;"><?php echo isset( $props['leading'] ) ? esc_html( $props['leading'] ) : $brand_cell( $name . '-leading' ); ?></td>
			<td style="font-family:var(--font-mono); font-size:11px;"><?php echo $mobile; ?></td>
			<td style="min-width:360px;"><span class="sg-type-sample" style="font-family: var(--<?php echo esc_attr( $name ); ?>-font, var(--font-primary)); font-size: var(--<?php echo esc_attr( $name ); ?>-size); font-weight: var(--<?php echo esc_attr( $name ); ?>-weight, var(--weight-regular)); line-height: var(--<?php echo esc_attr( $name ); ?>-leading, var(--leading-heading)); text-transform: var(--<?php echo esc_attr( $name ); ?>-case, none);">Almost before we knew it</span><?php if ( isset( $props['case'] ) ) : ?><span style="display:block; font-family:var(--font-mono); font-size:10px; color: var(--text-tertiary);"><?php echo esc_html( $props['case'] ); ?></span><?php endif; ?></td>
			<td style="font-size:11px; color: var(--text-secondary); max-width:300px;"><?php echo isset( $props['evidence'] ) ? esc_html( $props['evidence'] ) : ''; ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	</div>
	<div class="sg-note">Specimens render at the style's REAL size — no caps, no fakes (styleguide-is-data law;
		the capped specimen was a violation, corrected 2026-07-03). <em>brand</em> = the property lives in the
		brand token files: heading-lg SIZE (AIG fluid 32→44 / AIF 36 + mobile 28), heading-md font/weight
		(Lazzer AIG / Inter AIF), plus four weight/leading splits.
		COLLAPSED on live evidence: <b>subheadline</b> + <b>lead-quote</b> were the SAME 24px voice as
		<code>lead</code> (no 24px-medium exists anywhere live) — ONE style remains.
		RETIRED on verdict: <code>subtitle</code> (SG 60) had NO live usage — removed 2026-07-03; the 32px card-title
		voices (cert-card Inter 800, reservation-modal SG 700) are component-batch candidates, not core styles.</div>

	<h2 class="sg-section-h">6 · Transforms</h2>
	<div class="sg-note"><b>Article context</b>: inside <code>.article-layout__content</code> every heading slot
		steps ONE style down (h2 renders heading-md, h3 renders heading-sm, h4 renders heading-xs) — see “Prose
		contexts”. <b>Mobile</b>: each style's own size var re-declared under 768px (see column above).
		<b>Brand divergence</b>: the 7 <em>brand</em> cells above. <b>Decorations</b>: links = 1px hairline
		underline at 4px offset (thickens on brand hover); perex/quote = 4px left border reading
		<code>--perex-border</code> (adapts per background — see Colors → transforms).
		<b>Emphasis</b>: <strong>bold</strong> = weight-bold within any style; <em>italic</em> = browser italic —
		neither creates a new style.</div>

	<h2 class="sg-section-h">RHYTHM — the flow law, live</h2>
	<div class="sg-note">Space BEFORE a heading ≈ 2× the space after (48/24 · 40/24 · 32/16): a heading belongs
		to what follows. Paragraph = 24 below · lists 16 · figures 40 · article pull-quotes 56/40 (unified at
		AIG's deliberate value). First heading in a container resets to 0.</div>
	<main class="section-light" data-test="rhythm-specimen" style="border:1px dashed var(--border-strong); padding:32px; max-width:760px;">
		<article class="article-layout__content">
			<h1>The document headline</h1>
			<p class="text--perex">The intro perex opens the article in the lead voice — 24px accent bold with
				the support border, exactly as harvested.</p>
			<p>Body follows at the article reading voice (18px, 1.7). A paragraph carries 24px below — that is
				the entire space between paragraphs.</p>
			<p>Second paragraph, so the paragraph rhythm is visible right here.</p>
			<h2>A section heading — 48px above, 24px below</h2>
			<p>The 48px above came from the heading's own margin collapsing with the paragraph's 24 — the
				larger wins, so the heading visibly belongs to this text, not the previous block. In the
				article context this h2 renders the heading-md style (the ramp steps down).</p>
			<h3>A subsection — 40px above</h3>
			<p>Same law, one step tighter.</p>
			<h4>The smallest step — 32px above, 16px below</h4>
			<p>Closing the ramp.</p>
			<ul><li>Lists sit 16px from what follows,</li><li>with their own internal rhythm.</li></ul>
			<p>Closing paragraph after the list.</p>
		</article>
	</main>
	<?php
}

function aifds_sg_item_spacing() {
	?>
	<h2 class="sg-section-h">Raw scale</h2>
	<?php foreach ( array( 2, 4, 8, 12, 16, 24, 32, 40, 48, 56, 80, 120 ) as $s ) : ?>
	<div class="sg-space-row"><span class="lbl">--spacing-<?php echo (int) $s; ?></span><div class="bar" style="width: var(--spacing-<?php echo (int) $s; ?>);"></div></div>
	<?php endforeach; ?>
	<h2 class="sg-section-h">Semantic layer (NEW v2.0)</h2>
	<div class="sg-note">v2.0: mapped to current values (no visual change). v2.1 makes these fluid (clamp — Apple-grade dynamics). Components adopt them batch by batch.</div>
	<?php foreach ( array( 'section', 'block', 'stack', 'tight' ) as $s ) : ?>
	<div class="sg-space-row"><span class="lbl">--space-<?php echo esc_html( $s ); ?></span><div class="bar" style="width: var(--space-<?php echo esc_attr( $s ); ?>); background: var(--magenta);"></div></div>
	<?php endforeach; ?>
	<?php
}

function aifds_sg_item_containers() {
	?>
	<h2 class="sg-section-h">Container widths</h2>
	<?php foreach ( array( 'narrow', 'article', 'wide', 'wider', 'max', 'ultra' ) as $c ) : ?>
	<div class="sg-cont-row">--container-<?php echo esc_html( $c ); ?> <span class="hex" data-token="--container-<?php echo esc_attr( $c ); ?>"></span>
		<div class="bar" style="max-width:100%; width: var(--container-<?php echo esc_attr( $c ); ?>);"></div></div>
	<?php endforeach; ?>
	<?php
}

function aifds_sg_item_breakpoints() {
	$man     = aifds_tokens_manifest();
	$bp      = isset( $man['breakpoints'] ) ? $man['breakpoints'] : array( 'cuts' => array(), 'buckets' => array() );
	$cuts    = $bp['cuts'];
	$buckets = $bp['buckets'];
	$scale   = 1600; // px — the bar's right edge; the open-ended bucket runs to it
	?>
	<div class="sg-note"><b>BREAKPOINTS — four cuts, five buckets (ratified 2026-07-03,
		<code>docs/proposals/BREAKPOINTS.md</code>).</b><br>
		Apple's shape: few content-driven cuts, desktop-first, named buckets, stepped type per style.
		Carbon's machinery: a CLOSED set, build-time constants, machine-enforced.
		Harvested from the live themes (324 media blocks, 29 distinct widths → these 4) — nothing invented.<br>
		<b>BOUNDARY LAW</b>: exclusive pairs — <code>max</code> uses value−1 (599/767/1023), <code>min</code> the value.
		<b>DIRECTION LAW</b>: overrides are <code>max-width</code>; <code>min-width</code> only for the additive
		wide tier. <b>CLOSED SET</b>: any other width in DS CSS fails the build (lint LAW 4) unless the line
		declares <code>/* bp-exception: reason */</code> — the Apple nav rule: local, declared, never a shared tier.<br>
		NO <code>--bp-*</code> custom properties exist ON PURPOSE: CSS cannot read <code>var()</code> inside
		<code>@media</code> — emitting them would be fiction. The build derives every query from
		<code>tokens/breakpoints.json</code>.</div>

	<h2 class="sg-section-h">The buckets — resize this window, the marker follows</h2>
	<div style="font-family:var(--font-mono); font-size:12px; margin:16px 0 4px;" data-test="bp-live">
		viewport <b data-bp-width>?</b>px → bucket <b data-bp-bucket>?</b></div>
	<div style="position:relative; height:64px; border:1px solid var(--border-strong);" data-test="bp-bar"
		data-buckets='<?php echo esc_attr( wp_json_encode( $buckets ) ); ?>'>
		<?php foreach ( $buckets as $i => $b ) :
			$to    = null === $b['to'] ? $scale : min( $b['to'] + 1, $scale );
			$left  = 100 * $b['from'] / $scale;
			$width = 100 * ( $to - $b['from'] ) / $scale;
			?>
		<div style="position:absolute; top:0; bottom:0; left:<?php echo esc_attr( $left ); ?>%; width:<?php echo esc_attr( $width ); ?>%; box-sizing:border-box; border-left:<?php echo $i ? '2px solid var(--border-strong)' : 'none'; ?>; background: <?php echo $i % 2 ? 'var(--bg-alt)' : 'var(--bg)'; ?>; padding:6px 8px; overflow:hidden;">
			<span style="display:block; font:700 11px/1.2 var(--font-mono);"><?php echo esc_html( $b['name'] ); ?></span>
			<span style="display:block; font:400 10px/1.4 var(--font-mono); color: var(--text-tertiary);"><?php echo esc_html( $b['from'] . ( null === $b['to'] ? '+' : '–' . $b['to'] ) ); ?></span>
		</div>
		<?php endforeach; ?>
		<div data-bp-marker style="position:absolute; top:0; bottom:0; width:2px; background: var(--brand); display:none;"></div>
	</div>

	<h2 class="sg-section-h">The cuts — <?php echo (int) count( $cuts ); ?> tokens, generated from <code>tokens/breakpoints.json</code></h2>
	<table class="sg-table" data-test="bp-table">
		<tr><th>token</th><th>value</th><th>opens bucket</th><th>query forms (BOUNDARY LAW)</th><th>meaning</th><th>live evidence (3-theme harvest)</th></tr>
		<?php foreach ( $cuts as $name => $c ) : ?>
		<tr>
			<td style="font-family:var(--font-mono); font-size:12px;"><?php echo esc_html( $name ); ?></td>
			<td style="font-family:var(--font-mono); font-size:12px;"><?php echo esc_html( $c['value'] ); ?></td>
			<td style="font-size:12px;"><?php echo esc_html( $c['bucket'] ); ?></td>
			<td style="font-family:var(--font-mono); font-size:11px;"><?php echo esc_html( $c['min'] ); ?><br><?php echo esc_html( $c['max'] ); ?></td>
			<td style="font-size:12px; color: var(--text-secondary);"><?php echo esc_html( $c['description'] ); ?></td>
			<td style="font-size:11px; color: var(--text-secondary); max-width:300px;"><?php echo esc_html( $c['evidence'] ); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>

	<h2 class="sg-section-h">How the mechanisms use the cuts</h2>
	<div class="sg-note"><b>Type needs almost none of this</b> (MECHANISM LAW): display styles are FLUID
		(clamp — zero media queries), the content ramp takes its ONE step at <code>bp-md</code> (the same
		767px block that tokens.css emits), reading/UI styles are CONSTANT. Layout is where the cuts work:
		grids collapse at <code>bp-md</code>/<code>bp-lg</code>, navs at <code>bp-lg</code>, wide-tier
		enhancements add at <code>bp-xl</code>. Capability queries (<code>prefers-reduced-motion</code>,
		<code>hover</code>, <code>resolution</code>) are NOT breakpoints and stay free.
		Currently <b>0 declared bp-exceptions</b> in DS CSS — the lint reports every new one.</div>
	<script>
	(function () {
		var bar = document.querySelector('[data-test="bp-bar"]');
		if (!bar) { return; }
		var buckets = JSON.parse(bar.getAttribute('data-buckets'));
		var marker = bar.querySelector('[data-bp-marker]');
		var wEl = document.querySelector('[data-bp-width]');
		var bEl = document.querySelector('[data-bp-bucket]');
		function update() {
			var w = window.innerWidth;
			var active = buckets[0];
			buckets.forEach(function (b) { if (w >= b.from) { active = b; } });
			wEl.textContent = w;
			bEl.textContent = active.name + (active.cut ? ' (' + active.cut + ')' : '');
			var pct = Math.min(100, 100 * w / <?php echo (int) $scale; ?>);
			marker.style.display = 'block';
			marker.style.left = pct + '%';
		}
		window.addEventListener('resize', update);
		update();
	}());
	</script>
	<?php
}

function aifds_sg_item_radius_shadows() {
	?>
	<h2 class="sg-section-h">Border widths — the stroke scale</h2>
	<div class="sg-note">Line widths: <code>--stroke-1/2/3/4/6</code>. <code>--stroke-4</code> is the signifier bar
		(perex, blockquote, info box); <code>--flow-indent</code> subtracts <code>--stroke-4</code> so the text still
		lands on the shared inset — change the stroke and the indent follows. (Glyph strokes like the checkbox tick and
		the 14px bullet stay off this scale — they're drawn marks, not borders.)</div>
	<div style="display:flex; flex-direction:column; gap:12px; max-width:420px; margin:16px 0;">
		<?php foreach ( array( '1' => '1px', '1_5' => '1.5px — icon / thin-line weight (Apple)', '2' => '2px', '3' => '3px', '4' => '4px — the signifier bar', '6' => '6px' ) as $slug => $label ) : ?>
		<div style="border-left: var(--stroke-<?php echo esc_attr( $slug ); ?>) solid var(--brand); padding:10px 16px; background: var(--bg-alt); font-family: var(--font-mono); font-size:12px;">
			--stroke-<?php echo esc_html( $slug ); ?> · <?php echo esc_html( $label ); ?>
		</div>
		<?php endforeach; ?>
	</div>
	<h2 class="sg-section-h">Border styles — solid vs dashed</h2>
	<div class="sg-note">The border STYLE is a token too, so <code>dashed</code> is never hand-typed in a component:
		<code>--stroke-style-solid</code> is the default continuous rule (fields, chips, menus);
		<code>--stroke-style-dashed</code> is the drop / placeholder affordance — <b>defined once here and reused by
		the File upload dropzone</b>. A border reads three tokens: <code>var(--stroke-2) var(--stroke-style-dashed)
		var(--field-border)</code> = width · style · color.</div>
	<div style="display:flex; gap:16px; flex-wrap:wrap; margin:16px 0;">
		<?php foreach ( array( 'solid' => 'the default rule', 'dashed' => 'the drop / placeholder affordance' ) as $style => $label ) : ?>
		<div style="border: var(--stroke-2) var(--stroke-style-<?php echo esc_attr( $style ); ?>) var(--field-border-strong); background: var(--field-bg); padding:20px 24px; font-family: var(--font-mono); font-size:12px; color: var(--text-secondary);">
			--stroke-style-<?php echo esc_html( $style ); ?><br><span style="color: var(--text-tertiary);"><?php echo esc_html( $label ); ?></span>
		</div>
		<?php endforeach; ?>
	</div>
	<h2 class="sg-section-h">Border radius</h2>
	<div class="sg-cards">
		<div class="sg-radius-card" style="border-radius: var(--radius-full); border:2px solid var(--border-strong);">--radius-full</div>
	</div>
	<h2 class="sg-section-h">Shadows</h2>
	<div class="sg-cards">
		<?php foreach ( array( 'sm', 'md', 'lg', 'xl' ) as $s ) : ?>
		<div class="sg-shadow-card" style="box-shadow: var(--shadow-<?php echo esc_attr( $s ); ?>); border:none;">--shadow-<?php echo esc_html( $s ); ?></div>
		<?php endforeach; ?>
	</div>
	<?php
}

function aifds_sg_item_icon_system() {
	?>
	<h2 class="sg-section-h">Stepped stroke (operator 2026-07-04)</h2>
	<div class="sg-note"><b>Stepped stroke</b> (supersedes the constant-1.5 law): the stroke weight follows the
		rendered size — <b>&lt;16px → <code>--stroke-1</code></b> · <b>16–32px → <code>--stroke-1_5</code></b> ·
		<b>&gt;32px → <code>--stroke-3</code></b>. Small icons stay fine, large icons keep presence.
		<code>vector-effect: non-scaling-stroke</code> keeps the width in screen px (no viewBox math);
		<code>aifds_icon()</code> picks the step from its <code>size</code> arg
		(<code>.icon--stroked-fine/-heavy</code>). Shape and colored icons are exempt; <code>check-bold</code>
		stays a deliberate 3px.</div>
	<table class="sg-table">
		<tr><th>rendered size</th><th>visual stroke</th><th>preview (arrow-right, outline)</th></tr>
		<?php
		$steps = array( 14 => '1px (fine)', 16 => '1.5px', 20 => '1.5px', 24 => '1.5px', 32 => '1.5px', 48 => '3px (heavy)' );
		foreach ( $steps as $px => $stroke ) : ?>
		<tr>
			<td><?php echo (int) $px; ?>px</td>
			<td><?php echo esc_html( $stroke ); ?></td>
			<td><?php echo aifds_icon( 'arrow-right', array( 'size' => $px ) ); ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
	<?php
}

/* Decisions tab RETIRED (operator verdict 2026-07-03) — history lives in docs/DECISIONS.md. */

function aifds_sg_item_surfaces() {
	// family → the classes that carry its role tokens (mirrors build.mjs SURFACES)
	$families = array(
		'light-1' => 'section-light',
		'light-2' => 'content-section--secondary',
		'light-3' => 'content-section--tertiary',
		'dark-1'  => 'section-dark',
		'dark-2'  => 'section-dark content-section--dark-secondary',
		'dark-3'  => 'section-dark content-section--dark-tertiary',
		'brand'   => 'section-brand',
		'support' => 'surface-support',
	);
	?>
	<div class="sg-note"><b>Tier 2.5 — surfaces are token-remapping scopes</b> (Carbon layer pattern, harvested from the
		57-context census). The SAME markup below is repeated on every surface — buttons and links adapt because their
		component tokens resolve through surface roles. Watch <b>primary on the brand surface render DARK with no extra
		class</b>: that is the newsletter law, now structural. The <code>-inverted</code> classes and
		<code>.btn--newsletter-primary</code> are hereby on the deprecation queue.</div>
	<?php foreach ( $families as $family => $classes ) : ?>
	<h3 class="sg-h3"><?php echo esc_html( $family ); ?> <span style="font-weight:400; color:var(--text-tertiary); font-family:var(--font-mono); font-size:11px;">.<?php echo esc_html( str_replace( ' ', ' .', $classes ) ); ?></span></h3>
	<div class="<?php echo esc_attr( $classes ); ?>" data-surface="<?php echo esc_attr( $family ); ?>" style="background: var(--bg); color: var(--text); border:1px solid var(--border); border-radius:12px; padding:20px 24px;">
		<main style="display:contents;">
			<div style="display:flex; gap:12px; align-items:center; flex-wrap:wrap;">
				<a href="#" class="btn btn--sm btn--primary">Primary</a>
				<a href="#" class="btn btn--sm btn--secondary">Secondary</a>
				<a href="#" class="btn btn--sm btn--tertiary">Tertiary</a>
				<a href="#" class="badge badge--default">badge</a>
				<span>Text on this surface with <a href="#">an inline link</a>.</span>
			</div>
			<div class="content-container" style="padding:0; margin-top:16px;">
				<p class="text--perex" style="margin-bottom:0;">Perex voice — the border adapts to this surface.</p>
			</div>
		</main>
	</div>
	<?php endforeach; ?>
	<?php
}

/* ═══════════════════════ COMPONENT ITEMS ═════════════════════════════════ */

function aifds_sg_item_text_classes() {
	?>
	<h2 class="sg-section-h">Look ≠ tag (proof)</h2>
	<div class="sg-note">Each row renders its class on a DELIBERATELY mismatched tag — the look is tag-independent by design.</div>
	<div class="sg-type-row"><span class="sg-type-label">&lt;h2 class="heading-sm"&gt;</span><h2 class="sg-type-sample heading-sm">Second outline level, small look</h2></div>
	<div class="sg-type-row"><span class="sg-type-label">&lt;h3 class="heading-xl"&gt;</span><h3 class="sg-type-sample heading-xl">Third level, extra-large look</h3></div>
	<div class="sg-type-row"><span class="sg-type-label">&lt;p class="heading-lg"&gt;</span><p class="sg-type-sample heading-lg">A paragraph styled as heading-lg</p></div>
	<div class="sg-type-row"><span class="sg-type-label">&lt;span class="lead"&gt;</span><span class="sg-type-sample lead">Lead voice on a span — Space Grotesk Bold</span></div>
	<div class="sg-type-row"><span class="sg-type-label">&lt;p class="meta"&gt;</span><p class="sg-type-sample meta">12PX META ON A PARAGRAPH</p></div>
	<?php
}

function aifds_sg_item_buttons() {
	?>
	<div class="sg-note"><b>Model: look = hierarchy × size × surface.</b> Hierarchy = primary / secondary / tertiary.
		Surface (light / dark / brand) remaps tokens — it never creates new button kinds. Semantic CTAs (newsletter, nav)
		are color-only overrides composed ON the system. <code>--outline</code> deprecated (duplicate of secondary);
		<code>--primary-inverted</code> reclassified (→ newsletter CTA).</div>

	<h3 class="sg-h3">Styles — all at md</h3>
	<div class="sg-row">
		<a href="#" class="btn btn--md btn--primary">Primary</a>
		<a href="#" class="btn btn--md btn--secondary">Secondary</a>
		<a href="#" class="btn btn--md btn--tertiary">Tertiary</a>
	</div>

	<h3 class="sg-h3">Sizes — primary at lg / md / sm</h3>
	<div class="sg-row">
		<a href="#" class="btn btn--lg btn--primary">Large</a>
		<a href="#" class="btn btn--md btn--primary">Medium</a>
		<a href="#" class="btn btn--sm btn--primary">Small</a>
	</div>

	<h3 class="sg-h3">States — disabled (per style, md)</h3>
	<div class="sg-row">
		<button class="btn btn--md btn--primary" disabled>Primary</button>
		<button class="btn btn--md btn--secondary" disabled>Secondary</button>
		<button class="btn btn--md btn--tertiary" disabled>Tertiary</button>
	</div>

	<h3 class="sg-h3">With icon — icon BEFORE text (default) · icon-after variant allowed</h3>
	<div class="sg-row">
		<a href="#" class="btn btn--md btn--primary"><?php echo aifds_icon( 'calendar', array( 'size' => 20 ) ); ?>Icon before (default)</a>
		<a href="#" class="btn btn--md btn--secondary">Icon after<?php echo aifds_icon( 'arrow-right', array( 'size' => 20 ) ); ?></a>
	</div>

	<h3 class="sg-h3">Link buttons</h3>
	<div class="sg-row">
		<a href="#" class="btn btn--link"><?php echo aifds_icon( 'arrow-right', array( 'size' => 16 ) ); ?>Read more</a>
		<a href="#" class="btn btn--sm btn--link" data-test="link-sm"><?php echo aifds_icon( 'arrow-right', array( 'size' => 16 ) ); ?>Read more (sm stays 16px)</a>
		<a href="#" class="btn btn--link btn--destructive"><?php echo aifds_icon( 'trash-2', array( 'size' => 16 ) ); ?>Delete</a>
	</div>

	<h3 class="sg-h3">Smart button — ONE icon, the bulb fill reacts to the brand token (--icon-character-accent)</h3>
	<div class="sg-row">
		<span class="smart-btn"><?php echo aifds_icon( 'smart-button', array( 'size' => 32 ) ); ?><a href="#" class="btn btn--sm btn--primary">Smart button</a></span>
	</div>

	<div class="sg-note"><b>Chatbot bubble: not a DS component.</b> The production bubble (aigb-chat mu-plugin:
		56px disc, robot icon, hover scale 1.06 with stroke compensation) is theme territory and stays defined there.</div>

	<h3 class="sg-h3">Button group — layout primitive: normal buttons in one line, gap + alignment, nothing else</h3>
	<div class="button-group button-group--left">
		<a href="#" class="btn btn--md btn--primary">First</a>
		<a href="#" class="btn btn--md btn--secondary">Second</a>
		<a href="#" class="btn btn--md btn--tertiary">Third</a>
	</div>

	<h3 class="sg-h3">Background variations — SAME hierarchy classes, the surface supplies the look</h3>
	<div class="sg-dark section-dark">
		<div class="sg-row" style="margin:0;">
			<a href="#" class="btn btn--md btn--primary">Primary</a>
			<a href="#" class="btn btn--md btn--secondary">Secondary</a>
			<a href="#" class="btn btn--md btn--tertiary">Tertiary</a>
			<button class="btn btn--md btn--primary" disabled>Disabled</button>
			<span class="meta">dark surface</span>
		</div>
	</div>
	<div class="section-brand" style="border-radius:12px; padding:24px; margin-top:12px; background: var(--brand);">
		<div class="sg-row" style="margin:0;">
			<a href="#" class="btn btn--md btn--primary">Primary</a>
			<a href="#" class="btn btn--md btn--secondary">Secondary</a>
			<a href="#" class="btn btn--md btn--tertiary">Tertiary</a>
			<button class="btn btn--md btn--primary" disabled>Disabled</button>
			<span class="meta" style="color: var(--text);">brand surface — primary auto-darkens</span>
		</div>
	</div>
	<div class="surface-support" style="border-radius:12px; padding:24px; margin-top:12px; background: var(--bg);">
		<div class="sg-row" style="margin:0;">
			<a href="#" class="btn btn--md btn--primary">Primary</a>
			<a href="#" class="btn btn--md btn--secondary">Secondary</a>
			<a href="#" class="btn btn--md btn--tertiary">Tertiary</a>
			<button class="btn btn--md btn--primary" disabled>Disabled</button>
			<span class="meta" style="color: var(--text);">support surface</span>
		</div>
	</div>
	<div class="sg-note">The legacy <code>-inverted</code> classes are DEPRECATED — surfaces made them unnecessary.
		Pending its batch: AIF nav CTA (menu font-size at smaller geometry) — header batch.</div>
	<?php
}

function aifds_sg_item_input() {
	?>
	<div class="sg-note"><b>Input — the text field.</b> <code>.form-group</code> ▸ <code>.form-label-row</code> (label + <code>.form-mandatory</code>) ▸ <code>.form-control-wrapper</code> ▸ <code>.form-control</code> (+ optional <code>.form-icon</code>) ▸ <code>.form-helper-row</code>. Field colors are surface roles → adapts to any background. <b>Textarea is the multi-line VARIANT</b> (<code>.form-control-wrapper--textarea</code>), not a separate element. STATES: default · error · disabled. SCALE: LARGE is the token root, SMALL is the <code>.form-scale-small</code> scope — both shown below, and both relax on touch (see <b>Form composition → Mobile &amp; touch</b>).</div>

	<h3 class="sg-h3">Anatomy + states (LARGE)</h3>
	<div style="max-width:480px; display:flex; flex-direction:column; gap:24px;">
		<div class="form-group">
			<div class="form-label-row"><label class="form-label" for="sgf-1">E-mail</label><span class="form-mandatory">*</span></div>
			<div class="form-control-wrapper">
				<input type="email" id="sgf-1" class="form-control" placeholder="you@example.com">
				<?php echo aifds_icon( 'mail-check', array( 'size' => 24, 'class' => 'form-icon' ) ); ?>
			</div>
			<div class="form-helper-row"><span class="form-helper-text">Helper text under the field.</span></div>
		</div>
		<div class="form-group form-group--error">
			<div class="form-label-row"><label class="form-label" for="sgf-2">With error</label></div>
			<div class="form-control-wrapper"><input type="text" id="sgf-2" class="form-control" value="wrong value"></div>
			<div class="form-helper-row"><span class="form-helper-text">This field has an error.</span></div>
		</div>
		<div class="form-group">
			<div class="form-label-row"><label class="form-label" for="sgf-3">Disabled</label></div>
			<div class="form-control-wrapper"><input type="text" id="sgf-3" class="form-control" placeholder="Disabled" disabled></div>
		</div>
		<div class="form-group">
			<div class="form-label-row"><label class="form-label" for="sgf-4">Textarea (variant)</label></div>
			<div class="form-control-wrapper form-control-wrapper--textarea"><textarea id="sgf-4" class="form-control" placeholder="Longer text…"></textarea></div>
		</div>
	</div>

	<h3 class="sg-h3">Scale — text field AND textarea in LARGE (token root) &amp; SMALL (.form-scale-small), same markup</h3>
	<div style="display:flex; gap:48px; flex-wrap:wrap;">
		<?php foreach ( array( 'large' => '', 'small' => ' form-scale-small' ) as $scale => $sc ) : ?>
		<div class="<?php echo esc_attr( trim( $sc ) ); ?>" data-test="input-<?php echo esc_attr( $scale ); ?>" style="width:340px; display:flex; flex-direction:column; gap:16px;">
			<span class="meta"><?php echo 'large' === $scale ? 'LARGE — the token root' : 'SMALL — .form-scale-small scope'; ?></span>
			<div class="form-group">
				<div class="form-label-row"><label class="form-label">Text field</label></div>
				<div class="form-control-wrapper"><input type="text" class="form-control" placeholder="you@example.com"></div>
			</div>
			<div class="form-group">
				<div class="form-label-row"><label class="form-label">Textarea</label></div>
				<div class="form-control-wrapper form-control-wrapper--textarea"><textarea class="form-control" placeholder="Longer text…"></textarea></div>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php
}

function aifds_sg_item_select() {
	?>
	<div class="sg-note"><b>Select</b> — pick ONE from a KNOWN set, a REAL native <code>&lt;select&gt;</code> (Strategy B). The closed control is styled everywhere via the field wrapper + our chevron; desktop Chromium upgrades the open picker via <code>appearance: base-select</code>, iPhone&nbsp;Safari + Firefox render the native picker (the better touch UX). NO JS — native is the source of truth (keyboard, typeahead, form-submit, AT). The <code>&lt;select&gt;</code> floors at 16px so iOS never zoom-jumps on focus (a deliberate exception to SMALL = 14).</div>
	<h3 class="sg-h3">Native control, both scales</h3>
	<div style="display:flex; gap:48px; flex-wrap:wrap;">
		<div class="form-group" style="width:280px;">
			<div class="form-label-row"><label class="form-label" for="sg-select-lg">Large</label></div>
			<div class="form-control-wrapper form-select-wrapper">
				<select class="form-control form-select" id="sg-select-lg">
					<option>Option one</option>
					<option selected>Option two</option>
					<option>Option three</option>
				</select>
				<?php echo aifds_icon( 'chevron-down', array( 'size' => 20, 'class' => 'form-icon form-select-chevron' ) ); ?>
			</div>
		</div>
		<div class="form-scale-small" data-test="select-small" style="width:280px;">
			<div class="form-group">
				<div class="form-label-row"><label class="form-label" for="sg-select-sm">Small</label></div>
				<div class="form-control-wrapper form-select-wrapper">
					<select class="form-control form-select" id="sg-select-sm">
						<option>Option one</option>
						<option selected>Option two</option>
						<option>Option three</option>
					</select>
					<?php echo aifds_icon( 'chevron-down', array( 'size' => 20, 'class' => 'form-icon form-select-chevron' ) ); ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function aifds_sg_item_datepicker() {
	?>
	<div class="sg-note"><b>Datepicker</b> — a text field with a calendar icon. Ruling: the trigger field scales; the calendar keeps ONE density (floating overlay, touch targets). STATES on the grid: today (quiet outline) · selected (brand fill) · outside month (muted).</div>
	<h3 class="sg-h3">Collapsed trigger, both scales</h3>
	<div style="display:flex; gap:48px; flex-wrap:wrap;">
		<div class="form-group datepicker" style="width:280px;">
			<div class="form-label-row"><label class="form-label" for="sgdp-l">Large</label></div>
			<div class="form-control-wrapper">
				<input type="text" id="sgdp-l" class="form-control" placeholder="07/03/2026">
				<?php echo aifds_icon( 'calendar', array( 'size' => 20, 'class' => 'form-icon' ) ); ?>
			</div>
		</div>
		<div class="form-scale-small" data-test="datepicker-small" style="width:280px;">
			<div class="form-group datepicker">
				<div class="form-label-row"><label class="form-label" for="sgdp-s">Small</label></div>
				<div class="form-control-wrapper">
					<input type="text" id="sgdp-s" class="form-control" placeholder="07/03/2026">
					<?php echo aifds_icon( 'calendar', array( 'size' => 20, 'class' => 'form-icon' ) ); ?>
				</div>
			</div>
		</div>
	</div>
	<h3 class="sg-h3">Open calendar</h3>
	<div class="datepicker datepicker--open" style="max-width:320px; margin-bottom:320px;">
		<div class="form-control-wrapper">
			<input type="text" class="form-control" value="07/07/2026">
			<?php echo aifds_icon( 'calendar', array( 'size' => 20, 'class' => 'form-icon' ) ); ?>
		</div>
		<div class="datepicker-calendar">
			<div class="calendar-header"><span>&larr;</span><span>July 2026</span><span>&rarr;</span></div>
			<div class="calendar-grid">
				<?php foreach ( array( 'Mo','Tu','We','Th','Fr','Sa','Su' ) as $d ) : ?><div class="calendar-day-label"><?php echo esc_html( $d ); ?></div><?php endforeach; ?>
				<div class="calendar-day calendar-day--outside">29</div><div class="calendar-day calendar-day--outside">30</div>
				<?php for ( $i = 1; $i <= 5; $i++ ) : ?><div class="calendar-day"><?php echo (int) $i; ?></div><?php endfor; ?>
				<div class="calendar-day calendar-day--today">6</div>
				<div class="calendar-day calendar-day--selected">7</div>
				<?php for ( $i = 8; $i <= 12; $i++ ) : ?><div class="calendar-day"><?php echo (int) $i; ?></div><?php endfor; ?>
			</div>
		</div>
	</div>
	<?php
}

function aifds_sg_item_checkbox() {
	?>
	<div class="sg-note"><b>Checkbox</b> — INDEPENDENT options; zero, one or many on. A checkbox GROUP carries the SAME label + helper as a text input &mdash; incl. the <code>.form-mandatory</code> marker — <code>.form-group</code> ▸ <code>.form-label-row</code> ▸ <code>.selection-group</code> ▸ <code>.form-helper-row</code> — so a form reads consistently whatever the control. <code>role="group"</code> + <code>aria-labelledby</code> name it for assistive tech. STATES: checked / unchecked / disabled; chip scales 24→20px.</div>

	<h3 class="sg-h3">Group — labelled and helped exactly like an input</h3>
	<div class="form-group" role="group" aria-labelledby="cbg-lbl" style="max-width:380px;">
		<div class="form-label-row"><label class="form-label" id="cbg-lbl">Employment type</label><span class="form-mandatory">*</span></div>
		<div class="selection-group">
			<label class="selection-item selection-item--checkbox">
				<input type="checkbox" class="selection-input" checked>
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Full-time</span></div>
			</label>
			<label class="selection-item selection-item--checkbox">
				<input type="checkbox" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Contract</span></div>
			</label>
			<label class="selection-item selection-item--checkbox">
				<input type="checkbox" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Internship</span></div>
			</label>
		</div>
		<div class="form-helper-row"><span class="form-helper-text">Select all that apply.</span></div>
	</div>

	<h3 class="sg-h3">States — checked / unchecked / disabled, both scales</h3>
	<div style="display:flex; gap:64px; flex-wrap:wrap;">
		<?php foreach ( array( 'large' => '', 'small' => ' form-scale-small' ) as $scale => $sc ) : ?>
		<div class="<?php echo esc_attr( trim( $sc ) ); ?>" style="display:flex; flex-direction:column; gap:16px;">
			<span class="meta"><?php echo 'large' === $scale ? 'LARGE — the token root' : 'SMALL — .form-scale-small scope'; ?></span>
			<label class="selection-item selection-item--checkbox">
				<input type="checkbox" class="selection-input" checked>
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Checked</span></div>
			</label>
			<label class="selection-item selection-item--checkbox">
				<input type="checkbox" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Unchecked</span></div>
			</label>
			<label class="selection-item selection-item--checkbox">
				<input type="checkbox" class="selection-input" disabled>
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Disabled</span></div>
			</label>
		</div>
		<?php endforeach; ?>
	</div>
	<?php
}

function aifds_sg_item_radio() {
	?>
	<div class="sg-note"><b>Radio</b> — MUTUALLY EXCLUSIVE options; exactly one of a small, always-visible set. A radio GROUP carries the SAME label + helper as a text input &mdash; incl. the <code>.form-mandatory</code> marker (<code>.form-group</code> ▸ <code>.form-label-row</code> ▸ <code>.selection-group</code> ▸ <code>.form-helper-row</code>); <code>role="radiogroup"</code> + <code>aria-labelledby</code> name it. Same chip as Checkbox, round, with a brand dot. STATES: selected / unselected / disabled.</div>

	<h3 class="sg-h3">Group — labelled and helped exactly like an input</h3>
	<div class="form-group" role="radiogroup" aria-labelledby="rbg-lbl" style="max-width:380px;">
		<div class="form-label-row"><label class="form-label" id="rbg-lbl">Seniority</label><span class="form-mandatory">*</span></div>
		<div class="selection-group">
			<label class="selection-item selection-item--radio">
				<input type="radio" name="rb-group" class="selection-input" checked>
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Any</span></div>
			</label>
			<label class="selection-item selection-item--radio">
				<input type="radio" name="rb-group" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Senior</span></div>
			</label>
			<label class="selection-item selection-item--radio">
				<input type="radio" name="rb-group" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Lead</span></div>
			</label>
		</div>
		<div class="form-helper-row"><span class="form-helper-text">Pick one.</span></div>
	</div>

	<h3 class="sg-h3">States — selected / unselected / disabled, both scales</h3>
	<div style="display:flex; gap:64px; flex-wrap:wrap;">
		<?php foreach ( array( 'large' => '', 'small' => ' form-scale-small' ) as $scale => $sc ) : ?>
		<div class="<?php echo esc_attr( trim( $sc ) ); ?>" style="display:flex; flex-direction:column; gap:16px;">
			<span class="meta"><?php echo 'large' === $scale ? 'LARGE — the token root' : 'SMALL — .form-scale-small scope'; ?></span>
			<label class="selection-item selection-item--radio">
				<input type="radio" name="sgr-<?php echo esc_attr( $scale ); ?>" class="selection-input" checked>
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Selected</span></div>
			</label>
			<label class="selection-item selection-item--radio">
				<input type="radio" name="sgr-<?php echo esc_attr( $scale ); ?>" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Unselected</span></div>
			</label>
			<label class="selection-item selection-item--radio">
				<input type="radio" name="sgr-dis-<?php echo esc_attr( $scale ); ?>" class="selection-input" disabled>
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Disabled</span></div>
			</label>
		</div>
		<?php endforeach; ?>
	</div>
	<?php
}

function aifds_sg_item_consent() {
	?>
	<div class="sg-note"><b>Consent (GDPR)</b> — a checkbox underneath, but its own element: mandatory legal opt-in, the SELECTION voice one size quieter (<code>.selection-item--consent</code>). Never pre-checked. STATES: default · error (mandatory, left unticked on submit) — the error <b>inherits the input error state</b>: <code>.selection-item--error</code> paints the chip border <code>--status-error</code>, exactly the rule <code>.form-group--error</code> uses on a field. Shown at both scales.</div>
	<div style="display:flex; gap:64px; flex-wrap:wrap;">
		<?php foreach ( array( 'large' => '', 'small' => ' form-scale-small' ) as $scale => $sc ) : ?>
		<div class="<?php echo esc_attr( trim( $sc ) ); ?>" style="display:flex; flex-direction:column; gap:24px; max-width:460px;">
			<span class="meta"><?php echo 'large' === $scale ? 'LARGE — the token root' : 'SMALL — .form-scale-small scope'; ?></span>
			<label class="selection-item selection-item--checkbox selection-item--consent">
				<input type="checkbox" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">I agree to the <a href="#">processing of personal data</a> and the <a href="#">terms of service</a>.</span></div>
			</label>
			<label class="selection-item selection-item--checkbox selection-item--consent selection-item--error">
				<input type="checkbox" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content">
					<span class="selection-label">I agree to the <a href="#">processing of personal data</a>.</span>
					<span class="consent-note">Required — please agree to continue.</span>
				</div>
			</label>
		</div>
		<?php endforeach; ?>
	</div>
	<?php
}

function aifds_sg_item_segmented() {
	?>
	<div class="sg-note"><b>Segmented control</b> — HARVESTED from the author-publish media toggle (<code>.aif-form__toggle</code>). A CONJOINED BUTTON GROUP: equal-width segments whose 2px borders overlap so adjacent edges read as one shared rule; exactly ONE segment active, control-accent filled, lifted over its neighbours. Semantically a radio group. Origin instance is the <b>None / Podcast / Video</b> media switch — each segment reveals its own panel (that disclosure is a composition fact; see <b>Form composition</b>).</div>
	<h3 class="sg-h3">The real None / Podcast / Video switch — both scales</h3>
	<div style="display:flex; gap:48px; flex-wrap:wrap; align-items:flex-start;">
		<div style="display:flex; flex-direction:column; gap:8px; width:360px;">
			<span class="meta">LARGE — the token root</span>
			<div class="segmented" role="tablist" aria-label="Media type">
				<button type="button" class="segmented-option segmented-option--active">None</button>
				<button type="button" class="segmented-option">Podcast</button>
				<button type="button" class="segmented-option">Video</button>
			</div>
		</div>
		<div class="form-scale-small" style="display:flex; flex-direction:column; gap:8px; width:320px;">
			<span class="meta">SMALL — .form-scale-small scope</span>
			<div class="segmented" role="tablist" aria-label="Media type small">
				<button type="button" class="segmented-option">None</button>
				<button type="button" class="segmented-option segmented-option--active">Podcast</button>
				<button type="button" class="segmented-option">Video</button>
			</div>
		</div>
	</div>
	<h3 class="sg-h3">Two segments + a disabled segment</h3>
	<div style="width:300px;">
		<div class="segmented">
			<button type="button" class="segmented-option segmented-option--active">A</button>
			<button type="button" class="segmented-option">B</button>
			<button type="button" class="segmented-option" disabled>Disabled</button>
		</div>
	</div>
	<?php
}

function aifds_sg_item_file_upload() {
	?>
	<div class="sg-note"><b>File upload (dropzone)</b> — HARVESTED from <code>.aif-publish__image-dropzone</code>, which served BOTH the image and audio inputs on the write-article page. The native <code>&lt;input type=file&gt;</code> is hidden; the dashed zone IS the label. Hover and drag-over (<code>.is-dragover</code>, toggled by the theme JS) share ONE accented state. STATES: rest · hover / drag-over · filled.</div>
	<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:24px; max-width:820px;">
		<div style="display:flex; flex-direction:column; gap:8px;">
			<span class="meta">REST</span>
			<label class="dropzone">
				<input type="file" class="dropzone-input" accept="image/jpeg,image/png,image/webp">
				<span class="dropzone-prompt">Drag &amp; drop an image here or click to browse</span>
				<span class="dropzone-formats">JPG, PNG or WEBP · max 5 MB</span>
			</label>
		</div>
		<div style="display:flex; flex-direction:column; gap:8px;">
			<span class="meta">DRAG-OVER (.is-dragover)</span>
			<div class="dropzone is-dragover">
				<span class="dropzone-prompt">Drop to upload</span>
				<span class="dropzone-formats">MP3, M4A, OGG or WAV · max 50 MB</span>
			</div>
		</div>
		<div style="display:flex; flex-direction:column; gap:8px;">
			<span class="meta">FILLED</span>
			<div class="dropzone">
				<div class="dropzone-preview">
					<span class="dropzone-filename">episode-04-final-mix.mp3</span>
					<button type="button" class="dropzone-remove" aria-label="Remove file"><?php echo aifds_icon( 'close', array( 'size' => 16 ) ); ?></button>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function aifds_sg_item_form_composition() {
	?>
	<div class="sg-note"><b>THE MODEL — one field system, three axes.</b> <b>ELEMENT</b>: each form control is its own tab. <b>SCALE</b>: LARGE is the token root (<code>--field-font-size/-pad-y/-pad-x, --selection-size, --selection-label-size</code>); SMALL is the <code>.form-scale-small</code> scope that remaps them — one drawing, sizes only, never family/color. Small relaxes back to large on a narrow viewport OR any touch device (see <b>Mobile &amp; touch</b> below). <b>SURFACE</b>: field colors are surface roles, so every element adapts to any background. States (hover / focus / error / disabled / checked) ride on top of all three axes. Third-party form engines (FluentForms etc.) are NOT DS elements — at adoption each site maps the engine's selectors onto these tokens and picks a scale by scope.</div>

	<h3 class="sg-h3">Form status banner — form-level orientation, complements inline errors</h3>
	<div class="sg-note">A FORM-scoped sibling of <code>info-box</code>: same color-mix tint + accent-left, but with a leading icon, a live-region role (<code>alert</code>/<code>status</code>), and a jump-link list. On submit-failure show the <code>--error</code> banner (<code>role="alert"</code>, focus moved to it) AND keep the inline field errors — the banner ORIENTS + links, the inline text REPAIRS. <code>info</code> reuses <code>--brand</code> (there is no <code>--status-info</code> token). 0 new tokens.</div>
	<div style="display:flex; flex-direction:column; gap:16px; max-width:560px; margin-bottom:32px;" data-test="form-banner">
		<div class="form-banner form-banner--error" role="alert" tabindex="-1">
			<?php echo aifds_icon( 'circle-alert', array( 'size' => 16, 'class' => 'form-banner__icon' ) ); ?>
			<div class="form-banner__content">
				<p class="form-banner__title">There is a problem</p>
				<ul class="form-banner__list">
					<li><a href="#email" class="form-banner__link">Enter a valid email address</a></li>
					<li><a href="#password" class="form-banner__link">Password must be 8 characters or more</a></li>
				</ul>
			</div>
		</div>
		<div class="form-banner form-banner--success" role="status">
			<?php echo aifds_icon( 'circle-check', array( 'size' => 16, 'class' => 'form-banner__icon' ) ); ?>
			<div class="form-banner__content"><p class="form-banner__title">Your changes have been saved</p></div>
		</div>
		<div class="form-banner form-banner--info" role="status">
			<?php echo aifds_icon( 'info', array( 'size' => 16, 'class' => 'form-banner__icon' ) ); ?>
			<div class="form-banner__content"><p class="form-banner__title">Heads up &mdash; your session expires in 5 minutes</p></div>
		</div>
	</div>

	<h3 class="sg-h3">Mobile &amp; touch — the field scale relaxes for fingers</h3>
	<div class="sg-note">The ONE place the touch behaviour is defined. The <code>.form-scale-small</code> scope is a
		desktop-pointer compactness — on touch it would be too small to tap. So every remapped token relaxes back to the
		LARGE (comfortable) values under <b>either</b> condition:
		<ul style="margin:8px 0 0; padding-left:20px;">
			<li><code>@media (max-width: 767px)</code> — a phone-narrow viewport, <b>or</b></li>
			<li><code>@media (pointer: coarse)</code> — <b>any</b> touch device (tablet, touch-laptop, kiosk), regardless of width.</li>
		</ul>
		Result: a small field is ~36px for mouse users on desktop, but grows to the ~46px comfortable touch target the moment
		a coarse pointer is present. The icon rides <code>--field-font-size</code>, so it scales up too. LARGE fields are already
		touch-sized, so only SMALL needs the relax. Rule lives in <code>assets/css/components.css</code> (the
		<code>.form-scale-small</code> block + its media query); this is its human description.</div>

	<h3 class="sg-h3">SCALE axis — every element in LARGE (token root) and SMALL (scope), SAME markup</h3>
	<div style="display:flex; gap:48px; flex-wrap:wrap;">
		<?php foreach ( array( 'large' => '', 'small' => ' form-scale-small' ) as $scale => $scale_class ) : ?>
		<div class="<?php echo esc_attr( trim( 'form-stack sg-scale-col' . $scale_class ) ); ?>" data-test="scale-<?php echo esc_attr( $scale ); ?>" style="width:320px;">
			<span class="meta"><?php echo esc_html( strtoupper( $scale ) ); ?><?php echo 'large' === $scale ? ' — the token root' : ' — .form-scale-small scope remap'; ?></span>
			<div class="form-group">
				<div class="form-label-row"><label class="form-label" for="sgsc-f-<?php echo esc_attr( $scale ); ?>">Text field</label></div>
				<div class="form-control-wrapper"><input type="text" id="sgsc-f-<?php echo esc_attr( $scale ); ?>" class="form-control" placeholder="you@example.com"></div>
			</div>
			<div class="form-group">
				<div class="form-label-row"><label class="form-label">Select (closed)</label></div>
				<div class="form-control-wrapper form-select-wrapper">
					<span class="form-control" style="display:flex; align-items:center;">Option two</span>
					<?php echo aifds_icon( 'chevron-down', array( 'size' => 20, 'class' => 'form-icon' ) ); ?>
				</div>
			</div>
			<label class="selection-item selection-item--checkbox">
				<input type="checkbox" class="selection-input" checked>
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Checkbox</span></div>
			</label>
			<label class="selection-item selection-item--radio">
				<input type="radio" name="sgsc-r-<?php echo esc_attr( $scale ); ?>" class="selection-input" checked>
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Radio</span></div>
			</label>
			<label class="selection-item selection-item--checkbox selection-item--consent">
				<input type="checkbox" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">I agree to the <a href="#">processing of personal data</a> — the ONE consent variant, both scales.</span></div>
			</label>
			<div class="segmented">
				<button type="button" class="segmented-option segmented-option--active">A</button>
				<button type="button" class="segmented-option">B</button>
				<button type="button" class="segmented-option">C</button>
			</div>
		</div>
		<?php endforeach; ?>
	</div>

	<h3 class="sg-h3">SURFACE axis — same elements on a DARK surface (field roles adapt; composes with scale)</h3>
	<div class="sg-dark section-dark" data-test="dark-selection">
		<div class="form-stack" style="max-width:480px;">
			<label class="selection-item selection-item--checkbox">
				<input type="checkbox" class="selection-input" checked>
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Checked on dark</span></div>
			</label>
			<label class="selection-item selection-item--radio">
				<input type="radio" name="sgrd" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Unchecked radio on dark — field tokens adapt the chip</span></div>
			</label>
			<div class="form-group" data-test="dark-field">
				<div class="form-label-row"><label class="form-label" for="sgf-5">Large field on dark</label></div>
				<div class="form-control-wrapper"><input type="text" id="sgf-5" class="form-control" placeholder="Field on a dark surface"></div>
			</div>
			<div class="form-scale-small" data-test="dark-field-small">
				<div class="form-group">
					<div class="form-label-row"><label class="form-label" for="sgf-6">Small field on dark — scale × surface compose</label></div>
					<div class="form-control-wrapper"><input type="text" id="sgf-6" class="form-control" placeholder="Both axes at once"></div>
				</div>
			</div>
		</div>
	</div>

	<h3 class="sg-h3">SURFACE axis — selection controls on a BRAND surface (the AIF job-board filter)</h3>
	<div class="sg-note" style="margin-top:0;">Job-board filters sit on the brand hero. The field chip stays white with a black rule (field tokens transform); the CHECKED accent is the <code>--control-accent</code> role — brand on light/dark, <b>black + paper tick on brand</b> so it never disappears into the background. Only color transforms — the markup is identical to every other surface. Shown SMALL (filter density).</div>
	<div class="section-brand form-scale-small" data-test="brand-selection" style="border-radius:12px; padding:24px; background: var(--brand); display:flex; gap:48px; flex-wrap:wrap; max-width:760px;">
		<div style="display:flex; flex-direction:column; gap:14px; min-width:160px;">
			<label class="form-label" style="color: var(--text);">Employment</label>
			<label class="selection-item selection-item--checkbox">
				<input type="checkbox" class="selection-input" checked>
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Full-time</span></div>
			</label>
			<label class="selection-item selection-item--checkbox">
				<input type="checkbox" class="selection-input" checked>
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Contract</span></div>
			</label>
			<label class="selection-item selection-item--checkbox">
				<input type="checkbox" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Internship</span></div>
			</label>
		</div>
		<div style="display:flex; flex-direction:column; gap:14px; min-width:160px;">
			<label class="form-label" style="color: var(--text);">Seniority</label>
			<label class="selection-item selection-item--radio">
				<input type="radio" name="jb-sen" class="selection-input" checked>
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Any</span></div>
			</label>
			<label class="selection-item selection-item--radio">
				<input type="radio" name="jb-sen" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Senior</span></div>
			</label>
			<label class="selection-item selection-item--radio">
				<input type="radio" name="jb-sen" class="selection-input">
				<div class="selection-control"></div>
				<div class="selection-content"><span class="selection-label">Lead</span></div>
			</label>
		</div>
		<div style="display:flex; flex-direction:column; gap:14px;">
			<label class="form-label" style="color: var(--text);">Mode</label>
			<div class="segmented" style="max-width:260px;">
				<button type="button" class="segmented-option segmented-option--active">Remote</button>
				<button type="button" class="segmented-option">Hybrid</button>
				<button type="button" class="segmented-option">On-site</button>
			</div>
		</div>
	</div>

	<h3 class="sg-h3">Input pair — layout primitive: ANY input + ANY button (resize below 600px: stacks, border restores)</h3>
	<div class="input-pair" style="max-width:480px;" data-test="pair-light">
		<div class="form-control-wrapper" style="min-height:60px; padding:0 var(--spacing-16);">
			<input type="email" class="form-control" placeholder="Your e-mail">
		</div>
		<button type="submit" class="btn btn--lg btn--primary"><?php echo aifds_icon( 'send', array( 'size' => 18 ) ); ?><span>Subscribe</span></button>
	</div>

	<h3 class="sg-h3">Disclosure — a segmented segment reveals its panel (the real media switch)</h3>
	<div class="sg-note" style="margin-top:0;">The None / Podcast / Video toggle is the segmented control; picking a segment reveals its matching panel (audio → dropzone, video → URL input). One control drives which composed block shows.</div>
	<div style="max-width:420px; display:flex; flex-direction:column; gap:16px;">
		<div class="segmented" role="tablist" aria-label="Media type">
			<button type="button" class="segmented-option">None</button>
			<button type="button" class="segmented-option segmented-option--active">Podcast</button>
			<button type="button" class="segmented-option">Video</button>
		</div>
		<label class="dropzone">
			<span class="dropzone-prompt">Drag &amp; drop an audio file here or click to browse</span>
			<span class="dropzone-formats">MP3, M4A, OGG or WAV · max 50 MB</span>
		</label>
	</div>

	<h3 class="sg-h3">Newsletter capture — the input-pair in production (harvested contexts, PLAIN system buttons)</h3>
	<?php aifds_sg_item_newsletter(); ?>
	<?php
}

function aifds_sg_item_newsletter() {
	?>
	<div class="sg-note">ONE component, ZERO newsletter-specific styles (operator law): plain hierarchy buttons on
		their surfaces — AIF = <code>btn--lg btn--primary</code> on BRAND (auto-dark) · AIG = <code>btn--md btn--tertiary</code>
		on DARK. Same structure, same arrow icon, same 2px border, send icon in both. Rendered from the REAL
		production structure; focus the inputs — states are real. (GM exceptions: footer border 1px→2px; AIG footer
		button subtle-filled → tertiary-on-dark.)</div>
	<div class="section-brand" style="border-radius:12px; padding:24px; margin-top:16px; background: var(--brand);">
		<p class="meta" style="margin:0 0 8px; color: var(--text);">Brand-surface variant — .hero-aif__form context · 60px conjoined</p>
		<div class="hero-aif__form" style="max-width:480px;">
			<form class="aif-ecomail-form mc4wp-form" novalidate onsubmit="return false;">
				<div class="mc4wp-form-fields">
					<div class="form-control-wrapper">
						<?php echo aifds_icon( 'arrow-right', array( 'size' => 18, 'class' => 'form-control-icon' ) ); ?>
						<input type="email" name="email" class="form-control" placeholder="Your e-mail" required autocomplete="email">
					</div>
					<button type="submit" class="btn btn--lg btn--primary"><?php echo aifds_icon( 'send', array( 'size' => 18 ) ); ?><span>Subscribe</span></button>
				</div>
						<p class="mc4wp-consent-note">By clicking Subscribe you agree to the <a href="#">processing of personal data</a>.</p>
		</form>
		</div>
	</div>
	<div class="sg-dark section-dark" style="margin-top:16px;">
		<p class="meta" style="margin:0 0 8px;">Dark-surface variant — .aif-ecomail-form--footer-dark context · 52px conjoined</p>
		<form class="aif-ecomail-form aif-ecomail-form--footer-dark" novalidate onsubmit="return false;" style="max-width:480px;">
			<div class="mc4wp-form-fields">
				<div class="form-control-wrapper">
					<?php echo aifds_icon( 'arrow-right', array( 'size' => 18, 'class' => 'form-control-icon' ) ); ?>
					<input type="email" name="email" class="form-control" placeholder="Your e-mail" required autocomplete="email">
				</div>
				<button type="submit" class="btn btn--md btn--tertiary"><?php echo aifds_icon( 'send', array( 'size' => 18 ) ); ?><span>Subscribe</span></button>
			</div>
			<p class="mc4wp-consent-note">By clicking Subscribe you agree to the <a href="#">processing of personal data</a>.</p>
		</form>
	</div>
	<?php
}

function aifds_sg_item_badges() {
	?>
	<div class="sg-note"><b>Model: a badge is 4 independent axes</b> — color (basic grey | colored category accent) ·
		icon (with | without) · behavior (clickable &lt;a&gt; with hover | static &lt;span&gt;) · surface (light | brand/colored).
		Harvested variant classes map onto the axes; axis-based classes on the rationalization queue.</div>

	<h3 class="sg-h3">Axis 1 — color: basic vs colored (category accents)</h3>
	<div class="sg-row">
		<a href="#" class="badge badge--default">basic (grey)</a>
		<a href="#" class="badge badge--editorial">editorial</a>
		<a href="#" class="badge badge--weekly-summary">weekly-summary</a>
		<a href="#" class="badge badge--signal">signal</a>
		<a href="#" class="badge badge--location">location</a>
		<a href="#" class="badge badge--promo">promo</a>
	</div>

	<h3 class="sg-h3">Axis 2 — icon: without vs with (__icon slot)</h3>
	<div class="sg-row">
		<a href="#" class="badge badge--default">no icon</a>
		<a href="#" class="badge badge--default"><span class="badge__icon"><?php echo aifds_icon( 'map-pin', array( 'size' => 12 ) ); ?></span>with icon</a>
		<a href="#" class="badge badge--location"><span class="badge__icon"><?php echo aifds_icon( 'pin', array( 'size' => 12 ) ); ?></span>colored + icon</a>
	</div>

	<h3 class="sg-h3">Axis 3 — behavior: clickable (hover glow) vs static (no hover)</h3>
	<div class="sg-row">
		<a href="#" class="badge badge--default">clickable &lt;a&gt; — hover me</a>
		<span class="badge badge--default">static &lt;span&gt; (source badge)</span>
		<a href="#" class="badge badge--promo">promo — colored but static by design</a>
	</div>

	<h3 class="sg-h3">Axis 4 — surface: light vs brand background (article hero, REAL harvested context)</h3>
	<div class="sg-row">
		<a href="#" class="badge badge--default">on light</a>
		<a href="#" class="badge badge--editorial">editorial on light</a>
	</div>
	<div class="section-brand" style="border-radius:12px; padding:24px; margin-top:8px; background: var(--brand);">
		<div class="sg-row article-hero__badges" style="margin:0;">
			<a href="#" class="badge badge--default">default → dark card, light text</a>
			<a href="#" class="badge badge--editorial">editorial → dark card, GREEN text</a>
			<a href="#" class="badge badge--weekly-summary">weekly-summary → same</a>
			<span class="badge badge--default">static source badge</span>
		</div>
	</div>
	<div class="sg-note">Harvested byte-identical from both themes (.article-hero__badges). Legacy variants
		(--inverse, --special, --special-inverse) exist in CSS but have no verified surface context —
		rationalization queue, not showcased as placements.</div>
	<?php
}

function aifds_sg_item_info_box() {
	$variants = array(
		'info'    => array( 'label' => 'Info',    'note' => '--brand (primary color)' ),
		'success' => array( 'label' => 'Success', 'note' => '--status-success' ),
		'warning' => array( 'label' => 'Warning', 'note' => '--status-warning' ),
		'error'   => array( 'label' => 'Error',   'note' => '--status-error' ),
		'neutral' => array( 'label' => 'Neutral', 'note' => '--border-strong (greyscale)' ),
	);
	?>
	<h2 class="sg-section-h">Five colour variants (default 16px, no icon, square corners)</h2>
	<div class="sg-note">The DS skeleton note box, abstracted: a tinted background + a thick 4px accent border,
		nothing else. The tint is derived from the accent via <code>color-mix(… 22%, --bg)</code> — <b>zero new
		tokens</b>, surface-aware. Toggle the brand: <b>info</b> follows <code>--brand</code> (yellow AIG / blue AIF);
		status variants hold their hue; <b>neutral</b> is true greyscale.</div>
	<div style="display:flex; flex-direction:column; gap:16px; max-width:640px;">
		<?php foreach ( $variants as $key => $v ) : ?>
		<div class="info-box info-box--<?php echo esc_attr( $key ); ?>">
			<strong><?php echo esc_html( $v['label'] ); ?></strong> — accent reads <code><?php echo esc_html( $v['note'] ); ?></code>. Body copy at the default 16px reading size.
		</div>
		<?php endforeach; ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">Three sizes (independent of colour)</h2>
	<div style="display:flex; flex-direction:column; gap:16px; max-width:640px;">
		<div class="info-box info-box--info info-box--small">Small — <code>--small</code>, 14px. Compact notes and captions.</div>
		<div class="info-box info-box--info">Default — 16px. The everyday reading size.</div>
		<div class="info-box info-box--info info-box--article">Article — <code>--article</code>, 18px. Matches article body copy.</div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">On a dark surface — status colours invert automatically</h2>
	<div class="sg-note">Nothing special on the box: it reads <code>--status-*</code>, which the dark scopes now remap to
		the <b>bright</b> variants (<code>success/warning/error-bright</code>). Drop the same markup inside
		<code>.section-dark</code> and the accents, tint and text all re-resolve.</div>
	<div class="section-dark" style="padding:24px; border-radius:12px; display:flex; flex-direction:column; gap:16px; max-width:640px;">
		<div class="info-box info-box--info"><strong>Info</strong> — brand accent on dark.</div>
		<div class="info-box info-box--success"><strong>Success</strong> — success-bright on dark.</div>
		<div class="info-box info-box--warning"><strong>Warning</strong> — warning-bright on dark.</div>
		<div class="info-box info-box--error"><strong>Error</strong> — error-bright on dark.</div>
		<div class="info-box info-box--neutral"><strong>Neutral</strong> — greyscale on dark.</div>
	</div>
	<?php
}

function aifds_sg_item_data_tables() {
	// One sample table, rendered with different classes. Every specimen ships the
	// .table-scroll wrapper — the production discipline (AIF wraps every article
	// table), so no demo overflows the page on narrow viewports.
	$table = function ( $classes ) {
		ob_start(); ?>
		<div class="table-scroll">
		<table class="data-table <?php echo esc_attr( $classes ); ?>">
			<thead><tr><th>Cohort</th><th>Starts</th><th>Status</th><th class="cell--num">Price</th></tr></thead>
			<tbody>
				<tr><td>Spring — Praha</td><td>4 May</td><td class="cell--success">Open</td><td class="cell--num">12 000</td></tr>
				<tr><td>Summer — Brno</td><td>2 Jul</td><td class="cell--warning">Few left</td><td class="cell--num">12 000</td></tr>
				<tr><td>Autumn — online</td><td>1 Oct</td><td class="cell--error">Full</td><td class="cell--num">9 000</td></tr>
			</tbody>
		</table>
		</div>
		<?php return ob_get_clean();
	};
	?>
	<h2 class="sg-section-h">The skeleton — mono/uppercase header, reading-font cells, 1px full grid (default)</h2>
	<div class="sg-note">Harvest-confirmed across the article, newsletter and cohort tables: header = <code>--font-mono</code>
		uppercase bold; cells = reading font. <b>Conservative data-view</b> (operator ruling): everything reads through
		<b>borders + text + status</b> — all surface-aware roles, no fills (a fill needs <code>--raised</code>, which is
		black on the brand surface, so it would break). <b>1px grid on every cell, header included.</b>
		<b>0 new tokens</b> — <code>--stroke-1</code>/<code>--stroke-2</code> + <code>--border</code>, and
		<code>--status-*</code> for status cells.</div>
	<?php echo $table( '' ); ?>

	<h2 class="sg-section-h" style="margin-top:32px;">Size axis — condensed · standard (default) · large</h2>
	<div class="sg-note">condensed = the article density (14px cell / 12px head / 6·8 pad); standard sits between
		(16 / 12 / 12·16); large is roomy (18 / 14 / 16·24). Derived from the type + spacing scales, not hand-picked.</div>
	<h3 class="sg-h3">condensed <span style="font-weight:400;color:var(--text-tertiary);font-family:var(--font-mono);font-size:12px;">.data-table--condensed</span></h3>
	<?php echo $table( 'data-table--condensed' ); ?>
	<h3 class="sg-h3">standard (default)</h3>
	<?php echo $table( '' ); ?>
	<h3 class="sg-h3">large <span style="font-weight:400;color:var(--text-tertiary);font-family:var(--font-mono);font-size:12px;">.data-table--large</span></h3>
	<?php echo $table( 'data-table--large' ); ?>

	<h2 class="sg-section-h" style="margin-top:32px;">Grid axis — full grid (default) · --plain (rows only) · --banded (invisible grid)</h2>
	<div class="sg-note"><b>--banded</b> is the AIG course-detail (cohort) grammar: no continuous grid — each row is an
		outlined box with a gap between. All three are border-only (no fills), so they hold on any surface.</div>
	<h3 class="sg-h3">--plain <span style="font-weight:400;color:var(--text-tertiary);font-family:var(--font-mono);font-size:12px;">rows only</span></h3>
	<?php echo $table( 'data-table--plain' ); ?>
	<h3 class="sg-h3">--banded <span style="font-weight:400;color:var(--text-tertiary);font-family:var(--font-mono);font-size:12px;">invisible grid, separated rows</span></h3>
	<?php echo $table( 'data-table--banded' ); ?>

	<h2 class="sg-section-h" style="margin-top:32px;">Signifiers — a translucent brand FILL (no line transforms)</h2>
	<div class="sg-note"><b>Row</b> (<code>tr.is-emphasized</code>) and <b>column</b> (<code>th/td.is-emphasized</code>)
		= <code>--brand-tint</code>, a brand fill at 22% ALPHA (not mixed with <code>--bg</code>) so it holds on every
		surface incl. brand — same idea as the info box's "info". <b>Status cells</b>
		(<code>.cell--success/-warning/-error</code>) reuse the status roles (text colour only).</div>
	<div class="table-scroll">
	<table class="data-table">
		<thead><tr><th>Plan</th><th class="is-emphasized">AI Guild</th><th>Typical</th></tr></thead>
		<tbody>
			<tr><td>Price</td><td class="is-emphasized">Free forever</td><td>Freemium</td></tr>
			<tr class="is-emphasized"><td>Sources</td><td class="is-emphasized">Editorial</td><td>RSS</td></tr>
			<tr><td>Ads</td><td class="is-emphasized">None</td><td>Sponsored</td></tr>
		</tbody>
	</table>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">Wide tables — the <code>.table-scroll</code> wrapper (harvested from AIF production)</h2>
	<div class="sg-note">A wide table never overflows the page: it scrolls horizontally inside
		<code>.table-scroll</code> (<code>overflow-x:auto</code> + touch momentum — the exact production mechanism from
		the AIF theme; articles get it auto-wrapped by <code>js/components/table-scroll.js</code>, hand-authored tables
		ship the wrapper in markup). Scroll is the DEFAULT behavior for wide tables; a mobile stacking transform is a
		possible FUTURE special override (tracked), never the default.</div>
	<div class="table-scroll">
		<table class="data-table" style="min-width:900px;">
			<thead><tr><th>Cohort</th><th>Starts</th><th>Ends</th><th>Location</th><th>Language</th><th>Format</th><th>Status</th><th class="cell--num">Price</th><th class="cell--num">Seats</th></tr></thead>
			<tbody>
				<tr><td>Spring — Praha</td><td>4 May</td><td>20 Jun</td><td>Praha, Impact Hub</td><td>English</td><td>In person</td><td class="cell--success">Open</td><td class="cell--num">12 000</td><td class="cell--num">24</td></tr>
				<tr><td>Summer — Brno</td><td>2 Jul</td><td>15 Aug</td><td>Brno, Titanium</td><td>Czech</td><td>Hybrid</td><td class="cell--warning">Few left</td><td class="cell--num">12 000</td><td class="cell--num">18</td></tr>
				<tr><td>Autumn — online</td><td>1 Oct</td><td>12 Nov</td><td>Online</td><td>English</td><td>Remote</td><td class="cell--error">Full</td><td class="cell--num">9 000</td><td class="cell--num">40</td></tr>
			</tbody>
		</table>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">On surfaces — the tokens re-resolve (toggle brand too)</h2>
	<div class="section-dark" style="padding:24px; border-radius:12px; margin-bottom:16px;"><?php echo $table( 'data-table--condensed' ); ?></div>
	<div class="section-brand" style="padding:24px; border-radius:12px; background:var(--brand);"><?php echo $table( 'data-table--condensed' ); ?></div>
	<?php
}

function aifds_sg_item_record_list() {
	// One record renderer per consumer — reused on light + dark.
	$cohort_record = function ( $c ) {
		$mod = 'ok' === $c['s'] ? 'record__field--success' : ( 'full' === $c['s'] ? 'record__field--error' : 'record__field--warning' );
		$btn = 'full' === $c['s'] ? 'secondary' : 'primary';
		ob_start(); ?>
		<article class="record">
			<div class="record__fields">
				<div class="record__field record__field--strong" data-label="Date"><?php echo esc_html( $c['date'] ); ?></div>
				<div class="record__field" data-label="Location"><?php echo esc_html( $c['place'] ); ?></div>
				<div class="record__field record__field--strong record__field--nowrap" data-label="Price"><?php echo esc_html( $c['price'] ); ?></div>
				<div class="record__field" data-label="Language"><?php echo esc_html( $c['lang'] ); ?></div>
				<div class="record__field <?php echo esc_attr( $mod ); ?>" data-label="Capacity"><?php echo esc_html( $c['cap'] ); ?></div>
				<div class="record__field record__field--action"><a href="#" class="btn btn--sm btn--<?php echo esc_attr( $btn ); ?>"><?php echo esc_html( $c['cta'] ); ?></a></div>
			</div>
		</article>
		<?php return ob_get_clean();
	};
	$cohorts = array(
		array( 's' => 'ok',      'cap' => 'Filling fast', 'date' => '4 May - 20 Jun',  'place' => 'Prague', 'price' => '45,500 CZK', 'lang' => 'English', 'cta' => 'Reserve' ),
		array( 's' => 'warning', 'cap' => 'Last spots',   'date' => '17 Sep - 31 Oct', 'place' => 'Online', 'price' => '45,500 CZK', 'lang' => 'English', 'cta' => 'Reserve' ),
		array( 's' => 'full',    'cap' => 'Sold out',     'date' => '2 Nov - 15 Dec',  'place' => 'Brno',   'price' => '45,500 CZK', 'lang' => 'English', 'cta' => 'Request availability' ),
	);
	$cohort_cols = '1.5fr 1fr 0.9fr 0.8fr 1fr auto';

	$events = array(
		array( 'name' => 'AI Founders Meetup', 'date' => '12 Feb 2026, 18:00', 'venue' => 'Impact Hub, Prague', 'type' => 'Meetup', 'desc' => '<p>An evening of lightning talks and open networking for people building with AI in Czechia. <a href="#">See the agenda</a>.</p>' ),
		array( 'name' => 'Builders Night',     'date' => '9 Mar 2026, 19:00',  'venue' => 'Online',            'type' => 'Webinar', 'desc' => '<p>A hands-on remote session &mdash; bring what you are building and get live feedback from the community.</p>' ),
	);
	$event_cols = '1.3fr 1.6fr 1fr auto';
	?>
	<h2 class="sg-section-h">Record list - an ABSTRACT record-card list (N columns, any consumer)</h2>
	<div class="sg-note">A list of self-contained <code>&lt;article&gt;</code> records. The consumer owns the columns via
		<code>--record-columns</code> (a grid template; the <b>action is just one of the columns</b>). Fields are
		label+value pairs (label inside the card, above the value); a field can carry a status colour
		(<code>record__field--warning</code> etc., reusing <code>--status-*</code>). No baked variants. The
		progress/occupancy bar is a <b>separate component</b> (tracked), composed in when a record needs a meter.</div>

	<h3 class="sg-h3">Consumer A - course cohorts <span style="font-weight:400;color:var(--text-tertiary);font-family:var(--font-mono);font-size:12px;">6 columns: 4 fields + a status field + action</span></h3>
	<div class="record-list" style="--record-columns: <?php echo esc_attr( $cohort_cols ); ?>;">
		<?php foreach ( $cohorts as $c ) { echo $cohort_record( $c ); } ?>
	</div>

	<h3 class="sg-h3" style="margin-top:32px;">Consumer B - AIF events (mock) <span style="font-weight:400;color:var(--text-tertiary);font-family:var(--font-mono);font-size:12px;">4 columns, different fields + a title head - SAME component</span></h3>
	<div class="record-list" style="--record-columns: <?php echo esc_attr( $event_cols ); ?>;">
		<?php foreach ( $events as $e ) : ?>
		<article class="record">
			<div class="record__head">
					<div class="record__title"><?php echo esc_html( $e['name'] ); ?></div>
					<div class="record__description"><?php echo wp_kses_post( $e['desc'] ); ?></div>
				</div>
			<div class="record__fields">
				<div class="record__field record__field--strong" data-label="Date"><?php echo esc_html( $e['date'] ); ?></div>
				<div class="record__field" data-label="Venue"><?php echo esc_html( $e['venue'] ); ?></div>
				<div class="record__field" data-label="Type"><?php echo esc_html( $e['type'] ); ?></div>
				<div class="record__field record__field--action"><a href="#" class="btn btn--sm btn--primary">RSVP</a></div>
			</div>
		</article>
		<?php endforeach; ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">On dark - status fields invert</h2>
	<div class="section-dark" style="padding:24px; border-radius:12px;">
		<div class="record-list" style="--record-columns: <?php echo esc_attr( $cohort_cols ); ?>;">
			<?php foreach ( $cohorts as $c ) { echo $cohort_record( $c ); } ?>
		</div>
	</div>

	<div class="sg-note" style="margin-top:16px;">Mobile (&lt;768px): each record stacks and every field label moves inline (left of its value).</div>
	<?php
}

function aifds_sg_item_preview_card() {
	$photo = AIFDS_URL . 'assets/img/demo/article-1.jpg';

	// ONE semantic placeholder renderer — the COMPONENT, not content.
	$card = function ( $condensed = false, $slots = array() ) use ( $photo ) {
		$s = array_merge( array(
			'photo' => true, 'headline_link' => true, 'meta' => true,
			'badges' => true, 'skills' => false, 'text' => true, 'actions' => 1,
		), $slots );
		ob_start(); ?>
		<article class="preview-card<?php echo $condensed ? ' preview-card--condensed' : ''; ?>">
			<?php if ( $s['photo'] ) : ?>
			<a class="preview-card__photo" href="#"><img src="<?php echo esc_url( $photo ); ?>" alt="Photo slot"></a>
			<?php endif; ?>
			<div class="preview-card__content">
				<h3 class="preview-card__headline"><?php $ht = ! empty( $s['headline_long'] ) ? 'Headline slot with a deliberately long two-line title to stress the row' : ( $s['headline_link'] ? 'Headline slot — always present' : 'Headline slot — plain (not linked)' ); ?><?php if ( $s['headline_link'] ) : ?><a class="card-title-link" href="#"><?php echo esc_html( $ht ); ?></a><?php else : ?><?php echo esc_html( $ht ); ?><?php endif; ?></h3>
				<?php if ( $s['meta'] ) : ?>
				<div class="preview-card__meta"><?php
					$facts = array();
					for ( $i = 0; $i < max( 1, (int) $s['meta'] ); $i++ ) { $facts[] = 'Meta fact ' . chr( 65 + $i ); }
					echo implode( '<span class="preview-card__meta-separator">|</span>', $facts );
				?></div>
				<?php endif; ?>
				<?php if ( $s['badges'] ) : ?>
				<div class="preview-card__badges"><span class="badge badge--default">Badge</span><span class="badge badge--default">Badge</span></div>
				<?php endif; ?>
				<?php if ( $s['skills'] ) : ?>
				<div class="preview-card__skills"><?php echo aifds_icon( 'skills', array( 'size' => 16 ) ); ?> <span><a href="#">Skill link</a>, <a href="#">Skill link</a>, plain skill</span></div>
				<?php endif; ?>
				<?php if ( 'rich' === $s['text'] ) : ?>
				<div class="preview-card__text"><p>Text slot as a rich block — first paragraph.</p><p>Second paragraph (the signals exception).</p></div>
				<?php elseif ( 'long' === $s['text'] ) : ?>
				<p class="preview-card__text">Text slot with a much longer excerpt to stress vertical rhythm across a grid row — several clauses, a second sentence, and enough words that this card grows visibly taller than its neighbours.</p>
				<?php elseif ( $s['text'] ) : ?>
				<p class="preview-card__text">Text slot — the excerpt voice, one paragraph.</p>
				<?php endif; ?>
				<?php if ( $s['actions'] ) : ?>
				<div class="preview-card__actions">
					<a href="#" class="btn btn--sm btn--link"><?php echo aifds_icon( 'arrow-right', array( 'size' => 16 ) ); ?> Action 1</a>
					<?php if ( $s['actions'] > 2 ) : ?><button type="button" class="btn btn--sm btn--link btn--destructive"><?php echo aifds_icon( 'trash-2', array( 'size' => 16 ) ); ?> Destructive</button><?php endif; ?>
					<?php if ( $s['actions'] > 1 ) : ?><a href="#" class="btn btn--sm btn--link"><?php echo aifds_icon( 'source', array( 'size' => 16 ) ); ?> Action 2</a><?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
		</article>
		<?php return ob_get_clean();
	};
	?>
	<h2 class="sg-section-h">1 · Anatomy — the slots (semantic deconstruction)</h2>
	<div class="sg-note">ONE component. Only the headline is required; everything else composes. Semantics:
		the card is an <code>&lt;article&gt;</code>, the headline an <code>&lt;h3&gt;</code>, meta/badges/skills/actions
		are <code>&lt;div&gt;</code> rows, text a <code>&lt;p&gt;</code> (or a <code>&lt;div&gt;</code> of paragraphs
		for rich content).</div>
	<table class="sg-table">
		<tr><th>slot</th><th>element</th><th>required</th><th>contract</th></tr>
		<tr><td><code>__photo</code></td><td><code>&lt;a&gt;</code> (linked) / <code>&lt;div&gt;</code> (plain — possible, unused)</td><td>no</td><td>546/306 crop; linked = hover zoom 1.02; ABSENT → top hairline appears automatically</td></tr>
		<tr><td><code>__headline</code></td><td><code>&lt;h3&gt;</code> (+ <code>a.card-title-link</code> when linked)</td><td><b>YES</b></td><td>heading-md; <code>--condensed</code> → heading-sm; link = no rest underline, decent hover</td></tr>
		<tr><td><code>__meta</code></td><td><code>&lt;div&gt;</code></td><td>no</td><td>caption voice between hairlines; 1..n facts (A | B | C | …) — <code>__meta-separator</code> is content</td></tr>
		<tr><td><code>__badges</code></td><td><code>&lt;div&gt;</code> of DS badges</td><td>no</td><td>flex wrap, gap 12</td></tr>
		<tr><td><code>__skills</code></td><td><code>&lt;div&gt;</code></td><td>no</td><td>DS <code>skills</code> icon 16 + comma-separated names (links inherit + idiom underline)</td></tr>
		<tr><td><code>__text</code></td><td><code>&lt;p&gt;</code> / <code>&lt;div&gt;</code> of <code>&lt;p&gt;</code></td><td>no</td><td>description voice; ~30 words (articles) · ~20 (events) · rich block = the signals exception</td></tr>
		<tr><td><code>__actions</code></td><td><code>&lt;div&gt;</code> of <code>.btn--sm.btn--link</code></td><td>no</td><td>Action 1..n, bottom-pinned; <b>destructive is ALWAYS LAST</b> (enforced via flex order); icon grammar: <code>arrow-right</code> internal · <code>source</code> external · <code>edit</code> edit</td></tr>
	</table>
	<div style="max-width:560px; margin-top:16px;">
		<?php echo $card( false, array( 'skills' => true, 'actions' => 3, 'meta' => 4 ) ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">2 · Size axis — SAME content, normal vs condensed</h2>
	<div class="sg-note">One variable changes: the headline bundle (heading-md → heading-sm). Nothing else.</div>
	<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px;">
		<div><p class="sg-h3" style="margin:0 0 8px;">default</p><?php echo $card( false ); ?></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">--condensed</p><?php echo $card( true ); ?></div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">3 · Slot toggles — SAME content, one slot changing</h2>
	<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px;">
		<div><p class="sg-h3" style="margin:0 0 8px;">without photo → automatic top hairline</p><?php echo $card( true, array( 'photo' => false ) ); ?></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">plain headline (not linked)</p><?php echo $card( true, array( 'photo' => false, 'headline_link' => false ) ); ?></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">+ skills slot</p><?php echo $card( true, array( 'photo' => false, 'skills' => true ) ); ?></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">rich text block (the signals exception)</p><?php echo $card( true, array( 'photo' => false, 'text' => 'rich' ) ); ?></div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">4 · Actions grammar — Action 1..n, destructive ALWAYS LAST</h2>
	<div class="sg-note">The destructive member renders last MECHANICALLY (flex <code>order</code>) — the 3-action demo
		below is deliberately authored with Destructive in the middle of the markup; it still lands at the end.</div>
	<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px;">
		<div><p class="sg-h3" style="margin:0 0 8px;">single action</p><?php echo $card( true, array( 'photo' => false, 'text' => false, 'badges' => false, 'actions' => 1 ) ); ?></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">Action 1 · Action 2 · Destructive (authored out of order)</p><?php echo $card( true, array( 'photo' => false, 'text' => false, 'badges' => false, 'actions' => 3 ) ); ?></div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">5 · Stacking — the 3×3 grid simulation (where the friction lived)</h2>
	<div class="sg-note">Nine cards, deliberately unequal content — long/short headlines, one/two-line titles,
		long/short text, photo/no-photo, 1..3 meta facts. THE CONTRACT: grid rows stretch the cards to equal
		height, the actions row is bottom-pinned (<code>margin-top:auto</code>), so <b>every bottom hairline and
		every action row aligns across each row</b> regardless of content length.
		<br><br><b>SINGLE SEPARATOR LAW</b> (harvested from the production signal archive): the CONSUMER grid strips
		<code>border-bottom</code> from every card that is not in the last row
		(<code>:nth-last-child(n+cols+1)</code>, per breakpoint) — a no-photo row below then contributes the ONLY
		line via its own top hairline; photo rows meet the row above with no line at all; the final row closes
		the stack. Never a double line.</div>
	<style>
		/* CONSUMER rule (this demo = the consumer) — production: .articles-grid--signals .article:nth-last-child(n+5) */
		#preview-stack-demo .preview-card:nth-last-child(n+4) { border-bottom: none; }
	</style>
	<div style="overflow-x:auto;"><div id="preview-stack-demo" style="display:grid; grid-template-columns:repeat(3, 1fr); gap:24px; min-width:560px;">
		<?php echo $card( true, array( 'meta' => 1 ) ); ?>
		<?php echo $card( true, array( 'headline_long' => true, 'meta' => 2 ) ); ?>
		<?php echo $card( true, array( 'text' => 'long', 'meta' => 3 ) ); ?>
		<?php echo $card( true, array( 'photo' => false, 'text' => 'long' ) ); ?>
		<?php echo $card( true, array( 'photo' => false, 'headline_long' => true, 'badges' => false ) ); ?>
		<?php echo $card( true, array( 'photo' => false, 'meta' => 4 ) ); ?>
		<?php echo $card( true, array( 'skills' => true, 'meta' => 2 ) ); ?>
		<?php echo $card( true, array( 'text' => false ) ); ?>
		<?php echo $card( true, array( 'headline_long' => true, 'text' => 'long', 'actions' => 2 ) ); ?>
	</div></div>

	<h2 class="sg-section-h" style="margin-top:32px;">6 · Consumer mappings (reference only — content types are COMPOSITIONS, not variants)</h2>
	<table class="sg-table">
		<tr><th>consumer</th><th>size</th><th>slots used</th><th>action icon</th></tr>
		<tr><td>Article — AIF home/archive, AIG archive</td><td>default</td><td>photo · linked headline · meta (date|author) · badges · text ~30w · 1 action</td><td>arrow-right (internal)</td></tr>
		<tr><td>Event</td><td>condensed</td><td>headline · meta (datetime) · badges (location) · text ~20w · 1 action</td><td>source (external)</td></tr>
		<tr><td>Signal</td><td>condensed</td><td>headline · meta (date|source) · badges (Signal) · RICH text · 1 action</td><td>source (external)</td></tr>
		<tr><td>Job position (AIG)</td><td>condensed</td><td>plain headline · meta (company|salary) · badges (location/seniority) · SKILLS · text · 1 action</td><td>source (external)</td></tr>
		<tr><td>Management (AIF My Articles)</td><td>condensed</td><td>headline · meta (status date) · badges (status) · text · 3 actions</td><td>edit · arrow-right · destructive</td></tr>
	</table>
	<?php
}

function aifds_sg_item_accordion() {
	// One item renderer — the canonical markup from both themes' landing-page-primitives.md.
	$acc = function ( $q, $a ) {
		ob_start(); ?>
		<div class="accordion">
			<button class="accordion__header" aria-expanded="false">
				<h3 class="accordion__title"><?php echo esc_html( $q ); ?></h3>
				<div class="accordion__icon"><?php echo aifds_icon( 'arrow-right', array( 'size' => 24 ) ); ?></div>
			</button>
			<div class="accordion__content">
				<div class="accordion__inner"><?php echo wp_kses_post( $a ); ?></div>
			</div>
		</div>
		<?php return ob_get_clean();
	};
	?>
	<h2 class="sg-section-h">The FAQ card — harvested 1:1 (AIF /newsletter + AIG course detail)</h2>
	<div class="sg-note">Both themes ship near-twin accordions; the DS unifies to <b>AIG's 1px card border</b>
		(AIF live is borderless on a tinted section — GM exception) and AIG's <b>synced icon transition</b>.
		Title = the <code>heading-xs</code> bundle verbatim (18/Inter/extrabold/1.35 harvested); body =
		<code>body-lg</code> (18/1.7); icon = <code>arrow-right</code> in <code>--deep</code>, rotates −90° when open.
		Height animation = measured <code>scrollHeight</code> (<code>js/components/accordion.js</code>, the AIG engine).
		<b>0 new tokens.</b></div>

	<h3 class="sg-h3">Independent (default) — the AIG course-detail behavior</h3>
	<div class="sg-note">Each item toggles on its own; several can be open at once.</div>
	<div style="display:flex; flex-direction:column; gap:var(--spacing-16); max-width:844px;">
		<?php echo $acc( 'Who is the course for?', '<p>Founders and operators who want hands-on AI skills. No prior coding needed.</p>' ); ?>
		<?php echo $acc( 'Do I get a certificate?', '<p>Yes — an accredited certificate after the final project.</p><p>It is recognized by the labour office subsidy scheme.</p>' ); ?>
	</div>

	<h3 class="sg-h3" style="margin-top:32px;">Exclusive — the AIF newsletter behavior <span style="font-weight:400;color:var(--text-tertiary);font-family:var(--font-mono);font-size:12px;">[data-accordion="exclusive"]</span></h3>
	<div class="sg-note">Opening one closes its open sibling (harvested from the /newsletter FAQ inline script,
		generalized from its page-specific <code>.lp-faq</code> scoping to the wrapper attribute).</div>
	<div data-accordion="exclusive" style="display:flex; flex-direction:column; gap:var(--spacing-16); max-width:844px;">
		<?php echo $acc( 'How much does it cost?', '<p>Nothing. The newsletter is and stays free — no paid tier, no premium.</p>' ); ?>
		<?php echo $acc( 'How often does it arrive?', '<p>Weekly, roughly a 7-minute read.</p>' ); ?>
		<?php echo $acc( 'Can I unsubscribe?', '<p>Anytime, one click in the footer of every issue.</p>' ); ?>
	</div>
	<?php
}

function aifds_sg_item_breadcrumb() {
	?>
	<h2 class="sg-section-h">The trail — harvested 1:1 from AIF (<code>inc/breadcrumbs.php</code>)</h2>
	<div class="sg-note">Home → archive → current; the <b>→ separator is content</b> (the PHP helper renders it),
		not CSS. Voice = caption-sized accent (<code>--font-accent</code> 14 bold, harvested). Links are TEXT-colored
		(excluded from the global link chain) and follow the <b>link idiom</b> (resting 1px/4px, hover thickens to 2px
		— live hover REMOVED the underline, the outlawed pattern; GM exception). <b>0 new tokens.</b></div>
	<nav class="breadcrumbs" aria-label="Breadcrumb"><span class="breadcrumb__list">
		<a href="#" class="breadcrumb__link">Home</a>
		<span class="breadcrumb__separator">&rarr;</span>
		<a href="#" class="breadcrumb__link">Articles</a>
		<span class="breadcrumb__separator">&rarr;</span>
		<span class="breadcrumb__current">AI in Practice</span>
	</span></nav>

	<h2 class="sg-section-h" style="margin-top:32px;">On surfaces</h2>
	<div class="section-dark" style="padding:24px;">
		<nav class="breadcrumbs" aria-label="Breadcrumb"><span class="breadcrumb__list">
			<a href="#" class="breadcrumb__link">Home</a>
			<span class="breadcrumb__separator">&rarr;</span>
			<span class="breadcrumb__current">Signals</span>
		</span></nav>
	</div>
	<?php
}

function aifds_sg_item_pagination() {
	?>
	<h2 class="sg-section-h">Archive pagination — harvested 1:1 (byte-identical in BOTH themes)</h2>
	<div class="sg-note">Styles the WordPress <code>paginate_links()</code> output —
		<code>.archive-pagination &gt; .nav-links &gt; .page-numbers</code> (the inner classes are WP core, unrenameable).
		Chips = <b>44px calibrated touch target</b> (the mobile-button constant). Unifications: <b>square</b> (radius
		retirement; live had 4px), declared medium/semibold weights were <b>fiction</b> (live renders 400 — verified),
		hover scoped to <code>&lt;a&gt;</code> only (the current/dots spans are static). Rail = <code>--bg-alt</code>,
		hover = <code>--bg-band</code>, current = <code>--bg</code>. <b>0 new tokens.</b></div>
	<nav class="archive-pagination" aria-label="Pagination" style="margin-top:0; padding:16px 0;">
		<div class="nav-links">
			<a class="page-numbers" href="#">&laquo;</a>
			<span aria-current="page" class="page-numbers current">1</span>
			<a class="page-numbers" href="#">2</a>
			<a class="page-numbers" href="#">3</a>
			<span class="page-numbers dots">&hellip;</span>
			<a class="page-numbers" href="#">12</a>
			<a class="page-numbers" href="#">&raquo;</a>
		</div>
	</nav>
	<?php
}

function aifds_sg_item_nav_tabs() {
	$tabs = function ( $active ) {
		$items = array( 'Write article', 'My articles', 'Preferences', 'Profile', 'Statistics' );
		ob_start(); ?>
		<nav class="nav-tabs" aria-label="Author portal">
			<?php foreach ( $items as $i => $label ) : ?>
				<?php if ( $i === $active ) : ?>
					<span class="nav-tabs__tab nav-tabs__tab--active"><?php echo esc_html( $label ); ?></span>
				<?php else : ?>
					<a href="#" class="nav-tabs__tab"><?php echo esc_html( $label ); ?></a>
				<?php endif; ?>
			<?php endforeach; ?>
		</nav>
		<?php return ob_get_clean();
	};
	?>
	<h2 class="sg-section-h">Nav tabs — harvested from AIF <code>.author-tabs</code> (renamed generic)</h2>
	<div class="sg-note">These are TABS — <b>the row DOCKS on its section's bottom edge</b> and the active white
		chip lands FLUSH on the section below (harvested contract, live-verified: hero bottom == chip bottom ==
		next-section top). A padded container whose LAST child is the tab row drops its bottom padding
		automatically (<code>:where(…):has(&gt; .nav-tabs:last-child)</code>). The ACTIVE tab is a
		<code>&lt;span&gt;</code> with a CONSTANT white chip (<code>--white</code> — <code>var(--bg)</code> would
		resolve to brand inside the hero and vanish); inactive tabs are text-colored links on the link idiom.
		<code>js/components/nav-tabs.js</code> centers the active tab on load. Mobile: the row bleeds to the
		viewport edges (full-bleed, live-verified) and tabs step down to body-md. Theme alias
		<code>author-tabs → nav-tabs</code> at adoption. <b>0 new tokens.</b></div>

	<h3 class="sg-h3">Docked on the brand hero (the production context) — the chip connects to the section below</h3>
	<div class="section-brand" style="padding-top:32px; padding-left:24px; padding-right:24px; background:var(--brand);">
		<p style="margin:0 0 48px; font-family:var(--font-display); font-size:28px; font-weight:900;">Author portal</p>
		<?php echo $tabs( 2 ); ?>
	</div>
	<div style="background:var(--bg); padding:24px; border:var(--stroke-1) solid var(--border); border-top:0;">
		<p style="margin:0; color:var(--text-secondary); font-size:14px;">The content section — the active chip above sits flush on this surface.</p>
	</div>

	<h3 class="sg-h3" style="margin-top:32px;">Docked on a light band</h3>
	<div style="background:var(--bg-alt); padding-top:32px; padding-left:24px; padding-right:24px;">
		<?php echo $tabs( 0 ); ?>
	</div>
	<div style="background:var(--bg); padding:24px; border:var(--stroke-1) solid var(--border); border-top:0;">
		<p style="margin:0; color:var(--text-secondary); font-size:14px;">Content below.</p>
	</div>
	<?php
}

function aifds_sg_item_reference_card() {
	?>
	<h2 class="sg-section-h">One canonical card — appearance from the background (operator ruling)</h2>
	<div class="sg-note">Harvested from both themes' <code>[testimonial]</code> shortcode. Production's dark
		testimonial ships as <code>reference-card section-dark</code> — <b>the scope class ON the card</b>; the light
		case-study is the SAME card on the page surface. The old per-variant color overrides collapse into roles
		(fill <code>--raised</code>, quote mark <code>--bullet</code>). Quote voice = accent 18/1.7 (declared
		Light/300 was the outlawed fiction → regular). Quote icon = <code>aifds_icon('quote-brackets')</code>
		(harvested art, registered). The AIF newsletter <code>.testimonial-card</code> skin dies at adoption.
		<b>0 new tokens.</b></div>

	<h3 class="sg-h3">Testimonial — dark (the scope class ON the card)</h3>
	<div class="reference-card section-dark">
		<span class="reference-card__quote" aria-hidden="true"></span>
		<div class="reference-card__header">
			<div class="reference-card__avatar"><img src="<?php echo esc_url( AIFDS_URL . 'assets/img/demo/persona-2.jpg' ); ?>" alt="Jana Kovářová"></div>
			<div class="reference-card__title-group">
				<h4 class="reference-card__name">Jana Kovářová</h4>
				<p class="reference-card__subtitle">Head of Product, Raiffeisen</p>
			</div>
		</div>
		<div class="reference-card__body">
			<div class="reference-card__content">
				<p>The course paid for itself in the first month. <strong>Our team now ships AI features weekly</strong> instead of debating them quarterly.</p>
			</div>
		</div>
	</div>

	<h3 class="sg-h3" style="margin-top:32px;">Classic personal testimonial — light (same markup, no scope class)</h3>
	<div class="reference-card">
		<span class="reference-card__quote" aria-hidden="true"></span>
		<div class="reference-card__header">
			<div class="reference-card__avatar"><img src="<?php echo esc_url( AIFDS_URL . 'assets/img/demo/persona-1.jpg' ); ?>" alt="Petr Novák"></div>
			<div class="reference-card__title-group">
				<h4 class="reference-card__name">Petr Novák</h4>
				<p class="reference-card__subtitle">Founder, Creative Dock</p>
			</div>
		</div>
		<div class="reference-card__body">
			<div class="reference-card__content">
				<p>We went from zero AI literacy to <strong>three shipped automations</strong> in one quarter. The guild format works.</p>
			</div>
		</div>
	</div>

	<h3 class="sg-h3" style="margin-top:32px;">Case study — logo slot + THE QUOTE VOICE stress test (headline, lists, strong)</h3>
	<div class="sg-note">One voice, AIG’s fixed grammar, no exceptions: p, li, strong AND headlines — Space Grotesk at
		ONE size (headlines = strong as a block: same size, bold; h1–h6 identical). List markers (ul arrows + ol
		counters) share ONE muted color and counters inherit the text exactly — never bold, never a different face.</div>
	<div class="reference-card">
		<div class="reference-card__header">
			<div class="reference-card__logo-wrapper"><span class="icon-placeholder" style="width:100%;height:100%;">LOGO</span></div>
			<div class="reference-card__title-group">
				<h4 class="reference-card__name">Creative Dock</h4>
				<p class="reference-card__subtitle">Company builder, 3 markets</p>
			</div>
		</div>
		<div class="reference-card__body">
			<div class="reference-card__content">
				<h3>What changed after the program</h3>
				<p>Three things, in order of impact — and note this headline above is the ONE headline style.</p>
				<ul>
					<li>Internal AI assistants handle first-line support</li>
					<li>Content pipeline runs <strong>4× faster</strong> across all markets</li>
				</ul>
				<h5>The rollout (h5 — renders identically to the h3 above)</h5>
				<ol>
					<li>Audit of every manual workflow</li>
					<li>Pilot with the content team</li>
					<li>Rollout — <strong>now company-wide</strong></li>
				</ol>
			</div>
		</div>
	</div>
	<?php
}

function aifds_sg_item_persona_card() {
	// One renderer, full content contract: photo, name, role, bio, bottom block
	// = meta (location, pinned) then socials (below the location, lowest).
	$card = function ( $name, $role, $bio, $meta, $photo = 1, $socials = false, $extra_class = '', $link = false ) {
		$img = AIFDS_URL . 'assets/img/demo/persona-' . (int) $photo . '.jpg';
		ob_start(); ?>
		<div class="persona-card <?php echo esc_attr( $extra_class ); ?>">
			<div class="persona-card__avatar"><?php if ( $link ) : ?><a class="card-image-link" href="#" aria-label="<?php echo esc_attr( $name ); ?>"><?php endif; ?><img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>"><?php if ( $link ) : ?></a><?php endif; ?></div>
			<div class="persona-card__content">
				<div class="persona-card__header">
					<h4 class="persona-card__name"><?php if ( $link ) : ?><a class="card-title-link" href="#"><?php echo esc_html( $name ); ?></a><?php else : ?><?php echo esc_html( $name ); ?><?php endif; ?></h4>
					<p class="persona-card__role"><?php echo esc_html( $role ); ?></p>
				</div>
				<div class="persona-card__bio"><p><?php echo esc_html( $bio ); ?></p></div>
				<?php if ( $meta ) : ?><p class="persona-card__meta"><?php echo esc_html( $meta ); ?></p><?php endif; ?>
				<?php if ( $socials ) : ?>
				<div class="persona-card__socials">
					<a class="persona-card__social-link" href="#" aria-label="LinkedIn"><?php echo aifds_icon( 'linkedin', array( 'size' => 20 ) ); ?></a>
					<a class="persona-card__social-link" href="#" aria-label="X"><?php echo aifds_icon( 'x', array( 'size' => 20 ) ); ?></a>
					<a class="persona-card__social-link" href="#" aria-label="Web"><?php echo aifds_icon( 'web', array( 'size' => 20 ) ); ?></a>
				</div>
				<?php endif; ?>
			</div>
		</div>
		<?php return ob_get_clean();
	};
	?>
	<h2 class="sg-section-h">One canonical, surface-riding card (operator ruling)</h2>
	<div class="sg-note"><code>.persona-card</code> was REMOVED from the dark-3 scope map — the card reads roles
		(fill <code>--raised</code>, text roles) and takes its look from the background: place it in a dark section
		for the dark look; the article author card is the SAME card on light (the old <code>--light</code> override
		block collapsed). Voices = heading-sm / description / body-md / meta bundles. Avatar stays SQUARE (harvested:
		"no hallucinated rounding"). <code>--horizontal</code> = the single-person feature layout at ≥768.
		<b>0 new tokens.</b></div>

	<h3 class="sg-h3">In a dark section (the homepage grid context) — bottom block aligns across the grid</h3>
	<div class="sg-note">The location pins to the card bottom; socials sit BELOW the location (the lowest element).
		Cards with different bio lengths keep their bottom blocks aligned.</div>
	<div class="section-dark" style="padding:24px; display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 262px)); gap:24px;">
		<?php echo $card( 'Petr Novák', 'AI Engineer, AI Guild', 'LINKED version — this persona has a detail page: the photo and the name are links (card-image-link / card-title-link, decent hover, no link-blue).', 'Prague · CZ/EN', 1, true, '', true ); ?>
		<?php echo $card( 'Marie Dvořáková', 'Editor-in-chief, AI Founders', 'Unlinked version — no detail page, nothing is clickable except socials.', 'Brno · CZ', 2, true ); ?>
		<?php echo $card( 'Petr Novák', 'AI Engineer', 'No socials on this one — the location alone still pins.', 'Prague', 1, false ); ?>
	</div>

	<h3 class="sg-h3" style="margin-top:32px;">On light (the article author card — same component)</h3>
	<div id="persona-light-demo" style="max-width:262px;">
		<?php echo $card( 'Petr Novák', 'AI Engineer, AI Guild', 'Builds production agent systems and teaches the automation track.', 'Prague · CZ/EN', 1, true ); ?>
	</div>

	<h3 class="sg-h3" style="margin-top:32px;">Edge cases — long everything (name, position, bio, location) + socials</h3>
	<div class="section-dark" style="padding:24px; display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 262px)); gap:24px;">
		<?php echo $card(
			'Maximilián Vojtěch Dvořáček-Novotný',
			'Principal Applied AI Research Engineer & Head of Automation Curriculum, AI Guild / AI Founders',
			'Builds production agent systems and teaches the automation track. Previously shipped ML infrastructure at scale across three markets, led two platform migrations, and wrote the internal handbook on evaluation-driven agent development that the whole guild now uses.',
			'Praha–Karlín · Brno · remote across CET/EST · CZ/EN/DE',
			2, true
		); ?>
		<?php echo $card( 'Jan Malý', 'Editor', 'Short card for contrast — bottom blocks still align.', 'Ostrava', 1, true ); ?>
	</div>

	<h3 class="sg-h3" style="margin-top:32px;">Container-based orientation — same viewport, two slot widths</h3>
	<div class="sg-note">The card is wrapped in <code>.persona-card-slot</code> (the query container — a container
		can't query itself). <b>Horizontal is EARNED at slot ≥ 560px</b>, vertical is the only state below — no
		viewport queries. <b>The portrait is FULL HEIGHT — ALWAYS</b> (operator ruling): the img is absolutely
		positioned inside the photo cell so it has ZERO say in the card's height — the content column alone sizes
		the row and the portrait cover-crops ("zooms") to fill it. Photo track =
		<code>clamp(200px, 40cqi, 320px)</code>. Both cards below render at the SAME viewport — only the slot
		width differs.</div>
	<div class="section-dark" style="padding:24px; display:flex; gap:24px; align-items:flex-start; flex-wrap:wrap;">
		<div class="persona-card-slot" style="flex:1 1 620px; min-width:0;">
			<div class="persona-card">
				<div class="persona-card__avatar"><img src="<?php echo esc_url( AIFDS_URL . 'assets/img/demo/persona-1.jpg' ); ?>" alt="Petr Novák"></div>
				<div class="persona-card__content">
					<div class="persona-card__header">
						<h4 class="persona-card__name">Petr Novák</h4>
						<p class="persona-card__role">AI Engineer, AI Guild</p>
					</div>
					<div class="persona-card__bio"><p>Builds production agent systems and teaches the automation track. Previously shipped ML infrastructure at scale. This bio is deliberately long to prove the contract: the content sizes the card, and the portrait zooms its crop to stay FULL HEIGHT — never whitespace, never a stretched photo.</p></div>
					<p class="persona-card__meta">Prague · CZ/EN</p>
				</div>
			</div>
		</div>
		<div class="persona-card-slot" style="flex:0 0 262px;">
			<div class="persona-card">
				<div class="persona-card__avatar"><img src="<?php echo esc_url( AIFDS_URL . 'assets/img/demo/persona-1.jpg' ); ?>" alt="Petr Novák"></div>
				<div class="persona-card__content">
					<div class="persona-card__header">
						<h4 class="persona-card__name">Petr Novák</h4>
						<p class="persona-card__role">AI Engineer, AI Guild</p>
					</div>
					<div class="persona-card__bio"><p>Same markup, narrow slot → vertical. Automatically.</p></div>
					<p class="persona-card__meta">Prague · CZ/EN</p>
				</div>
			</div>
		</div>
	</div>

	<h3 class="sg-h3" style="margin-top:32px;">Drag to resize the slot — watch the flip at 560px</h3>
	<div style="background:var(--bg-alt); padding:24px;">
		<div class="persona-card-slot" style="resize:horizontal; overflow:hidden; border:var(--stroke-1) dashed var(--border); padding-right:12px; max-width:100%;">
			<div class="persona-card">
				<div class="persona-card__avatar"><img src="<?php echo esc_url( AIFDS_URL . 'assets/img/demo/persona-1.jpg' ); ?>" alt="Petr Novák"></div>
				<div class="persona-card__content">
					<div class="persona-card__header">
						<h4 class="persona-card__name">Marie Dvořáková</h4>
						<p class="persona-card__role">Editor-in-chief, AI Founders</p>
					</div>
					<div class="persona-card__bio"><p>Runs the newsroom and the weekly signal digest.</p></div>
					<p class="persona-card__meta">Brno · CZ</p>
				</div>
			</div>
		</div>
	</div>

	<h3 class="sg-h3" style="margin-top:32px;">Short bio — the min-height guard (no sliver, portrait stays full height)</h3>
	<div class="sg-note">A short card can never get shorter than the photo column is wide
		(<code>min-height: clamp(200px, 40cqi, 320px)</code>) — the portrait never letterboxes.</div>
	<div class="section-dark" style="padding:24px;">
		<div class="persona-card-slot" id="persona-short-demo">
			<div class="persona-card">
				<div class="persona-card__avatar"><img src="<?php echo esc_url( AIFDS_URL . 'assets/img/demo/persona-2.jpg' ); ?>" alt="Marie Dvořáková"></div>
				<div class="persona-card__content">
					<div class="persona-card__header">
						<h4 class="persona-card__name">Marie Dvořáková</h4>
						<p class="persona-card__role">Editor-in-chief</p>
					</div>
					<div class="persona-card__bio"><p>One line.</p></div>
					<p class="persona-card__meta">Brno · CZ</p>
				</div>
			</div>
		</div>
	</div>
	<?php
}

function aifds_sg_item_avatars() {
	$photo = AIFDS_URL . 'assets/img/demo/persona-1.jpg';
	?>
	<h2 class="sg-section-h">Sizes</h2>
	<div class="sg-row" style="align-items:flex-end;">
		<span class="avatar avatar--sm"><img src="<?php echo esc_url( $photo ); ?>" alt="sm 64"></span>
		<span class="avatar avatar--md"><img src="<?php echo esc_url( $photo ); ?>" alt="md 160"></span>
		<span class="avatar avatar--lg"><img src="<?php echo esc_url( $photo ); ?>" alt="lg 260 square"></span>
	</div>
	<?php
}

function aifds_sg_item_icons() {
	?>
	<div class="sg-note">LAW: icons carry NO brand color — they inherit and recolor from context (currentColor).
		Taxonomy: <b>outline</b> (stepped stroke) · <b>shape</b> (solid fill) · <b>colored</b> (baked art — brand
		character glyphs only). <?php echo count( aifds_icon_slugs() ); ?> icons total.</div>
	<?php
	$shape_icons   = array( 'linkedin', 'x', 'instagram', 'bluesky', 'lightbulb-filled' );
	$colored_icons = array( 'smart-button' );
	$outline_icons = array_values( array_diff( aifds_icon_slugs(), $shape_icons, $colored_icons ) );
	$groups = array(
		'Outline (' . count( $outline_icons ) . ')' => $outline_icons,
		'Shape ('   . count( $shape_icons )   . ')' => $shape_icons,
		'Colored (' . count( $colored_icons ) . ')' => $colored_icons,
	);
	foreach ( $groups as $glabel => $slugs ) : ?>
	<h3 class="sg-h3"><?php echo esc_html( $glabel ); ?></h3>
	<div class="sg-grid">
		<?php foreach ( $slugs as $slug ) : ?>
		<div class="sg-swatch" style="padding:12px; display:flex; align-items:center; gap:10px;">
			<?php echo aifds_icon( $slug, array( 'size' => 24 ) ); ?>
			<span style="font-family:var(--font-mono); font-size:11px;"><?php echo esc_html( $slug ); ?></span>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endforeach; ?>
	<h3 class="sg-h3">Recoloring proof — same outline icon, color from context only</h3>
	<div class="sg-row">
		<span style="color: var(--text);"><?php echo aifds_icon( 'arrow-right', array( 'size' => 24 ) ); ?></span>
		<span style="color: var(--brand);"><?php echo aifds_icon( 'arrow-right', array( 'size' => 24 ) ); ?></span>
		<span style="color: var(--magenta);"><?php echo aifds_icon( 'arrow-right', array( 'size' => 24 ) ); ?></span>
		<span style="color: var(--status-error);"><?php echo aifds_icon( 'arrow-right', array( 'size' => 24 ) ); ?></span>
	</div>
	<?php
}

function aifds_sg_item_text_elements() {
	// Each specimen renders in a real prose container so the actual rules apply.
	$open  = '<main class="section-light" style="border:1px dashed var(--border-strong); padding:20px 24px; max-width:640px; margin:8px 0 4px;"><div class="content-container" style="padding:0;">';
	$close = '</div></main>';
	?>
	<h2 class="sg-section-h">The signifier family — a left marker + text at one shared inset</h2>
	<div class="sg-note">These four elements share ONE mechanism: a <b>signifier</b> in the left gutter (a 4px border, an
		arrow bullet, or a number) and their text all starts at <code>--flow-indent</code> (24px). The bordered ones
		subtract their 4px border so the text lands on the same line as the list text — <b>aligned by construction</b>.
		The <a href="<?php echo esc_url( aifds_styleguide_url( aifds_styleguide_brand(), 'info-box' ) ); ?>">info box</a>
		is the boxed member of the same family.</div>

	<h3 class="sg-h3">Perex <span style="font-weight:400; color:var(--text-tertiary); font-family:var(--font-mono); font-size:12px;">.text--perex</span></h3>
	<div class="sg-note" style="background:none; border:none; padding:0; margin:0 0 4px;">Lead / intro voice — Space Grotesk Bold 24px, support-colored 4px left border. Use for an article or page intro.</div>
	<?php echo $open; ?><p class="text--perex">A perex introduces the piece in the lead voice, carrying the support-colored left border — the same signifier as the blockquote.</p><?php echo $close; ?>

	<h3 class="sg-h3">Blockquote <span style="font-weight:400; color:var(--text-tertiary); font-family:var(--font-mono); font-size:12px;">&lt;blockquote&gt;</span></h3>
	<div class="sg-note" style="background:none; border:none; padding:0; margin:0 0 4px;">A pull-quote in the same perex voice + border. Plain <code>&lt;blockquote&gt;</code> markup — no class needed.</div>
	<?php echo $open; ?><blockquote><p>The best way to predict the future is to invent it — a blockquote reads in the perex voice.</p></blockquote><?php echo $close; ?>

	<h3 class="sg-h3">Unordered list <span style="font-weight:400; color:var(--text-tertiary); font-family:var(--font-mono); font-size:12px;">&lt;ul&gt;</span></h3>
	<div class="sg-note" style="background:none; border:none; padding:0; margin:0 0 4px;">Arrow-bullet marker in the gutter; text at the shared inset. Size follows the voice class (article / body / perex).</div>
	<?php echo $open; ?><ul><li>First point, with the arrow bullet</li><li>Second point aligns its text with the perex and quote</li></ul><?php echo $close; ?>

	<h3 class="sg-h3">Ordered list <span style="font-weight:400; color:var(--text-tertiary); font-family:var(--font-mono); font-size:12px;">&lt;ol&gt;</span></h3>
	<div class="sg-note" style="background:none; border:none; padding:0; margin:0 0 4px;">Auto-numbered marker in the gutter; text at the same shared inset.</div>
	<?php echo $open; ?><ol><li>First step</li><li>Second step — the number sits in the gutter, text aligned</li></ol><?php echo $close; ?>
	<?php
}

function aifds_sg_item_prose() {
	?>
	<h2 class="sg-section-h">TWO contexts, real markup (toggle the brand to see each site's voice)</h2>

	<h3 class="sg-h3">Standard PAGE prose — full-size heading scale (h2 36 / h3 28 / h4 22)</h3>
	<main class="section-light" style="border:1px dashed var(--border-strong); padding:24px; max-width:720px;">
		<div class="content-container" style="padding:0;">
		<h2>Page heading level two</h2>
		<p class="text--perex">Intro perex on a page — Space Grotesk Bold, 24px, support left border: the same voice as the blockquote.</p>
		<p>A paragraph with an <a href="#">inline link</a> and <strong>bold emphasis</strong> — behaves exactly like editor content.</p>
		<h3>Page heading level three (brand-divergent font)</h3>
		<ul>
			<li>First item with the arrow bullet</li>
			<li>Second item</li>
		</ul>
		<h4>Page heading level four</h4>
		<ol>
			<li>Numbered item</li>
			<li>Another numbered item</li>
		</ol>
		<blockquote><p>A page blockquote — the intro-perex voice.</p></blockquote>
		</div>
	</main>

	<h3 class="sg-h3">ARTICLE prose — .article-layout__content: scale one step down (h2 28 / h3 22 / h4 18)</h3>
	<main class="section-light" style="border:1px dashed var(--border-strong); padding:24px; max-width:720px; margin-top:16px;">
		<div class="article-layout__content text--article content-container">
			<h2>Article heading level two</h2>
			<p class="text--perex">Article intro perex — the same voice, carrying the support-color left border inside the article column.</p>
			<p>Article body paragraph with an <a href="#">inline link</a> and <strong>bold emphasis</strong>.</p>
			<h3>Article heading level three</h3>
			<ul>
				<li>Article list item with the arrow bullet</li>
				<li>Second item</li>
			</ul>
			<h4>Article heading level four</h4>
			<blockquote><p>An article pull-quote — the intro-perex voice with the support border.</p></blockquote>
		</div>
	</main>

		<h3 class="sg-h3">NUMBERED HEADINGS — .numbered-headings: auto-numbered section headings (course-detail syllabus)</h3>
		<div class="sg-note">ONE mechanism, harvested from both themes' <code>.course-syllabus</code>: each
			<code>&lt;h3&gt;</code> auto-increments a number in a <b>brand-colored tile</b> (fill = <code>--brand</code>,
			so yellow on AIG / blue on AIF — toggle the brand); following content indents to align under the heading
			text. UNIFIED to AIG's tile on both brands — AIF live shows a plain "N." (GM exception). Numbers are pure CSS
			counters: no markup change, reorder-safe.</div>
		<main class="section-light" style="border:1px dashed var(--border-strong); padding:24px; max-width:720px; margin-top:16px;">
			<div class="numbered-headings content-container" style="padding:0;">
				<h3>First module — the number tile leads</h3>
				<p>Body copy for the first numbered section, indented to sit under the heading text.</p>
				<ul>
					<li>A supporting point with the arrow bullet</li>
					<li>Another point</li>
				</ul>
				<h3>Second module — counter auto-increments</h3>
				<p>The second block's number is <code>02</code> with zero markup change.</p>
				<h3>Third module — reorder-safe by construction</h3>
				<p>Move any block and the numbers re-flow; the tile stays aligned to the content indent.</p>
			</div>
		</main>
	<?php
}

/**
 * Course card — SPEC SHEET. THE contract: orientation comes from the SLOT
 * (container query, >=720px = horizontal), never from a variant class.
 */
function aifds_sg_item_course_card() {
	$photo = AIFDS_URL . 'assets/img/demo/article-1.jpg';
	// One placeholder renderer drives every demo. Slots via $s flags.
	$card = function ( $s = array() ) use ( $photo ) {
		$s = array_merge( array(
			'image'    => true,   // true = linked (zoom) | 'plain' | false
			'eyebrow'  => true,   // true (default accent) | 'primary'|'secondary'|'quaternary' | false
			'link'     => true,   // title linked
			'subtitle' => true,
			'text'     => true,   // true | 'long'
			'inactive' => false,
			'slot'     => true,   // wrap in .course-card-slot (the container)
		), $s );
		$accent = is_string( $s['eyebrow'] ) ? ' course-accent--' . $s['eyebrow'] : '';
		ob_start(); ?>
		<?php if ( $s['slot'] ) : ?><div class="course-card-slot"><?php endif; ?>
		<article class="course-info-card<?php echo $s['inactive'] ? ' course-info-card--inactive' : ''; ?>">
			<?php if ( true === $s['image'] && ! $s['inactive'] ) : ?>
			<a href="#" class="course-info-card__illustration-lg card-image-link"><img src="<?php echo esc_url( $photo ); ?>" alt=""></a>
			<?php elseif ( $s['image'] ) : ?>
			<div class="course-info-card__illustration-lg"><img src="<?php echo esc_url( $photo ); ?>" alt=""></div>
			<?php endif; ?>
			<div class="course-info-card__content">
				<?php if ( $s['eyebrow'] ) : ?>
				<p class="course-info-card__eyebrow<?php echo esc_attr( $accent ); ?>">Eyebrow slot</p>
				<?php endif; ?>
				<h3 class="course-info-card__title"><?php if ( $s['link'] && ! $s['inactive'] ) : ?><a href="#" class="card-title-link">Course title slot &mdash; the display voice</a><?php else : ?>Course title slot &mdash; the display voice<?php endif; ?></h3>
				<?php if ( $s['subtitle'] ) : ?>
				<p class="course-info-card__subtitle">Subtitle slot &mdash; meta facts (hours | weeks)</p>
				<?php endif; ?>
				<?php if ( 'long' === $s['text'] ) : ?>
				<p class="course-info-card__description">Description slot with a deliberately longer excerpt to stress the row height &mdash; several clauses, a second sentence, and enough words that this card grows visibly taller than a neighbour with a short description.</p>
				<?php elseif ( $s['text'] ) : ?>
				<p class="course-info-card__description">Description slot &mdash; the course pitch, ~30 words.</p>
				<?php endif; ?>
				<?php if ( $s['inactive'] ) : ?>
				<a href="#" class="btn btn--md btn--tertiary"><?php echo aifds_icon( 'course', array( 'size' => 20 ) ); ?> Inactive CTA (contact)</a>
				<?php else : ?>
				<a href="#" class="btn btn--md btn--primary"><?php echo aifds_icon( 'course', array( 'size' => 20 ) ); ?> Course CTA</a>
				<?php endif; ?>
			</div>
		</article>
		<?php if ( $s['slot'] ) : ?></div><?php endif; ?>
		<?php return ob_get_clean();
	};
	?>
	<main class="sg-main">
	<h1 class="sg-page-h">Course card</h1>
	<div class="sg-note">THE listing/promo card for courses (harvested from AI Guild; the AIF twin is dead code
		&mdash; its article ads use the native promo since 2026-06). ONE canonical card; production&rsquo;s
		<code>--editorial</code> class is a legacy alias. <b>Orientation comes from THE SLOT, not a variant</b>:
		wrap the card in <code>.course-card-slot</code> &mdash; a slot <b>&ge;720px</b> renders horizontal
		(image left, fixed 420px track, centered content), a narrower slot renders vertical (16/9 image on top,
		top-aligned content, CTA pinned). Production&rsquo;s viewport breakpoint AND its count-3/count-4 grid
		overrides both collapse into this one rule.</div>

	<h2 class="sg-section-h">1 &middot; Anatomy &mdash; slots</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Slot</th><th>Required</th><th>Contract</th></tr>
		<tr><td><code>__illustration-lg</code></td><td>optional</td><td>16/9 vertical / 420px track horizontal; <code>&lt;a.card-image-link&gt;</code> = hover zoom 1.02; <code>&lt;div&gt;</code> = plain (inactive)</td></tr>
		<tr><td><code>__eyebrow</code></td><td>optional</td><td>mono-label voice; per-course accent class <code>course-accent--primary/secondary/tertiary/quaternary</code>; magenta default</td></tr>
		<tr><td><code>__title</code></td><td>required</td><td>heading-lg + display treatment (<code>--leading-snug</code> 1.05, <code>--tracking-display</code> -0.022em); link via <code>card-title-link</code></td></tr>
		<tr><td><code>__subtitle</code></td><td>optional</td><td>description voice, <code>--text-tertiary</code>; meta facts</td></tr>
		<tr><td><code>__description</code></td><td>optional</td><td>body voice; ~30 words</td></tr>
		<tr><td>CTA</td><td>optional</td><td><code>.btn--md</code>; active = primary, inactive = tertiary; pinned bottom in vertical mode; full-width &le;767 viewport</td></tr>
	</table></div>
	<?php echo $card( array() ); ?>

	<h2 class="sg-section-h" style="margin-top:32px;">2 &middot; Orientation &mdash; the slot decides (720px contract)</h2>
	<div class="sg-note">The SAME markup twice; only the slot width changes. Left: a narrow slot (360px) &rarr;
		vertical. Right: the full-width slot above is horizontal. Grids need NO count classes &mdash; three
		cards in a row are three narrow slots.</div>
	<div id="course-orientation-demo">
		<div style="max-width:360px;"><p class="sg-h3" style="margin:0 0 8px;">360px slot &rarr; vertical</p><?php echo $card( array() ); ?></div>
		<div style="margin-top:24px;"><p class="sg-h3" style="margin:0 0 8px;">full-width slot &rarr; horizontal (&ge;720px)</p><?php echo $card( array() ); ?></div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">3 &middot; Slot toggles</h2>
	<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px;">
		<div><p class="sg-h3" style="margin:0 0 8px;">no image</p><?php echo $card( array( 'image' => false ) ); ?></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">no eyebrow / no subtitle</p><?php echo $card( array( 'eyebrow' => false, 'subtitle' => false ) ); ?></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">unlinked (no title link, plain image)</p><?php echo $card( array( 'link' => false, 'image' => 'plain' ) ); ?></div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">4 &middot; State axis &mdash; active / inactive</h2>
	<div class="sg-note">Inactive (&ldquo;coming soon&rdquo;): grayscaled image, disabled voices, muted eyebrow
		(beats the accent class), NO hover zoom, tertiary CTA (production opens the contact modal).</div>
	<div id="course-state-demo" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(300px, 1fr)); gap:24px;">
		<div><p class="sg-h3" style="margin:0 0 8px;">active</p><?php echo $card( array() ); ?></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">inactive</p><?php echo $card( array( 'inactive' => true, 'eyebrow' => 'primary' ) ); ?></div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">5 &middot; Eyebrow accent axis (per-course admin color)</h2>
	<div id="course-accent-demo" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(240px, 1fr)); gap:24px;">
		<div><p class="sg-h3" style="margin:0 0 8px;">primary (brand-strong)</p><?php echo $card( array( 'eyebrow' => 'primary', 'image' => false, 'text' => false, 'subtitle' => false ) ); ?></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">secondary</p><?php echo $card( array( 'eyebrow' => 'secondary', 'image' => false, 'text' => false, 'subtitle' => false ) ); ?></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">tertiary (default)</p><?php echo $card( array( 'image' => false, 'text' => false, 'subtitle' => false ) ); ?></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">quaternary</p><?php echo $card( array( 'eyebrow' => 'quaternary', 'image' => false, 'text' => false, 'subtitle' => false ) ); ?></div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">6 &middot; Grid simulation &mdash; the &ldquo;Na&scaron;e kurzy&rdquo; case (3-up, equal heights)</h2>
	<div class="sg-note">Three narrow slots in a grid &rarr; all vertical, NO count classes. THE CONTRACT: grid
		stretch + <code>min-height:100%</code> on the slotted card + the pinned CTA &rArr; card bottoms and CTA
		rows align across the row regardless of copy length.</div>
	<div id="course-grid-demo" style="display:grid; grid-template-columns:repeat(3, minmax(0, 1fr)); gap:24px; align-items:stretch;">
		<?php echo $card( array() ); ?>
		<?php echo $card( array( 'text' => 'long' ) ); ?>
		<?php echo $card( array( 'subtitle' => false ) ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">7 &middot; Surfaces &mdash; --bg-base pops to the page surface</h2>
	<div class="sg-note">The fill is <code>--bg-base</code>: white on EVERY light scope (the article
		&ldquo;Na&scaron;e kurzy&rdquo; white-on-grey override is now the canon), black on dark + brand scopes.
		Eyebrow accents are Tier-1 palette reads &mdash; unharvested territory on dark.</div>
	<div class="content-section--secondary" style="padding:24px;">
		<p class="sg-h3" style="margin:0 0 8px;">on secondary (grey) &mdash; card pops white</p>
		<?php echo $card( array( 'image' => false ) ); ?>
	</div>
	<div class="section-dark" style="padding:24px; margin-top:16px;">
		<p class="sg-h3" style="margin:0 0 8px; color:var(--text);">on dark &mdash; card pops black</p>
		<?php echo $card( array( 'image' => false ) ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">8 &middot; Consumer mappings (production)</h2>
	<table class="sg-props-table">
		<tr><th>Consumer</th><th>Composition</th></tr>
		<tr><td>AIG homepage course lists</td><td>full-width slots (horizontal), stacked; active + inactive mixed; per-course accents</td></tr>
		<tr><td>AIG course archive (taxonomy)</td><td>full-width slots, stacked</td></tr>
		<tr><td>AIG article &ldquo;Na&scaron;e kurzy&rdquo;</td><td>1&ndash;2 courses = stacked full-width (horizontal); 3&ndash;4 = grid of narrow slots (vertical) &mdash; count classes DIE, the slot decides</td></tr>
		<tr><td>AIG lecturer / skill detail</td><td>full-width slots (horizontal)</td></tr>
		<tr><td>AIF article course ads</td><td>NONE &mdash; redesigned to the native promo (2026-06); the AIF course-info-card CSS is dead</td></tr>
	</table>
	</main>
	<?php
}

/**
 * Engagement — SPEC SHEET. Aha! + share pills + the post-Aha toast.
 * The FIRST .aif-engagement on the page is LIVE (js/components/engagement.js
 * binds it); the state demos below it are static markup.
 */
function aifds_sg_item_engagement() {
	?>
	<main class="sg-main">
	<h1 class="sg-page-h">Engagement</h1>
	<div class="sg-note">The article engagement row (harvested from AIF; the AIG copy is byte-identical —
		verbatim cross-brand twins). Two ghost pills framed by hairlines: <b>Aha!</b> (the appreciation
		action — the BULB carries the clicked state, not the button) and <b>Share</b> (opens the toast).
		<br><br><b>THE ADDTOANY BOUNDARY</b>: the toast <i>clones</i> the AddToAny plugin&rsquo;s
		<code>.a2a_kit</code> and rewrites its placeholder hrefs into real share URLs (the plugin&rsquo;s JS
		does not bind on clones). The DS owns the widget + the in-toast kit look; the counting engine
		(AJAX, nonce, dedup) and the rules hiding the standalone plugin output stay theme/plugin territory —
		without <code>data-ajax-url</code> the DS engine degrades to optimistic UI (this page).</div>

	<h2 class="sg-section-h">1 &middot; Anatomy &mdash; LIVE widget (click Aha!)</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Slot</th><th>Required</th><th>Contract</th></tr>
		<tr><td><code>.aif-aha</code></td><td>required</td><td>ghost pill; <code>aria-pressed</code>; clicked = filled-bulb swap + pulse, text &rarr; <code>--text</code>; count is tabular-nums</td></tr>
		<tr><td><code>.aif-share</code></td><td>optional</td><td>same pill grammar; count <code>[hidden]</code> until &gt; 0; click opens the toast without Aha! side effects</td></tr>
		<tr><td><code>.aif-engagement-toast</code></td><td>required</td><td>compact <code>--raised</code> pill, slides down (max-height engine); title + cloned kit + close; auto-hides after 8s</td></tr>
		<tr><td><code>data-*</code></td><td>adoption</td><td><code>post-id</code> &middot; <code>nonce</code> &middot; <code>ajax-url</code> (omit = no counting engine) &middot; <code>i18n</code> JSON</td></tr>
	</table></div>

	<?php /* Fake AddToAny kit — the toast clones the first .a2a_kit it finds.
	         Hidden stand-in matching the plugin's class grammar. */ ?>
	<div class="a2a_kit" hidden aria-hidden="true">
		<a class="a2a_button_linkedin" href="/#linkedin"><?php echo aifds_icon( 'linkedin', array( 'size' => 24 ) ); ?></a>
		<a class="a2a_button_email" href="/#email"><?php echo aifds_icon( 'mail', array( 'size' => 24 ) ); ?></a>
		<a class="a2a_button_copy_link" href="/#copy"><?php echo aifds_icon( 'source', array( 'size' => 24 ) ); ?></a>
	</div>

	<div class="aif-engagement"
		data-post-id="sg-demo"
		data-i18n='{"ahaLabel":"Aha!","ahaThanks":"Glad it helped!","shareLabel":"Share article"}'>
		<div class="aif-engagement__row">
			<button type="button" class="aif-aha" aria-pressed="false" aria-label="Aha!">
				<span class="aif-aha__icon-wrap"><?php echo aifds_icon( 'lightbulb', array( 'size' => 24 ) ); ?><?php echo aifds_icon( 'lightbulb-filled', array( 'size' => 24 ) ); ?></span>
				<span class="aif-aha__label">Aha!</span>
				<span class="aif-aha__count">12</span>
			</button>
			<button type="button" class="aif-share" aria-label="Share article">
				<span class="aif-share__icon-wrap"><?php echo aifds_icon( 'share', array( 'size' => 24 ) ); ?></span>
				<span class="aif-share__label">Share article</span>
				<span class="aif-share__count">3</span>
			</button>
		</div>
		<div class="aif-engagement-toast" hidden>
			<div class="aif-engagement-toast__title">Share with someone who would benefit too:</div>
			<div class="aif-engagement-toast__buttons"></div>
			<button type="button" class="aif-engagement-toast__close" aria-label="Close">&times;</button>
		</div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">2 &middot; State axis (static)</h2>
	<div id="engagement-states" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(280px, 1fr)); gap:24px;">
		<div><p class="sg-h3" style="margin:0 0 8px;">rest &mdash; outline bulb, tertiary text</p>
			<div class="aif-engagement"><div class="aif-engagement__row">
				<button type="button" class="aif-aha" aria-pressed="false"><span class="aif-aha__icon-wrap"><?php echo aifds_icon( 'lightbulb', array( 'size' => 24 ) ); ?><?php echo aifds_icon( 'lightbulb-filled', array( 'size' => 24 ) ); ?></span><span class="aif-aha__label">Aha!</span><span class="aif-aha__count">12</span></button>
			</div></div></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">clicked &mdash; filled bulb carries the state</p>
			<div class="aif-engagement"><div class="aif-engagement__row">
				<button type="button" class="aif-aha aif-aha--clicked" aria-pressed="true"><span class="aif-aha__icon-wrap"><?php echo aifds_icon( 'lightbulb', array( 'size' => 24 ) ); ?><?php echo aifds_icon( 'lightbulb-filled', array( 'size' => 24 ) ); ?></span><span class="aif-aha__label">Aha!</span><span class="aif-aha__count">13</span></button>
			</div></div></div>
		<div><p class="sg-h3" style="margin:0 0 8px;">share at zero &mdash; count hidden</p>
			<div class="aif-engagement"><div class="aif-engagement__row">
				<button type="button" class="aif-share"><span class="aif-share__icon-wrap"><?php echo aifds_icon( 'share', array( 'size' => 24 ) ); ?></span><span class="aif-share__label">Share article</span><span class="aif-share__count" hidden>0</span></button>
			</div></div></div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">3 &middot; Toast &mdash; static open state</h2>
	<div class="sg-note">The <code>--raised</code> pill with <code>--shadow-sm</code>; kit icons are
		desaturated at rest (<code>grayscale(0.7) opacity(0.7)</code>, harvested restraint idiom) and reveal
		full color on hover; the AddToAny &ldquo;+&rdquo; overflow is hidden.</div>
	<div id="engagement-toast-demo" class="aif-engagement" style="border:0; padding:0; margin:0;">
		<div class="aif-engagement-toast aif-engagement-toast--open">
			<div class="aif-engagement-toast__title">Share with someone who would benefit too:</div>
			<div class="aif-engagement-toast__buttons"><div class="a2a_kit">
				<a class="a2a_button_linkedin" href="#"><?php echo aifds_icon( 'linkedin', array( 'size' => 24 ) ); ?></a>
				<a class="a2a_button_email" href="#"><?php echo aifds_icon( 'mail', array( 'size' => 24 ) ); ?></a>
				<a class="a2a_button_copy_link" href="#"><?php echo aifds_icon( 'source', array( 'size' => 24 ) ); ?></a>
			</div></div>
			<button type="button" class="aif-engagement-toast__close" aria-label="Close">&times;</button>
		</div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">4 &middot; Behavior contract (js/components/engagement.js &mdash; the production engine, ported)</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Event</th><th>Behavior</th></tr>
		<tr><td>Aha! click</td><td>optimistic +1 &rarr; <code>--clicked</code> + pulse + localStorage &rarr; AJAX <code>aif_aha</code> &rarr; server count reconciles &rarr; toast opens, label swaps to the thanks message</td></tr>
		<tr><td>Share click</td><td>toast opens WITHOUT Aha! side effects</td></tr>
		<tr><td>Toast</td><td>clones the first <code>.a2a_kit</code>, rewrites hrefs per platform (fb/x/li/email/mastodon/reddit/wa/tg/copy), auto-hides in 8s, hover pauses the timer</td></tr>
		<tr><td>Kit click</td><td>AJAX <code>aif_share</code> + platform; server dedupes per visitor/post/platform for 5 min</td></tr>
		<tr><td>No <code>data-ajax-url</code></td><td>graceful: optimistic UI only (this page)</td></tr>
	</table></div>

	<h2 class="sg-section-h" style="margin-top:32px;">5 &middot; Consumer mappings</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Consumer</th><th>Composition</th></tr>
		<tr><td>AIF article detail</td><td>below the_content, above the author block; counting engine in functions.php; AddToAny standalone output hidden by THEME rules</td></tr>
		<tr><td>AIG article detail</td><td>byte-identical twin (same classes, own AJAX handlers)</td></tr>
		<tr><td>Toast primitive</td><td>the sweep notes <code>.aif-engagement-toast</code> as a candidate generic feedback primitive &mdash; still tracked, unchanged by this row</td></tr>
	</table></div>
	</main>
	<?php
}

/**
 * Comments — SPEC SHEET. AIF-only threaded discussion (Medium-style, no
 * bubbles); every visual state is static markup — the edit/delete AJAX
 * engine stays plugin territory.
 */
function aifds_sg_item_comments() {
	$photo = AIFDS_URL . 'assets/img/demo/persona-1.jpg';
	?>
	<main class="sg-main">
	<h1 class="sg-page-h">Comments</h1>
	<div class="sg-note">Threaded article discussion, <b>AIF-only</b> (AI Guild adopts this canon when it
		turns comments on). Medium-style: NO bubbles &mdash; hairlines separate top-level comments; depth-2
		replies draw 1px L-shaped <b>thread connectors</b> whose geometry derives from the avatar (48px
		&rarr; corner at its center y=24; 40px on mobile &rarr; y=20). Avatars compose the DS avatar
		(<code>--xs</code> + <code>--initials</code>, minted here); author name links ride
		<code>card-title-link</code>, avatar links <code>card-image-link</code>. The edit/delete ENGINE
		(15-min self-edit, GDPR tombstone) stays plugin territory &mdash; the DS owns every visual state.
		The guest registration banner is a separate component (sweep candidate), not this row.</div>

	<h2 class="sg-section-h">1 &middot; Anatomy &mdash; the thread</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Slot</th><th>Required</th><th>Contract</th></tr>
		<tr><td><code>__heading</code></td><td>required</td><td>heading-sm voice; &ldquo;Comments: N&rdquo; or the zero-state invitation line</td></tr>
		<tr><td><code>.comment-author</code></td><td>required</td><td>avatar (<code>avatar--xs</code>, photo or <code>--initials</code>) + name (heading-xs) + metadata (caption voice, <code>&middot;</code>-separated: time, (edited), admin edit, delete icon)</td></tr>
		<tr><td><code>.comment-body__bubble</code></td><td>required</td><td>body voice paragraphs + action row (Reply <code>&lt;a&gt;</code> &middot; Edit <code>&lt;button&gt;</code> &mdash; BOTH read the link idiom)</td></tr>
		<tr><td><code>.children</code></td><td>optional</td><td>depth 2 only; L-connector to each reply, spine continues through non-last replies; 72px indent (56 mobile)</td></tr>
		<tr><td><code>.comment-respond</code></td><td>required</td><td>heading-xs title + DS textarea + <code>.btn--primary.btn--sm</code> submit, hairline-framed</td></tr>
		<tr><td>states</td><td>&mdash;</td><td>tombstone (<code>.aif-tombstone</code>) &middot; edited badge &middot; awaiting moderation &middot; closed &middot; pagination</td></tr>
	</table></div>

	<section class="article-comments" id="comments-anatomy-demo" style="margin-top:0; padding-top:0;">
		<h2 class="article-comments__heading">Comments: 4</h2>
		<ol class="article-comments__list comment-list">
			<li class="comment">
				<article class="comment-body">
					<header class="comment-author vcard">
						<a class="card-image-link" href="#" aria-label="Author profile"><span class="aif-comment__avatar avatar avatar--xs"><img src="<?php echo esc_url( $photo ); ?>" alt=""></span></a>
						<div class="comment-author__meta">
							<b class="fn"><a class="card-title-link" href="#">Author name slot &mdash; linked (has public profile)</a></b>
							<div class="comment-metadata"><time datetime="2026-06-12T09:34">June 12, 2026 &middot; 9:34 am</time></div>
						</div>
					</header>
					<div class="comment-body__bubble">
						<div class="comment-content"><p>Comment body slot &mdash; the body voice, one or more paragraphs.</p></div>
						<div class="comment-actions"><a href="#">Reply</a></div>
					</div>
				</article>
				<ol class="children">
					<li class="comment">
						<article class="comment-body">
							<header class="comment-author vcard">
								<span class="aif-comment__avatar avatar avatar--xs avatar--initials">R</span>
								<div class="comment-author__meta">
									<b class="fn">Reply author &mdash; unlinked (initials avatar)</b>
									<div class="comment-metadata"><time>June 12, 2026 &middot; 10:02 am</time></div>
								</div>
							</header>
							<div class="comment-body__bubble">
								<div class="comment-content"><p>First reply slot &mdash; the connector&rsquo;s L points at this avatar; the spine continues below because a sibling follows.</p></div>
								<div class="comment-actions"><a href="#">Reply</a></div>
							</div>
						</article>
					</li>
					<li class="comment">
						<article class="comment-body">
							<header class="comment-author vcard">
								<span class="aif-comment__avatar avatar avatar--xs avatar--initials">L</span>
								<div class="comment-author__meta">
									<b class="fn">Last reply author</b>
									<div class="comment-metadata"><time>June 12, 2026 &middot; 11:15 am</time></div>
								</div>
							</header>
							<div class="comment-body__bubble">
								<div class="comment-content"><p>Last reply slot &mdash; the spine STOPS at this connector.</p></div>
								<div class="comment-actions"><a href="#">Reply</a></div>
							</div>
						</article>
					</li>
				</ol>
			</li>
			<li class="comment">
				<article class="comment-body">
					<header class="comment-author vcard">
						<span class="aif-comment__avatar avatar avatar--xs avatar--initials">S</span>
						<div class="comment-author__meta">
							<b class="fn">Second top-level author</b>
							<div class="comment-metadata"><time>June 13, 2026 &middot; 8:20 am</time></div>
						</div>
					</header>
					<div class="comment-body__bubble">
						<div class="comment-content"><p>A hairline separates top-level comments &mdash; except below the last one.</p></div>
						<div class="comment-actions"><a href="#">Reply</a></div>
					</div>
				</article>
			</li>
		</ol>
	</section>

	<h2 class="sg-section-h" style="margin-top:32px;">2 &middot; Item states</h2>
	<div id="comments-states-demo">
	<section class="article-comments" style="margin-top:0; padding-top:0;">
		<ol class="article-comments__list comment-list">
			<li class="comment aif-can-edit aif-can-delete">
				<article class="comment-body">
					<header class="comment-author vcard">
						<span class="aif-comment__avatar avatar avatar--xs avatar--initials">M</span>
						<div class="comment-author__meta">
							<b class="fn">Own comment &mdash; owner actions live</b>
							<div class="comment-metadata"><time>4:22 pm</time>
								<span class="comment-edited-badge" title="edited timestamp">&middot; (edited)</span>
								&middot; <button type="button" class="aif-comment-delete-link" title="Delete comment" aria-label="Delete comment"><?php echo aifds_icon( 'trash-2', array( 'size' => 16 ) ); ?></button>
							</div>
						</div>
					</header>
					<div class="comment-body__bubble">
						<div class="comment-content"><p>The metadata line carries (edited) + the trash icon; the action row carries Reply &middot; Edit.</p></div>
						<div class="comment-actions"><a href="#">Reply</a><span class="comment-actions__sep" aria-hidden="true">&middot;</span><button type="button" class="aif-comment-edit-link">Edit</button></div>
					</div>
				</article>
			</li>
			<li class="comment aif-tombstone">
				<article class="comment-body">
					<header class="comment-author vcard">
						<span class="aif-comment__avatar avatar avatar--xs avatar--initials">&times;</span>
						<div class="comment-author__meta">
							<b class="fn">Deleted</b>
							<div class="comment-metadata"><time>June 13, 2026 &middot; 9:05 am</time></div>
						</div>
					</header>
					<div class="comment-body__bubble">
						<div class="comment-content"><p>Tombstone &mdash; author-deleted; the row stays so replies keep nesting. Same weights, muted color only. No action row.</p></div>
					</div>
				</article>
			</li>
			<li class="comment">
				<article class="comment-body">
					<header class="comment-author vcard">
						<span class="aif-comment__avatar avatar avatar--xs avatar--initials">P</span>
						<div class="comment-author__meta">
							<b class="fn">Pending author</b>
							<div class="comment-metadata"><time>just now</time></div>
						</div>
					</header>
					<div class="comment-body__bubble">
						<span class="comment-awaiting-moderation">Your comment is awaiting moderation.</span>
						<div class="comment-content"><p>Awaiting-moderation note sits above the body.</p></div>
					</div>
				</article>
			</li>
		</ol>
	</section>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">3 &middot; Inline edit &mdash; the open state</h2>
	<section class="article-comments" id="comments-edit-demo" style="margin-top:0; padding-top:0;">
		<ol class="article-comments__list comment-list">
			<li class="comment aif-can-edit">
				<article class="comment-body">
					<header class="comment-author vcard">
						<span class="aif-comment__avatar avatar avatar--xs avatar--initials">M</span>
						<div class="comment-author__meta"><b class="fn">Own comment &mdash; editing</b>
							<div class="comment-metadata"><time>4:22 pm</time></div></div>
					</header>
					<div class="comment-body__bubble">
						<div class="aif-comment-edit-form form-stack">
							<div class="form-group"><div class="form-control-wrapper"><textarea class="form-control" rows="4">Editable comment text &mdash; the DS textarea.</textarea></div></div>
							<div class="aif-comment-edit-actions">
								<button type="button" class="btn btn--primary btn--sm aif-comment-edit-save">Save</button>
								<button type="button" class="aif-comment-edit-cancel">Cancel</button>
							</div>
						</div>
					</div>
				</article>
			</li>
		</ol>
	</section>

	<h2 class="sg-section-h" style="margin-top:32px;">4 &middot; Comment form + terminal states</h2>
	<section class="article-comments" id="comments-form-demo" style="margin-top:0; padding-top:0;">
		<div class="comment-respond">
			<h3 class="comment-reply-title">Add a comment <small>Cancel reply</small></h3>
			<form class="form-stack article-comments__form comment-form" onsubmit="return false;">
				<p class="comment-form-comment"><div class="form-control-wrapper"><textarea class="form-control" rows="6" placeholder="What do you think?"></textarea></div></p>
				<p class="form-submit"><button type="submit" class="btn btn--primary btn--sm">Post comment</button></p>
			</form>
		</div>
		<nav class="article-comments__pagination" aria-label="Comments navigation"><a href="#">&larr; Older</a><a href="#">Newer &rarr;</a></nav>
		<p class="article-comments__closed">Comments are closed.</p>
	</section>

	<h2 class="sg-section-h" style="margin-top:32px;">5 &middot; Boundary + adoption map</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Piece</th><th>Owner</th></tr>
		<tr><td>All visuals above (thread, connectors, states, form skin)</td><td><b>DS</b> (this row)</td></tr>
		<tr><td>Renderer markup (<code>aif_publisher_render_comment</code>), AJAX edit/delete, 15-min window, tombstoning</td><td>aif-publisher plugin</td></tr>
		<tr><td><code>comment-edit.js</code> (swap content &harr; form, countdown)</td><td>theme (drives plugin endpoints)</td></tr>
		<tr><td>Guest state (<code>.register-banner--subtle</code>)</td><td>separate component &mdash; sweep candidate</td></tr>
		<tr><td>Avatar classes <code>nav-avatar__circle--lg</code> &rarr; <code>avatar avatar--xs [--initials]</code>; <code>aif-comment__author-link</code> &rarr; <code>card-title-link</code>; <code>aif-comment__avatar-link</code> &rarr; <code>card-image-link</code></td><td>alias at adoption</td></tr>
	</table></div>
	</main>
	<?php
}

/**
 * Modal — SPEC SHEET, rebuilt from scratch (operator 2026-07-05: THE form
 * modal — AI Guild Fluent-Forms modals; the invented close-less dialog is
 * WITHDRAWN). The modal body composes the DS FORM SYSTEM exactly as the
 * fluent-forms-override mapping renders it in production. Static demo traps
 * position:fixed inside a transformed box; the live trigger runs
 * js/components/modal.js.
 */
function aifds_sg_item_modal() {
	// One renderer: THE form modal — title + DS form (fields, consent, submit).
	$modal = function ( $id, $live = true, $long = false ) {
		ob_start(); ?>
		<div id="<?php echo esc_attr( $id ); ?>" class="modal" <?php echo $live ? 'aria-hidden="true"' : 'style="opacity:1; visibility:visible;"'; ?>>
			<div class="modal__overlay" <?php echo $live ? 'data-close-modal' : ''; ?>></div>
			<div class="modal__container" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $id ); ?>-title">
				<button class="modal__close" <?php echo $live ? 'data-close-modal' : ''; ?> aria-label="Close"><?php echo aifds_icon( 'close', array( 'size' => 24 ) ); ?></button>
				<div class="modal__content">
					<h2 id="<?php echo esc_attr( $id ); ?>-title" class="modal__title">Title slot &mdash; the form pitch</h2>
					<p class="modal__text">Text slot &mdash; the body voice: what this form does and why the reader should fill it, one or two sentences.</p>
					<div class="info-box info-box--info info-box--small"><p>Info-box slot &mdash; any DS info box composes inside the modal; the CONDENSED <code>--small</code> variant fits the form density (operator ruling).</p></div>
					<div class="form-stack">
						<div class="form-group">
							<div class="form-label-row"><label class="form-label" for="<?php echo esc_attr( $id ); ?>-name">Name slot</label><span class="form-mandatory">*</span></div>
							<div class="form-control-wrapper"><input id="<?php echo esc_attr( $id ); ?>-name" type="text" class="form-control" placeholder="Jane Novak"></div>
						</div>
						<div class="form-group">
							<div class="form-label-row"><label class="form-label" for="<?php echo esc_attr( $id ); ?>-email">Email slot</label><span class="form-mandatory">*</span></div>
							<div class="form-control-wrapper"><input id="<?php echo esc_attr( $id ); ?>-email" type="email" class="form-control" placeholder="you@example.com"></div>
						</div>
						<div class="form-group">
							<div class="form-label-row"><label class="form-label" for="<?php echo esc_attr( $id ); ?>-note">Message slot</label></div>
							<div class="form-control-wrapper"><textarea id="<?php echo esc_attr( $id ); ?>-note" class="form-control" rows="3" placeholder="The DS textarea"></textarea></div>
						</div>
						<?php if ( $long ) : for ( $i = 1; $i <= 6; $i++ ) : ?>
						<div class="form-group">
							<div class="form-label-row"><label class="form-label" for="<?php echo esc_attr( $id ); ?>-extra-<?php echo (int) $i; ?>">Extra field slot <?php echo (int) $i; ?></label></div>
							<div class="form-control-wrapper"><input id="<?php echo esc_attr( $id ); ?>-extra-<?php echo (int) $i; ?>" type="text" class="form-control" placeholder="A long reservation form outgrows the viewport"></div>
						</div>
						<?php endfor; endif; ?>
						<label class="selection-item selection-item--checkbox selection-item--consent">
							<input type="checkbox" class="selection-input">
							<div class="selection-control"></div>
							<div class="selection-content"><span class="selection-label">Consent slot &mdash; I agree to the <a href="#">processing of personal data</a>.</span></div>
						</label>
						<button type="button" class="btn btn--primary btn--md" <?php echo $live ? 'data-close-modal' : ''; ?>>Submit slot</button>
					</div>
				</div>
			</div>
		</div>
		<?php return ob_get_clean();
	};
	?>
	<main class="sg-main">
	<h1 class="sg-page-h">Modal</h1>
	<div class="sg-note"><b>THE FORM MODAL</b> &mdash; a centered box on a dark scrim carrying a form. This is
		how AI Guild ships every modal (reservation forms 4/5/7, the inactive-course contact form) and AIF its
		registration modal: <b>Fluent Forms inside</b>, which the themes map onto the DS form system
		(fluent-forms-override) &mdash; so the specimen composes the DS FORMS COMPONENT directly. Engine =
		the production a11y contract (<code>aria-hidden</code> flip, body scroll lock, ESC, overlay +
		<code>data-close-modal</code>, focus, Fluent-Forms auto-close), generalized in
		<code>js/components/modal.js</code>. Mobile = the reservation modal&rsquo;s <b>dvh full-screen sheet</b>
		(iOS visible-viewport fix + <code>overscroll-behavior: contain</code>). Scrim = <code>color-mix</code>
		on <code>--black</code> at the harvested 70%.</div>

	<h2 class="sg-section-h">1 &middot; Anatomy + LIVE behavior</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Slot</th><th>Required</th><th>Contract</th></tr>
		<tr><td><code>__overlay</code></td><td>required</td><td>the scrim; <code>data-close-modal</code> &mdash; click closes</td></tr>
		<tr><td><code>__container</code></td><td>required</td><td>the box: <code>--bg</code> fill, sharp corners, 560px, 90vh internal scroll; <code>role="dialog" aria-modal</code></td></tr>
		<tr><td><code>__close</code></td><td>required</td><td>40px ghost hit, <code>close</code> icon 24, <code>--text-secondary &rarr; --text</code> hover</td></tr>
		<tr><td><code>__title</code></td><td>required</td><td>heading-md voice; <code>aria-labelledby</code> target; opener may override via <code>data-modal-title</code> (the registration modal&rsquo;s per-event title)</td></tr>
		<tr><td>form body</td><td>required</td><td>THE DS FORM SYSTEM: <code>.form-group</code> fields, consent <code>.selection-item--consent</code>, <code>.btn--primary</code> submit &mdash; Fluent Forms markup maps onto these at adoption</td></tr>
		<tr><td>trigger</td><td>&mdash;</td><td><code>data-modal-open="&lt;modal id&gt;"</code>; or <code>window.aifdsModal.open/close(id)</code></td></tr>
	</table></div>
	<div style="display:flex; gap:16px; flex-wrap:wrap;">
		<button type="button" class="btn btn--md btn--primary" data-modal-open="sg-modal-live">Open the form modal</button>
		<button type="button" class="btn btn--md btn--secondary" data-modal-open="sg-modal-live" data-modal-title="Title slot &mdash; overridden by this opener">Open with a title override</button>
		<button type="button" class="btn btn--md btn--tertiary" data-modal-open="sg-modal-long">Open scrollable modal</button>
	</div>
	<?php echo $modal( 'sg-modal-live', true ); ?>
	<?php echo $modal( 'sg-modal-long', true, true ); ?>

	<h2 class="sg-section-h" style="margin-top:32px;">2 &middot; Static render &mdash; the form modal on the scrim</h2>
	<div class="sg-note">Production-aligned box: 560px CONTENT + 48px padding (656px total, live-measured); title,
		text, info box and the form stack inside. The frame below is a <b>stand-in short viewport</b>: when the
		form outgrows it the BOX scrolls internally (<code>overflow-y:auto</code>) &mdash; in production the same
		engine caps the box at 90vh. Try the live &ldquo;Open scrollable modal&rdquo; button on a short window.</div>
	<div id="modal-static-default" data-sg-overlap-ok style="position:relative; transform:translateZ(0); height:520px; overflow:hidden;">
		<?php /* static display artifact: inline visibility, NO aria-hidden hook (the live engine queries it) */ ?>
		<?php echo $modal( 'sg-modal-static', false ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">3 &middot; Behavior contract (js/components/modal.js)</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Event</th><th>Behavior</th></tr>
		<tr><td>opener click</td><td><code>aria-hidden=false</code> + <code>body.modal-open</code> lock + focus the first field; optional title override</td></tr>
		<tr><td>ESC / overlay / <code>data-close-modal</code></td><td>close; body unlocks when no modal stays open</td></tr>
		<tr><td>Fluent Forms success</td><td>auto-close after 2s (harvested; jQuery-guarded)</td></tr>
		<tr><td>&le;599 viewport</td><td>full-screen dvh sheet, 64px top clearance, overscroll contained</td></tr>
		<tr><td>theme wiring</td><td>hidden-field population + AIG&rsquo;s 4/5/7 form switching stay THEME JS on <code>window.aifdsModal</code></td></tr>
	</table></div>

	<h2 class="sg-section-h" style="margin-top:32px;">4 &middot; Consumer mappings</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Production copy</th><th>Fate at adoption</th></tr>
		<tr><td>AIG <code>.reservation-modal</code> (Fluent forms 4/5/7)</td><td>alias classes; the form-switch engine stays THEME JS on the DS open/close</td></tr>
		<tr><td>AIG inline contact modal (&times;3 templates)</td><td>markup already <code>.modal</code>; the naive display-toggle script dies &mdash; <code>data-modal-open</code> replaces <code>data-open-contact-form</code>, gains aria/ESC/lock</td></tr>
		<tr><td>AIF <code>.registration-modal</code> (Fluent Form + data-application/price)</td><td>alias classes; hidden-field wiring stays THEME on the open call</td></tr>
		<tr><td>AIF newsletter/consent page one-offs</td><td><b>NOT this component</b> &mdash; page-specific, not ported</td></tr>
	</table></div>
	</main>
	<?php
}

/**
 * Sticky bar — SPEC SHEET. One fixed-bottom primitive; type compositions
 * (email / button / save), the chatbot axis, and THE SAMPLER (surface from
 * the rendered background). Static frames trap position:fixed in transformed
 * boxes; the LIVE bar at the bottom runs js/components/sticky-bar.js.
 */
function aifds_sg_item_sticky_bar() {
	// One renderer: $type = 'email'|'button'; $static bars render visible with
	// no engine hooks.
	$bar = function ( $type, $classes = '', $attrs = '', $static = true ) {
		ob_start(); ?>
		<div class="sticky-bar sticky-bar--<?php echo esc_attr( $type ); ?><?php echo $classes ? ' ' . esc_attr( $classes ) : ''; ?><?php echo $static ? ' sticky-bar--visible' : ''; ?>"<?php echo $static ? '' : ' data-sticky-cta aria-hidden="true"'; ?><?php echo $attrs; // phpcs:ignore ?>>
			<div class="sticky-bar__inner">
				<?php if ( 'email' === $type ) : ?>
				<div class="sticky-bar__pitch"><p class="sticky-bar__benefit">The week&rsquo;s most important AI news &mdash; no fluff.</p><p class="sticky-bar__consent">Consent slot &mdash; by clicking &ldquo;Subscribe&rdquo; you agree to the <a href="#">processing of personal data</a>.</p></div>
				<div class="sticky-bar__form form-scale-small">
					<div class="input-pair">
						<div class="form-control-wrapper"><input type="email" class="form-control" placeholder="Your e-mail"></div>
						<button type="button" class="btn btn--primary">Subscribe slot</button>
					</div>
				</div>
				<a href="#newsletter-signup" class="btn btn--primary sticky-bar__btn-mobile">Subscribe slot</a>
				<?php else : ?>
				<span class="sticky-bar__meta">Meta slot &mdash; next cohort 12. 8. 2026</span>
				<a href="#" class="btn btn--primary"><?php echo aifds_icon( 'course', array( 'size' => 20 ) ); ?> CTA slot</a>
				<?php endif; ?>
			</div>
		</div>
		<?php return ob_get_clean();
	};
	?>
	<main class="sg-main">
	<h1 class="sg-page-h">Sticky bar</h1>
	<div class="sg-note">ONE fixed-bottom primitive &mdash; SIX production instances collapse
		(<code>docs/proposals/STICKY-BAR-MAP.md</code>). <b>TYPE</b> is composition: <code>--email</code>
		(consent pitch + capture form) &middot; <code>--button</code> (meta + one CTA, the course bar) &middot;
		the preferences save bar composes freely. <b>CHATBOT axis</b>: <code>--chatbot-clear</code> leaves
		100px for the bubble. <b>SURFACE IS THE SAMPLER&rsquo;S JOB</b> (generalized from the AIG course
		detail): <code>[data-sticky-sample]</code> reads the RENDERED background behind the bar and toggles
		<code>.section-dark</code> ON THE BAR &mdash; roles re-skin everything; sections need NO registration,
		only opaque backgrounds. <b>MOBILE</b> (&le;1023): the email bar collapses to one full-width anchor
		button; the button bar drops its meta. Skin canon = the slim light article bar (operator-ruled in
		production: 40px controls, quiet 1px edge). Shadow = the NEW <code>--shadow-up</code>, visible-only
		(the phantom-glow fix).</div>

	<h2 class="sg-section-h">1 &middot; Anatomy &mdash; slots + engine attributes</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Slot / attr</th><th>Required</th><th>Contract</th></tr>
		<tr><td><code>__inner</code></td><td>required</td><td>container-max flex row, nowrap; 6px vertical rhythm (slim, operator-ruled)</td></tr>
		<tr><td><code>__pitch</code> &rsaquo; <code>__consent</code></td><td>email</td><td>caption-size secondary text, links ride the global chain; shrinks (min-width:0), may wrap two lines</td></tr>
		<tr><td><code>__form</code></td><td>email</td><td>the DS capture form (input-pair); 40px slim controls (calibrated)</td></tr>
		<tr><td><code>__btn-mobile</code></td><td>email</td><td>hidden &ge;1024; the ONE full-width anchor button below</td></tr>
		<tr><td><code>__meta</code></td><td>button</td><td>accent bold body-size text; hides &le;1023</td></tr>
		<tr><td><code>--chatbot-clear</code></td><td>axis</td><td>100px right clearance (desktop only)</td></tr>
		<tr><td><code>data-show-anchor / data-show-gate=&quot;top&quot; / data-hide-anchor</code></td><td>engine</td><td>anchor geometry: 50%-passed gate (articles) or fully-above gate (course hero); hide at anchor top</td></tr>
		<tr><td><code>data-show-fraction / data-hide-fraction</code></td><td>engine</td><td>page geometry (the landing mode): show past f&times;viewport, hide f viewports from the end</td></tr>
		<tr><td><code>data-sticky-sample</code></td><td>engine</td><td>THE SAMPLER &mdash; auto <code>.section-dark</code> from the rendered background; re-samples only while visible</td></tr>
		<tr><td><code>data-suppress-key</code></td><td>engine</td><td>localStorage suppression (production: <code>aif-subscribed</code>)</td></tr>
	</table></div>

	<h2 class="sg-section-h" style="margin-top:32px;">2 &middot; Email type &mdash; light skin + chatbot clearance (static)</h2>
	<div id="sticky-static-email" style="position:relative; transform:translateZ(0); height:220px; overflow:hidden; background:var(--bg-alt); border:1px solid var(--border);">
		<p class="meta" style="padding:16px;">a stand-in page &mdash; the bar docks to this frame&rsquo;s bottom edge; the BUBBLE owns the corner the clearance protects</p>
		<?php echo $bar( 'email', 'sticky-bar--chatbot-clear' ); ?>
		<div class="sg-fake-bubble" aria-hidden="true" style="position:absolute; right:16px; bottom:12px; width:56px; height:56px; border-radius:50%; background:var(--brand); color:var(--text-on-brand); display:flex; align-items:center; justify-content:center; z-index:41; box-shadow:var(--shadow-md); font-size:22px;">&#128172;</div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">2b &middot; Chatbot axis &mdash; the SAME bar with and without clearance</h2>
	<div class="sg-note">ONE variable changes: <code>--chatbot-clear</code>. Without it the form runs to the right
		edge (no bubble on the page); with it the inner keeps 100px free &mdash; on EVERY viewport (harvested:
		&ldquo;right: chatbot clearance, same as desktop&rdquo;).</div>
	<div id="sticky-axis-off" style="position:relative; transform:translateZ(0); height:140px; overflow:hidden; background:var(--bg-alt); border:1px solid var(--border);">
		<p class="meta" style="padding:12px 16px;">chatbot OFF &mdash; form reaches the edge</p>
		<?php echo $bar( 'email' ); ?>
	</div>
	<div id="sticky-axis-on" style="position:relative; transform:translateZ(0); height:140px; overflow:hidden; background:var(--bg-alt); border:1px solid var(--border); margin-top:16px;">
		<p class="meta" style="padding:12px 16px;">chatbot ON &mdash; 100px stays free for the bubble</p>
		<?php echo $bar( 'email', 'sticky-bar--chatbot-clear' ); ?>
		<div class="sg-fake-bubble" aria-hidden="true" style="position:absolute; right:16px; bottom:12px; width:56px; height:56px; border-radius:50%; background:var(--brand); color:var(--text-on-brand); display:flex; align-items:center; justify-content:center; z-index:41; box-shadow:var(--shadow-md); font-size:22px;">&#128172;</div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">3 &middot; Button type &mdash; over a dark page (static; the sampler would set this class)</h2>
	<div id="sticky-static-button" style="position:relative; transform:translateZ(0); height:220px; overflow:hidden;">
		<div class="section-dark" style="position:absolute; inset:0; padding:16px;"><p class="meta" style="color:var(--text-tertiary);">a dark section behind the bar &rarr; the sampler flips the bar&rsquo;s scope</p></div>
		<?php echo $bar( 'button', 'section-dark sticky-bar--chatbot-clear' ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">3b &middot; Mobile (&le;1023) &mdash; the chatbot axis changes the SHAPE</h2>
	<div class="sg-note">REPLICA frames (the real rules live in the <code>&le;1023</code> media query &mdash; a
		desktop page can&rsquo;t trigger them in a narrow box; the gate asserts the true state at 390px).
		<b>Without the bubble</b> the email bar collapses to ONE full-width anchor button (AIF harvested).
		<b>With <code>--chatbot-clear</code></b> the clearance survives and the button stays natural width,
		left-aligned at the 14px submit size &mdash; the bubble owns the bottom-right (AIG harvested:
		&ldquo;NOT full width &mdash; the chatbot bubble lives bottom-right&rdquo;).</div>
	<style>
		/* demo-only replica of the <=1023 rules, scoped to the two frames below */
		.sg-sticky-mobile .sticky-bar__pitch, .sg-sticky-mobile .sticky-bar__form { display: none; }
		.sg-sticky-mobile .sticky-bar__btn-mobile { display: inline-flex; flex: 1 1 100%; width: 100%; }
		.sg-sticky-mobile .sticky-bar__inner { padding: var(--spacing-6) var(--spacing-16); gap: 0; }
		.sg-sticky-mobile .sticky-bar--chatbot-clear .sticky-bar__inner { padding-right: 100px; justify-content: flex-start; }
		.sg-sticky-mobile .sticky-bar--chatbot-clear .sticky-bar__btn-mobile { flex: 0 1 auto; width: auto; }
	</style>
	<div style="display:flex; gap:24px; flex-wrap:wrap;">
		<div class="sg-sticky-mobile" style="position:relative; transform:translateZ(0); width:390px; height:180px; overflow:hidden; background:var(--bg-alt); border:1px solid var(--border);">
			<p class="meta" style="padding:12px 16px;">no chatbot &rarr; FULL-WIDTH button</p>
			<?php echo $bar( 'email' ); ?>
		</div>
		<div class="sg-sticky-mobile" style="position:relative; transform:translateZ(0); width:390px; height:180px; overflow:hidden; background:var(--bg-alt); border:1px solid var(--border);">
			<p class="meta" style="padding:12px 16px;">chatbot &rarr; natural button, bubble corner FREE</p>
			<?php echo $bar( 'email', 'sticky-bar--chatbot-clear' ); ?>
			<div class="sg-fake-bubble" aria-hidden="true" style="position:absolute; right:16px; bottom:12px; width:56px; height:56px; border-radius:50%; background:var(--brand); color:var(--text-on-brand); display:flex; align-items:center; justify-content:center; z-index:41; box-shadow:var(--shadow-md); font-size:22px;">&#128172;</div>
		</div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">4 &middot; LIVE &mdash; scroll this page: fraction show + THE SAMPLER</h2>
	<div class="sg-note">The bar below is LIVE (<code>data-show-fraction="0.15"</code> +
		<code>data-sticky-sample</code> + <code>--chatbot-clear</code>): scroll down and it slides up; keep
		scrolling until it rests over the DARK band &mdash; it flips to <code>.section-dark</code>; past the
		band it flips back. On a &le;1023 window the email composition is the single full-width button.</div>
	<div id="sticky-live-light1" style="height:420px; background:var(--bg-alt); display:flex; align-items:center; justify-content:center;"><span class="meta">light region &mdash; the live bar stays light here</span></div>
	<div id="sticky-live-dark" class="section-dark" style="height:820px; display:flex; align-items:center; justify-content:center;"><span class="meta" style="color:var(--text-tertiary);">DARK BAND &mdash; when the bar floats over this, the sampler flips it dark</span></div>
	<div id="sticky-live-light2" style="height:820px; background:var(--bg-alt); display:flex; align-items:center; justify-content:center;"><span class="meta">light again &mdash; the bar flips back</span></div>
	<?php echo $bar( 'email', 'sticky-bar--chatbot-clear', ' data-show-fraction="0.15" data-sticky-sample', false ); ?>

	<h2 class="sg-section-h" style="margin-top:32px;">5 &middot; Consumer mappings &mdash; the six production instances</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Production instance</th><th>DS composition</th></tr>
		<tr><td>/newsletter landing <code>.lp-sticky</code> (dark, 2px stripe, inline script, DB markup)</td><td><code>--email</code> + fractions (0.8 / 1.5); the tall dark skin + stripe DIE &mdash; adoption needs the <code>landing_html</code> DB edit</td></tr>
		<tr><td>AIF article bar</td><td><code>--email</code> + <code>data-show-anchor=".aif-article-body"</code> + suppression; guests-only stays a PHP include guard</td></tr>
		<tr><td>AIG article bar</td><td>same + <code>data-hide-anchor=".kurz-more-courses"</code> + <code>--chatbot-clear</code></td></tr>
		<tr><td>AIG positions bars</td><td><code>--email</code>, always-on (empty show-anchor), hide at the footer newsletter</td></tr>
		<tr><td>AIG course <code>.sticky-cta</code> (the sampler original)</td><td><code>--button</code> + <code>data-show-anchor=".hero-card__actions" data-show-gate="top" data-hide-anchor="#terminy" data-sticky-sample</code> + <code>--chatbot-clear</code></td></tr>
		<tr><td>AIF preferences save bar</td><td>the primitive + a status span and a submit button &mdash; form-state driven (no scroll engine), theme JS toggles <code>--visible</code></td></tr>
	</table></div>
	</main>
	<?php
}

/**
 * Info bar — SPEC SHEET. The statement stripe: the first benefits-family
 * member resolved (operator V2 verdict, sandbox-judged). Surface-riding
 * --raised band, 4px brand rules at the perex indent, bold statements,
 * 1..n items.
 */
function aifds_sg_item_info_bar() {
	$statements = array(
		'Statement slot &mdash; a single bold claim, one or two lines.',
		'Statement slot &mdash; every item flexes equally in the row.',
		'Statement slot &mdash; the rule and indent match the blockquote grammar.',
	);
	$four = array_merge( $statements, array( 'Statement slot &mdash; the fourth item (the /newsletter case) wraps or compresses gracefully.' ) );
	$bar = function ( $items ) {
		ob_start(); ?>
		<div class="info-bar"><div class="info-bar__wrapper">
			<?php foreach ( $items as $t ) : ?>
			<div class="info-bar__item"><p><?php echo $t; // phpcs:ignore ?></p></div>
			<?php endforeach; ?>
		</div></div>
		<?php return ob_get_clean();
	};
	?>
	<main class="sg-main">
	<h1 class="sg-page-h">Info bar</h1>
	<div class="sg-note">THE STATEMENT STRIPE &mdash; a full-width band of 1..n bold claims (operator V2
		verdict, 2026-07-06; the first benefits-family member resolved). <b>Blockquote-aligned</b>: 4px
		<code>--brand</code> left rule with the text landing at <code>--flow-indent</code> &mdash; the exact
		perex indent. <b>Surface-riding</b>: the band fill is <code>--raised</code> (one step off the host
		surface) &mdash; on a dark section that IS production&rsquo;s inverse-secondary stripe; on a light
		section it resolves the AIG homepage light-flip experiment for free. Statements = description font
		(Space Grotesk), body-md, BOLD.</div>

	<h2 class="sg-section-h">1 &middot; Anatomy &mdash; on a dark host (the production home)</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Slot</th><th>Required</th><th>Contract</th></tr>
		<tr><td><code>.info-bar</code></td><td>required</td><td><code>--raised</code> band, <code>--spacing-40</code> vertical rhythm; the HOST section picks the world (scope class on the consumer, never on the bar)</td></tr>
		<tr><td><code>__wrapper</code></td><td>required</td><td>container-max row, wraps; stacks &le;767</td></tr>
		<tr><td><code>__item</code> &rsaquo; <code>p</code></td><td>1..n</td><td>4px <code>--brand</code> rule INSIDE the perex indent; description font, body-md, <b>bold</b>, <code>--text</code>; 240px wrap floor</td></tr>
	</table></div>
	<div class="section-dark" id="infobar-dark" style="padding:32px 0;">
		<?php echo $bar( $statements ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">2 &middot; 1..n axis &mdash; FOUR statements (the /newsletter case)</h2>
	<div class="section-dark" id="infobar-four" style="padding:32px 0;">
		<?php echo $bar( $four ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">3 &middot; Surface axis &mdash; the SAME bar on a light host (the resolved AIG experiment)</h2>
	<div id="infobar-light" style="padding:32px 0;">
		<?php echo $bar( $statements ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">4 &middot; Consumer mappings</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Production instance</th><th>Fate at adoption</th></tr>
		<tr><td>AIF front-page <code>.info-bar</code> (dark stripe, 2px regular)</td><td>markup keeps its classes; the V2 skin (4px bold, perex indent) replaces the page.css block</td></tr>
		<tr><td>AIG <code>.homepage-infobar</code> (1:1 mirror + light-flip experiment)</td><td>aliases to <code>.info-bar__*</code>; the light-flip overrides DIE &mdash; the bar is surface-riding, the section picks light or dark</td></tr>
		<tr><td>Benefits-family rest (cert-cards, blurbs, lp-what, footer)</td><td>NOT this component &mdash; still awaiting the family architecture verdict (BENEFITS-FAMILY-MAP)</td></tr>
	</table></div>
	</main>
	<?php
}

/**
 * Footer — SPEC SHEET. The closing chrome from byte-identical twins
 * (FOOTER-MAP): band stack (optional newsletter dark-2 stripe + partners +
 * canon columns + bottom bar + legal), interior-only dividers, the
 * arrow-link idiom, the chatbot clearance axis.
 */
function aifds_sg_item_footer() {
	$columns = function () {
		ob_start(); ?>
		<div class="stack-grid" style="--stack-cols: 3;">
			<div class="blurb">
				<p class="blurb__eyebrow">Contact slot</p>
				<p class="blurb__text blurb__text--sm">Rich-text slot — a sentence with an inline <a href="#">link</a> riding the dark link idiom.</p>
			</div>
			<div class="blurb">
				<p class="blurb__eyebrow">Link column slot</p>
				<a href="#" class="footer__subtle-link"><span>Arrow link one</span></a>
				<a href="#" class="footer__subtle-link"><span>Arrow link two</span></a>
				<a href="#" class="footer__subtle-link"><span>Arrow link three</span></a>
			</div>
			<div class="blurb">
				<p class="blurb__eyebrow">Second column slot</p>
				<a href="#" class="footer__subtle-link"><span>Sister project</span></a>
				<a href="#" class="footer__subtle-link"><span>Another project</span></a>
			</div>
		</div>
		<?php return ob_get_clean();
	};
	$bottom_legal = function () {
		ob_start(); ?>
		<div class="footer__bottom-bar">
			<a href="#top" class="footer__logo-link" aria-label="Brand - Back to top"><span class="logo-placeholder">LOGO</span></a>
			<div class="footer__social-icons">
				<a href="#" class="footer__social-link" aria-label="LinkedIn"><?php echo aifds_icon( 'linkedin', array( 'size' => 16 ) ); ?></a>
				<a href="#" class="footer__social-link" aria-label="Facebook"><?php echo aifds_icon( 'facebook', array( 'size' => 16 ) ); ?></a>
				<a href="#" class="footer__social-link" aria-label="RSS"><?php echo aifds_icon( 'rss', array( 'size' => 16 ) ); ?></a>
			</div>
		</div>
		<div class="footer__legal-section">
			<div class="footer__divider"></div>
			<p class="footer__legal">&copy;brand.cz | <a href="#" class="footer__legal-link">Impressum</a> | <a href="#" class="footer__legal-link">Privacy</a> | <a href="#" class="footer__legal-link">Credits</a></p>
		</div>
		<?php return ob_get_clean();
	};
	?>
	<main class="sg-main">
	<h1 class="sg-page-h">Footer</h1>
	<div class="sg-note"><b>THE CLOSING CHROME</b> (<b>FOOTER-MAP</b>, byte-identical twins): a stack of
		BANDS — optional <b>newsletter capture</b> (full-bleed <code>dark-2</code> stripe; AIG renders it,
		AIF&rsquo;s capture stays page content, operator) &middot; optional <b>partners</b> &middot;
		<b>columns = THE CANON</b> (<code>.stack-grid</code> + <code>.blurb</code> &mdash; footer-blurb dies
		at adoption) &middot; <b>bottom bar</b> &middot; <b>legal</b>. The shell paints NOTHING &mdash; markup
		carries <code>.footer.section-dark</code> (live on both sites; the dark-1 <code>.footer</code> alias
		DROPPED from the scope map). Dividers are interior-only by production&rsquo;s own law. GM: legal-link
		hover KEEPS its underline (link law); partners opacity-stack + <code>zoom</code> hacks die.</div>

	<h2 class="sg-section-h">1 &middot; Full composition &mdash; every slot on (the AIG shape) + chatbot clearance</h2>
	<footer class="footer footer--chatbot-clear section-dark" id="footer-full" style="margin-top:48px;">
		<section class="footer__newsletter-section" aria-label="Newsletter">
			<div class="footer__newsletter-row">
				<p class="footer__newsletter-headline">Headline slot &mdash; one sentence pitching the newsletter.</p>
				<div class="footer__newsletter-form-wrap">
					<form class="aif-ecomail-form aif-ecomail-form--footer-dark" novalidate onsubmit="return false;">
						<div class="mc4wp-form-fields">
							<div class="form-control-wrapper">
								<?php echo aifds_icon( 'arrow-right', array( 'size' => 18, 'class' => 'form-control-icon' ) ); ?>
								<input type="email" name="email" class="form-control" placeholder="Your e-mail" required autocomplete="email">
							</div>
							<button type="submit" class="btn btn--md btn--tertiary"><?php echo aifds_icon( 'send', array( 'size' => 18 ) ); ?><span>Subscribe</span></button>
						</div>
						<p class="mc4wp-consent-note">By clicking Subscribe you agree to the <a href="#">processing of personal data</a>.</p>
					</form>
				</div>
			</div>
		</section>
		<div class="footer__inner">
			<div class="footer__partners">
				<p class="footer__label">Partners label slot</p>
				<div class="footer__partners-grid">
					<span style="display:inline-block;width:120px;height:44px;background:var(--raised);"></span>
					<span style="display:inline-block;width:90px;height:44px;background:var(--raised);"></span>
					<span style="display:inline-block;width:140px;height:44px;background:var(--raised);"></span>
				</div>
			</div>
			<div class="footer__divider"></div>
			<?php echo $columns(); ?>
			<?php echo $bottom_legal(); ?>
		</div>
	</footer>

	<h2 class="sg-section-h" style="margin-top:32px;">2 &middot; Minimal composition &mdash; optional slots off (the AIF shape)</h2>
	<footer class="footer section-dark" id="footer-minimal">
		<div class="footer__inner">
			<?php echo $columns(); ?>
			<?php echo $bottom_legal(); ?>
		</div>
	</footer>

	<h2 class="sg-section-h" style="margin-top:32px;">3 &middot; Slots &amp; knobs</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Piece</th><th>Contract</th></tr>
		<tr><td>shell <code>.footer</code></td><td>paints nothing; markup carries <code>.section-dark</code>; knobs <code>--footer-pad-top/-bottom</code> (80/24 &rarr; 48/24 &rarr; 40/16)</td></tr>
		<tr><td><code>--chatbot-clear</code></td><td>bottom clearance for the bubble (sticky-bar precedent): 56 desktop &middot; 80 mobile</td></tr>
		<tr><td>newsletter band</td><td>OPTIONAL; <code>dark-2</code> scope class = the surface; negative pull auto-syncs to the pad-top knob; hosts the shipped <code>aif-ecomail-form--footer-dark</code> composition; row re-caps at <code>--container-max</code></td></tr>
		<tr><td>partners band</td><td>OPTIONAL; mono label + 44px logo row at ONE 0.5 dim step (the harvested opacity stack + <code>zoom</code> died)</td></tr>
		<tr><td>columns</td><td>THE CANON: <code>.stack-grid</code> (80 rhythm; 2-col 768&ndash;1023) &rsaquo; <code>.blurb</code> &mdash; eyebrow QUATERNARY (operator; palette-direct <code>--dark-400</code> GM) + <code>__text--sm</code> or a <code>.footer__subtle-link</code> stack</td></tr>
		<tr><td><code>.footer__subtle-link</code></td><td>the arrow-link idiom: &ldquo;&rarr;&nbsp;&rdquo; prefix never underlines, the <code>&lt;span&gt;</code> label does; <code>--link</code> &rarr; <code>--link-hover</code></td></tr>
		<tr><td>bottom bar</td><td>16px logo <code>&rarr; #top</code> (instant jump, harvested) | 16px socials, gap 16 (GM from raw 20), hover fade 0.7</td></tr>
		<tr><td>legal band</td><td>THE divider (2px <code>--raised</code>) + right-aligned caption in <code>--text-tertiary</code>; links underline ALWAYS (hover = color shift to <code>--text-secondary</code> only &mdash; link law)</td></tr>
	</table></div>

	<h2 class="sg-section-h" style="margin-top:32px;">4 &middot; Adoption mappings</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Production</th><th>Fate</th></tr>
		<tr><td><code>.footer &gt; .container</code></td><td>&rarr; <code>.footer__inner</code> (the DS owns its chrome span; no theme .container dependency)</td></tr>
		<tr><td><code>.footer-blurb / __title / __body / __description</code></td><td>DIE &rarr; <code>.blurb / __eyebrow / __text--sm</code> (the benefits verdict)</td></tr>
		<tr><td>dark-1 <code>.footer</code> scope alias</td><td>DROPPED &mdash; live markup already emits <code>.section-dark</code> (operator; persona precedent)</td></tr>
		<tr><td>AIG <code>btn--lg btn--primary-inverted</code> submit</td><td>&rarr; <code>.btn--md .btn--tertiary</code> (the standing newsletter-capture ruling)</td></tr>
		<tr><td>Legal-link <code>!important</code> pair</td><td>DIES &rarr; the exclusion chain knows <code>.footer__legal-link</code></td></tr>
		<tr><td>AIF <code>.newsletter-cta</code> (blue page band)</td><td>NOT footer anatomy &mdash; its own future distillation row (operator)</td></tr>
		<tr><td>Footer types (default/requal)</td><td>data-level only (ACF prefix swap) &mdash; ONE DS footer</td></tr>
		<tr><td>Partners <code>zoom:.5</code> + opacity stack, raw <code>gap:20</code>, faux-bold mono 700</td><td>hacks die at adoption; mono 700 needs the font loadout extended (theme fix)</td></tr>
	</table></div>
	</main>
	<?php
}

/**
 * Header — SPEC SHEET. Distilled from byte-identical theme twins
 * (HEADER-MAP): surface-riding chrome (surfaces replace --light/--dark),
 * slots not content, two modes (fixed+shrink / overlay), a11y-upgraded
 * dropdown grammar, dual-path reading progress.
 */
function aifds_sg_item_header() {
	$header = function ( $s = array() ) {
		$s = array_merge( array(
			'scope'    => 'surface-support', // or 'section-dark'
			'scrolled' => false,
			'cta'      => true,
			'lang'     => true,
			'lang_open' => false,
			'progress' => null,   // null off | float 0..1 forced fill
			'active'   => 1,      // which nav item carries --active
		), $s );
		$items = array( 'Articles', 'Signals', 'Events', 'About' );
		ob_start(); ?>
		<header class="main-header <?php echo esc_attr( $s['scope'] ); ?><?php echo $s['scrolled'] ? ' main-header--scrolled' : ''; ?>">
			<div class="main-header__inner">
				<a href="#" class="site-logo" aria-label="Brand — Home">
					<span class="logo-placeholder">LOGO</span>
				</a>
				<nav class="site-nav site-nav--desktop" aria-label="Main navigation">
					<?php foreach ( $items as $i => $label ) : ?>
					<a href="#" class="nav-item<?php echo $i === $s['active'] ? ' nav-item--active' : ''; ?>"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
					<?php if ( $s['cta'] ) : ?>
					<a href="#" class="btn btn--primary">Action slot</a>
					<?php endif; ?>
					<?php if ( $s['lang'] ) : ?>
					<div class="nav-item nav-item--has-dropdown<?php echo $s['lang_open'] ? ' nav-item--open' : ''; ?>">
						<button type="button" class="nav-item__trigger">
							CZ
							<span class="nav-item-icon"><?php echo aifds_icon( 'chevron-down', array( 'size' => 20 ) ); ?></span>
						</button>
						<div class="nav-dropdown">
							<a href="#" class="nav-dropdown-item nav-dropdown-item--active" lang="cs">CZ</a>
							<a href="#" class="nav-dropdown-item" lang="en">EN</a>
						</div>
					</div>
					<?php endif; ?>
				</nav>
				<nav class="site-nav site-nav--mobile" aria-label="Mobile navigation">
					<button class="burger-toggle" aria-label="Menu" aria-expanded="false">
						<svg viewBox="0 0 24 24"><path class="line-top" d="M3 5h18"/><path class="line-mid" d="M3 12h18"/><path class="line-bot" d="M3 19h18"/></svg>
					</button>
				</nav>
			</div>
			<?php if ( null !== $s['progress'] ) : ?>
			<div class="reading-progress" aria-hidden="true" style="display:block; --reading-progress: <?php echo esc_attr( $s['progress'] ); ?>;"></div>
			<?php endif; ?>
		</header>
		<?php return ob_get_clean();
	};
	?>
	<main class="sg-main">
	<h1 class="sg-page-h">Header</h1>
	<div class="sg-note"><b>DISTILLED FROM BYTE-IDENTICAL TWINS</b> (<a href="#">HEADER-MAP</a>): one
		skeleton, one shrink engine, one dropdown grammar, one burger, one dual-path progress bar.
		<b>Surfaces replace variants</b>: production&rsquo;s <code>--light/--dark</code> DIE &mdash; the header
		rides its scope (<code>.surface-support</code> = the brand band, <code>.section-dark</code> = dark
		chrome); rest bg = <code>--bg</code>, scrolled = <code>--raised</code>. <b>Slots, no content</b>: logo
		&middot; nav items (active + dropdown axes) &middot; action = a REAL <code>.btn</code> at the header
		rung (36px) &middot; burger &middot; overlay &middot; progress. <b>New roles</b> (operator):
		<code>--nav-active</code> (dimmed brand &mdash; kills #98C3D9/#63531B), <code>--progress-fill</code>
		(AIF lime / AIG magenta). <b>A11y GM</b>: dropdowns also open on <code>:focus-within</code> +
		button triggers get aria; scroll-lock is canon.</div>

	<style>
		/* spec-sheet frames only: contain position:fixed via the transform trap */
		.header-demo { position: relative; transform: translateZ(0); overflow: hidden; border: var(--stroke-1) solid var(--border); }
		.header-demo .main-header { position: absolute; }
		.header-demo--tall { height: 320px; }
	</style>

	<h2 class="sg-section-h">1 &middot; Anatomy &mdash; slots on the light band (<code>.surface-support</code>)</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Slot</th><th>Contract</th></tr>
		<tr><td>logo</td><td><code>.site-logo</code> &rsaquo; img 21px tall (17 scrolled, CALIBRATED brand-family parity) or <code>.logo-placeholder</code></td></tr>
		<tr><td>nav item</td><td><code>.nav-item</code> &mdash; SG 16/500, 36px rung; underline grows on hover (<code>--brand</code>); <code>--active</code> = persistent <code>--nav-active</code>, wakes to brand on hover</td></tr>
		<tr><td>action</td><td>a real <code>.btn</code> (any variant) at the header rung &mdash; production&rsquo;s bespoke <code>.nav-item--cta</code> dies</td></tr>
		<tr><td>dropdown</td><td><code>.nav-item--has-dropdown</code> &rsaquo; trigger + <code>.nav-dropdown</code> panel; 2 levels MAX (harvested ceiling); opens on hover / <code>:focus-within</code> / <code>--open</code></td></tr>
		<tr><td>burger</td><td><code>.burger-toggle</code> &mdash; 3 lines &rarr; perfect cross; <code>aria-expanded</code> wired by the engine</td></tr>
		<tr><td>progress</td><td><code>.reading-progress</code> &mdash; consumer renders it on article pages; visible only scrolled; fill <code>--progress-fill</code></td></tr>
	</table></div>
	<div class="header-demo" id="header-light" style="height:240px;">
		<?php echo $header( array() ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">2 &middot; Surface axis &mdash; the SAME markup on dark chrome</h2>
	<div class="header-demo" id="header-dark" style="height:240px;">
		<?php echo $header( array( 'scope' => 'section-dark' ) ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">3 &middot; THE SHRINK &mdash; scrolled state (80 &rarr; 56; light &rarr; WHITE + shadow, dark &rarr; raised + shadow; logo 21 &rarr; 17)</h2>
	<div class="sg-note">Engine: 50px threshold, <code>.main-header--scrolled</code>. The scrolled light chrome
		is <b>white</b> (<code>--bg-base</code>, operator) with <code>--shadow-xl</code>; dark keeps the raised
		lift + shadow. <b>The progress bar is an AXIS, not part of the state</b>: only article pages render
		<code>.reading-progress</code> (first demo, fill forced to 0.6 &mdash; dual engine:
		<code>animation-timeline: scroll(root)</code> zero-JS where supported, rAF fallback); every other page
		scrolls bare (second demo).</div>
	<div class="header-demo" id="header-scrolled" style="height:200px;">
		<?php echo $header( array( 'scrolled' => true, 'progress' => 0.6 ) ); ?>
	</div>
	<div class="header-demo" id="header-scrolled-dark" style="height:200px; margin-top:16px;">
		<?php echo $header( array( 'scope' => 'section-dark', 'scrolled' => true ) ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">4 &middot; Dropdown axis &mdash; forced open (language switcher composition)</h2>
	<div class="header-demo header-demo--tall" id="header-dropdown">
		<?php echo $header( array( 'lang_open' => true ) ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">5 &middot; Burger &mdash; rest / open (the perfect cross)</h2>
	<div style="display:flex; gap:32px; align-items:center;" id="burger-pair">
		<button class="burger-toggle" aria-label="Menu demo rest"><svg viewBox="0 0 24 24"><path class="line-top" d="M3 5h18"/><path class="line-mid" d="M3 12h18"/><path class="line-bot" d="M3 19h18"/></svg></button>
		<button class="burger-toggle burger-toggle--open" aria-label="Menu demo open"><svg viewBox="0 0 24 24"><path class="line-top" d="M3 5h18"/><path class="line-mid" d="M3 12h18"/><path class="line-bot" d="M3 19h18"/></svg></button>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">6 &middot; Mobile overlay &mdash; live engine in a frame (click the burger)</h2>
	<div class="sg-note">Full-screen overlay at z-99 &mdash; deliberately UNDER the opaque z-100 header.
		Overlay slots: items (+ always-expanded <code>--sub</code> level, harvested &mdash; no accordion) &middot;
		divider (<code>--nav-active</code>) &middot; action btn &middot; lang row pinned to the bottom
		(<code>margin-top: auto</code>). Scroll-lock (<code>body.menu-open</code>) is canon &mdash; page mode
		only, never in this frame.</div>
	<div class="header-demo" id="header-mobile" style="height:480px; max-width:390px;">
		<header class="main-header surface-support" style="--header-height:64px;">
			<div class="main-header__inner">
				<a href="#" class="site-logo"><span class="logo-placeholder">LOGO</span></a>
				<nav class="site-nav" style="display:flex;">
					<button class="burger-toggle" aria-label="Menu" aria-expanded="false">
						<svg viewBox="0 0 24 24"><path class="line-top" d="M3 5h18"/><path class="line-mid" d="M3 12h18"/><path class="line-bot" d="M3 19h18"/></svg>
					</button>
				</nav>
			</div>
		</header>
		<div class="mobile-menu-overlay surface-support" style="--header-height:64px; position:absolute;">
			<div class="mobile-menu-content">
				<a href="#" class="mobile-nav-item mobile-nav-item--active">Articles</a>
				<a href="#" class="mobile-nav-item">Signals</a>
				<a href="#" class="mobile-nav-item">Events</a>
				<a href="#" class="mobile-nav-item mobile-nav-item--sub"><?php echo aifds_icon( 'arrow-right', array( 'size' => 14 ) ); ?>Meetups</a>
				<div class="mobile-nav-divider"></div>
				<a href="#" class="btn btn--primary btn--md">Action slot</a>
				<div class="mobile-lang-row">
					<a href="#" class="mobile-lang-item mobile-lang-item--active" lang="cs">CZ</a>
					<a href="#" class="mobile-lang-item" lang="en">EN</a>
				</div>
			</div>
		</div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">7 &middot; Modes + adoption mappings</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Axis</th><th>Ruling</th></tr>
		<tr><td>Mode: default</td><td>fixed + THE SHRINK (50px &rarr; <code>--scrolled</code>); body compensates <code>padding-top: var(--header-height)</code>; never hides on scroll</td></tr>
		<tr><td>Mode: <code>--overlay</code></td><td>hero pages (AIG kurz/landing, AIF landing): absolute, scrolls away, engine bails</td></tr>
		<tr><td>Mobile transform</td><td>&le;1023: heights 64/52 via the knobs; desktop nav &rarr; burger + overlay; overlay <code>padding-top</code> auto-syncs</td></tr>
		<tr><td><code>main-header--light</code></td><td>DIES &rarr; <code>.surface-support</code> scope class</td></tr>
		<tr><td><code>main-header--dark</code></td><td>DIES &rarr; <code>.section-dark</code> scope class</td></tr>
		<tr><td><code>.nav-item--cta</code></td><td>DIES &rarr; real <code>.btn .btn--primary</code> at the header rung</td></tr>
		<tr><td>#98C3D9 / #63531B (&times;7)</td><td>DIE &rarr; <code>--nav-active</code> (palette <code>brand-muted</code>)</td></tr>
		<tr><td>Progress fill lime/magenta</td><td>&rarr; <code>--progress-fill</code> (palette <code>progress</code>, per-brand by ruling)</td></tr>
		<tr><td>Language switcher</td><td>an OPTIONAL slot composed from the dropdown grammar (desktop) + <code>.mobile-lang-row</code> (overlay bottom) &mdash; AIG can adopt by rendering it</td></tr>
		<tr><td>Avatar/account (AIF logged-in)</td><td>slot: any control in the actions position opening a <code>.nav-dropdown</code>; the <code>href="#"</code> stubs are theme bugs, not DS</td></tr>
		<tr><td>Dead code</td><td><code>.mobile-menu-close</code>, <code>.nav-lang-row</code>, <code>--mobile</code> modifier, 767px duplicate blocks &mdash; NOT ported</td></tr>
	</table></div>
	</main>
	<?php
}

/**
 * Blurb + stack grid — SPEC SHEET. The benefits family resolved: ONE content
 * component (slots on/off, closed rungs) + ONE universal stacking layout
 * (THE PIZZA LAW separators). The box = a consumer wrapper (crust + fill +
 * bleed) — demonstrated, not a component.
 */
function aifds_sg_item_blurb() {
	$blurb = function ( $s = array() ) {
		$s = array_merge( array(
			'icon'     => false,
			'eyebrow'  => null,   // null = off | '' = auto-number | 'Label'
			'headline' => 'Headline slot',
			'h'        => '',     // '' sm | 'md' | 'lg'
			'text'     => 'Text slot — the body, two or three lines of supporting copy.',
			't'        => '',     // '' md | 'lg' | 'sm'
			'action'   => false,
		), $s );
		ob_start(); ?>
		<div class="blurb">
			<?php if ( $s['icon'] ) : ?><div class="blurb__icon"><span style="display:block;width:100%;height:100%;background:var(--raised);"></span></div><?php endif; ?>
			<?php if ( null !== $s['eyebrow'] ) : ?><p class="blurb__eyebrow"><?php echo esc_html( $s['eyebrow'] ); ?></p><?php endif; ?>
			<?php if ( $s['headline'] ) : ?><h3 class="blurb__headline<?php echo $s['h'] ? ' blurb__headline--' . esc_attr( $s['h'] ) : ''; ?>"><?php echo esc_html( $s['headline'] ); ?></h3><?php endif; ?>
			<?php if ( $s['text'] ) : ?><p class="blurb__text<?php echo $s['t'] ? ' blurb__text--' . esc_attr( $s['t'] ) : ''; ?>"><?php echo esc_html( $s['text'] ); ?></p><?php endif; ?>
			<?php if ( $s['action'] ) : ?><div class="blurb__action"><a href="#" class="btn btn--sm btn--secondary">Action slot</a></div><?php endif; ?>
		</div>
		<?php return ob_get_clean();
	};
	?>
	<main class="sg-main">
	<h1 class="sg-page-h">Blurb + stack grid</h1>
	<div class="sg-note"><b>THE BENEFITS FAMILY RESOLVED</b> (operator architecture, sandbox-judged): ONE
		content component + ONE universal stacking layout, NO container component &mdash; the box is a
		consumer wrapper (crust padding + fill + the bleed recipe). <b>Closed sets</b>: 3 headline rungs
		(sm 18 &middot; md 22 &middot; lg = the fluid <code>benefit-display</code> bundle) and 3 text sizes =
		the info-box ladder (18/16/14). <b>THE PIZZA LAW</b>: separators belong to the grid and span only its
		content extent &mdash; never the crust. Separators are <b>INTERIOR-ONLY</b> (operator veto): no line
		above the first row, no line on any outer edge &mdash; there is NO top-border grammar in this family;
		the harvested cert rule-tops and the lp-what grid border DIE at adoption.
		Retires: both cert-cards, the nl-benefits skin, dark-blurb, footer-blurb, the lp-what cells.
		Info-bar stays its own shipped component.</div>

	<h2 class="sg-section-h">1 &middot; Anatomy &mdash; slots on/off</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Slot</th><th>Required</th><th>Contract</th></tr>
		<tr><td><code>__icon</code></td><td>optional</td><td>64px illustration slot</td></tr>
		<tr><td><code>__eyebrow</code></td><td>optional</td><td>the mono atom; a label, or AUTO-NUMBERED (01/02&hellip;) when empty inside <code>.stack-grid--numbered</code></td></tr>
		<tr><td><code>__headline</code></td><td>optional</td><td>3 rungs: default sm (heading-xs 18) &middot; <code>--md</code> (heading-sm 22) &middot; <code>--lg</code> (benefit-display, fluid 24&rarr;32)</td></tr>
		<tr><td><code>__text</code></td><td>required</td><td>the info-box ladder: default 16 &middot; <code>--lg</code> 18 (38ch measure) &middot; <code>--sm</code> 14</td></tr>
		<tr><td><code>__action</code></td><td>optional</td><td>any buttons/links/meta, bottom-pinned so rows align</td></tr>
	</table></div>
	<div style="max-width:380px;"><?php echo $blurb( array( 'icon' => true, 'eyebrow' => 'Eyebrow slot', 'action' => true ) ); ?></div>

	<h2 class="sg-section-h" style="margin-top:32px;">2 &middot; Headline rungs &mdash; the closed set of three</h2>
	<div class="stack-grid" id="blurb-rungs">
		<?php echo $blurb( array( 'headline' => 'Headline sm (18) — the default', 'h' => '' ) ); ?>
		<?php echo $blurb( array( 'headline' => 'Headline md (22)', 'h' => 'md' ) ); ?>
		<?php echo $blurb( array( 'headline' => 'Headline lg — fluid display', 'h' => 'lg' ) ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">3 &middot; Text ladder &mdash; 18 / 16 / 14 (= info box)</h2>
	<div class="stack-grid" id="blurb-ladder">
		<?php echo $blurb( array( 'headline' => 'Text lg (18)', 't' => 'lg' ) ); ?>
		<?php echo $blurb( array( 'headline' => 'Text md (16) — the default', 't' => '' ) ); ?>
		<?php echo $blurb( array( 'headline' => 'Text sm (14)', 't' => 'sm' ) ); ?>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">4 &middot; The numbered benefits row (dark) &mdash; THE PIZZA PATTERN</h2>
	<div class="sg-note">Production drew a rule-top over each cell &mdash; that top border DIES (operator
		veto: separators are interior-only). The divided grid supplies the pizza cuts; the fill rides the
		surface (<code>--stack-fill</code> defaults to <code>--bg</code>).</div>
	<div class="section-dark" style="padding:40px 24px;">
		<div class="stack-grid stack-grid--divided stack-grid--numbered" id="blurb-numbered" style="max-width:1200px;margin:0 auto;">
			<?php echo $blurb( array( 'eyebrow' => '', 'headline' => 'Practical skill, not theory', 'h' => 'lg', 'text' => 'You leave with a working AI workflow you built yourself, on your own use-case.', 't' => 'lg' ) ); ?>
			<?php echo $blurb( array( 'eyebrow' => '', 'headline' => 'A certificate that counts', 'h' => 'lg', 'text' => 'Ministry-accredited retraining certificate recognized by employers.', 't' => 'lg' ) ); ?>
			<?php echo $blurb( array( 'eyebrow' => '', 'headline' => 'Lifetime community access', 'h' => 'lg', 'text' => 'Alumni channel, monthly meetups, and first access to new courses.', 't' => 'lg' ) ); ?>
		</div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">5 &middot; The newsletter landing &mdash; &ldquo;&Scaron;est oblast&iacute;&rdquo; on the SAME divided grid (light, 3&times;2)</h2>
	<div class="sg-note">Production drew ONE 2px border on the grid top &mdash; that edge border DIES too;
		the 3&times;2 grid gets interior pizza cuts only. Same component, same grid, light surface.</div>
	<div style="padding:8px 0;">
		<div class="stack-grid stack-grid--divided" id="blurb-rules-light" style="max-width:1200px;">
			<?php
			$areas = array( 'AI v byznysu', 'Automatizace', 'N&aacute;stroje', 'Strategie', 'Data', 'Praxe' );
			foreach ( $areas as $i => $a ) :
				echo $blurb( array( 'eyebrow' => 'Oblast ' . ( $i + 1 ), 'headline' => html_entity_decode( $a ), 'h' => 'md', 'text' => 'Text slot — the area description, two lines of supporting copy on the light page.' ) );
			endforeach; ?>
		</div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">6 &middot; THE PIZZA LAW &mdash; divided 3&times;2 grid inside a crusted box</h2>
	<div class="sg-note">The wrapper below is the CONSUMER: <code>--raised</code> fill + crust padding (+ in
		production the &minus;80px bleed). The grid paints the separators through its 1px gaps &mdash; they
		span the grid&rsquo;s content extent only, never the crust; middle cells carry full lines, edge
		cells&rsquo; lines end at their edges; crossings just work.</div>
	<div class="section-dark" style="padding:40px 24px;">
		<div id="blurb-pizza-box" style="background:var(--raised); padding:var(--spacing-24); max-width:1200px; margin:0 auto;">
			<div class="stack-grid stack-grid--divided" style="--stack-fill: var(--raised);">
				<?php for ( $i = 1; $i <= 6; $i++ ) : ?>
				<?php echo $blurb( array( 'headline' => 'Cell ' . $i, 'text' => 3 === $i ? 'A deliberately longer cell to prove the row stretches and the separators follow the tallest element in the row.' : 'Text slot — the separators around this cell obey the pizza law.', 't' => 'sm', 'action' => 1 === $i ) ); ?>
				<?php endfor; ?>
			</div>
		</div>
	</div>

	<h2 class="sg-section-h" style="margin-top:32px;">7 &middot; Family mappings &mdash; every dialect as a composition</h2>
	<div style="overflow-x:auto;"><table class="sg-props-table">
		<tr><th>Production dialect</th><th>Composition</th></tr>
		<tr><td>AIG editorial cert-card (homepage + kurz-benefits)</td><td><code>stack-grid --divided --numbered</code> &rsaquo; blurb: empty eyebrow + <code>__headline--lg</code> + <code>__text--lg</code>, on dark &mdash; the per-cell rule-tops DIE; interior pizza cuts replace them</td></tr>
		<tr><td>nl-benefits skin</td><td>same divided row, <code>__headline--md</code> &mdash; the override skin dies</td></tr>
		<tr><td>AIF old cert-card (centered, icon)</td><td>blurb + <code>__icon</code>; CENTERING DIES (no alignment axis) &mdash; the pre-editorial look retires</td></tr>
		<tr><td>dark-blurb (hero mini-benefits, front-page)</td><td><code>stack-grid--divided</code> in a raised wrapper (crust + bleed recipe) &rsaquo; blurb + headline sm + text sm/md + pinned action</td></tr>
		<tr><td>footer-blurb</td><td>blurb: eyebrow ONLY (headline off) + <code>__text--sm</code></td></tr>
		<tr><td>lp-what &ldquo;&Scaron;est oblast&iacute;&rdquo; cells (light)</td><td><code>stack-grid--divided</code> &rsaquo; blurb: label eyebrow + <code>__headline--md</code> + text md &mdash; the grid&rsquo;s lone 2px top border DIES; interior pizza cuts only</td></tr>
		<tr><td>info-bar</td><td><b>NOT this component</b> &mdash; shipped separately (the blockquote-grammar statement stripe)</td></tr>
	</table></div>
	</main>
	<?php
}


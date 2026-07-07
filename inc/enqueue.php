<?php
/**
 * Style registry.
 *
 * Stage 1: styles are REGISTERED only — nothing is enqueued on theme pages,
 * so activating this plugin changes NOTHING on the sites. The styleguide
 * route enqueues these handles itself. Theme adoption (Stage 2/3) will
 * switch the themes to depend on these handles instead of their own copies.
 *
 * Load order contract: normalize → tokens → components (→ theme page CSS).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aifds_register_styles() {
	$base_url = AIFDS_URL . 'assets/css/';
	$base_dir = AIFDS_DIR . 'assets/css/';

	wp_register_style(
		'aifds-normalize',
		$base_url . 'normalize.css',
		array(),
		(string) @filemtime( $base_dir . 'normalize.css' )
	);

	wp_register_style(
		'aifds-fonts',
		$base_url . 'fonts.css',
		array(),
		(string) @filemtime( $base_dir . 'fonts.css' )
	);

	wp_register_style(
		'aifds-tokens',
		$base_url . 'tokens.css',
		array( 'aifds-normalize', 'aifds-fonts' ),
		(string) @filemtime( $base_dir . 'tokens.css' )
	);

	wp_register_style(
		'aifds-components',
		$base_url . 'components.css',
		array( 'aifds-tokens' ),
		(string) @filemtime( $base_dir . 'components.css' )
	);
}
add_action( 'init', 'aifds_register_styles' );

/**
 * Component JS engines (Stage 2 — theme adoption).
 *
 * All 9 engines are REGISTERED front-end, but ENQUEUED only when the
 * active theme declares `add_theme_support( 'aifds-engines' )`. Markup on
 * both sites is DS-native, so ungated enqueue would double-bind with the
 * still-alive theme scripts during the adoption window (P1–P3 of
 * THEME-REFACTOR-SPEC). The theme flips support in the same commit that
 * dequeues its own dying scripts (P3). Every engine is defensive: it
 * no-ops without its markup.
 */
function aifds_register_scripts() {
	$engines  = array(
		'accordion',
		'datepicker',
		'dropdown',
		'engagement',
		'menu',
		'modal',
		'nav-tabs',
		'sticky-bar',
		'table-scroll',
	);
	$base_url = AIFDS_URL . 'js/components/';
	$base_dir = AIFDS_DIR . 'js/components/';

	foreach ( $engines as $engine ) {
		wp_register_script(
			'aifds-' . $engine,
			$base_url . $engine . '.js',
			array(),
			(string) @filemtime( $base_dir . $engine . '.js' ),
			true
		);
	}
}
add_action( 'init', 'aifds_register_scripts' );

function aifds_enqueue_engines() {
	if ( ! current_theme_supports( 'aifds-engines' ) ) {
		return;
	}
	foreach ( array( 'accordion', 'datepicker', 'dropdown', 'engagement', 'menu', 'modal', 'nav-tabs', 'sticky-bar', 'table-scroll' ) as $engine ) {
		wp_enqueue_script( 'aifds-' . $engine );
	}
}
add_action( 'wp_enqueue_scripts', 'aifds_enqueue_engines' );

/**
 * Google Fonts URL used by both themes today (Inter, Space Grotesk,
 * Spline Sans Mono). The styleguide loads it directly; whether the plugin
 * should self-host these (GDPR/perf) is an open manifest question.
 */
function aifds_google_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700;800;900&family=Space+Grotesk:wght@300;400;500;700&family=Spline+Sans+Mono:wght@400;500&display=swap';
}

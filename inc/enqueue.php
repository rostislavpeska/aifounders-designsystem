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

function aigds_register_styles() {
	$base_url = AIGDS_URL . 'assets/css/';
	$base_dir = AIGDS_DIR . 'assets/css/';

	wp_register_style(
		'aigds-normalize',
		$base_url . 'normalize.css',
		array(),
		(string) @filemtime( $base_dir . 'normalize.css' )
	);

	wp_register_style(
		'aigds-fonts',
		$base_url . 'fonts.css',
		array(),
		(string) @filemtime( $base_dir . 'fonts.css' )
	);

	wp_register_style(
		'aigds-tokens',
		$base_url . 'tokens.css',
		array( 'aigds-normalize', 'aigds-fonts' ),
		(string) @filemtime( $base_dir . 'tokens.css' )
	);

	wp_register_style(
		'aigds-components',
		$base_url . 'components.css',
		array( 'aigds-tokens' ),
		(string) @filemtime( $base_dir . 'components.css' )
	);
}
add_action( 'init', 'aigds_register_styles' );

/**
 * Google Fonts URL used by both themes today (Inter, Space Grotesk,
 * Spline Sans Mono). The styleguide loads it directly; whether the plugin
 * should self-host these (GDPR/perf) is an open manifest question.
 */
function aigds_google_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700;800;900&family=Space+Grotesk:wght@300;400;500;700&family=Spline+Sans+Mono:wght@400;500&display=swap';
}

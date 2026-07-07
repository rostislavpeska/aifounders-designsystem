<?php
/**
 * Plugin Name:       AI Founders Design System
 * Plugin URI:        https://github.com/rostislavpeska/aifounders-designsystem
 * Description:       Canonical design system for aifounders.cz + aiguild.cz — tokens, components, icons, and the /design-system/ styleguide. Code is the source of truth; Figma is a projection.
 * Version:           2.0.0-rc.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Rostislav Peška
 * Text Domain:       aifounders-designsystem
 *
 * ARCHITECTURE (see docs/DESIGN_SYSTEM.md):
 * - This plugin owns: normalize, tokens, components CSS, icons, component JS,
 *   section primitives, vendor overrides. Themes own page composition.
 * - Stage 1 (current): styles are REGISTERED but never enqueued on theme
 *   pages. The plugin is only visible on the /design-system/ styleguide.
 *   Theme adoption happens in Stage 2 (AIF) / Stage 3 (AIG).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'AIFDS_VERSION', '2.0.0-dev.0' );
define( 'AIFDS_DIR', plugin_dir_path( __FILE__ ) );
define( 'AIFDS_URL', plugin_dir_url( __FILE__ ) );

require_once AIFDS_DIR . 'inc/enqueue.php';
require_once AIFDS_DIR . 'inc/icons.php';
require_once AIFDS_DIR . 'inc/styleguide.php';
require_once AIFDS_DIR . 'inc/sandbox.php';

register_activation_hook( __FILE__, function () {
	aifds_styleguide_add_rewrite();
	flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, function () {
	flush_rewrite_rules();
} );

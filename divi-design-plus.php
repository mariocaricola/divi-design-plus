<?php
/**
 * Plugin Name:       DIVI Design Plus
 * Plugin URI:        https://github.com/mariocaricola/divi-design-plus
 * Description:       Premium CSS effects library for Divi 5. Apply liquid glass, bento, aurora, hover-lift and scroll-reveal effects by adding a <code>class</code> Attribute in Divi's Advanced tab.
 * Version:           1.0.0
 * Requires at least: 6.4
 * Requires PHP:      8.1
 * Author:            Mario Caricola
 * Author URI:        https://github.com/mariocaricola
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       divi-design-plus
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'DDP_VERSION',    '1.0.0' );
define( 'DDP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// ─── Frontend ────────────────────────────────────────────────────────────────

add_action( 'wp_enqueue_scripts', 'ddp_enqueue_assets' );

function ddp_enqueue_assets(): void {
	wp_enqueue_style(
		'divi-design-plus',
		DDP_PLUGIN_URL . 'assets/css/main.css',
		[],
		DDP_VERSION
	);

	wp_enqueue_script(
		'divi-design-plus',
		DDP_PLUGIN_URL . 'assets/js/animations.js',
		[],
		DDP_VERSION,
		true
	);
}

// ─── Divi 5 Visual Builder ────────────────────────────────────────────────────
// Divi 5 VB renders inside a frontend iframe, but et_fb_enqueue_assets fires
// for the builder shell; the iframe picks up wp_enqueue_scripts automatically.
// We hook here anyway so effects are visible while editing.

add_action( 'et_fb_enqueue_assets', 'ddp_enqueue_vb_assets' );

function ddp_enqueue_vb_assets(): void {
	wp_enqueue_style(
		'divi-design-plus-vb',
		DDP_PLUGIN_URL . 'assets/css/main.css',
		[],
		DDP_VERSION
	);
}

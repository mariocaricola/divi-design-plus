<?php
/**
 * Plugin Name:       DIVI Design Plus
 * Plugin URI:        https://github.com/mariocaricola/divi-design-plus
 * Description:       Premium CSS effects library for Divi 5. Apply liquid glass, bento, aurora, hover-lift and scroll-reveal effects by adding a <code>class</code> Attribute in Divi's Advanced tab.
 * Version:           1.4.3
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

define( 'DDP_VERSION',     '1.4.3' );
define( 'DDP_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'DDP_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );

// ─── Admin panel ─────────────────────────────────────────────────────────────

if ( is_admin() ) {
	require_once DDP_PLUGIN_DIR . 'includes/admin.php';
}

// ─── Frontend: built-in assets ───────────────────────────────────────────────

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

// ─── Frontend: CSS variables saved from admin panel ─────────────────────────

add_action( 'wp_head', 'ddp_output_css_vars', 15 );

function ddp_aurora_palettes(): array {
	return [
		'stripe'   => [ 'label' => 'Stripe (defecto)', 'colors' => [ '#ee7752','#e73c7e','#23a6d5','#23d5ab','#6c63ff','#f7971e' ] ],
		'sunset'   => [ 'label' => 'Sunset',           'colors' => [ '#f7971e','#ff6b6b','#feca57','#ff9ff3','#ff6348','#ffeaa7' ] ],
		'ocean'    => [ 'label' => 'Ocean',             'colors' => [ '#0652DD','#1289A7','#006266','#00b894','#0984e3','#74b9ff' ] ],
		'forest'   => [ 'label' => 'Forest',            'colors' => [ '#00b894','#00cec9','#6c5ce7','#a29bfe','#55efc4','#81ecec' ] ],
		'candy'    => [ 'label' => 'Candy',             'colors' => [ '#fd79a8','#e17055','#fdcb6e','#a29bfe','#fd79a8','#fab1a0' ] ],
		'midnight' => [ 'label' => 'Midnight',          'colors' => [ '#2d3436','#6c5ce7','#0984e3','#00b894','#a29bfe','#74b9ff' ] ],
		'mono'     => [ 'label' => 'Mono',              'colors' => [ '#f8f9fa','#dee2e6','#adb5bd','#6c757d','#343a40','#212529' ] ],
	];
}

function ddp_output_css_vars(): void {
	$saved = get_option( 'ddp_css_vars', [] );
	if ( empty( $saved ) ) return;

	$v = wp_parse_args( $saved, [
		'glass_blur'      => 20,
		'glass_opacity'   => 12,
		'glass_border'    => 30,
		'bento_radius'    => 24,
		'bento_shadow'    => 5,
		'lift_y'          => 10,
		'lift_shadow'     => 16,
		'reveal_duration' => 0.65,
		'slideup_dist'    => 36,
		'reveal_scale'    => 94,
		'aurora_duration' => 10,
		'aurora_palette'  => 'stripe',
		'custom_c1'       => '#ee7752',
		'custom_c2'       => '#e73c7e',
		'custom_c3'       => '#23a6d5',
		'custom_c4'       => '#23d5ab',
		'custom_c5'       => '#6c63ff',
		'custom_c6'       => '#f7971e',
	] );

	$palettes = ddp_aurora_palettes();
	if ( $v['aurora_palette'] === 'custom' ) {
		$colors = [
			sanitize_hex_color( $v['custom_c1'] ?? '#ee7752' ),
			sanitize_hex_color( $v['custom_c2'] ?? '#e73c7e' ),
			sanitize_hex_color( $v['custom_c3'] ?? '#23a6d5' ),
			sanitize_hex_color( $v['custom_c4'] ?? '#23d5ab' ),
			sanitize_hex_color( $v['custom_c5'] ?? '#6c63ff' ),
			sanitize_hex_color( $v['custom_c6'] ?? '#f7971e' ),
		];
	} else {
		$colors = ( $palettes[ $v['aurora_palette'] ] ?? $palettes['stripe'] )['colors'];
	}

	$vars = sprintf(
		'--ddp-glass-blur:%dpx;--ddp-glass-opacity:%.2f;--ddp-glass-border:%.2f;' .
		'--ddp-bento-radius:%dpx;--ddp-bento-shadow:%.2f;' .
		'--ddp-lift-y:-%dpx;--ddp-lift-shadow:%.2f;' .
		'--ddp-reveal-duration:%.2fs;--ddp-slideup-dist:%dpx;--ddp-reveal-scale:%.2f;' .
		'--ddp-aurora-duration:%ds;--ddp-aurora-c1:%s;--ddp-aurora-c2:%s;--ddp-aurora-c3:%s;--ddp-aurora-c4:%s;--ddp-aurora-c5:%s;--ddp-aurora-c6:%s;',
		absint( $v['glass_blur'] ),
		absint( $v['glass_opacity'] ) / 100,
		absint( $v['glass_border'] )  / 100,
		absint( $v['bento_radius'] ),
		absint( $v['bento_shadow'] )  / 100,
		absint( $v['lift_y'] ),
		absint( $v['lift_shadow'] )   / 100,
		(float) $v['reveal_duration'],
		absint( $v['slideup_dist'] ),
		absint( $v['reveal_scale'] )  / 100,
		absint( $v['aurora_duration'] ),
		esc_attr( $colors[0] ), esc_attr( $colors[1] ), esc_attr( $colors[2] ),
		esc_attr( $colors[3] ), esc_attr( $colors[4] ), esc_attr( $colors[5] )
	);

	echo '<style id="ddp-css-vars">:root{' . $vars . '}</style>' . "\n";
}

// ─── Frontend: custom effects CSS saved from admin ───────────────────────────

add_action( 'wp_head', 'ddp_output_custom_css', 20 );

function ddp_output_custom_css(): void {
	$effects = get_option( 'ddp_custom_effects', [] );
	if ( empty( $effects ) ) {
		return;
	}
	echo '<style id="ddp-custom-effects">' . "\n";
	foreach ( $effects as $effect ) {
		if ( ! empty( $effect['css'] ) ) {
			// wp_strip_all_tags keeps CSS text but removes any injected HTML
			echo wp_strip_all_tags( $effect['css'] ) . "\n";
		}
	}
	echo '</style>' . "\n";
}

// ─── Divi 5 Visual Builder ────────────────────────────────────────────────────

add_action( 'et_fb_enqueue_assets', 'ddp_enqueue_vb_assets' );

function ddp_enqueue_vb_assets(): void {
	wp_enqueue_style(
		'divi-design-plus-vb',
		DDP_PLUGIN_URL . 'assets/css/main.css',
		[],
		DDP_VERSION
	);
}

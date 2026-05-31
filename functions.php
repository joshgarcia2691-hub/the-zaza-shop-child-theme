<?php
/**
 * Child theme asset loading.
 *
 * Keeps homepage assets scoped to the front page so cart, checkout, product
 * pages, and payment gateways are not affected.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register editable menu locations for custom theme surfaces.
 */
function zaza_child_register_menus() {
	register_nav_menus(
		array(
			'zaza_home_nav' => __( 'Zaza Homepage Navigation', 'the-zaza-shop-child' ),
		)
	);
}
add_action( 'after_setup_theme', 'zaza_child_register_menus' );

/**
 * Enqueue custom homepage assets only on the front page.
 */
function zaza_child_enqueue_home_assets() {
	if ( ! is_front_page() ) {
		return;
	}

	$theme_dir = get_stylesheet_directory();
	$theme_uri = get_stylesheet_directory_uri();

	$home_css_path = $theme_dir . '/assets/css/zaza-home.css';
	$home_js_path  = $theme_dir . '/assets/js/zaza-home.js';

	wp_enqueue_style(
		'zaza-home',
		$theme_uri . '/assets/css/zaza-home.css',
		array(),
		file_exists( $home_css_path ) ? filemtime( $home_css_path ) : '1.0.0'
	);

	wp_enqueue_script(
		'zaza-home',
		$theme_uri . '/assets/js/zaza-home.js',
		array(),
		file_exists( $home_js_path ) ? filemtime( $home_js_path ) : '1.0.0',
		true
	);
}
add_action( 'wp_enqueue_scripts', 'zaza_child_enqueue_home_assets', 20 );

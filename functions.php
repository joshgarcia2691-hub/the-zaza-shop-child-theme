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
 * Force the coded homepage template for the front page.
 *
 * Twenty Twenty-Four is a block theme, so this prevents the parent block
 * homepage template from taking over when WP Pusher refreshes the child theme.
 */
function zaza_child_force_front_page_template( $template ) {
	if ( ! is_front_page() ) {
		return $template;
	}

	$front_page_template = get_stylesheet_directory() . '/front-page.php';

	return file_exists( $front_page_template ) ? $front_page_template : $template;
}
add_filter( 'template_include', 'zaza_child_force_front_page_template', 99 );

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

/**
 * Hide the parent block-theme title/header where the custom Zaza header is used
 * or where product category pages otherwise show the oversized site title.
 */
function zaza_child_hide_parent_theme_title() {
	if ( ! is_front_page() && ! is_tax( 'product_cat' ) ) {
		return;
	}
	?>
	<style id="zaza-parent-title-cleanup">
		<?php if ( is_front_page() ) : ?>
		body > header:not(.zaza-header),
		.wp-site-blocks > header.wp-block-template-part:first-child,
		.wp-site-blocks > header:not(.zaza-header),
		.wp-site-blocks > .wp-block-template-part:first-child,
		.wp-site-blocks > .wp-block-group:first-child:has(.wp-block-site-title) {
			display: none !important;
		}
		<?php endif; ?>

		.wp-block-site-title,
		.wp-block-site-title a,
		.wp-site-blocks header .wp-block-site-title,
		.wp-site-blocks header .wp-block-site-title a,
		.wp-site-blocks .wp-block-template-part .wp-block-site-title,
		.wp-site-blocks .wp-block-template-part .wp-block-site-title a,
		.wp-site-blocks > .wp-block-site-title,
		.wp-site-blocks > .wp-block-site-title a {
			display: none !important;
			visibility: hidden !important;
		}

		<?php if ( is_front_page() ) : ?>
		.wp-block-post-title:first-child,
		.wp-site-blocks .wp-block-post-title:first-child,
		.wp-site-blocks > main .wp-block-post-title:first-child {
			display: none !important;
		}
		<?php endif; ?>
	</style>
	<?php
}
add_action( 'wp_head', 'zaza_child_hide_parent_theme_title', 5 );

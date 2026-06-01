<?php
/**
 * Child theme asset loading.
 *
 * Keeps storefront header assets scoped to the public frontend while leaving
 * WooCommerce, checkout, and payment gateway logic untouched.
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
 * Check whether the current request is a public storefront page.
 *
 * @return bool
 */
function zaza_child_is_public_storefront_page() {
	if ( is_admin() ) {
		return false;
	}

	if ( function_exists( 'wp_doing_ajax' ) && wp_doing_ajax() ) {
		return false;
	}

	if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
		return false;
	}

	if ( function_exists( 'is_feed' ) && is_feed() ) {
		return false;
	}

	return true;
}

/**
 * Check whether the current request is a WooCommerce product archive surface.
 *
 * @return bool
 */
function zaza_child_is_zaza_product_archive_page() {
	if ( is_post_type_archive( 'product' ) || is_tax( 'product_cat' ) ) {
		return true;
	}

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		return true;
	}

	if ( function_exists( 'is_product_taxonomy' ) && is_product_taxonomy() ) {
		return true;
	}

	return false;
}

/**
 * Backward-compatible alias for the public Zaza storefront shell.
 *
 * @return bool
 */
function zaza_child_is_zaza_ecommerce_page() {
	return zaza_child_is_public_storefront_page();
}

/**
 * Check whether the current request should receive home/archive surface styling.
 *
 * @return bool
 */
function zaza_child_is_zaza_visual_surface_page() {
	if ( is_front_page() ) {
		return true;
	}

	if ( zaza_child_is_zaza_product_archive_page() ) {
		return true;
	}

	return false;
}

if ( ! function_exists( 'zaza_home_shop_url' ) ) {
	/**
	 * Resolve the WooCommerce shop URL with a fallback.
	 *
	 * @return string
	 */
	function zaza_home_shop_url() {
		if ( function_exists( 'wc_get_page_permalink' ) ) {
			$shop_url = wc_get_page_permalink( 'shop' );

			if ( $shop_url ) {
				return $shop_url;
			}
		}

		if ( function_exists( 'wc_get_page_id' ) ) {
			$shop_page_id = wc_get_page_id( 'shop' );

			if ( $shop_page_id && 0 < $shop_page_id ) {
				$shop_url = get_permalink( $shop_page_id );

				if ( $shop_url ) {
					return $shop_url;
				}
			}
		}

		return home_url( '/shop/' );
	}
}

if ( ! function_exists( 'zaza_home_cart_url' ) ) {
	/**
	 * Resolve the WooCommerce cart URL with a fallback.
	 *
	 * @return string
	 */
	function zaza_home_cart_url() {
		if ( function_exists( 'wc_get_cart_url' ) ) {
			return wc_get_cart_url();
		}

		return home_url( '/cart/' );
	}
}

if ( ! function_exists( 'zaza_get_product_category_url' ) ) {
	/**
	 * Resolve a WooCommerce product category URL by slug.
	 *
	 * @param string $slug Product category slug.
	 * @return string
	 */
	function zaza_get_product_category_url( $slug ) {
		$slug = sanitize_title( $slug );

		if ( function_exists( 'taxonomy_exists' ) && function_exists( 'get_term_by' ) && function_exists( 'get_term_link' ) && taxonomy_exists( 'product_cat' ) ) {
			$term = get_term_by( 'slug', $slug, 'product_cat' );

			if ( $term && ! is_wp_error( $term ) ) {
				$term_link = get_term_link( $term );

				if ( ! is_wp_error( $term_link ) && $term_link ) {
					return $term_link;
				}
			}
		}

		return home_url( '/product-category/' . $slug . '/' );
	}
}

if ( ! function_exists( 'zaza_home_shop_collection_url' ) ) {
	/**
	 * Build a dynamic shop collection URL for canonical nav items.
	 *
	 * @param string $orderby WooCommerce catalog orderby value.
	 * @return string
	 */
	function zaza_home_shop_collection_url( $orderby ) {
		return add_query_arg( 'orderby', sanitize_key( $orderby ), zaza_home_shop_url() );
	}
}

if ( ! function_exists( 'zaza_home_render_canonical_nav' ) ) {
	/**
	 * Render the canonical product navigation.
	 *
	 * @return void
	 */
	function zaza_home_render_canonical_nav() {
		$nav_items = array(
			array(
				'label' => esc_html__( 'Join The Club', 'the-zaza-shop-child' ),
				'url'   => home_url( '/#join-the-club' ),
			),
			array(
				'label' => esc_html__( 'New Arrivals', 'the-zaza-shop-child' ),
				'url'   => zaza_home_shop_collection_url( 'date' ),
			),
			array(
				'label' => esc_html__( 'Best Sellers', 'the-zaza-shop-child' ),
				'url'   => zaza_home_shop_collection_url( 'popularity' ),
			),
			array(
				'label' => esc_html__( 'Flower', 'the-zaza-shop-child' ),
				'url'   => zaza_get_product_category_url( 'flower' ),
			),
			array(
				'label' => esc_html__( 'Bulk', 'the-zaza-shop-child' ),
				'url'   => zaza_get_product_category_url( 'bulk' ),
			),
			array(
				'label' => esc_html__( 'Organic Exotic', 'the-zaza-shop-child' ),
				'url'   => zaza_get_product_category_url( 'organic-exotic' ),
			),
			array(
				'label' => esc_html__( 'Smalls', 'the-zaza-shop-child' ),
				'url'   => zaza_get_product_category_url( 'smalls' ),
			),
			array(
				'label' => esc_html__( 'Edibles', 'the-zaza-shop-child' ),
				'url'   => zaza_get_product_category_url( 'edibles' ),
			),
			array(
				'label' => esc_html__( 'Pre Rolls', 'the-zaza-shop-child' ),
				'url'   => zaza_get_product_category_url( 'pre-rolls' ),
			),
			array(
				'label' => esc_html__( 'Concentrates', 'the-zaza-shop-child' ),
				'url'   => zaza_get_product_category_url( 'concentrates' ),
			),
			array(
				'label'    => esc_html__( 'THC Vape', 'the-zaza-shop-child' ),
				'url'      => zaza_get_product_category_url( 'vapes' ),
				'children' => array(
					array(
						'label' => esc_html__( 'All Whole Melts', 'the-zaza-shop-child' ),
						'url'   => zaza_get_product_category_url( 'all-whole-melts' ),
					),
					array(
						'label' => esc_html__( 'Muha Meds', 'the-zaza-shop-child' ),
						'url'   => zaza_get_product_category_url( 'muha-meds' ),
					),
					array(
						'label' => esc_html__( 'Boutique', 'the-zaza-shop-child' ),
						'url'   => zaza_get_product_category_url( 'boutique' ),
					),
					array(
						'label' => esc_html__( 'Switch', 'the-zaza-shop-child' ),
						'url'   => zaza_get_product_category_url( 'switch' ),
					),
					array(
						'label' => esc_html__( 'Hit Stick', 'the-zaza-shop-child' ),
						'url'   => zaza_get_product_category_url( 'hit-stick' ),
					),
				),
			),
			array(
				'label'    => esc_html__( 'Accessories', 'the-zaza-shop-child' ),
				'url'      => zaza_get_product_category_url( 'accessories' ),
				'children' => array(
					array(
						'label' => esc_html__( 'Lighters', 'the-zaza-shop-child' ),
						'url'   => zaza_get_product_category_url( 'lighters' ),
					),
					array(
						'label' => esc_html__( 'Rolling Trays', 'the-zaza-shop-child' ),
						'url'   => zaza_get_product_category_url( 'rolling-trays' ),
					),
					array(
						'label' => esc_html__( 'Papers', 'the-zaza-shop-child' ),
						'url'   => zaza_get_product_category_url( 'papers' ),
					),
					array(
						'label' => esc_html__( 'Other Accessories', 'the-zaza-shop-child' ),
						'url'   => zaza_get_product_category_url( 'other-accessories' ),
					),
				),
			),
		);
		?>
		<ul class="zaza-nav-menu zaza-nav-menu--canonical">
			<?php foreach ( $nav_items as $item ) : ?>
				<?php $has_children = ! empty( $item['children'] ); ?>
				<li class="zaza-nav-menu__item<?php echo $has_children ? ' zaza-dropdown menu-item-has-children' : ''; ?>">
					<a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
					<?php if ( $has_children ) : ?>
						<ul class="sub-menu zaza-dropdown__menu">
							<?php foreach ( $item['children'] as $child ) : ?>
								<li class="zaza-dropdown__item">
									<a href="<?php echo esc_url( $child['url'] ); ?>"><?php echo esc_html( $child['label'] ); ?></a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ul>
		<?php
	}
}

if ( ! function_exists( 'zaza_render_custom_header' ) ) {
	/**
	 * Render the canonical custom Zaza header/navigation.
	 *
	 * @return void
	 */
	function zaza_render_custom_header() {
		?>
		<header class="zaza-header" data-zaza-nav>
			<div class="zaza-header__inner">
				<a class="zaza-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr__( 'The Zaza Club home', 'the-zaza-shop-child' ); ?>">
					<img class="zaza-header__brand-logo" src="<?php echo esc_url( 'https://thezazaclub.com/wp-content/uploads/2026/05/logo.jpeg' ); ?>" alt="" width="42" height="42">
					<span class="zaza-header__brand-copy">
						<span class="zaza-header__brand-name"><?php echo esc_html__( 'The Zaza Club', 'the-zaza-shop-child' ); ?></span>
					</span>
				</a>

				<button class="zaza-nav-toggle" type="button" data-zaza-nav-toggle aria-expanded="false" aria-controls="zaza-home-nav-menu">
					<span class="zaza-nav-toggle__bar"></span>
					<span class="zaza-nav-toggle__bar"></span>
					<span class="zaza-nav-toggle__bar"></span>
					<span class="zaza-sr-only"><?php echo esc_html__( 'Toggle navigation', 'the-zaza-shop-child' ); ?></span>
				</button>

				<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
					<a class="zaza-header__cart" href="<?php echo esc_url( zaza_home_cart_url() ); ?>" aria-label="<?php echo esc_attr__( 'View cart', 'the-zaza-shop-child' ); ?>">
						<span aria-hidden="true"><?php echo esc_html__( 'Cart', 'the-zaza-shop-child' ); ?></span>
					</a>
				<?php endif; ?>

				<nav id="zaza-home-nav-menu" class="zaza-nav" data-zaza-nav-panel aria-label="<?php echo esc_attr__( 'Product navigation', 'the-zaza-shop-child' ); ?>">
					<?php zaza_home_render_canonical_nav(); ?>
				</nav>
			</div>
		</header>
		<?php
	}
}

/**
 * Add the custom Zaza header to public storefront surfaces.
 *
 * The front page renders it inside front-page.php to keep popup ordering intact.
 */
function zaza_child_render_custom_archive_header() {
	if ( is_front_page() || ! zaza_child_is_public_storefront_page() ) {
		return;
	}

	zaza_render_custom_header();
}
add_action( 'wp_body_open', 'zaza_child_render_custom_archive_header', 20 );

/**
 * Add scoped body classes for the Zaza ecommerce shell.
 *
 * @param string[] $classes Body classes.
 * @return string[]
 */
function zaza_child_ecommerce_body_classes( $classes ) {
	if ( zaza_child_is_public_storefront_page() ) {
		$classes[] = 'zaza-storefront-page';
	}

	if ( zaza_child_is_zaza_visual_surface_page() ) {
		$classes[] = 'zaza-commerce-page';
	}

	if ( zaza_child_is_zaza_product_archive_page() ) {
		$classes[] = 'zaza-product-archive-page';
	}

	return $classes;
}
add_filter( 'body_class', 'zaza_child_ecommerce_body_classes' );

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
 * Enqueue custom Zaza shell assets on public storefront pages.
 */
function zaza_child_enqueue_home_assets() {
	if ( ! zaza_child_is_public_storefront_page() ) {
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
 * Enqueue archive polish only for the WooCommerce shop and product categories.
 */
function zaza_child_enqueue_shop_assets() {
	if ( ! zaza_child_is_zaza_product_archive_page() ) {
		return;
	}

	$shop_css_path = get_stylesheet_directory() . '/assets/css/zaza-shop.css';

	wp_enqueue_style(
		'zaza-shop',
		get_stylesheet_directory_uri() . '/assets/css/zaza-shop.css',
		array(),
		file_exists( $shop_css_path ) ? filemtime( $shop_css_path ) : '1.0.0'
	);
}
add_action( 'wp_enqueue_scripts', 'zaza_child_enqueue_shop_assets', 25 );

/**
 * Hide the parent block-theme title/header where the custom Zaza header is used
 * or where product category pages otherwise show the oversized site title.
 */
function zaza_child_hide_parent_theme_title() {
	if ( ! zaza_child_is_public_storefront_page() ) {
		return;
	}
	?>
	<style id="zaza-parent-title-cleanup">
		body > header:not(.zaza-header),
		.wp-site-blocks > header.wp-block-template-part:first-child,
		.wp-site-blocks > header:not(.zaza-header),
		.wp-site-blocks > .wp-block-template-part.header,
		.wp-site-blocks > .wp-block-template-part:first-child,
		.wp-site-blocks > .wp-block-group:first-child:has(.wp-block-site-title) {
			display: none !important;
		}

		.wp-site-blocks header .wp-block-site-title,
		.wp-site-blocks header .wp-block-site-title a,
		.wp-site-blocks .wp-block-template-part .wp-block-site-title,
		.wp-site-blocks .wp-block-template-part .wp-block-site-title a,
		.wp-site-blocks > header .wp-block-site-title,
		.wp-site-blocks > header .wp-block-site-title a {
			display: none !important;
			visibility: hidden !important;
		}

		<?php if ( zaza_child_is_zaza_visual_surface_page() ) : ?>
		.wp-block-post-title:first-child,
		.wp-site-blocks .wp-block-post-title:first-child,
		.wp-site-blocks > main .wp-block-post-title:first-child,
		.wp-site-blocks > main .wp-block-query-title:first-child {
			display: none !important;
		}
		<?php endif; ?>
	</style>
	<?php
}
add_action( 'wp_head', 'zaza_child_hide_parent_theme_title', 5 );

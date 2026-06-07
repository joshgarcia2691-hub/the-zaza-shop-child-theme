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

if ( ! function_exists( 'zaza_child_favicon_url' ) ) {
	/**
	 * Return the Media Library logo URL used for the browser tab favicon.
	 *
	 * @return string
	 */
	function zaza_child_favicon_url() {
		return 'https://thezazaclub.com/wp-content/uploads/2026/05/logo.jpeg';
	}
}

if ( ! function_exists( 'zaza_child_render_favicon_links' ) ) {
	/**
	 * Print favicon links for public, login, and admin browser tabs.
	 */
	function zaza_child_render_favicon_links() {
		$favicon_url = zaza_child_favicon_url();

		if ( ! $favicon_url ) {
			return;
		}
		?>
		<link rel="icon" href="<?php echo esc_url( $favicon_url ); ?>" sizes="32x32" type="image/jpeg">
		<link rel="icon" href="<?php echo esc_url( $favicon_url ); ?>" sizes="192x192" type="image/jpeg">
		<link rel="apple-touch-icon" href="<?php echo esc_url( $favicon_url ); ?>">
		<meta name="msapplication-TileImage" content="<?php echo esc_url( $favicon_url ); ?>">
		<?php
	}
}
add_action( 'wp_head', 'zaza_child_render_favicon_links', 1 );
add_action( 'login_head', 'zaza_child_render_favicon_links', 1 );
add_action( 'admin_head', 'zaza_child_render_favicon_links', 1 );

/**
 * Keep WooCommerce email headers as plain brand text.
 *
 * The email-improvements header links the text logo when no logo image is set.
 * On the live mailbox that linked text is arriving with an unclosed inlined
 * style attribute, which causes PrivateEmail to swallow the order body.
 *
 * @return string
 */
function zaza_child_disable_woocommerce_email_header_link() {
	return '';
}
add_filter( 'woocommerce_email_header_image_url', 'zaza_child_disable_woocommerce_email_header_link', 20 );

if ( ! function_exists( 'zaza_child_email_improvements_enabled' ) ) {
	/**
	 * Check whether WooCommerce's email-improvements feature is enabled.
	 *
	 * @return bool
	 */
	function zaza_child_email_improvements_enabled() {
		return class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' )
			&& \Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled( 'email_improvements' );
	}
}

if ( ! function_exists( 'zaza_child_email_text_align' ) ) {
	/**
	 * Return the current email text alignment.
	 *
	 * @return string
	 */
	function zaza_child_email_text_align() {
		return is_rtl() ? 'right' : 'left';
	}
}

if ( ! function_exists( 'zaza_child_email_render_greeting' ) ) {
	/**
	 * Render a simple WooCommerce customer greeting.
	 *
	 * @param WC_Order $order Order object.
	 */
	function zaza_child_email_render_greeting( $order ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}
		?>
		<p>
		<?php
		if ( ! empty( $order->get_billing_first_name() ) ) {
			printf(
				/* translators: %s: Customer first name. */
				esc_html__( 'Hi %s,', 'woocommerce' ),
				esc_html( $order->get_billing_first_name() )
			);
		} else {
			esc_html_e( 'Hi,', 'woocommerce' );
		}
		?>
		</p>
		<?php
	}
}

if ( ! function_exists( 'zaza_child_email_render_customer_addresses' ) ) {
	/**
	 * Render billing and shipping addresses in a separate email-safe table.
	 *
	 * @param WC_Order $order Order object.
	 */
	function zaza_child_email_render_customer_addresses( $order ) {
		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		$text_align       = zaza_child_email_text_align();
		$billing_address  = $order->get_formatted_billing_address() ? $order->get_formatted_billing_address() : esc_html__( 'N/A', 'woocommerce' );
		$shipping_address = $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : $billing_address;
		?>
		<table cellspacing="0" cellpadding="0" border="0" width="100%" style="width: 100%; margin-top: 28px;" role="presentation">
			<tr>
				<td width="50%" style="width: 50%; padding: 0 12px 0 0; vertical-align: top; text-align: <?php echo esc_attr( $text_align ); ?>;">
					<h2 style="margin: 0 0 10px;"><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></h2>
					<address style="font-style: normal; line-height: 1.6;">
						<?php echo wp_kses_post( $billing_address ); ?>
						<?php if ( $order->get_billing_phone() ) : ?>
							<br><?php echo esc_html( $order->get_billing_phone() ); ?>
						<?php endif; ?>
						<?php if ( $order->get_billing_email() ) : ?>
							<br><?php echo esc_html( $order->get_billing_email() ); ?>
						<?php endif; ?>
					</address>
				</td>
				<td width="50%" style="width: 50%; padding: 0 0 0 12px; vertical-align: top; text-align: <?php echo esc_attr( $text_align ); ?>;">
					<h2 style="margin: 0 0 10px;"><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></h2>
					<address style="font-style: normal; line-height: 1.6;">
						<?php echo wp_kses_post( $shipping_address ); ?>
					</address>
				</td>
			</tr>
		</table>
		<?php
	}
}

if ( ! function_exists( 'zaza_child_email_render_additional_content' ) ) {
	/**
	 * Render configured WooCommerce email additional content.
	 *
	 * @param string $additional_content Additional content from settings.
	 */
	function zaza_child_email_render_additional_content( $additional_content ) {
		if ( ! $additional_content ) {
			return;
		}

		$email_improvements_enabled = zaza_child_email_improvements_enabled();

		echo $email_improvements_enabled ? '<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"><tr><td class="email-additional-content">' : '';
		echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
		echo $email_improvements_enabled ? '</td></tr></table>' : '';
	}
}

/**
 * Register a shop filter area for WooCommerce archive pages.
 */
function zaza_child_register_shop_filter_sidebar() {
	register_sidebar(
		array(
			'name'          => __( 'Zaza Shop Filters', 'the-zaza-shop-child' ),
			'id'            => 'zaza-shop-filters',
			'description'   => __( 'Optional WooCommerce filter widgets for the shop and product category layout.', 'the-zaza-shop-child' ),
			'before_widget' => '<section id="%1$s" class="zaza-shop-filter-widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="zaza-shop-filter__title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'zaza_child_register_shop_filter_sidebar' );

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

if ( ! function_exists( 'zaza_home_checkout_url' ) ) {
	/**
	 * Resolve the WooCommerce checkout URL with a fallback.
	 *
	 * @return string
	 */
	function zaza_home_checkout_url() {
		if ( function_exists( 'wc_get_checkout_url' ) ) {
			return wc_get_checkout_url();
		}

		return home_url( '/checkout/' );
	}
}

if ( ! function_exists( 'zaza_home_get_cart_count' ) ) {
	/**
	 * Return the current WooCommerce cart quantity.
	 *
	 * @return int
	 */
	function zaza_home_get_cart_count() {
		if ( function_exists( 'WC' ) ) {
			$woocommerce = WC();

			if ( $woocommerce && $woocommerce->cart ) {
				return absint( $woocommerce->cart->get_cart_contents_count() );
			}
		}

		return 0;
	}
}

if ( ! function_exists( 'zaza_home_get_header_cart_link_html' ) ) {
	/**
	 * Build the custom header cart link.
	 *
	 * @return string
	 */
	function zaza_home_get_header_cart_link_html() {
		$count      = zaza_home_get_cart_count();
		$item_label = 1 === $count
			? esc_html__( '1 item in cart', 'the-zaza-shop-child' )
			: sprintf(
				/* translators: %d: Cart item quantity. */
				esc_html__( '%d items in cart', 'the-zaza-shop-child' ),
				$count
			);

		ob_start();
		?>
		<a class="zaza-header__cart" href="<?php echo esc_url( zaza_home_cart_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( '%s, %s', __( 'View cart', 'the-zaza-shop-child' ), $item_label ) ); ?>" data-zaza-cart data-cart-count="<?php echo esc_attr( $count ); ?>" data-cart-url="<?php echo esc_url( zaza_home_cart_url() ); ?>" data-checkout-url="<?php echo esc_url( zaza_home_checkout_url() ); ?>">
			<span class="zaza-header__cart-icon" aria-hidden="true"></span>
			<span class="zaza-header__cart-label" aria-hidden="true"><?php echo esc_html__( 'Cart', 'the-zaza-shop-child' ); ?></span>
			<span class="zaza-header__cart-count" aria-hidden="true"><?php echo esc_html( $count ); ?></span>
		</a>
		<?php
		return trim( ob_get_clean() );
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
				'url'      => zaza_get_product_category_url( 'thc-vape' ),
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

				<?php
				if ( function_exists( 'wc_get_cart_url' ) ) {
					echo zaza_home_get_header_cart_link_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				?>

				<nav id="zaza-home-nav-menu" class="zaza-nav" data-zaza-nav-panel aria-label="<?php echo esc_attr__( 'Product navigation', 'the-zaza-shop-child' ); ?>">
					<?php zaza_home_render_canonical_nav(); ?>
				</nav>
			</div>
		</header>
		<?php
	}
}

/**
 * Keep the custom header cart link in WooCommerce AJAX cart fragments.
 *
 * @param array<string,string> $fragments Existing fragments.
 * @return array<string,string>
 */
function zaza_child_refresh_header_cart_fragment( $fragments ) {
	$fragments['.zaza-header__cart'] = zaza_home_get_header_cart_link_html();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'zaza_child_refresh_header_cart_fragment' );

if ( ! function_exists( 'zaza_render_custom_footer' ) ) {
	/**
	 * Render the canonical custom Zaza footer once per request.
	 *
	 * @return void
	 */
	function zaza_render_custom_footer() {
		if ( ! empty( $GLOBALS['zaza_child_footer_rendered'] ) ) {
			return;
		}

		$GLOBALS['zaza_child_footer_rendered'] = true;
		?>
		<footer class="zaza-footer" aria-label="<?php echo esc_attr__( 'Site footer', 'the-zaza-shop-child' ); ?>">
			<div class="zaza-footer__inner">
				<div class="zaza-footer__top">
					<a class="zaza-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr__( 'The Zaza Club home', 'the-zaza-shop-child' ); ?>">
						<img class="zaza-footer__logo" src="<?php echo esc_url( 'https://thezazaclub.com/wp-content/uploads/2026/05/logo.jpeg' ); ?>" alt="" width="58" height="58">
						<span><?php echo esc_html__( 'The Zaza Club', 'the-zaza-shop-child' ); ?></span>
					</a>

					<nav class="zaza-footer__nav" aria-label="<?php echo esc_attr__( 'Footer product links', 'the-zaza-shop-child' ); ?>">
						<a href="<?php echo esc_url( zaza_get_product_category_url( 'flower' ) ); ?>"><?php echo esc_html__( 'Flower', 'the-zaza-shop-child' ); ?></a>
						<a href="<?php echo esc_url( zaza_get_product_category_url( 'edibles' ) ); ?>"><?php echo esc_html__( 'Edibles', 'the-zaza-shop-child' ); ?></a>
						<a href="<?php echo esc_url( zaza_get_product_category_url( 'smalls' ) ); ?>"><?php echo esc_html__( 'Smalls', 'the-zaza-shop-child' ); ?></a>
						<a href="<?php echo esc_url( zaza_get_product_category_url( 'thc-vape' ) ); ?>"><?php echo esc_html__( 'THC Vape', 'the-zaza-shop-child' ); ?></a>
						<a href="<?php echo esc_url( zaza_get_product_category_url( 'accessories' ) ); ?>"><?php echo esc_html__( 'Accessories', 'the-zaza-shop-child' ); ?></a>
					</nav>
				</div>

				<div class="zaza-footer__meta">
					<span><?php echo esc_html__( 'Adults 21+ where permitted.', 'the-zaza-shop-child' ); ?></span>
					<span><?php echo esc_html__( 'Secure checkout.', 'the-zaza-shop-child' ); ?></span>
					<span><?php echo esc_html__( 'Shipping rules apply.', 'the-zaza-shop-child' ); ?></span>
				</div>
			</div>
		</footer>
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
 * Add the custom Zaza footer on public storefront pages that use parent block
 * templates instead of the child theme footer.php.
 */
function zaza_child_render_global_footer() {
	if ( ! zaza_child_is_public_storefront_page() ) {
		return;
	}

	zaza_render_custom_footer();
}
add_action( 'wp_footer', 'zaza_child_render_global_footer', 5 );

/**
 * Replace the parent block theme footer template part with the Zaza footer.
 *
 * @param string $block_content Rendered block HTML.
 * @param array  $block         Parsed block data.
 * @return string
 */
function zaza_child_replace_parent_footer_template_part( $block_content, $block ) {
	if ( ! zaza_child_is_public_storefront_page() || empty( $block['blockName'] ) || 'core/template-part' !== $block['blockName'] ) {
		return $block_content;
	}

	$attrs = isset( $block['attrs'] ) && is_array( $block['attrs'] ) ? $block['attrs'] : array();
	$slug  = isset( $attrs['slug'] ) ? sanitize_key( $attrs['slug'] ) : '';

	if ( 'footer' !== $slug ) {
		return $block_content;
	}

	ob_start();
	zaza_render_custom_footer();

	return ob_get_clean();
}
add_filter( 'render_block', 'zaza_child_replace_parent_footer_template_part', 20, 2 );

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
 * Force a controlled PHP template for WooCommerce product archives.
 *
 * The parent block theme's product archive template is too constrained for the
 * left-filter/right-grid storefront layout, so product archives use the child
 * theme template while cart, checkout, account, and single products remain on
 * their normal WooCommerce templates.
 *
 * @param string $template Current template path.
 * @return string
 */
function zaza_child_force_product_archive_template( $template ) {
	if ( ! zaza_child_is_zaza_product_archive_page() ) {
		return $template;
	}

	$archive_template = get_stylesheet_directory() . '/archive-product.php';

	return file_exists( $archive_template ) ? $archive_template : $template;
}
add_filter( 'template_include', 'zaza_child_force_product_archive_template', 100 );

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
		'zaza-fonts',
		'https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);

	wp_enqueue_style(
		'zaza-home',
		$theme_uri . '/assets/css/zaza-home.css',
		array(),
		file_exists( $home_css_path ) ? filemtime( $home_css_path ) : '1.0.0'
	);

	wp_enqueue_script(
		'zaza-home',
		$theme_uri . '/assets/js/zaza-home.js',
		array( 'jquery' ),
		file_exists( $home_js_path ) ? filemtime( $home_js_path ) : '1.0.0',
		true
	);

	wp_localize_script(
		'zaza-home',
		'zazaCartData',
		array(
			'cartUrl'       => zaza_home_cart_url(),
			'checkoutUrl'   => zaza_home_checkout_url(),
			'addedLabel'    => __( 'Added to cart', 'the-zaza-shop-child' ),
			'checkoutLabel' => __( 'Checkout now', 'the-zaza-shop-child' ),
		)
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
 * Get safe product category filter terms for the archive sidebar.
 *
 * @return WP_Term[]
 */
function zaza_child_get_shop_filter_terms() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$current_term = is_tax( 'product_cat' ) ? get_queried_object() : null;
	$parent_id    = 0;

	if ( $current_term instanceof WP_Term ) {
		$children = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'parent'     => (int) $current_term->term_id,
			)
		);

		if ( ! is_wp_error( $children ) && ! empty( $children ) ) {
			return $children;
		}

		$parent_id = (int) $current_term->parent;
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => $parent_id,
			'orderby'    => 'name',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
		return array();
	}

	return $terms;
}

/**
 * Render the shop filter/sidebar panel.
 *
 * @return string
 */
function zaza_child_get_shop_filter_panel_html() {
	ob_start();
	$zaza_clean  = function ( $value ) {
		if ( function_exists( 'wc_clean' ) ) {
			return wc_clean( $value );
		}

		return sanitize_text_field( $value );
	};
	$current_slug = is_tax( 'product_cat' ) ? get_query_var( 'product_cat' ) : '';
	$terms        = zaza_child_get_shop_filter_terms();
	$min_price    = isset( $_GET['min_price'] ) ? $zaza_clean( wp_unslash( $_GET['min_price'] ) ) : '';
	$max_price    = isset( $_GET['max_price'] ) ? $zaza_clean( wp_unslash( $_GET['max_price'] ) ) : '';
	$form_action  = remove_query_arg( array( 'min_price', 'max_price', 'paged' ) );
	?>
	<aside class="zaza-shop-filters" aria-label="<?php echo esc_attr__( 'Shop filters', 'the-zaza-shop-child' ); ?>">
		<?php if ( is_active_sidebar( 'zaza-shop-filters' ) ) : ?>
			<?php dynamic_sidebar( 'zaza-shop-filters' ); ?>
		<?php else : ?>
			<?php if ( ! empty( $terms ) ) : ?>
				<section class="zaza-shop-filter">
					<h3 class="zaza-shop-filter__title"><?php echo esc_html__( 'Product Type', 'the-zaza-shop-child' ); ?></h3>
					<ul class="zaza-shop-filter__list">
						<?php foreach ( $terms as $term ) : ?>
							<?php
							if ( ! $term instanceof WP_Term ) {
								continue;
							}

							$term_link = get_term_link( $term );

							if ( is_wp_error( $term_link ) ) {
								continue;
							}

							$is_current = $current_slug === $term->slug;
							?>
							<li>
								<a class="zaza-shop-filter__link<?php echo $is_current ? ' is-active' : ''; ?>" href="<?php echo esc_url( $term_link ); ?>">
									<span class="zaza-shop-filter__box" aria-hidden="true"></span>
									<span class="zaza-shop-filter__label"><?php echo esc_html( $term->name ); ?></span>
									<span class="zaza-shop-filter__count"><?php echo esc_html( (string) $term->count ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</section>
			<?php endif; ?>

			<section class="zaza-shop-filter">
				<h3 class="zaza-shop-filter__title"><?php echo esc_html__( 'Price', 'the-zaza-shop-child' ); ?></h3>
				<form class="zaza-shop-price-filter" method="get" action="<?php echo esc_url( $form_action ); ?>">
					<?php foreach ( $_GET as $key => $value ) : ?>
						<?php
						$key = sanitize_key( $key );

						if ( '' === $key || in_array( $key, array( 'min_price', 'max_price', 'paged' ), true ) || is_array( $value ) ) {
							continue;
						}
						?>
						<input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $zaza_clean( wp_unslash( $value ) ) ); ?>">
					<?php endforeach; ?>
					<div class="zaza-shop-price-filter__fields">
						<label>
							<span><?php echo esc_html__( 'Min', 'the-zaza-shop-child' ); ?></span>
							<input type="number" min="0" step="1" name="min_price" value="<?php echo esc_attr( $min_price ); ?>" placeholder="0">
						</label>
						<label>
							<span><?php echo esc_html__( 'Max', 'the-zaza-shop-child' ); ?></span>
							<input type="number" min="0" step="1" name="max_price" value="<?php echo esc_attr( $max_price ); ?>" placeholder="100">
						</label>
					</div>
					<button type="submit"><?php echo esc_html__( 'Apply', 'the-zaza-shop-child' ); ?></button>
				</form>
			</section>
		<?php endif; ?>
	</aside>
	<?php
	return ob_get_clean();
}

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

		<?php if ( zaza_child_is_zaza_visual_surface_page() && ! zaza_child_is_zaza_product_archive_page() ) : ?>
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

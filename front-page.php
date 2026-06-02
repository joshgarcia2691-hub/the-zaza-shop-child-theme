<?php
/**
 * Custom front page for The Zaza Club.
 *
 * Template is intentionally self-contained and WooCommerce-aware. It reads
 * product and category data when available and falls back gracefully when the
 * store is still being populated.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'zaza_home_placeholder_image' ) ) {
	/**
	 * Get a placeholder image URL.
	 *
	 * @return string
	 */
	function zaza_home_placeholder_image() {
		if ( function_exists( 'wc_placeholder_img_src' ) ) {
			return wc_placeholder_img_src( 'full' );
		}

		return includes_url( 'images/media/default.png' );
	}
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

if ( ! function_exists( 'zaza_home_get_products' ) ) {
	/**
	 * Get featured products first, then recent products if none are featured.
	 *
	 * @param int $limit Product count.
	 * @return WC_Product[]
	 */
	function zaza_home_get_products( $limit = 8 ) {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return array();
		}

		$featured_products = wc_get_products(
			array(
				'status'   => 'publish',
				'limit'    => $limit,
				'featured' => true,
				'orderby'  => 'menu_order',
				'order'    => 'ASC',
			)
		);

		if ( is_array( $featured_products ) && ! empty( $featured_products ) ) {
			return $featured_products;
		}

		$recent_products = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => $limit,
				'orderby' => 'date',
				'order'   => 'DESC',
			)
		);

		return is_array( $recent_products ) ? $recent_products : array();
	}
}

if ( ! function_exists( 'zaza_home_product_image_url' ) ) {
	/**
	 * Get a product image URL or a placeholder.
	 *
	 * @param WC_Product $product Product object.
	 * @param string     $size    Image size.
	 * @return string
	 */
	function zaza_home_product_image_url( $product, $size = 'large' ) {
		if ( is_object( $product ) && method_exists( $product, 'get_image_id' ) ) {
			$image_id = $product->get_image_id();

			if ( $image_id ) {
				$image_url = wp_get_attachment_image_url( $image_id, $size );

				if ( $image_url ) {
					return $image_url;
				}
			}
		}

		return zaza_home_placeholder_image();
	}
}

if ( ! function_exists( 'zaza_home_prepare_hero_slides' ) ) {
	/**
	 * Normalize editable hero banner slides and provide image fallbacks.
	 *
	 * @param array[] $slides Hero slide definitions.
	 * @return array[]
	 */
	function zaza_home_prepare_hero_slides( $slides ) {
		$prepared_slides = array();

		foreach ( $slides as $slide ) {
			if ( ! is_array( $slide ) ) {
				continue;
			}

			$prepared_slides[] = array(
				'image'        => ! empty( $slide['image'] ) ? $slide['image'] : zaza_home_placeholder_image(),
				'kicker'       => isset( $slide['kicker'] ) ? $slide['kicker'] : ( isset( $slide['eyebrow'] ) ? $slide['eyebrow'] : '' ),
				'title'        => isset( $slide['title'] ) ? $slide['title'] : ( isset( $slide['headline'] ) ? $slide['headline'] : '' ),
				'button_label' => isset( $slide['button_label'] ) ? $slide['button_label'] : ( isset( $slide['button_text'] ) ? $slide['button_text'] : '' ),
				'button_url'   => ! empty( $slide['button_url'] ) ? $slide['button_url'] : zaza_home_shop_url(),
			);
		}

		if ( empty( $prepared_slides ) ) {
			$prepared_slides[] = array(
				'image'        => zaza_home_placeholder_image(),
				'kicker'       => '',
				'title'        => esc_html__( 'Welcome to The Zaza Club', 'child-theme' ),
				'button_label' => esc_html__( 'Shop Now', 'child-theme' ),
				'button_url'   => zaza_home_shop_url(),
			);
		}

		return $prepared_slides;
	}
}

if ( ! function_exists( 'zaza_home_get_categories' ) ) {
	/**
	 * Get visible WooCommerce product categories.
	 *
	 * @param int $limit Category count.
	 * @return WP_Term[]
	 */
	function zaza_home_get_categories( $limit = 6 ) {
		if ( ! taxonomy_exists( 'product_cat' ) ) {
			return array();
		}

		$exclude = array();
		$default = get_option( 'default_product_cat' );

		if ( $default ) {
			$exclude[] = (int) $default;
		}

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'number'     => $limit,
				'orderby'    => 'name',
				'order'      => 'ASC',
				'exclude'    => $exclude,
			)
		);

		if ( is_wp_error( $terms ) || ! is_array( $terms ) ) {
			return array();
		}

		return $terms;
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

if ( ! function_exists( 'zaza_home_nav_url' ) ) {
	/**
	 * Build a temporary fallback URL for the homepage nav.
	 *
	 * @param string $path Relative path.
	 * @return string
	 */
	function zaza_home_nav_url( $path ) {
		return home_url( '/' . ltrim( $path, '/' ) );
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
				'label' => esc_html__( 'Join The Club', 'child-theme' ),
				'url'   => home_url( '/#join-the-club' ),
			),
			array(
				'label' => esc_html__( 'New Arrivals', 'child-theme' ),
				'url'   => zaza_home_shop_collection_url( 'date' ),
			),
			array(
				'label' => esc_html__( 'Best Sellers', 'child-theme' ),
				'url'   => zaza_home_shop_collection_url( 'popularity' ),
			),
			array(
				'label' => esc_html__( 'Flower', 'child-theme' ),
				'url'   => zaza_get_product_category_url( 'flower' ),
			),
			array(
				'label' => esc_html__( 'Bulk', 'child-theme' ),
				'url'   => zaza_get_product_category_url( 'bulk' ),
			),
			array(
				'label' => esc_html__( 'Organic Exotic', 'child-theme' ),
				'url'   => zaza_get_product_category_url( 'organic-exotic' ),
			),
			array(
				'label' => esc_html__( 'Smalls', 'child-theme' ),
				'url'   => zaza_get_product_category_url( 'smalls' ),
			),
			array(
				'label' => esc_html__( 'Edibles', 'child-theme' ),
				'url'   => zaza_get_product_category_url( 'edibles' ),
			),
			array(
				'label' => esc_html__( 'Pre Rolls', 'child-theme' ),
				'url'   => zaza_get_product_category_url( 'pre-rolls' ),
			),
			array(
				'label' => esc_html__( 'Concentrates', 'child-theme' ),
				'url'   => zaza_get_product_category_url( 'concentrates' ),
			),
			array(
				'label'    => esc_html__( 'THC Vape', 'child-theme' ),
				'url'      => zaza_get_product_category_url( 'thc-vape' ),
				'children' => array(
					array(
						'label' => esc_html__( 'All Whole Melts', 'child-theme' ),
						'url'   => zaza_get_product_category_url( 'all-whole-melts' ),
					),
					array(
						'label' => esc_html__( 'Muha Meds', 'child-theme' ),
						'url'   => zaza_get_product_category_url( 'muha-meds' ),
					),
					array(
						'label' => esc_html__( 'Boutique', 'child-theme' ),
						'url'   => zaza_get_product_category_url( 'boutique' ),
					),
					array(
						'label' => esc_html__( 'Switch', 'child-theme' ),
						'url'   => zaza_get_product_category_url( 'switch' ),
					),
					array(
						'label' => esc_html__( 'Hit Stick', 'child-theme' ),
						'url'   => zaza_get_product_category_url( 'hit-stick' ),
					),
				),
			),
			array(
				'label'    => esc_html__( 'Accessories', 'child-theme' ),
				'url'      => zaza_get_product_category_url( 'accessories' ),
				'children' => array(
					array(
						'label' => esc_html__( 'Lighters', 'child-theme' ),
						'url'   => zaza_get_product_category_url( 'lighters' ),
					),
					array(
						'label' => esc_html__( 'Rolling Trays', 'child-theme' ),
						'url'   => zaza_get_product_category_url( 'rolling-trays' ),
					),
					array(
						'label' => esc_html__( 'Papers', 'child-theme' ),
						'url'   => zaza_get_product_category_url( 'papers' ),
					),
					array(
						'label' => esc_html__( 'Other Accessories', 'child-theme' ),
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

if ( ! function_exists( 'zaza_home_render_fallback_nav' ) ) {
	/**
	 * Backward-compatible wrapper for older calls.
	 *
	 * @return void
	 */
	function zaza_home_render_fallback_nav() {
		zaza_home_render_canonical_nav();
	}
}

if ( ! function_exists( 'zaza_home_render_product_card' ) ) {
	/**
	 * Render a WooCommerce product card.
	 *
	 * @param WC_Product $product Product object.
	 * @return void
	 */
	function zaza_home_render_product_card( $product ) {
		if ( ! is_object( $product ) || ! method_exists( $product, 'get_id' ) ) {
			return;
		}

		$product_id   = $product->get_id();
		$product_name = $product->get_name();
		$product_url  = get_permalink( $product_id );
		$product_url  = $product_url ? $product_url : zaza_home_shop_url();
		$image_html   = $product->get_image(
			'woocommerce_thumbnail',
			array(
				'class'   => 'zaza-product-card__image',
				'loading' => 'lazy',
			)
		);
		$button_classes = array(
			'zaza-button',
			'zaza-button--dark',
			'zaza-product-card__button',
			'button',
			'product_type_' . sanitize_html_class( $product->get_type() ),
		);

		if ( $product->is_purchasable() && $product->is_in_stock() ) {
			$button_classes[] = 'add_to_cart_button';
		}

		if ( $product->supports( 'ajax_add_to_cart' ) && $product->is_purchasable() && $product->is_in_stock() ) {
			$button_classes[] = 'ajax_add_to_cart';
		}
		?>
		<article class="zaza-product-card">
			<a class="zaza-product-card__media" href="<?php echo esc_url( $product_url ); ?>" aria-label="<?php echo esc_attr( $product_name ); ?>">
				<?php echo wp_kses_post( $image_html ); ?>
			</a>
			<div class="zaza-product-card__body">
				<h3 class="zaza-product-card__title">
					<a href="<?php echo esc_url( $product_url ); ?>"><?php echo esc_html( $product_name ); ?></a>
				</h3>
				<div class="zaza-product-card__price">
					<?php echo wp_kses_post( $product->get_price_html() ); ?>
				</div>
				<a
					href="<?php echo esc_url( $product->add_to_cart_url() ); ?>"
					data-quantity="1"
					data-product_id="<?php echo esc_attr( $product_id ); ?>"
					data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>"
					class="<?php echo esc_attr( implode( ' ', array_filter( $button_classes ) ) ); ?>"
					aria-label="<?php echo esc_attr( sprintf( '%s: %s', $product->add_to_cart_text(), $product_name ) ); ?>"
					rel="nofollow"
				>
					<?php echo esc_html( $product->add_to_cart_text() ); ?>
				</a>
			</div>
		</article>
		<?php
	}
}

if ( ! function_exists( 'zaza_home_render_category_card' ) ) {
	/**
	 * Render a WooCommerce product category card.
	 *
	 * @param WP_Term $term Product category term.
	 * @return void
	 */
	function zaza_home_render_category_card( $term ) {
		if ( ! is_object( $term ) || empty( $term->term_id ) ) {
			return;
		}

		$thumbnail_id = get_term_meta( $term->term_id, 'thumbnail_id', true );
		$image_url    = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'large' ) : '';
		$image_url    = $image_url ? $image_url : zaza_home_placeholder_image();
		$term_link    = get_term_link( $term );

		if ( is_wp_error( $term_link ) ) {
			$term_link = zaza_home_shop_url();
		}
		?>
		<a class="zaza-category-card" href="<?php echo esc_url( $term_link ); ?>">
			<span class="zaza-category-card__media" style="<?php echo esc_attr( sprintf( 'background-image: url("%s");', esc_url_raw( $image_url ) ) ); ?>"></span>
			<span class="zaza-category-card__body">
				<span class="zaza-category-card__title"><?php echo esc_html( $term->name ); ?></span>
				<span class="zaza-category-card__cta" aria-hidden="true"></span>
			</span>
		</a>
		<?php
	}
}

$zaza_products = zaza_home_get_products( 8 );

/*
 * Editable hero banner slides.
 *
 * Replace image values below with updated Media Library URLs as hero artwork changes.
 * Leave image empty to use the WooCommerce/WordPress placeholder fallback.
 */
$zaza_hero_slides = array(
	array(
		'image'        => 'https://thezazaclub.com/wp-content/uploads/2026/05/ZAZA_Web_Slider_3.0_4g_DESKTOP_ALT.jpg',
		'kicker'       => esc_html__( 'Buy One Get One', 'child-theme' ),
		'title'        => esc_html__( '4G Flower', 'child-theme' ),
		'button_label' => esc_html__( 'Get Deal!', 'child-theme' ),
		'button_url'   => zaza_get_product_category_url( 'flower' ),
	),
	array(
		'image'        => 'https://thezazaclub.com/wp-content/uploads/2026/05/ZAZA_Web_Slider_3.0_Gummies_Desktop.jpg',
		'kicker'       => esc_html__( 'New Drop', 'child-theme' ),
		'title'        => esc_html__( 'Gummies', 'child-theme' ),
		'button_label' => esc_html__( 'Shop Gummies', 'child-theme' ),
		'button_url'   => zaza_get_product_category_url( 'edibles' ),
	),
	array(
		'image'        => 'https://thezazaclub.com/wp-content/uploads/2026/05/ZAZA_Web_Littles.jpg',
		'kicker'       => esc_html__( 'Small Buds', 'child-theme' ),
		'title'        => esc_html__( 'Littles', 'child-theme' ),
		'button_label' => esc_html__( 'Shop Littles', 'child-theme' ),
		'button_url'   => zaza_get_product_category_url( 'smalls' ),
	),
);

$zaza_slides     = zaza_home_prepare_hero_slides( $zaza_hero_slides );
$zaza_categories = zaza_home_get_categories( 6 );
$zaza_promo_img  = ! empty( $zaza_products ) ? zaza_home_product_image_url( reset( $zaza_products ), 'full' ) : zaza_home_placeholder_image();

get_header();
?>

<div class="zaza-entry-popups" aria-live="polite">
	<div class="zaza-modal zaza-age-modal" data-zaza-age-modal role="dialog" aria-modal="true" aria-labelledby="zaza-age-title" hidden>
		<div class="zaza-modal__panel zaza-modal__panel--age">
			<img class="zaza-modal__logo" src="<?php echo esc_url( 'https://thezazaclub.com/wp-content/uploads/2026/05/logo.jpeg' ); ?>" alt="<?php echo esc_attr__( 'The Zaza Club', 'child-theme' ); ?>">
			<p class="zaza-modal__eyebrow"><?php echo esc_html__( 'Age Verification', 'child-theme' ); ?></p>
			<h2 id="zaza-age-title" class="zaza-modal__title"><?php echo esc_html__( 'Adults 21+ Only', 'child-theme' ); ?></h2>
			<p class="zaza-modal__copy"><?php echo esc_html__( 'Please confirm you are at least 21 years old to continue browsing The Zaza Club.', 'child-theme' ); ?></p>
			<div class="zaza-modal__actions">
				<button class="zaza-button zaza-button--dark" type="button" data-zaza-age-accept><?php echo esc_html__( "I'm over 21", 'child-theme' ); ?></button>
				<button class="zaza-button zaza-button--light" type="button" data-zaza-age-deny><?php echo esc_html__( "I'm under 21", 'child-theme' ); ?></button>
			</div>
			<p class="zaza-modal__restricted" data-zaza-age-message hidden><?php echo esc_html__( 'Access is restricted. This site is intended only for adults 21+ where permitted by law.', 'child-theme' ); ?></p>
		</div>
	</div>

	<div class="zaza-modal zaza-email-modal" data-zaza-email-modal role="dialog" aria-modal="true" aria-labelledby="zaza-email-title" hidden>
		<div class="zaza-modal__panel zaza-modal__panel--email">
			<button class="zaza-modal__close" type="button" aria-label="<?php echo esc_attr__( 'Close discount signup', 'child-theme' ); ?>" data-zaza-email-dismiss>&times;</button>
			<img class="zaza-modal__logo" src="<?php echo esc_url( 'https://thezazaclub.com/wp-content/uploads/2026/05/logo.jpeg' ); ?>" alt="<?php echo esc_attr__( 'The Zaza Club', 'child-theme' ); ?>">
			<p class="zaza-modal__eyebrow"><?php echo esc_html__( 'Welcome Offer', 'child-theme' ); ?></p>
			<h2 id="zaza-email-title" class="zaza-modal__title"><?php echo esc_html__( 'Get First Access to Drops', 'child-theme' ); ?></h2>
			<p class="zaza-modal__copy"><?php echo esc_html__( 'Join the list for launch updates, rotating bundles, and limited-time offers.', 'child-theme' ); ?></p>
			<form class="zaza-email-form" data-zaza-email-form>
				<label class="zaza-sr-only" for="zaza-email-input"><?php echo esc_html__( 'Email address', 'child-theme' ); ?></label>
				<input id="zaza-email-input" class="zaza-email-form__input" type="email" name="email" placeholder="<?php echo esc_attr__( 'Email address', 'child-theme' ); ?>" required>
				<button class="zaza-button zaza-button--accent zaza-email-form__button" type="submit"><?php echo esc_html__( 'Send My Offer', 'child-theme' ); ?></button>
			</form>
			<button class="zaza-modal__secondary" type="button" data-zaza-email-dismiss><?php echo esc_html__( 'No thanks', 'child-theme' ); ?></button>
			<p class="zaza-modal__success" data-zaza-email-success hidden><?php echo esc_html__( 'Thanks. You are on the list.', 'child-theme' ); ?></p>
		</div>
	</div>
</div>

<?php zaza_render_custom_header(); ?>

<main id="primary" class="zaza-home">
	<section class="zaza-hero" data-zaza-carousel aria-label="<?php echo esc_attr__( 'Homepage banner carousel', 'child-theme' ); ?>">
		<div class="zaza-hero__slides">
			<?php foreach ( $zaza_slides as $index => $slide ) : ?>
				<?php
				$is_active   = 0 === $index;
				$slide_style = sprintf(
					'--zaza-slide-image: url("%s");',
					esc_url_raw( $slide['image'] )
				);
				?>
				<article class="zaza-hero__slide<?php echo $is_active ? ' is-active' : ''; ?>" data-zaza-slide style="<?php echo esc_attr( $slide_style ); ?>" aria-hidden="<?php echo esc_attr( $is_active ? 'false' : 'true' ); ?>">
					<div class="zaza-hero__inner">
						<div class="zaza-hero__copy">
							<?php if ( ! empty( $slide['kicker'] ) ) : ?>
								<p class="zaza-eyebrow"><?php echo esc_html( $slide['kicker'] ); ?></p>
							<?php endif; ?>
							<?php if ( ! empty( $slide['title'] ) ) : ?>
								<h1 class="zaza-hero__headline"><?php echo esc_html( $slide['title'] ); ?></h1>
							<?php endif; ?>
							<?php if ( ! empty( $slide['button_label'] ) ) : ?>
								<a class="zaza-button zaza-button--accent zaza-hero__button" href="<?php echo esc_url( $slide['button_url'] ); ?>"><?php echo esc_html( $slide['button_label'] ); ?></a>
							<?php endif; ?>
						</div>
					</div>
				</article>
			<?php endforeach; ?>
		</div>
		<div class="zaza-hero__controls" aria-label="<?php echo esc_attr__( 'Carousel controls', 'child-theme' ); ?>">
			<button class="zaza-hero__arrow" type="button" data-zaza-prev aria-label="<?php echo esc_attr__( 'Previous slide', 'child-theme' ); ?>"></button>
			<div class="zaza-hero__dots">
				<?php foreach ( $zaza_slides as $index => $slide ) : ?>
					<button class="zaza-hero__dot<?php echo 0 === $index ? ' is-active' : ''; ?>" type="button" data-zaza-dot="<?php echo esc_attr( $index ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Go to slide %d', 'child-theme' ), $index + 1 ) ); ?>" aria-current="<?php echo esc_attr( 0 === $index ? 'true' : 'false' ); ?>"></button>
				<?php endforeach; ?>
			</div>
			<button class="zaza-hero__arrow zaza-hero__arrow--next" type="button" data-zaza-next aria-label="<?php echo esc_attr__( 'Next slide', 'child-theme' ); ?>"></button>
		</div>
	</section>

	<section class="zaza-section zaza-featured" aria-labelledby="zaza-featured-title">
		<div class="zaza-section__header">
			<p class="zaza-eyebrow"><?php echo esc_html__( 'Featured / Best Sellers', 'child-theme' ); ?></p>
			<h2 id="zaza-featured-title" class="zaza-section__title"><?php echo esc_html__( 'Popular Picks', 'child-theme' ); ?></h2>
			<a class="zaza-section__link" href="<?php echo esc_url( zaza_home_shop_url() ); ?>"><?php echo esc_html__( 'View Shop', 'child-theme' ); ?></a>
		</div>

		<?php if ( ! empty( $zaza_products ) ) : ?>
			<div class="zaza-product-grid">
				<?php foreach ( $zaza_products as $product ) : ?>
					<?php zaza_home_render_product_card( $product ); ?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="zaza-empty-state">
				<h3><?php echo esc_html__( 'Products are coming soon.', 'child-theme' ); ?></h3>
				<p><?php echo esc_html__( 'Featured products will appear here automatically once WooCommerce products are published.', 'child-theme' ); ?></p>
			</div>
		<?php endif; ?>
	</section>

	<section class="zaza-section zaza-categories" aria-labelledby="zaza-categories-title">
		<div class="zaza-section__header">
			<p class="zaza-eyebrow"><?php echo esc_html__( 'Shop by Category', 'child-theme' ); ?></p>
			<h2 id="zaza-categories-title" class="zaza-section__title"><?php echo esc_html__( 'Browse the Club', 'child-theme' ); ?></h2>
		</div>

		<?php if ( ! empty( $zaza_categories ) ) : ?>
			<div class="zaza-category-grid">
				<?php foreach ( $zaza_categories as $category ) : ?>
					<?php zaza_home_render_category_card( $category ); ?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<div class="zaza-category-grid zaza-category-grid--placeholder">
				<?php
				$zaza_placeholder_categories = array(
					esc_html__( 'Flower', 'child-theme' ),
					esc_html__( 'Pre-Rolls', 'child-theme' ),
					esc_html__( 'Bundles', 'child-theme' ),
				);
				?>
				<?php foreach ( $zaza_placeholder_categories as $placeholder_category ) : ?>
					<div class="zaza-category-card zaza-category-card--placeholder">
							<span class="zaza-category-card__media" style="<?php echo esc_attr( sprintf( 'background-image: url("%s");', esc_url_raw( zaza_home_placeholder_image() ) ) ); ?>"></span>
							<span class="zaza-category-card__body">
								<span class="zaza-category-card__title"><?php echo esc_html( $placeholder_category ); ?></span>
								<span class="zaza-category-card__cta"><?php echo esc_html__( 'Soon', 'child-theme' ); ?></span>
							</span>
						</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</section>

	<section class="zaza-promo" aria-labelledby="zaza-promo-title">
		<div class="zaza-promo__media" style="<?php echo esc_attr( sprintf( 'background-image: url("%s");', esc_url_raw( $zaza_promo_img ) ) ); ?>"></div>
		<div class="zaza-promo__content">
			<p class="zaza-eyebrow"><?php echo esc_html__( 'Bundle Drop', 'child-theme' ); ?></p>
			<h2 id="zaza-promo-title" class="zaza-promo__title"><?php echo esc_html__( 'Bundle More, Browse Less', 'child-theme' ); ?></h2>
			<p class="zaza-promo__copy"><?php echo esc_html__( 'Rotating bundles, seasonal picks, and limited-time offers can live here once final promo details are ready.', 'child-theme' ); ?></p>
			<a class="zaza-button zaza-button--light" href="<?php echo esc_url( zaza_home_shop_url() ); ?>"><?php echo esc_html__( 'Explore Bundles', 'child-theme' ); ?></a>
		</div>
	</section>

	<section class="zaza-trust-strip" aria-label="<?php echo esc_attr__( 'Store policies', 'child-theme' ); ?>">
		<div class="zaza-trust-strip__item">
			<strong><?php echo esc_html__( 'Age Restricted', 'child-theme' ); ?></strong>
			<span><?php echo esc_html__( 'Adults 21+ where permitted.', 'child-theme' ); ?></span>
		</div>
		<div class="zaza-trust-strip__item">
			<strong><?php echo esc_html__( 'Secure Checkout', 'child-theme' ); ?></strong>
			<span><?php echo esc_html__( 'Encrypted payment flow.', 'child-theme' ); ?></span>
		</div>
		<div class="zaza-trust-strip__item">
			<strong><?php echo esc_html__( 'Discreet Packaging', 'child-theme' ); ?></strong>
			<span><?php echo esc_html__( 'Plain, protective shipments.', 'child-theme' ); ?></span>
		</div>
		<div class="zaza-trust-strip__item">
			<strong><?php echo esc_html__( 'Shipping Rules Apply', 'child-theme' ); ?></strong>
			<span><?php echo esc_html__( 'Availability varies by location.', 'child-theme' ); ?></span>
		</div>
	</section>

	<section class="zaza-section zaza-reviews" aria-labelledby="zaza-reviews-title">
		<div class="zaza-section__header">
			<p class="zaza-eyebrow"><?php echo esc_html__( 'Social Proof', 'child-theme' ); ?></p>
			<h2 id="zaza-reviews-title" class="zaza-section__title"><?php echo esc_html__( 'What Customers Notice', 'child-theme' ); ?></h2>
		</div>
		<div class="zaza-review-grid">
			<article class="zaza-review">
				<p><?php echo esc_html__( 'Clean layout, easy browsing, and the product details are simple to scan.', 'child-theme' ); ?></p>
				<strong><?php echo esc_html__( 'Avery M.', 'child-theme' ); ?></strong>
			</article>
			<article class="zaza-review">
				<p><?php echo esc_html__( 'Checkout felt straightforward and the category pages made reordering quick.', 'child-theme' ); ?></p>
				<strong><?php echo esc_html__( 'Jordan K.', 'child-theme' ); ?></strong>
			</article>
			<article class="zaza-review">
				<p><?php echo esc_html__( 'The featured picks helped me compare options without digging around.', 'child-theme' ); ?></p>
				<strong><?php echo esc_html__( 'Sam R.', 'child-theme' ); ?></strong>
			</article>
		</div>
	</section>
</main>

<?php
get_footer();

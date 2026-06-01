<?php
/**
 * Custom WooCommerce product archive layout for The Zaza Shop.
 *
 * Keeps WooCommerce product loops, sorting, pagination, and add-to-cart/select
 * options behavior intact while replacing the constrained block archive layout.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$archive_description = '';

if ( is_tax( 'product_cat' ) ) {
	$archive_description = term_description();
}

if ( function_exists( 'wc_set_loop_prop' ) ) {
	wc_set_loop_prop( 'columns', 4 );
}
?>

<main id="wp--skip-link--target" class="zaza-shop-main">
	<section class="zaza-shop-intro" aria-labelledby="zaza-shop-title">
		<?php if ( function_exists( 'woocommerce_breadcrumb' ) ) : ?>
			<div class="zaza-shop-breadcrumb">
				<?php woocommerce_breadcrumb(); ?>
			</div>
		<?php endif; ?>

		<h1 id="zaza-shop-title" class="zaza-shop-title"><?php echo esc_html( function_exists( 'woocommerce_page_title' ) ? woocommerce_page_title( false ) : get_the_archive_title() ); ?></h1>

		<?php if ( $archive_description ) : ?>
			<div class="zaza-shop-intro__description">
				<?php echo wp_kses_post( $archive_description ); ?>
			</div>
		<?php endif; ?>
	</section>

	<?php if ( function_exists( 'woocommerce_output_all_notices' ) ) : ?>
		<div class="zaza-shop-notices">
			<?php woocommerce_output_all_notices(); ?>
		</div>
	<?php endif; ?>

	<div class="zaza-shop-topbar">
		<div class="zaza-shop-count">
			<?php
			if ( function_exists( 'woocommerce_result_count' ) ) {
				woocommerce_result_count();
			}
			?>
		</div>
		<div class="zaza-shop-sort">
			<?php
			if ( function_exists( 'woocommerce_catalog_ordering' ) ) {
				woocommerce_catalog_ordering();
			}
			?>
		</div>
	</div>

	<div class="zaza-shop-layout">
		<?php
		if ( function_exists( 'zaza_child_get_shop_filter_panel_html' ) ) {
			echo zaza_child_get_shop_filter_panel_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
		?>

		<section class="zaza-shop-results" aria-label="<?php echo esc_attr__( 'Products', 'the-zaza-shop-child' ); ?>">
			<?php if ( woocommerce_product_loop() ) : ?>
				<?php woocommerce_product_loop_start(); ?>

				<?php while ( have_posts() ) : ?>
					<?php
					the_post();
					wc_get_template_part( 'content', 'product' );
					?>
				<?php endwhile; ?>

				<?php woocommerce_product_loop_end(); ?>

				<?php
				if ( function_exists( 'woocommerce_pagination' ) ) {
					woocommerce_pagination();
				}
				?>
			<?php else : ?>
				<?php
				if ( function_exists( 'wc_no_products_found' ) ) {
					wc_no_products_found();
				}
				?>
			<?php endif; ?>
		</section>
	</div>
</main>

<?php
get_footer();

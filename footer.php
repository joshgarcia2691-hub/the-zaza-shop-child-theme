<?php
/**
 * Minimal child theme document footer.
 *
 * Closes the wrapper opened in header.php and preserves wp_footer() so
 * WooCommerce, payment gateways, and plugins can print required scripts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<footer class="zaza-footer" aria-label="<?php echo esc_attr__( 'Site footer', 'the-zaza-shop-child' ); ?>">
	<div class="zaza-footer__inner">
		<div class="zaza-footer__top">
			<a class="zaza-footer__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php echo esc_attr__( 'The Zaza Club home', 'the-zaza-shop-child' ); ?>">
				<img class="zaza-footer__logo" src="<?php echo esc_url( 'https://thezazaclub.com/wp-content/uploads/2026/05/logo.jpeg' ); ?>" alt="" width="58" height="58">
				<span><?php echo esc_html__( 'The Zaza Club', 'the-zaza-shop-child' ); ?></span>
			</a>

			<nav class="zaza-footer__nav" aria-label="<?php echo esc_attr__( 'Footer product links', 'the-zaza-shop-child' ); ?>">
				<a href="<?php echo esc_url( home_url( '/product-category/flower/' ) ); ?>"><?php echo esc_html__( 'Flower', 'the-zaza-shop-child' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/product-category/edibles/' ) ); ?>"><?php echo esc_html__( 'Edibles', 'the-zaza-shop-child' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/product-category/smalls/' ) ); ?>"><?php echo esc_html__( 'Smalls', 'the-zaza-shop-child' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/product-category/thc-vape/' ) ); ?>"><?php echo esc_html__( 'THC Vape', 'the-zaza-shop-child' ); ?></a>
				<a href="<?php echo esc_url( home_url( '/product-category/accessories/' ) ); ?>"><?php echo esc_html__( 'Accessories', 'the-zaza-shop-child' ); ?></a>
			</nav>
		</div>

		<div class="zaza-footer__meta">
			<span><?php echo esc_html__( 'Adults 21+ where permitted.', 'the-zaza-shop-child' ); ?></span>
			<span><?php echo esc_html__( 'Secure checkout.', 'the-zaza-shop-child' ); ?></span>
			<span><?php echo esc_html__( 'Shipping rules apply.', 'the-zaza-shop-child' ); ?></span>
		</div>
	</div>
</footer>
</div>
<?php wp_footer(); ?>
</body>
</html>

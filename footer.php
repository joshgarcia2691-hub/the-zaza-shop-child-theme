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
<?php
if ( function_exists( 'zaza_render_custom_footer' ) ) {
	zaza_render_custom_footer();
}
?>
</div>
<?php wp_footer(); ?>
</body>
</html>

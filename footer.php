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
</div>
<?php wp_footer(); ?>
</body>
</html>

<?php
/**
 * Minimal child theme document header.
 *
 * Prevents WordPress theme-compat header output from adding the default site
 * title above the custom Zaza navigation while preserving normal wp_head()
 * behavior for WooCommerce, plugins, and theme assets.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">

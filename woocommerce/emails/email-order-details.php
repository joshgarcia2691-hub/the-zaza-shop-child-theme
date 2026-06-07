<?php
/**
 * Order details table for WooCommerce emails.
 *
 * This shared override fixes Product / Quantity / Price rendering across
 * WooCommerce email templates that call WC_Emails::order_details().
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package TheZazaShopChild\WooCommerce\Emails
 * @version 10.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

zaza_child_email_render_order_details( $order, $sent_to_admin, $plain_text, $email );

<?php
/**
 * Admin failed order email.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package TheZazaShopChild\WooCommerce\Emails
 * @version 10.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p>
<?php
printf(
	/* translators: 1: Order number, 2: Customer billing full name. */
	esc_html__( 'Payment for order #%1$s from %2$s has failed. The order was as follows:', 'woocommerce' ),
	esc_html( $order->get_order_number() ),
	esc_html( $order->get_formatted_billing_full_name() )
);
?>
</p>

<?php
zaza_child_email_render_order_details( $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

zaza_child_email_render_customer_addresses( $order );

zaza_child_email_render_additional_content( isset( $additional_content ) ? $additional_content : '' );

do_action( 'woocommerce_email_footer', $email );

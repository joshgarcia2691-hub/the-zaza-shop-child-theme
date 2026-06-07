<?php
/**
 * Admin new order email.
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
	/* translators: %s: Customer billing full name. */
	esc_html__( 'You have received a new order from %s:', 'woocommerce' ),
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

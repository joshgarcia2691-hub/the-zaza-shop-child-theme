<?php
/**
 * Customer completed order email.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package TheZazaShopChild\WooCommerce\Emails
 * @version 10.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_improvements_enabled = zaza_child_email_improvements_enabled();

do_action( 'woocommerce_email_header', $email_heading, $email );

echo $email_improvements_enabled ? '<div class="email-introduction">' : '';
zaza_child_email_render_greeting( $order );
?>
<p><?php esc_html_e( 'We have finished processing your order.', 'woocommerce' ); ?></p>
<p><?php esc_html_e( 'Here are the details of your order:', 'woocommerce' ); ?></p>
<?php
echo $email_improvements_enabled ? '</div>' : '';

zaza_child_email_render_order_details( $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

zaza_child_email_render_customer_addresses( $order );

zaza_child_email_render_additional_content( isset( $additional_content ) ? $additional_content : '' );

do_action( 'woocommerce_email_footer', $email );

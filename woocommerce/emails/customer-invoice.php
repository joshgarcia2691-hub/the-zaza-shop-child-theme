<?php
/**
 * Customer invoice / order details email.
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

if ( $order->needs_payment() ) :
	?>
	<p><?php esc_html_e( 'An order has been created for you. You can review and pay for this order using the link below.', 'woocommerce' ); ?></p>
	<p>
		<a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>">
			<?php esc_html_e( 'Pay for this order', 'woocommerce' ); ?>
		</a>
	</p>
	<?php
else :
	?>
	<p><?php esc_html_e( 'Here are the details of your order:', 'woocommerce' ); ?></p>
	<?php
endif;

echo $email_improvements_enabled ? '</div>' : '';

do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

zaza_child_email_render_customer_addresses( $order );

zaza_child_email_render_additional_content( isset( $additional_content ) ? $additional_content : '' );

do_action( 'woocommerce_email_footer', $email );

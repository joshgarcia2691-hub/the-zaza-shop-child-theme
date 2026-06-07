<?php
/**
 * Customer processing order email.
 *
 * This child-theme override keeps the email copy branded while letting
 * WooCommerce render the order table, quantity, price, totals, metadata, and
 * customer details through its standard hooks.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package TheZazaShopChild\WooCommerce\Emails
 * @version 10.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_improvements_enabled = class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' )
	&& \Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled( 'email_improvements' );

/*
 * @hooked WC_Emails::email_header() Output the email header.
 */
do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<?php echo $email_improvements_enabled ? '<div class="email-introduction">' : ''; ?>
<p>
<?php
if ( ! empty( $order->get_billing_first_name() ) ) {
	printf(
		/* translators: %s: Customer first name. */
		esc_html__( 'Hi %s,', 'woocommerce' ),
		esc_html( $order->get_billing_first_name() )
	);
} else {
	esc_html_e( 'Hi,', 'woocommerce' );
}
?>
</p>

<?php if ( $email_improvements_enabled ) : ?>
	<p><?php esc_html_e( 'Just to let you know &mdash; we have received your order, and it is now being processed.', 'woocommerce' ); ?></p>
	<p><?php esc_html_e( 'Here is a reminder of what you ordered:', 'woocommerce' ); ?></p>
<?php else : ?>
	<p>
	<?php
	printf(
		/* translators: %s: Order number. */
		esc_html__( 'Just to let you know &mdash; we have received your order #%s, and it is now being processed:', 'woocommerce' ),
		esc_html( $order->get_order_number() )
	);
	?>
	</p>
<?php endif; ?>
<?php echo $email_improvements_enabled ? '</div>' : ''; ?>

<?php
/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details and email address.
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additional content from WooCommerce email settings.
 */
if ( $additional_content ) {
	echo $email_improvements_enabled ? '<table border="0" cellpadding="0" cellspacing="0" width="100%" role="presentation"><tr><td class="email-additional-content">' : '';
	echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
	echo $email_improvements_enabled ? '</td></tr></table>' : '';
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer.
 */
do_action( 'woocommerce_email_footer', $email );

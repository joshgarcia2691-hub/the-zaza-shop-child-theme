<?php
/**
 * Customer processing order email.
 *
 * This template uses a compact, email-safe order table so Product, Quantity,
 * Price, totals, and addresses stay visible in restrictive mail clients.
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

$text_align = is_rtl() ? 'right' : 'left';

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
 * Preserve gateway instructions such as cash-on-delivery notes.
 */
do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email );
?>

<h2 style="margin: 24px 0 12px; text-align: <?php echo esc_attr( $text_align ); ?>;">
	<?php
	printf(
		/* translators: %s: Order number. */
		esc_html__( 'Order #%s', 'woocommerce' ),
		esc_html( $order->get_order_number() )
	);
	?>
	<span style="display: block; margin-top: 4px; font-size: 13px; font-weight: normal;">
		<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
	</span>
</h2>

<table cellspacing="0" cellpadding="8" border="1" width="100%" style="width: 100%; border-collapse: collapse; border: 1px solid #dcdcdc; table-layout: fixed;" role="presentation">
	<thead>
		<tr>
			<th scope="col" style="width: 58%; border: 1px solid #dcdcdc; text-align: <?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
			<th scope="col" style="width: 16%; border: 1px solid #dcdcdc; text-align: center;"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
			<th scope="col" style="width: 26%; border: 1px solid #dcdcdc; text-align: <?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $order->get_items() as $item_id => $item ) : ?>
			<?php
			$product      = $item->get_product();
			$product_name = $item->get_name();
			$item_meta    = wc_display_item_meta(
				$item,
				array(
					'before'    => '<div style="margin-top: 6px; color: #666; font-size: 12px;">',
					'after'     => '</div>',
					'separator' => '<br>',
					'echo'      => false,
				)
			);
			?>
			<tr>
				<td style="border: 1px solid #dcdcdc; text-align: <?php echo esc_attr( $text_align ); ?>; vertical-align: top; word-break: break-word;">
					<strong><?php echo esc_html( $product_name ); ?></strong>
					<?php if ( $product && $product->get_sku() ) : ?>
						<div style="margin-top: 4px; color: #666; font-size: 12px;">
							<?php echo esc_html( sprintf( '%s: %s', __( 'SKU', 'woocommerce' ), $product->get_sku() ) ); ?>
						</div>
					<?php endif; ?>
					<?php echo wp_kses_post( $item_meta ); ?>
				</td>
				<td style="border: 1px solid #dcdcdc; text-align: center; vertical-align: top;">
					<?php echo esc_html( $item->get_quantity() ); ?>
				</td>
				<td style="border: 1px solid #dcdcdc; text-align: <?php echo esc_attr( $text_align ); ?>; vertical-align: top;">
					<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tbody>
	<tfoot>
		<?php foreach ( $order->get_order_item_totals() as $total ) : ?>
			<tr>
				<th scope="row" colspan="2" style="border: 1px solid #dcdcdc; text-align: <?php echo esc_attr( $text_align ); ?>;">
					<?php echo wp_kses_post( $total['label'] ); ?>
				</th>
				<td style="border: 1px solid #dcdcdc; text-align: <?php echo esc_attr( $text_align ); ?>;">
					<?php echo wp_kses_post( $total['value'] ); ?>
				</td>
			</tr>
		<?php endforeach; ?>
	</tfoot>
</table>

<?php
do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
?>

<table cellspacing="0" cellpadding="0" border="0" width="100%" style="width: 100%; margin-top: 28px;" role="presentation">
	<tr>
		<td width="50%" style="width: 50%; padding: 0 12px 0 0; vertical-align: top; text-align: <?php echo esc_attr( $text_align ); ?>;">
			<h2 style="margin: 0 0 10px;"><?php esc_html_e( 'Billing address', 'woocommerce' ); ?></h2>
			<address style="font-style: normal; line-height: 1.6;">
				<?php echo wp_kses_post( $order->get_formatted_billing_address() ? $order->get_formatted_billing_address() : esc_html__( 'N/A', 'woocommerce' ) ); ?>
				<?php if ( $order->get_billing_phone() ) : ?>
					<br><?php echo esc_html( $order->get_billing_phone() ); ?>
				<?php endif; ?>
				<?php if ( $order->get_billing_email() ) : ?>
					<br><?php echo esc_html( $order->get_billing_email() ); ?>
				<?php endif; ?>
			</address>
		</td>
		<td width="50%" style="width: 50%; padding: 0 0 0 12px; vertical-align: top; text-align: <?php echo esc_attr( $text_align ); ?>;">
			<h2 style="margin: 0 0 10px;"><?php esc_html_e( 'Shipping address', 'woocommerce' ); ?></h2>
			<address style="font-style: normal; line-height: 1.6;">
				<?php echo wp_kses_post( $order->get_formatted_shipping_address() ? $order->get_formatted_shipping_address() : $order->get_formatted_billing_address() ); ?>
			</address>
		</td>
	</tr>
</table>

<?php
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

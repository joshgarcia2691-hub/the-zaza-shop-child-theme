<?php
/**
 * Order details table shown in emails.
 *
 * Keeps WooCommerce's native email item loop while allowing a light child-theme
 * override for the surrounding order table.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package TheZazaShopChild\WooCommerce\Emails
 * @version 10.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$email_improvements_enabled = zaza_child_email_improvements_enabled();
$block_email_editor_enabled = class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' )
	&& \Automattic\WooCommerce\Utilities\FeaturesUtil::feature_is_enabled( 'block_email_editor' );

/**
 * Filter whether to display the section divider in the email body.
 *
 * @since 10.6.0
 * @param bool $display_section_divider Whether to display the section divider. Default true.
 */
$display_section_divider   = (bool) apply_filters( 'woocommerce_email_body_display_section_divider', true );
$heading_class              = $email_improvements_enabled ? 'email-order-detail-heading' : '';
$order_table_class          = $email_improvements_enabled ? 'email-order-details' : '';
$order_total_text_align     = $email_improvements_enabled ? 'right' : 'left';
$order_quantity_text_align  = $email_improvements_enabled ? 'right' : 'left';

if ( $email_improvements_enabled ) {
	add_filter( 'woocommerce_order_shipping_to_display_shipped_via', '__return_false' );
}

/**
 * Action hook to add custom content before order details in email.
 *
 * @param WC_Order $order Order object.
 * @param bool     $sent_to_admin Whether it's sent to admin or customer.
 * @param bool     $plain_text Whether it's a plain text email.
 * @param WC_Email $email Email object.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
<?php
$order_details_heading = '';
if ( $email_improvements_enabled ) {
	/**
	 * Filter the heading text shown in the order details section of emails.
	 *
	 * @since 10.8.0
	 * @param string   $heading The heading text.
	 * @param WC_Order $order   Order object.
	 * @param WC_Email $email   Email object.
	 */
	$order_details_heading = apply_filters( 'woocommerce_email_order_details_heading', __( 'Order summary', 'woocommerce' ), $order, $email );
}

/**
 * Filter whether to display the order number in the order details heading of emails.
 *
 * @since 10.8.0
 * @param bool     $display Whether to display the order number. Default true.
 * @param WC_Order $order   Order object.
 * @param WC_Email $email   Email object.
 */
$display_order_number = (bool) apply_filters( 'woocommerce_email_display_order_number', true, $order, $email );
if ( $order_details_heading || $display_order_number ) :
	?>
	<h2 class="<?php echo esc_attr( $heading_class ); ?>">
		<?php
	if ( $email_improvements_enabled ) {
		echo wp_kses_post( $order_details_heading );
	}
	if ( $display_order_number ) {
		if ( $sent_to_admin ) {
			$before = '<a class="link" href="' . esc_url( $order->get_edit_order_url() ) . '"' . ( $block_email_editor_enabled ? ' style="text-decoration: none;"' : '' ) . '>';
			$after  = '</a>';
		} else {
			$before = '';
			$after  = '';
		}
		if ( $email_improvements_enabled ) {
			if ( $order_details_heading ) {
				echo '<br><span>';
			} else {
				echo '<span>';
			}
		}
		/* translators: %s: Order ID. */
		$order_number_string = __( '[Order #%s]', 'woocommerce' );
		if ( $email_improvements_enabled ) {
			/* translators: %s: Order ID. */
			$order_number_string = __( 'Order #%s', 'woocommerce' );
		}
		echo wp_kses_post( $before . sprintf( $order_number_string . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );
		if ( $email_improvements_enabled ) {
			echo '</span>';
		}
	}
		?>
	</h2>
	<?php
endif;
?>

<div style="margin-bottom: <?php echo $email_improvements_enabled ? '24px' : '40px'; ?>;">
	<table class="td font-family <?php echo esc_attr( $order_table_class ); ?>" cellspacing="0" cellpadding="6" style="width: 100%;" border="1">
		<?php if ( ! $block_email_editor_enabled ) : ?>
		<thead>
			<tr>
				<th class="td text-align-left" scope="col"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="td text-align-<?php echo esc_attr( $order_quantity_text_align ); ?>" scope="col"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
				<th class="td text-align-<?php echo esc_attr( $order_total_text_align ); ?>" scope="col"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
			</tr>
		</thead>
		<?php endif; ?>
		<tbody>
			<?php
			$text_align       = is_rtl() ? 'right' : 'left';
			$number_align     = is_rtl() ? 'left' : 'right';
			$item_cell_style  = 'color: #414141; border: 0; font-family: Helvetica Neue, Helvetica, Roboto, Arial, sans-serif; padding: 10px 12px; padding-left: 0; vertical-align: top; word-wrap: break-word;';
			$line_style       = 'border-bottom: 1px solid rgba(30, 30, 30, 0.12); padding: 10px 0;';
			$label_style      = 'color: #636363; font-size: 12px; text-transform: uppercase;';
			$amount_style     = 'color: #111111; font-weight: 700;';
			?>
			<tr class="order_item zaza-order-items-summary">
				<td class="td font-family text-align-<?php echo esc_attr( $text_align ); ?>" colspan="3" style="<?php echo esc_attr( $item_cell_style ); ?>" align="<?php echo esc_attr( $text_align ); ?>">
					<?php

					foreach ( $order->get_items() as $item_id => $item ) {
						$product       = $item->get_product();
						$sku           = '';
						$purchase_note = '';

						if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
							continue;
						}

						if ( is_object( $product ) ) {
							$sku           = $product->get_sku();
							$purchase_note = $product->get_purchase_note();
						}

						$qty          = $item->get_quantity();
						$refunded_qty = $order->get_qty_refunded_for_item( $item_id );

						if ( $refunded_qty ) {
							$qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
						} else {
							$qty_display = esc_html( $qty );
						}

						$qty_display = apply_filters( 'woocommerce_email_order_item_quantity', $qty_display, $item );
						?>
					<div class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>" style="<?php echo esc_attr( $line_style ); ?>">
						<strong style="color: #111111; font-weight: 700;"><?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) ); ?></strong>
						<?php
						if ( $sent_to_admin && $sku ) {
							echo wp_kses_post( '<br><span style="color: #636363;">#' . esc_html( $sku ) . '</span>' );
						}

						do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );

						$item_meta = wc_display_item_meta(
							$item,
							array(
								'before'       => '<div class="email-order-item-meta" style="color: #636363; font-size: 13px; line-height: 1.45; margin-top: 4px;">',
								'after'        => '</div>',
								'separator'    => '<br>',
								'echo'         => false,
								'label_before' => '<span style="font-weight: 700;">',
								'label_after'  => ':</span> ',
							)
						);

						echo wp_kses_post( $item_meta );

						do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text );
						?>
						<br><span style="<?php echo esc_attr( $label_style ); ?>"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?>:</span> <?php echo wp_kses_post( $qty_display ); ?>
						<br><span style="<?php echo esc_attr( $label_style ); ?>"><?php esc_html_e( 'Price', 'woocommerce' ); ?>:</span> <span style="<?php echo esc_attr( $amount_style ); ?>"><?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?></span>
						<?php if ( ! $sent_to_admin && $order->is_paid() && $purchase_note ) : ?>
							<div style="margin-top: 8px;"><?php echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) ); ?></div>
						<?php endif; ?>
					</div>
						<?php
					}

					$item_totals = $order->get_order_item_totals();

					if ( ! $item_totals ) {
						$item_totals = array(
							array(
								'label' => __( 'Subtotal:', 'woocommerce' ),
								'value' => $order->get_subtotal_to_display(),
								'type'  => 'cart_subtotal',
							),
						);

						if ( $order->get_shipping_method() || (float) $order->get_shipping_total() > 0 ) {
							$item_totals[] = array(
								'label' => __( 'Shipping:', 'woocommerce' ),
								'value' => $order->get_shipping_to_display(),
								'type'  => 'shipping',
							);
						}

						if ( $order->get_payment_method_title() ) {
							$item_totals[] = array(
								'label' => __( 'Payment method:', 'woocommerce' ),
								'value' => wp_kses_post( $order->get_payment_method_title() ),
								'type'  => 'payment_method',
							);
						}

						$item_totals[] = array(
							'label' => __( 'Total:', 'woocommerce' ),
							'value' => $order->get_formatted_order_total(),
							'type'  => 'total',
						);
					}

					foreach ( $item_totals as $total ) {
						?>
					<div class="order-totals order-totals-<?php echo esc_attr( $total['type'] ?? 'unknown' ); ?>" style="padding: 8px 0;">
						<span style="<?php echo esc_attr( $label_style ); ?>"><?php echo wp_kses_post( $total['label'] ); ?></span>
						<?php
						if ( isset( $total['meta'] ) ) {
							echo wp_kses_post( $total['meta'] );
						}
						?>
						<br><span style="<?php echo esc_attr( $amount_style ); ?>"><?php echo wp_kses_post( $total['value'] ); ?></span>
					</div>
						<?php
					}
					?>
				</td>
			</tr>
			<?php
			if ( $order->get_customer_note() && ! $email_improvements_enabled ) {
				?>
			<tr>
				<th class="td text-align-left" scope="row" colspan="2"><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
				<td class="td text-align-left"><?php echo wp_kses( nl2br( wc_wptexturize_order_note( $order->get_customer_note() ) ), array() ); ?></td>
			</tr>
				<?php
			}
			?>
		</tbody>
	</table>
	<?php if ( $order->get_customer_note() && $email_improvements_enabled ) : ?>
		<?php if ( $display_section_divider ) : ?>
			<hr style="border: 0; border-top: 1px solid #1E1E1E; border-top-color: rgba(30, 30, 30, 0.2); margin: 20px 0;">
		<?php endif; ?>
		<table class="td font-family <?php echo esc_attr( $order_table_class ); ?>" cellspacing="0" cellpadding="6" style="width: 100%;" border="1" role="presentation">
			<tr class="order-customer-note">
				<td class="td text-align-left">
					<b><?php esc_html_e( 'Customer note', 'woocommerce' ); ?></b><br>
					<?php echo wp_kses( nl2br( wc_wptexturize_order_note( $order->get_customer_note() ) ), array( 'br' => array() ) ); ?>
				</td>
			</tr>
		</table>
	<?php endif; ?>
</div>

<?php
if ( $email_improvements_enabled ) {
	remove_filter( 'woocommerce_order_shipping_to_display_shipped_via', '__return_false' );
}

/**
 * Action hook to add custom content after order details in email.
 *
 * @param WC_Order $order Order object.
 * @param bool     $sent_to_admin Whether it's sent to admin or customer.
 * @param bool     $plain_text Whether it's a plain text email.
 * @param WC_Email $email Email object.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email );

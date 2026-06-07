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
			$item_cell_style  = 'color: #414141; border: 0; font-family: Helvetica Neue, Helvetica, Roboto, Arial, sans-serif; padding: 10px 12px; padding-left: 0; vertical-align: top; word-wrap: break-word;';
			?>
			<tr class="zaza-order-items-summary">
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
					<p>
						<strong><?php echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) ); ?></strong>
						<?php
						if ( $sent_to_admin && $sku ) {
							echo wp_kses_post( '<br>#' . esc_html( $sku ) );
						}

						do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );

						$item_meta = wc_display_item_meta(
							$item,
							array(
								'before'       => '<br>',
								'after'        => '',
								'separator'    => '<br>',
								'echo'         => false,
								'label_before' => '<strong>',
								'label_after'  => ':</strong> ',
							)
						);

						echo wp_kses_post( $item_meta );

						do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text );
						?>
						<br><strong><?php esc_html_e( 'Quantity', 'woocommerce' ); ?>:</strong> <?php echo wp_kses_post( $qty_display ); ?>
						<br><strong><?php esc_html_e( 'Price', 'woocommerce' ); ?>:</strong> <?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
						<?php if ( ! $sent_to_admin && $order->is_paid() && $purchase_note ) : ?>
							<br><?php echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) ); ?>
						<?php endif; ?>
					</p>
						<?php
					}

					$subtotal_text = html_entity_decode( wp_strip_all_tags( $order->get_subtotal_to_display() ), ENT_QUOTES, get_bloginfo( 'charset' ) );
					$shipping_text = html_entity_decode( wp_strip_all_tags( $order->get_shipping_to_display() ), ENT_QUOTES, get_bloginfo( 'charset' ) );
					$total_text    = html_entity_decode( wp_strip_all_tags( $order->get_formatted_order_total() ), ENT_QUOTES, get_bloginfo( 'charset' ) );
					?>
					<br><?php esc_html_e( 'Subtotal:', 'woocommerce' ); ?> <?php echo esc_html( $subtotal_text ); ?>
					<br><?php esc_html_e( 'Shipping:', 'woocommerce' ); ?> <?php echo esc_html( $shipping_text ); ?>
					<br><?php esc_html_e( 'Total:', 'woocommerce' ); ?> <?php echo esc_html( $total_text ); ?>
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

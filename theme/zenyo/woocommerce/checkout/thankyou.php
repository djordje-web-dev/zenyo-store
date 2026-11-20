<?php
/**
 * Stranica posle porudzbine ("Thank you").
 * Sve se cita iz sacuvane porudzbine, ne iz korpe: broj, datum, adresa, dostava, placanje, stavke.
 * Ako placanje nije uspelo, isti raspored prikazuje poruku o neuspehu.
 */

defined( 'ABSPATH' ) || exit;

if ( ! $order ) {
	?>
	<div class="zy-flow__empty">
		<p><?php esc_html_e( 'There is no recent order to show.', 'zenyo' ); ?></p>
		<a class="zy-summary__btn zy-summary__btn--inline" href="<?php echo esc_url( zenyo_shop_url() ); ?>"><?php esc_html_e( 'Return to shop', 'zenyo' ); ?></a>
	</div>
	<?php
	return;
}

$zenyo_pay    = zenyo_order_payment_info( $order );
$zenyo_failed = $zenyo_pay['failed'];
$zenyo_count  = $order->get_item_count();

// Nacin dostave: naziv i (ako postoji) napomena posle crte, npr. "1–2 business days".
list( $zenyo_ship_label, $zenyo_ship_note ) = zenyo_shipping_label_parts( $order->get_shipping_method() );

$zenyo_address = array_filter(
	array(
		trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
		$order->get_billing_address_1(),
		trim( $order->get_billing_city() . ' ' . $order->get_billing_postcode() ),
		WC()->countries->countries[ $order->get_billing_country() ] ?? '',
	)
);

if ( $zenyo_failed ) {
	$zenyo_subline = __( 'Your cart has been kept, so nothing is lost.', 'zenyo' );
	$zenyo_next    = array(
		__( 'No order has been completed and your cart is unchanged.', 'zenyo' ),
		__( 'You can return to your cart and try again.', 'zenyo' ),
		__( 'You can contact us if you have any questions.', 'zenyo' ),
	);
} else {
	/* translators: %s: mejl adresa kupca */
	$zenyo_subline = sprintf( __( 'We have sent a confirmation email to %s', 'zenyo' ), $order->get_billing_email() );
	$zenyo_next    = array(
		__( 'You will receive an order confirmation email with all details.', 'zenyo' ),
		__( 'We will prepare your order and notify you when it is shipped.', 'zenyo' ),
		__( 'You can contact us if you have any questions.', 'zenyo' ),
	);
}
?>
<div class="zy-thanks woocommerce-order">

	<div class="zy-thanks__main">

		<div class="zy-thanks__lead">
			<p class="zy-thanks__headline"><?php echo esc_html( $zenyo_pay['headline'] ); ?></p>
			<p><?php echo esc_html( $zenyo_subline ); ?></p>
		</div>

		<div class="zy-thanks__box">
			<div>
				<span class="zy-thanks__k"><?php esc_html_e( 'Order number', 'zenyo' ); ?></span>
				<span class="zy-thanks__number">#<?php echo esc_html( $order->get_order_number() ); ?></span>
			</div>
			<div>
				<span class="zy-thanks__k"><?php esc_html_e( 'Order date', 'zenyo' ); ?></span>
				<span class="zy-thanks__date"><?php echo esc_html( wc_format_datetime( $order->get_date_created(), 'j M Y, H:i' ) ); ?></span>
			</div>
		</div>

		<?php if ( $zenyo_failed ) : ?>
			<p class="zy-thanks__retry">
				<a class="zy-summary__btn zy-summary__btn--inline" href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>"><?php esc_html_e( 'Try payment again', 'zenyo' ); ?></a>
			</p>
		<?php endif; ?>

		<section class="zy-thanks__section" aria-labelledby="zy-thanks-delivery">
			<h2 id="zy-thanks-delivery"><?php esc_html_e( 'Delivery information', 'zenyo' ); ?></h2>
			<dl>
				<?php if ( $zenyo_ship_label ) : ?>
					<dt><?php esc_html_e( 'Delivery method', 'zenyo' ); ?></dt><dd><?php echo esc_html( $zenyo_ship_label ); ?></dd>
				<?php endif; ?>
				<?php if ( $zenyo_ship_note && preg_match( '/[0-9]/', $zenyo_ship_note ) ) : // samo ako napomena sadrzi rok, npr. "1–2 business days" ?>
					<dt><?php esc_html_e( 'Estimated delivery', 'zenyo' ); ?></dt><dd><?php echo esc_html( $zenyo_ship_note ); ?></dd>
				<?php endif; ?>
				<dt><?php esc_html_e( 'Shipping address', 'zenyo' ); ?></dt>
				<dd>
					<?php foreach ( $zenyo_address as $line ) : ?>
						<span><?php echo esc_html( $line ); ?></span>
					<?php endforeach; ?>
				</dd>
				<?php if ( $order->get_customer_note() ) : ?>
					<dt><?php esc_html_e( 'Order note', 'zenyo' ); ?></dt><dd><?php echo esc_html( $order->get_customer_note() ); ?></dd>
				<?php endif; ?>
			</dl>
		</section>

		<section class="zy-thanks__section" aria-labelledby="zy-thanks-payment">
			<h2 id="zy-thanks-payment"><?php esc_html_e( 'Payment information', 'zenyo' ); ?></h2>
			<dl>
				<dt><?php esc_html_e( 'Payment method', 'zenyo' ); ?></dt><dd><?php echo esc_html( $order->get_payment_method_title() ); ?></dd>
				<dt><?php esc_html_e( 'Payment status', 'zenyo' ); ?></dt>
				<dd>
					<strong><?php echo esc_html( $zenyo_pay['status'] ); ?></strong>
					<?php if ( $zenyo_pay['note'] ) : ?>
						<span class="zy-thanks__note"><?php echo esc_html( $zenyo_pay['note'] ); ?></span>
					<?php endif; ?>
				</dd>
			</dl>
			<?php
			// Ovde nacin placanja ispisuje svoja uputstva (npr. podaci za uplatu na racun).
			if ( ! $zenyo_failed ) {
				echo '<div class="zy-thanks__gateway">';
				do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() );
				echo '</div>';
			}
			?>
		</section>

		<section class="zy-thanks__section" aria-labelledby="zy-thanks-next">
			<h2 id="zy-thanks-next"><?php esc_html_e( 'What happens next?', 'zenyo' ); ?></h2>
			<?php foreach ( $zenyo_next as $line ) : ?>
				<p><?php echo esc_html( $line ); ?></p>
			<?php endforeach; ?>
		</section>

	</div>

	<aside class="zy-summary" aria-label="<?php esc_attr_e( 'Order summary', 'zenyo' ); ?>">
		<h2 class="zy-summary__title"><?php echo esc_html( sprintf( /* translators: %d: broj komada */ _n( 'Order summary (%d item)', 'Order summary (%d items)', $zenyo_count, 'zenyo' ), $zenyo_count ) ); ?></h2>
		<div class="zy-summary__cols" aria-hidden="true">
			<span><?php esc_html_e( 'Product', 'zenyo' ); ?></span>
			<span><?php esc_html_e( 'Qty', 'zenyo' ); ?></span>
			<span><?php esc_html_e( 'Total', 'zenyo' ); ?></span>
		</div>
		<ul class="zy-summary__lines">
			<?php
			foreach ( $order->get_items() as $item ) {
				$zenyo_parent = wc_get_product( $item->get_product_id() );
				$zenyo_qty    = $item->get_quantity();
				$zenyo_line   = (float) $item->get_total() + (float) $item->get_total_tax();
				zenyo_summary_line(
					$zenyo_parent ? $zenyo_parent->get_name() : $item->get_name(),
					zenyo_order_item_variant( $item ),
					$zenyo_qty,
					$zenyo_line,
					$zenyo_qty ? $zenyo_line / $zenyo_qty : $zenyo_line
				);
			}
			?>
		</ul>
		<dl class="zy-summary__rows">
			<div><dt><?php echo esc_html( zenyo_subtotal_label( $zenyo_count ) ); ?></dt><dd><?php echo wp_kses_post( wc_price( $order->get_subtotal() + $order->get_cart_tax() ) ); ?></dd></div>
			<?php if ( $order->get_shipping_method() ) : ?>
				<div><dt><?php esc_html_e( 'Shipping', 'zenyo' ); ?></dt><dd><?php echo wp_kses_post( wc_price( (float) $order->get_shipping_total() + (float) $order->get_shipping_tax() ) ); ?></dd></div>
			<?php endif; ?>
		</dl>
		<div class="zy-summary__grand zy-summary__grand--last">
			<span><?php esc_html_e( 'Total', 'zenyo' ); ?></span>
			<span class="zy-summary__amount"><?php echo wp_kses_post( wc_price( $order->get_total() ) ); ?></span>
		</div>
	</aside>

</div>
<?php
// Ovde se kace dodaci (analitika, fiskalni racuni...). Tabelu sa stavkama smo iskljucili u inc/checkout.php.
do_action( 'woocommerce_thankyou', $order->get_id() );

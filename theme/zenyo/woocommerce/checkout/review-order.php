<?php
/**
 * Pregled porudzbine na naplati: stavke, medjuzbir, dostava, ukupno. Samo tekst, bez slika.
 * WooCommerce ovaj deo sam osvezava (npr. kad se promeni nacin dostave) i nalazi ga
 * po klasi "woocommerce-checkout-review-order-table", zato ona mora da ostane.
 */

defined( 'ABSPATH' ) || exit;

$zenyo_cart  = WC()->cart;
$zenyo_count = $zenyo_cart->get_cart_contents_count();
?>
<div class="woocommerce-checkout-review-order-table">

	<div class="zy-summary__cols" aria-hidden="true">
		<span><?php esc_html_e( 'Product', 'zenyo' ); ?></span>
		<span><?php esc_html_e( 'Qty', 'zenyo' ); ?></span>
		<span><?php esc_html_e( 'Total', 'zenyo' ); ?></span>
	</div>

	<ul class="zy-summary__lines">
		<?php
		foreach ( $zenyo_cart->get_cart() as $cart_item ) {
			$_product = $cart_item['data'];
			if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
				continue;
			}
			$zenyo_parent = wc_get_product( $cart_item['product_id'] );
			zenyo_summary_line(
				$zenyo_parent ? $zenyo_parent->get_name() : $_product->get_name(),
				zenyo_cart_item_variant( $cart_item ),
				$cart_item['quantity'],
				$cart_item['line_total'] + $cart_item['line_tax'],
				wc_get_price_to_display( $_product )
			);
		}
		?>
	</ul>

	<dl class="zy-summary__rows">
		<div><dt><?php echo esc_html( zenyo_subtotal_label( $zenyo_count ) ); ?></dt><dd><?php echo wp_kses_post( $zenyo_cart->get_cart_subtotal() ); ?></dd></div>
		<?php if ( $zenyo_cart->needs_shipping() ) : ?>
			<div><dt><?php esc_html_e( 'Shipping', 'zenyo' ); ?></dt><dd><?php echo wp_kses_post( $zenyo_cart->get_cart_shipping_total() ); ?></dd></div>
		<?php endif; ?>
		<?php foreach ( $zenyo_cart->get_fees() as $fee ) : ?>
			<div><dt><?php echo esc_html( $fee->name ); ?></dt><dd><?php echo wp_kses_post( wc_price( $fee->total ) ); ?></dd></div>
		<?php endforeach; ?>
	</dl>

	<div class="zy-summary__grand">
		<span><?php esc_html_e( 'Total', 'zenyo' ); ?></span>
		<span class="zy-summary__amount"><?php echo wp_kses_post( wc_price( $zenyo_cart->get_total( 'edit' ) ) ); ?></span>
	</div>

</div>

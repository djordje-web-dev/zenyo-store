<?php
/**
 * Naplata: cetiri odeljka levo (kontakt, adresa, dostava, placanje), pregled porudzbine desno.
 *
 * Nazivi "checkout", "order_review", "payment", "place_order" i klase "woocommerce-checkout-*"
 * su WooCommerce-ovi i moraju da ostanu: njegova skripta i dodaci banaka ih traze.
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_checkout_form', $checkout );

if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

$zenyo_fields = $checkout->get_checkout_fields();
$zenyo_groups = zenyo_checkout_field_groups();
$zenyo_count  = WC()->cart->get_cart_contents_count();
?>
<form name="checkout" method="post" class="checkout woocommerce-checkout zy-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data" aria-label="<?php esc_attr_e( 'Checkout form', 'zenyo' ); ?>" novalidate>

	<div class="zy-checkout__main">

		<section class="zy-step" aria-labelledby="zy-step-1">
			<h2 class="zy-step__title" id="zy-step-1"><span class="zy-step__num" aria-hidden="true">1</span><?php esc_html_e( 'Contact', 'zenyo' ); ?></h2>
			<div class="zy-step__fields">
				<?php
				foreach ( $zenyo_groups['contact'] as $key ) {
					if ( isset( $zenyo_fields['billing'][ $key ] ) ) {
						woocommerce_form_field( $key, $zenyo_fields['billing'][ $key ], $checkout->get_value( $key ) );
					}
				}
				?>
			</div>
		</section>

		<section class="zy-step" aria-labelledby="zy-step-2">
			<h2 class="zy-step__title" id="zy-step-2"><span class="zy-step__num" aria-hidden="true">2</span><?php esc_html_e( 'Delivery address', 'zenyo' ); ?></h2>
			<div class="zy-step__fields">
				<?php
				foreach ( $zenyo_groups['address'] as $key ) {
					if ( isset( $zenyo_fields['billing'][ $key ] ) ) {
						woocommerce_form_field( $key, $zenyo_fields['billing'][ $key ], $checkout->get_value( $key ) );
					}
				}
				// Polja koja bi neki dodatak mogao da doda, da se ne izgube.
				foreach ( $zenyo_fields['billing'] as $key => $field ) {
					if ( ! in_array( $key, array_merge( $zenyo_groups['contact'], $zenyo_groups['address'] ), true ) ) {
						woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
					}
				}
				if ( isset( $zenyo_fields['order']['order_comments'] ) ) {
					woocommerce_form_field( 'order_comments', $zenyo_fields['order']['order_comments'], $checkout->get_value( 'order_comments' ) );
				}
				?>
			</div>
		</section>

		<?php if ( WC()->cart->needs_shipping() ) : ?>
			<section class="zy-step" aria-labelledby="zy-step-3">
				<h2 class="zy-step__title" id="zy-step-3"><span class="zy-step__num" aria-hidden="true">3</span><?php esc_html_e( 'Delivery method', 'zenyo' ); ?></h2>
				<?php echo zenyo_shipping_methods_html(); // phpcs:ignore ?>
			</section>
		<?php endif; ?>

		<section class="zy-step" aria-labelledby="zy-step-4">
			<h2 class="zy-step__title" id="zy-step-4"><span class="zy-step__num" aria-hidden="true"><?php echo WC()->cart->needs_shipping() ? '4' : '3'; ?></span><?php esc_html_e( 'Payment method', 'zenyo' ); ?></h2>
			<?php woocommerce_checkout_payment(); // sablon: woocommerce/checkout/payment.php ?>
		</section>

	</div>

	<aside class="zy-summary zy-summary--sticky woocommerce-checkout-review-order" id="order_review" aria-label="<?php esc_attr_e( 'Your order', 'zenyo' ); ?>">
		<h2 class="zy-summary__title"><?php echo esc_html( sprintf( /* translators: %d: broj komada */ _n( 'Your order (%d item)', 'Your order (%d items)', $zenyo_count, 'zenyo' ), $zenyo_count ) ); ?></h2>

		<?php woocommerce_order_review(); // sablon: woocommerce/checkout/review-order.php ?>

		<div class="form-row place-order">
			<?php wc_get_template( 'checkout/terms.php' ); ?>
			<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
			<button type="submit" class="zy-summary__btn" name="woocommerce_checkout_place_order" id="place_order" value="<?php esc_attr_e( 'Place order', 'zenyo' ); ?>" data-value="<?php esc_attr_e( 'Place order', 'zenyo' ); ?>"><?php esc_html_e( 'Place order', 'zenyo' ); ?></button>
			<?php do_action( 'woocommerce_review_order_after_submit' ); ?>
			<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
		</div>
	</aside>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

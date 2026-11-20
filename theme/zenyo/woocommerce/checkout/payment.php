<?php
/**
 * Nacini placanja na naplati (odeljak 4).
 * Dugme "Place order" nije ovde nego ispod pregleda porudzbine (form-checkout.php), kao u dizajnu.
 *
 * Svaki nacin placanja dolazi od WooCommerce-a ili dodatka banke. Ako dodatak ima svoja polja
 * ili napomenu, ispisuju se ispod naziva. Nazivi "payment", "wc_payment_methods", "payment_box"
 * i "payment_method_*" su WooCommerce-ovi i moraju da ostanu.
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="payment" class="woocommerce-checkout-payment">
	<?php if ( WC()->cart->needs_payment() ) : ?>
		<ul class="wc_payment_methods payment_methods methods zy-options">
			<?php
			if ( ! empty( $available_gateways ) ) {
				foreach ( $available_gateways as $gateway ) {
					?>
					<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?>">
						<label class="zy-option" for="payment_method_<?php echo esc_attr( $gateway->id ); ?>">
							<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>">
							<span class="zy-option__text">
								<span class="zy-option__label"><?php echo wp_kses_post( $gateway->get_title() ); ?></span>
								<?php if ( $gateway->get_description() && ! $gateway->has_fields() ) : ?>
									<span class="zy-option__note"><?php echo wp_kses_post( wp_strip_all_tags( $gateway->get_description() ) ); ?></span>
								<?php endif; ?>
							</span>
							<?php if ( $gateway->get_icon() ) : ?>
								<span class="zy-option__icon"><?php echo $gateway->get_icon(); // phpcs:ignore ?></span>
							<?php endif; ?>
						</label>
						<?php if ( $gateway->has_fields() ) : ?>
							<div class="payment_box payment_method_<?php echo esc_attr( $gateway->id ); ?>" <?php echo $gateway->chosen ? '' : 'style="display:none;"'; ?>>
								<?php $gateway->payment_fields(); ?>
							</div>
						<?php endif; ?>
					</li>
					<?php
				}
			} else {
				echo '<li class="zy-options__empty">' . esc_html__( 'No payment methods are available. Please contact us.', 'zenyo' ) . '</li>';
			}
			?>
		</ul>
	<?php endif; ?>
</div>

<?php
/**
 * Prazna korpa. Klase "wc-empty-cart-message" i "cart-empty" moraju da ostanu:
 * WooCommerce-ova skripta ih trazi kad se iz korpe ukloni poslednja stavka.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="zy-flow__empty wc-empty-cart-message">
	<p class="cart-empty"><?php esc_html_e( 'Your cart is empty.', 'zenyo' ); ?></p>
	<a class="zy-summary__btn zy-summary__btn--inline" href="<?php echo esc_url( zenyo_shop_url() ); ?>"><?php esc_html_e( 'Continue shopping', 'zenyo' ); ?></a>
</div>

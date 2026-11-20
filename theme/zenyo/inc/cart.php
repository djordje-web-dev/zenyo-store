<?php
/**
 * Brojac korpe u zaglavlju: "CART (2)".
 */

/** Broj komada u korpi (pojas x2 se broji kao 2). */
function zenyo_cart_count() {
	return ( function_exists( 'WC' ) && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
}

function zenyo_cart_count_html() {
	return '<span class="zy-cart__count">' . zenyo_cart_count() . '</span>';
}

/**
 * Kad kupac doda nesto u korpu bez ponovnog ucitavanja stranice, WooCommerce nam ovde
 * daje priliku da mu posaljemo nov broj, pa ga on sam zameni u zaglavlju.
 */
add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$fragments['span.zy-cart__count'] = zenyo_cart_count_html();
		return $fragments;
	}
);

// Skripta koja radi tu zamenu; WooCommerce je vise ne ucitava sam.
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( function_exists( 'WC' ) ) {
			wp_enqueue_script( 'wc-cart-fragments' );
		}
	},
	20
);

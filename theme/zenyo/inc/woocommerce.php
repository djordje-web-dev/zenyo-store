<?php
/**
 * Sitna prilagodjavanja WooCommerce-a koja vaze na celom sajtu.
 */

/**
 * Cena za prikaz u listama: "8.490 RSD", ili "From 5.590 RSD" kad cena zavisi od izbora.
 */
function zenyo_price_label( $product ) {
	if ( $product->is_type( 'variable' ) ) {
		$min = $product->get_variation_price( 'min', true );
		$max = $product->get_variation_price( 'max', true );
		if ( '' === $min ) {
			return '';
		}
		return $min === $max
			? wc_price( $min )
			/* translators: %s: najniza cena */
			: sprintf( __( 'From %s', 'zenyo' ), wc_price( $min ) );
	}
	return '' === $product->get_price() ? '' : wc_price( wc_get_price_to_display( $product ) );
}

// WooCommerce dinar ispisuje cirilicom ("рсд"). Dizajn trazi "RSD".
add_filter(
	'woocommerce_currency_symbol',
	function ( $symbol, $currency ) {
		return 'RSD' === $currency ? 'RSD' : $symbol;
	},
	10,
	2
);

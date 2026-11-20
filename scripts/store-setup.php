<?php
/**
 * Osnovna podesavanja WooCommerce-a za ZENYO.
 * Pokrece se kroz setup.sh (wp eval-file). Bezbedno za ponovno pokretanje.
 */

if ( ! class_exists( 'WooCommerce' ) ) {
	WP_CLI::error( 'WooCommerce nije aktivan.' );
}

$options = array(
	// Srbija, dinari, cena u obliku "29.900 RSD".
	'woocommerce_default_country'                          => 'RS',
	'woocommerce_currency'                                 => 'RSD',
	'woocommerce_currency_pos'                             => 'right_space',
	'woocommerce_price_thousand_sep'                       => '.',
	'woocommerce_price_decimal_sep'                        => ',',
	'woocommerce_price_num_decimals'                       => 0,
	'woocommerce_weight_unit'                              => 'kg',
	'woocommerce_dimension_unit'                           => 'cm',

	// Prodaja i dostava samo u Srbiji (za pocetak).
	'woocommerce_allowed_countries'                        => 'specific',
	'woocommerce_specific_allowed_countries'               => array( 'RS' ),
	'woocommerce_ship_to_countries'                        => '',

	// Porez: cene su unete sa PDV-om. Detalji se podesavaju sa knjigovodjom.
	'woocommerce_calc_taxes'                               => 'no',
	'woocommerce_prices_include_tax'                       => 'yes',

	// Kupovina bez naloga.
	'woocommerce_enable_guest_checkout'                    => 'yes',
	'woocommerce_enable_checkout_login_reminder'           => 'no',
	'woocommerce_enable_signup_and_login_from_checkout'    => 'no',
	'woocommerce_enable_myaccount_registration'            => 'no',

	// Dostava ide na adresu iz porudzbine (nema posebne "adrese za dostavu").
	'woocommerce_ship_to_destination'                      => 'billing_only',
	// Dok kupac ne unese adresu, racunamo dostavu kao za Srbiju, da korpa odmah pokaze cenu.
	'woocommerce_default_customer_address'                 => 'base',

	// Mejlovi: posiljalac i boje (Dark/Light Zenyo).
	'woocommerce_email_from_name'                          => 'ZENYO',
	'woocommerce_email_base_color'                         => '#0a0a0a',
	'woocommerce_email_background_color'                   => '#f3e7d8',
	'woocommerce_email_body_background_color'              => '#ffffff',
	'woocommerce_email_text_color'                         => '#0a0a0a',
	'woocommerce_email_footer_text'                        => 'ZENYO — Minimum Effort. Maximum Efficiency.',

	// Kuponi za popust se za sada ne koriste (nema ih u dizajnu).
	'woocommerce_enable_coupons'                           => 'no',

	// Zalihe.
	'woocommerce_manage_stock'                             => 'yes',
	'woocommerce_notify_low_stock_amount'                  => 2,
	'woocommerce_hide_out_of_stock_items'                  => 'no',

	// Bez carobnjaka i bez "coming soon" rezima na lokalu.
	'woocommerce_coming_soon'                              => 'no',
	'woocommerce_onboarding_profile'                       => array( 'skipped' => true ),
	'woocommerce_task_list_hidden'                         => 'yes',
	'woocommerce_show_marketplace_suggestions'             => 'no',
	'woocommerce_allow_tracking'                           => 'no',
);
foreach ( $options as $key => $value ) {
	update_option( $key, $value );
}
WP_CLI::log( 'Opcije upisane.' );

// Stranice prodavnice. Korpa i naplata idu kao klasicne (shortcode), ne kao blokovi,
// da bismo mogli da im menjamo sablone i da rade dodaci domacih banaka.
WC_Install::create_pages();
$classic = array(
	'cart'     => '<!-- wp:shortcode -->[woocommerce_cart]<!-- /wp:shortcode -->',
	'checkout' => '<!-- wp:shortcode -->[woocommerce_checkout]<!-- /wp:shortcode -->',
);
foreach ( $classic as $page => $content ) {
	$page_id = wc_get_page_id( $page );
	if ( $page_id > 0 ) {
		wp_update_post( array( 'ID' => $page_id, 'post_content' => $content ) );
	}
}
WP_CLI::log( 'Korpa i naplata prebacene na klasicnu verziju.' );

// Placanje pouzecem.
update_option(
	'woocommerce_cod_settings',
	array(
		'enabled'            => 'yes',
		'title'              => 'Cash on delivery',
		'description'        => 'Pay with cash when your order is delivered.',
		'instructions'       => 'Pay with cash when your order is delivered.',
		'enable_for_methods' => array(),
		'enable_for_virtual' => 'no',
	)
);
WP_CLI::log( 'Pouzece ukljuceno.' );

// Zona dostave "Srbija" sa fiksnom cenom. 390 RSD je privremena vrednost iz dizajna.
$exists = false;
foreach ( WC_Shipping_Zones::get_zones() as $z ) {
	if ( 'Srbija' === $z['zone_name'] ) {
		$exists = true;
	}
}
if ( ! $exists ) {
	$zone = new WC_Shipping_Zone();
	$zone->set_zone_name( 'Srbija' );
	$zone->save();
	$zone->add_location( 'RS', 'country' );
	$zone->save();
	$instance_id = $zone->add_shipping_method( 'flat_rate' );
	update_option(
		'woocommerce_flat_rate_' . $instance_id . '_settings',
		array(
			'title'      => 'Standard delivery',
			'tax_status' => 'none',
			'cost'       => '390',
		)
	);
	WP_CLI::log( 'Zona dostave napravljena (390 RSD, privremeno).' );
}

// Drugi nacin dostave iz dizajna. Tekst posle crte je napomena koja se ispisuje ispod naziva.
// Cene i rokovi su privremeni, iz dizajna.
foreach ( WC_Shipping_Zones::get_zones() as $z ) {
	if ( 'Srbija' !== $z['zone_name'] ) {
		continue;
	}
	$zone   = new WC_Shipping_Zone( $z['id'] );
	$titles = array();
	foreach ( $zone->get_shipping_methods() as $method ) {
		$titles[] = $method->get_title();
		if ( 'Standard delivery' === $method->get_title() ) {
			$settings          = get_option( 'woocommerce_flat_rate_' . $method->get_instance_id() . '_settings', array() );
			$settings['title'] = 'Standard delivery — Delivery across Serbia';
			update_option( 'woocommerce_flat_rate_' . $method->get_instance_id() . '_settings', $settings );
		}
	}
	if ( ! preg_grep( '/^Express delivery/', $titles ) ) {
		$instance_id = $zone->add_shipping_method( 'flat_rate' );
		update_option(
			'woocommerce_flat_rate_' . $instance_id . '_settings',
			array(
				'title'      => 'Express delivery — 1–2 business days',
				'tax_status' => 'none',
				'cost'       => '590',
			)
		);
		WP_CLI::log( 'Dodata ekspres dostava (590 RSD, privremeno).' );
	}
}

// Uklanjanje probnog sadrzaja koji WordPress pravi pri instalaciji.
foreach ( array( 'hello-world' => 'post', 'sample-page' => 'page' ) as $slug => $type ) {
	$found = get_page_by_path( $slug, OBJECT, $type );
	if ( $found ) {
		wp_delete_post( $found->ID, true );
	}
}

WP_CLI::success( 'Podesavanja prodavnice zavrsena.' );

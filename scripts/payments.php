<?php
/**
 * Placanje karticama: RaiAccept (Raiffeisen banka).
 * Pokrece se kroz setup.sh. Pristupne podatke cita iz fajla .env, da ne budu u kodu ni u Git-u:
 *
 *   RAIACCEPT_API_USERNAME, RAIACCEPT_API_PASSWORD    probni nalog (sandbox)
 *   RAIACCEPT_PROD_USERNAME, RAIACCEPT_PROD_PASSWORD  pravi nalog; stize od banke posle ugovora
 *   RAIACCEPT_MODE=production                         prebacuje na prava placanja (podrazumevano: sandbox)
 *
 * Dok podataka nema, kartice se na naplati ne prikazuju i ostaje samo pouzece.
 */

if ( ! class_exists( 'RaiAccept_Gateway' ) ) {
	WP_CLI::warning( 'Dodatak Raiffeisen Payment Gateway nije ukljucen; kartice preskocene.' );
	return;
}

$zp_env = function ( $key ) {
	return trim( (string) getenv( $key ) );
};

$zp_mode = 'production' === $zp_env( 'RAIACCEPT_MODE' ) ? 'production' : 'sandbox';
$zp_user = $zp_env( 'production' === $zp_mode ? 'RAIACCEPT_PROD_USERNAME' : 'RAIACCEPT_API_USERNAME' );
$zp_pass = $zp_env( 'production' === $zp_mode ? 'RAIACCEPT_PROD_PASSWORD' : 'RAIACCEPT_API_PASSWORD' );
$zp_on   = '' !== $zp_user && '' !== $zp_pass;

$zp_old = get_option( 'woocommerce_raiaccept_settings', array() );
$zp_new = array_merge(
	is_array( $zp_old ) ? $zp_old : array(),
	array(
		'enabled'             => $zp_on ? 'yes' : 'no',
		'enable_plugin'       => $zp_on ? 'yes' : 'no',
		'api_mode'            => $zp_mode,
		'sandbox_username'    => $zp_env( 'RAIACCEPT_API_USERNAME' ),
		'sandbox_password'    => $zp_env( 'RAIACCEPT_API_PASSWORD' ),
		'production_username' => $zp_env( 'RAIACCEPT_PROD_USERNAME' ),
		'production_password' => $zp_env( 'RAIACCEPT_PROD_PASSWORD' ),
		// Nazivi su engleski; srpski prevod je u theme/zenyo/languages/sr.php (odeljak dynamic).
		'title'               => 'Card payment',
		'description'         => 'Visa, Mastercard, DinaCard. You enter your card details on the secure page of Raiffeisen Bank.',
		// Kupac odlazi na stranicu banke i vraca se kod nas (ne otvara se prozor preko nase stranice).
		'display_type'        => 'redirect',
		// Bez pamcenja kartica: kupci nemaju naloge.
		'enable_one_click_checkout' => 'no',
		// Cene su u dinarima i tako se i naplacuju, bez preracunavanja.
		'currency'            => '0',
		'geo_zone_id'         => '-1',
		// Placena porudzbina ide u "Processing" (placeno, ceka slanje), a ne odmah u "Completed".
		// "Completed" stavljas ti kad paket krene, i tada kupac dobija mejl da je poslato.
		'order_status_completed_id' => 'wc-processing',
		'order_status_refunded_id'  => 'wc-refunded',
		'order_status_cancelled_id' => 'wc-cancelled',
		'order_status_failed_id'    => 'wc-failed',
		'enable_debug_log'    => 'no',
		// Apple Pay provera domena treba samo kad se placa u prozoru na nasoj stranici.
		'enable_applepay_domain_association' => 'no',
	)
);
update_option( 'woocommerce_raiaccept_settings', $zp_new );

// Stare prijave kod banke vise ne vaze ako su se podaci promenili; dodatak ce se sam ponovo prijaviti.
RaiAccept_Token_Storage::clear_all();

// Kartica je prva na spisku, pouzece drugo.
update_option( 'woocommerce_gateway_order', array( 'raiaccept' => 0, 'cod' => 1 ) );

WP_CLI::log( $zp_on ? 'Kartice ukljucene (RaiAccept, ' . $zp_mode . ').' : 'Kartice iskljucene: u .env nema RAIACCEPT podataka.' );

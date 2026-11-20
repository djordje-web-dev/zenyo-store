<?php
/**
 * Vraca probnu zalihu (10 komada) svim proizvodima i varijacijama.
 * Samo za lokalni rad: probe kupovine (tools/e2e-order.mjs) trose zalihu.
 *   docker compose run --rm cli eval-file /scripts/restock.php
 */

$ids = get_posts( array( 'post_type' => array( 'product', 'product_variation' ), 'post_status' => 'any', 'numberposts' => -1, 'fields' => 'ids' ) );
$n   = 0;
foreach ( $ids as $id ) {
	$product = wc_get_product( $id );
	if ( $product && $product->managing_stock() && (int) $product->get_stock_quantity() < 10 ) {
		WP_CLI::log( sprintf( '  %s: %d -> 10', $product->get_sku(), (int) $product->get_stock_quantity() ) );
		wc_update_product_stock( $product, 10 );
		++$n;
	}
}
WP_CLI::success( 'Dopunjeno: ' . $n );

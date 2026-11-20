<?php
/**
 * Pocetni sadrzaj za stranice kimona: "Product details" i tabela velicina.
 * Upisuje samo tamo gde je prazno, pa ne gazi izmene iz admina.
 * Tabele su prazne ("—") dok ne stignu zvanicne adidas mere.
 */

$zenyo_gis = array(
	'ZY-GI-IJF'  => array( '750 g', array( 165, 170, 175, 180, 185 ) ),
	'ZY-GI-TRN'  => array( '500 g', array( 150, 160, 170 ) ),
	'ZY-GI-KIDS' => array( '350 g', array( 120, 130, 140, 150, 160 ) ),
);

foreach ( $zenyo_gis as $sku => $gi ) {
	$product = wc_get_product( wc_get_product_id_by_sku( $sku ) );
	if ( ! $product ) {
		continue;
	}
	if ( '' === trim( $product->get_description() ) ) {
		$product->set_description( '<p><strong>Fabric weight:</strong> ' . $gi[0] . '</p>' );
	}
	if ( '' === (string) $product->get_meta( '_zenyo_size_guide' ) ) {
		$rows = '';
		foreach ( $gi[1] as $size ) {
			$rows .= '<tr><th scope="row">' . $size . '</th><td>—</td><td>—</td><td>—</td><td>—</td></tr>';
		}
		$product->update_meta_data(
			'_zenyo_size_guide',
			'<p>Sizes by height in cm.</p>'
			. '<table><thead><tr><th scope="col">Size</th><th scope="col">Height</th><th scope="col">Jacket</th><th scope="col">Sleeve</th><th scope="col">Trousers</th></tr></thead>'
			. '<tbody>' . $rows . '</tbody></table>'
		);
	}
	$product->save();
	WP_CLI::log( '  ' . $product->get_name() );
}

// Redosled boja kao u dizajnu (White pa Blue...). Menja se i u adminu: Products -> Attributes -> Colour -> Configure terms.
foreach ( array( 'White', 'Yellow', 'Orange', 'Green', 'Blue', 'Brown', 'Black' ) as $i => $name ) {
	$term = get_term_by( 'name', $name, 'pa_colour' );
	if ( $term && '' === (string) get_term_meta( $term->term_id, 'order', true ) ) {
		update_term_meta( $term->term_id, 'order', $i + 1 );
	}
}

WP_CLI::success( 'Sadrzaj proizvoda upisan.' );

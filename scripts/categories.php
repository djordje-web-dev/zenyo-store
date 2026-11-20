<?php
/**
 * Slike, kadriranje, redosled i opisi kategorija, kao u dizajnu.
 * Pokrece se kroz setup.sh posle catalog.php. Upisuje samo ono sto jos nije podeseno,
 * pa ne gazi izmene napravljene u adminu.
 */

require_once '/scripts/lib-image.php';

// naziv => [ roditelj, redosled, slika, kadriranje, boja natpisa, opis ]
$zenyo_categories = array(
	'IJF Competition Judo Gi' => array( '', 1, 'ijf.jpg', '50% 50%', 'light', '' ),
	'Training Judo Gi'        => array( '', 2, 'training.jpg', '50% 30%', 'light', '' ),
	'Kids Judo Gi'            => array( '', 3, 'kids.jpg', '50% 40%', 'light', '' ),
	'Belts'                   => array( '', 4, 'belt-ijf.jpg', '90% 50%', 'light', '' ),
	'ZENYO'                   => array( '', 5, 'cat-zenyo.jpg', '16% 50%', 'light', 'Objects inspired by judo and Japanese culture.' ),
	'Art'                     => array( 'ZENYO', 1, 'cat-art.jpg', '50% 50%', 'dark', '' ),
	'Jewellery'               => array( 'ZENYO', 2, 'cat-jewellery.jpg', '50% 50%', 'light', '' ),
	'Accessories'             => array( 'ZENYO', 3, 'cat-accessories.jpg', '50% 50%', 'light', '' ),
);

foreach ( $zenyo_categories as $name => $c ) {
	$parent_id = 0;
	if ( $c[0] ) {
		$parent    = get_term_by( 'name', $c[0], 'product_cat' );
		$parent_id = $parent ? (int) $parent->term_id : 0;
	}
	$found = get_terms( array( 'taxonomy' => 'product_cat', 'name' => $name, 'parent' => $parent_id, 'hide_empty' => false ) );
	if ( ! $found || is_wp_error( $found ) ) {
		WP_CLI::warning( 'Nema kategorije: ' . $name );
		continue;
	}
	$term = $found[0];

	if ( '' === (string) get_term_meta( $term->term_id, 'order', true ) || '0' === (string) get_term_meta( $term->term_id, 'order', true ) ) {
		update_term_meta( $term->term_id, 'order', $c[1] );
	}
	if ( ! get_term_meta( $term->term_id, 'thumbnail_id', true ) ) {
		$image_id = zenyo_image( $c[2] );
		if ( $image_id ) {
			update_term_meta( $term->term_id, 'thumbnail_id', $image_id );
		}
	}
	if ( ! get_term_meta( $term->term_id, 'zenyo_focus', true ) ) {
		update_term_meta( $term->term_id, 'zenyo_focus', $c[3] );
	}
	if ( ! get_term_meta( $term->term_id, 'zenyo_label_tone', true ) ) {
		update_term_meta( $term->term_id, 'zenyo_label_tone', $c[4] );
	}
	if ( $c[5] && '' === $term->description ) {
		wp_update_term( $term->term_id, 'product_cat', array( 'description' => $c[5] ) );
	}
	WP_CLI::log( '  ' . $name );
}

// Belts: oba pojasa se kupuju sa stranice kategorije; zajednicka tabela duzina.
$zenyo_belts = get_term_by( 'name', 'Belts', 'product_cat' );
if ( $zenyo_belts ) {
	if ( '' === (string) get_term_meta( $zenyo_belts->term_id, 'zenyo_layout', true ) ) {
		update_term_meta( $zenyo_belts->term_id, 'zenyo_layout', 'stack' );
	}
	if ( '' === (string) get_term_meta( $zenyo_belts->term_id, 'zenyo_size_guide', true ) ) {
		$rows = '';
		foreach ( array( 220 => 'Coloured', 240 => 'Coloured', 260 => 'Coloured / IJF Black', 280 => 'Coloured', 300 => 'Coloured / IJF Black', 320 => 'IJF Black' ) as $length => $belt ) {
			$rows .= '<tr><th scope="row">' . $length . '</th><td>' . $belt . '</td><td>—</td></tr>';
		}
		update_term_meta(
			$zenyo_belts->term_id,
			'zenyo_size_guide',
			'<p>Belt length in cm.</p><table><thead><tr><th scope="col">Length</th><th scope="col">Belt</th><th scope="col">Recommended for</th></tr></thead><tbody>' . $rows . '</tbody></table>'
		);
	}
}
$zenyo_black_belt = wc_get_product( wc_get_product_id_by_sku( 'ZY-BELT-IJF' ) );
if ( $zenyo_black_belt && '' === (string) $zenyo_black_belt->get_meta( '_zenyo_focus' ) ) {
	$zenyo_black_belt->update_meta_data( '_zenyo_focus', '70% 50%' );
	$zenyo_black_belt->save();
}

// Art, Jewellery, Accessories: galerija gore, kupovina dole.
$zenyo_galleries = array(
	'Art'         => array( 'Select artwork', 'Japanese concepts that inspire everyday life.' ),
	'Jewellery'   => array( 'Select jewellery', 'Japanese-inspired jewellery for everyday life.' ),
	'Accessories' => array( 'Select accessory', 'Japanese-inspired accessories for everyday life.' ),
);
foreach ( $zenyo_galleries as $name => $g ) {
	$term = get_term_by( 'name', $name, 'product_cat' );
	if ( ! $term ) {
		continue;
	}
	if ( '' === (string) get_term_meta( $term->term_id, 'zenyo_layout', true ) ) {
		update_term_meta( $term->term_id, 'zenyo_layout', 'gallery' );
	}
	if ( '' === (string) get_term_meta( $term->term_id, 'zenyo_select_label', true ) ) {
		update_term_meta( $term->term_id, 'zenyo_select_label', $g[0] );
	}
	if ( '' === $term->description ) {
		wp_update_term( $term->term_id, 'product_cat', array( 'description' => $g[1] ) );
	}
}

// Redosled proizvoda kao u dizajnu (po sifri). Menja se i u adminu prevlacenjem (Products -> Sorting).
$zenyo_order = array( 'ZY-GI-IJF', 'ZY-GI-TRN', 'ZY-GI-KIDS', 'ZY-BELT-COL', 'ZY-BELT-IJF', 'ZY-ART-01', 'ZY-ART-02', 'ZY-ART-03', 'ZY-ART-04', 'ZY-ART-05', 'ZY-ART-06', 'ZY-ART-SET', 'ZY-JW-01', 'ZY-JW-02', 'ZY-JW-03', 'ZY-JW-04', 'ZY-ACC-01', 'ZY-ACC-02', 'ZY-ACC-03' );
foreach ( $zenyo_order as $i => $sku ) {
	$id = wc_get_product_id_by_sku( $sku );
	if ( $id && 0 === (int) get_post_field( 'menu_order', $id ) ) {
		wp_update_post( array( 'ID' => $id, 'menu_order' => ( $i + 1 ) * 10 ) );
	}
}

WP_CLI::success( 'Kategorije podesene.' );

<?php
/**
 * Unos ZENYO kataloga: kategorije, atributi i proizvodi sa cenama iz dizajna.
 * Pokrece se kroz setup.sh (wp eval-file). Proizvod koji vec postoji (po sifri, SKU) se preskace,
 * pa ponovno pokretanje nista ne duplira i ne gazi izmene iz admina.
 *
 * Privremeno: zaliha je 10 komada po varijaciji, nazivi i opisi su na engleskom kao u dizajnu.
 */

require_once '/scripts/lib-image.php';

const ZENYO_SEED_STOCK = 10;

/* ---------- pomocne funkcije ---------- */

function zenyo_cat( $name, $parent = 0 ) {
	$term = term_exists( $name, 'product_cat', $parent );
	if ( ! $term ) {
		$term = wp_insert_term( $name, 'product_cat', array( 'parent' => $parent ) );
	}
	if ( is_wp_error( $term ) ) {
		WP_CLI::error( $term->get_error_message() );
	}
	return (int) $term['term_id'];
}

/** Globalni atribut (npr. Colour). Vraca naziv taksonomije, npr. "pa_colour". */
function zenyo_attr( $label, $slug ) {
	$taxonomy = 'pa_' . $slug;
	if ( ! wc_attribute_taxonomy_id_by_name( $slug ) ) {
		$id = wc_create_attribute(
			array(
				'name'         => $label,
				'slug'         => $slug,
				'type'         => 'select',
				'order_by'     => 'menu_order',
				'has_archives' => false,
			)
		);
		if ( is_wp_error( $id ) ) {
			WP_CLI::error( $id->get_error_message() );
		}
	}
	// Nov atribut WordPress "vidi" tek pri sledecem ucitavanju, pa ga ovde rucno prijavljujemo.
	if ( ! taxonomy_exists( $taxonomy ) ) {
		register_taxonomy( $taxonomy, array( 'product' ), array( 'hierarchical' => false ) );
	}
	return $taxonomy;
}

/** Vrednost atributa (npr. "Blue"). Vraca WP_Term. */
function zenyo_attr_term( $taxonomy, $name ) {
	$term = get_term_by( 'name', $name, $taxonomy );
	if ( ! $term ) {
		$res = wp_insert_term( $name, $taxonomy );
		if ( is_wp_error( $res ) ) {
			WP_CLI::error( $name . ': ' . $res->get_error_message() );
		}
		$term = get_term( $res['term_id'], $taxonomy );
	}
	return $term;
}

function zenyo_exists( $sku ) {
	if ( wc_get_product_id_by_sku( $sku ) ) {
		WP_CLI::log( '  preskacem (postoji): ' . $sku );
		return true;
	}
	return false;
}

/**
 * Varijabilni proizvod.
 *
 * @param array $d  sku, name, cats, image, short, meta,
 *                  attrs      => [ taksonomija => [ vrednosti ] ],
 *                  variations => [ [ 'attrs' => [ taksonomija => vrednost ], 'price' => 1234 ], ... ]
 */
function zenyo_variable( $d ) {
	if ( zenyo_exists( $d['sku'] ) ) {
		return;
	}
	$product = new WC_Product_Variable();
	$product->set_name( $d['name'] );
	$product->set_sku( $d['sku'] );
	$product->set_status( 'publish' );
	$product->set_category_ids( $d['cats'] );
	$product->set_short_description( $d['short'] ?? '' );
	$product->set_image_id( zenyo_image( $d['image'] ?? '' ) );

	$attributes = array();
	$slugs      = array(); // [taksonomija][vrednost] => slug
	foreach ( $d['attrs'] as $taxonomy => $values ) {
		$term_ids = array();
		foreach ( $values as $value ) {
			$term                       = zenyo_attr_term( $taxonomy, (string) $value );
			$term_ids[]                 = $term->term_id;
			$slugs[ $taxonomy ][ (string) $value ] = $term->slug;
		}
		$attribute = new WC_Product_Attribute();
		$attribute->set_id( wc_attribute_taxonomy_id_by_name( $taxonomy ) );
		$attribute->set_name( $taxonomy );
		$attribute->set_options( $term_ids );
		$attribute->set_visible( true );
		$attribute->set_variation( true );
		$attributes[] = $attribute;
	}
	$product->set_attributes( $attributes );
	foreach ( ( $d['meta'] ?? array() ) as $key => $value ) {
		$product->update_meta_data( $key, $value );
	}
	$product_id = $product->save();

	foreach ( $d['variations'] as $v ) {
		$chosen = array();
		$suffix = array();
		foreach ( $v['attrs'] as $taxonomy => $value ) {
			$chosen[ $taxonomy ] = $slugs[ $taxonomy ][ (string) $value ];
			$suffix[]            = strtoupper( $slugs[ $taxonomy ][ (string) $value ] );
		}
		$variation = new WC_Product_Variation();
		$variation->set_parent_id( $product_id );
		$variation->set_attributes( $chosen );
		$variation->set_sku( $d['sku'] . '-' . implode( '-', $suffix ) );
		$variation->set_regular_price( (string) $v['price'] );
		$variation->set_manage_stock( true );
		$variation->set_stock_quantity( ZENYO_SEED_STOCK );
		$variation->set_status( 'publish' );
		$variation->save();
	}
	WC_Product_Variable::sync( $product_id );
	WP_CLI::log( sprintf( '  + %s (%d varijacija)', $d['name'], count( $d['variations'] ) ) );
}

/** Prost proizvod. Bez cene ide kao nacrt (draft), da se ne prikazuje na sajtu. */
function zenyo_simple( $d ) {
	if ( zenyo_exists( $d['sku'] ) ) {
		return;
	}
	$product = new WC_Product_Simple();
	$product->set_name( $d['name'] );
	$product->set_sku( $d['sku'] );
	$product->set_category_ids( $d['cats'] );
	$product->set_short_description( $d['short'] ?? '' );
	$product->set_image_id( zenyo_image( $d['image'] ?? '' ) );
	if ( isset( $d['price'] ) ) {
		$product->set_regular_price( (string) $d['price'] );
		$product->set_status( 'publish' );
	} else {
		$product->set_status( 'draft' );
	}
	$product->set_manage_stock( true );
	$product->set_stock_quantity( ZENYO_SEED_STOCK );
	foreach ( ( $d['meta'] ?? array() ) as $key => $value ) {
		$product->update_meta_data( $key, $value );
	}
	$product->save();
	WP_CLI::log( '  + ' . $d['name'] . ( isset( $d['price'] ) ? '' : ' (nacrt, nema cenu)' ) );
}

/** Sve kombinacije boja x velicina sa cenom koju vraca $price( boja, velicina ). */
function zenyo_grid( $colours, $sizes, $price, $colour_tax, $size_tax ) {
	$out = array();
	foreach ( $colours as $colour ) {
		foreach ( $sizes as $size ) {
			$out[] = array(
				'attrs' => array( $colour_tax => $colour, $size_tax => $size ),
				'price' => $price( $colour, $size ),
			);
		}
	}
	return $out;
}

/* ---------- kategorije ---------- */

WP_CLI::log( 'Kategorije' );
$cat_ijf      = zenyo_cat( 'IJF Competition Judo Gi' );
$cat_training = zenyo_cat( 'Training Judo Gi' );
$cat_kids     = zenyo_cat( 'Kids Judo Gi' );
$cat_belts    = zenyo_cat( 'Belts' );
$cat_zenyo    = zenyo_cat( 'ZENYO' );
$cat_art      = zenyo_cat( 'Art', $cat_zenyo );
$cat_jewel    = zenyo_cat( 'Jewellery', $cat_zenyo );
$cat_acc      = zenyo_cat( 'Accessories', $cat_zenyo );

// WooCommerce pravi kategoriju "Uncategorized"; ostaje, ne koristimo je.

/* ---------- atributi ---------- */

WP_CLI::log( 'Atributi' );
$a_colour = zenyo_attr( 'Colour', 'colour' );
$a_size   = zenyo_attr( 'Size', 'size' );
$a_length = zenyo_attr( 'Length (cm)', 'length' );
$a_print  = zenyo_attr( 'Print size', 'print-size' );

/* ---------- kimona ---------- */

WP_CLI::log( 'Kimona' );

zenyo_variable(
	array(
		'sku'        => 'ZY-GI-IJF',
		'name'       => 'IJF Competition Judo Gi',
		'cats'       => array( $cat_ijf ),
		'image'      => 'ijf.jpg',
		'meta'       => array( '_zenyo_key_spec' => 'IJF APPROVED', '_zenyo_fabric_weight' => '750 g' ),
		'attrs'      => array( $a_colour => array( 'White', 'Blue' ), $a_size => array( 165, 170, 175, 180, 185 ) ),
		'variations' => zenyo_grid(
			array( 'White', 'Blue' ),
			array( 165, 170, 175, 180, 185 ),
			function ( $c ) {
				return 'White' === $c ? 29900 : 35900;
			},
			$a_colour,
			$a_size
		),
	)
);

zenyo_variable(
	array(
		'sku'        => 'ZY-GI-TRN',
		'name'       => 'Training Judo Gi',
		'cats'       => array( $cat_training ),
		'image'      => 'training.jpg',
		'meta'       => array( '_zenyo_key_spec' => '500 G', '_zenyo_fabric_weight' => '500 g' ),
		'attrs'      => array( $a_colour => array( 'White', 'Blue' ), $a_size => array( 150, 160, 170 ) ),
		'variations' => zenyo_grid(
			array( 'White', 'Blue' ),
			array( 150, 160, 170 ),
			function ( $c ) {
				return 'White' === $c ? 8490 : 10990;
			},
			$a_colour,
			$a_size
		),
	)
);

$kids_prices = array(
	'White' => array( 120 => 5590, 130 => 5890, 140 => 6190, 150 => 6490, 160 => 6790 ),
	'Blue'  => array( 120 => 6590, 130 => 7190, 140 => 7690, 150 => 8190, 160 => 8790 ),
);
zenyo_variable(
	array(
		'sku'        => 'ZY-GI-KIDS',
		'name'       => 'Kids Judo Gi',
		'cats'       => array( $cat_kids ),
		'image'      => 'kids.jpg',
		'meta'       => array( '_zenyo_key_spec' => '350 G', '_zenyo_fabric_weight' => '350 g' ),
		'attrs'      => array( $a_colour => array( 'White', 'Blue' ), $a_size => array( 120, 130, 140, 150, 160 ) ),
		'variations' => zenyo_grid(
			array( 'White', 'Blue' ),
			array( 120, 130, 140, 150, 160 ),
			function ( $c, $s ) use ( $kids_prices ) {
				return $kids_prices[ $c ][ $s ];
			},
			$a_colour,
			$a_size
		),
	)
);

/* ---------- pojasevi ---------- */

WP_CLI::log( 'Pojasevi' );

// Samo kombinacije koje postoje. Ostale se ne prave, pa kupac ne moze da ih izabere.
$belt_lengths = array(
	'White'  => array( 240, 260 ),
	'Yellow' => array( 220, 240, 260, 280 ),
	'Orange' => array( 220, 240, 260, 280 ),
	'Green'  => array( 220, 240, 260, 280, 300 ),
	'Blue'   => array( 240, 260, 280, 300 ),
	'Brown'  => array( 280, 300 ),
);
$belt_price   = array( 'White' => 980, 'Yellow' => 980, 'Orange' => 980, 'Green' => 980, 'Blue' => 1100, 'Brown' => 1100 );
$belt_vars    = array();
foreach ( $belt_lengths as $colour => $lengths ) {
	foreach ( $lengths as $length ) {
		$belt_vars[] = array(
			'attrs' => array( $a_colour => $colour, $a_length => $length ),
			'price' => $belt_price[ $colour ],
		);
	}
}
zenyo_variable(
	array(
		'sku'        => 'ZY-BELT-COL',
		'name'       => 'Coloured Judo Belt',
		'cats'       => array( $cat_belts ),
		'attrs'      => array( $a_colour => array_keys( $belt_lengths ), $a_length => array( 220, 240, 260, 280, 300 ) ),
		'variations' => $belt_vars,
	)
);

zenyo_variable(
	array(
		'sku'        => 'ZY-BELT-IJF',
		'name'       => 'IJF Black Belt',
		'cats'       => array( $cat_belts ),
		'image'      => 'belt-ijf.jpg',
		'meta'       => array( '_zenyo_key_spec' => 'IJF APPROVED' ),
		'attrs'      => array( $a_length => array( 260, 300, 320 ) ),
		'variations' => array_map(
			function ( $length ) use ( $a_length ) {
				return array( 'attrs' => array( $a_length => $length ), 'price' => 3890 );
			},
			array( 260, 300, 320 )
		),
	)
);

/* ---------- ZENYO: Art ---------- */

WP_CLI::log( 'Art' );

$print_sizes = array(
	'35 × 50 cm'  => array( 'single' => 5000, 'set' => 27000 ),
	'50 × 70 cm'  => array( 'single' => 6000, 'set' => 32000 ),
	'70 × 100 cm' => array( 'single' => 7000, 'set' => 36000 ),
);
$artworks    = array(
	array( '01', 'IKIGAI', '生き甲斐', 'Why you live — what gives you meaning and a reason to get up every day.' ),
	array( '02', 'DŌ', '道', 'How you live — the path you follow and the way you live something, not only the final goal.' ),
	array( '03', 'KAIZEN', '改善', 'How you improve — continuous, gradual improvement through small steps.' ),
	array( '04', 'WABI-SABI', '侘寂', 'How you see beauty — value and beauty in the imperfect, worn and transient.' ),
	array( '05', 'IBASHO', '居場所', 'Where you belong — a place where you feel you belong and can be yourself.' ),
	array( '06', 'BUSHIDŌ', '武士道', 'How you behave — a code of values, discipline, honour and character that guides your actions.' ),
);
$print_vars  = function ( $key ) use ( $print_sizes, $a_print ) {
	$out = array();
	foreach ( $print_sizes as $size => $prices ) {
		$out[] = array( 'attrs' => array( $a_print => $size ), 'price' => $prices[ $key ] );
	}
	return $out;
};
foreach ( $artworks as $art ) {
	zenyo_variable(
		array(
			'sku'        => 'ZY-ART-' . $art[0],
			'name'       => $art[1],
			'cats'       => array( $cat_art ),
			'image'      => 'art' . $art[0] . '.jpg',
			'short'      => $art[3],
			'meta'       => array( '_zenyo_japanese' => $art[2], '_zenyo_material' => 'Printed on canvas' ),
			'attrs'      => array( $a_print => array_keys( $print_sizes ) ),
			'variations' => $print_vars( 'single' ),
		)
	);
}
zenyo_variable(
	array(
		'sku'        => 'ZY-ART-SET',
		'name'       => 'Complete Set — All 6 Artworks',
		'cats'       => array( $cat_art ),
		'image'      => 'art01.jpg',
		'short'      => 'Six Japanese concepts. One collection.',
		'meta'       => array( '_zenyo_is_set' => 'yes', '_zenyo_material' => 'Printed on canvas' ),
		'attrs'      => array( $a_print => array_keys( $print_sizes ) ),
		'variations' => $print_vars( 'set' ),
	)
);

/* ---------- ZENYO: Jewellery ---------- */

WP_CLI::log( 'Jewellery' );

$jewellery = array(
	array( '01', 'Kimono Necklace', 1290, 'A judo-inspired pendant shaped by one of the sport’s most recognizable symbols.' ),
	array( '02', 'Torii Necklace', 1290, 'A Japanese-inspired pendant based on the iconic form of the Torii gate.' ),
	array( '03', 'Belt Necklace', 1790, 'A judo-inspired pendant referencing the belt as a symbol of progress and discipline.' ),
	array( '04', 'Judo Bracelet', 1790, 'A minimal bracelet inspired by Japanese martial arts and judo culture.' ),
);
foreach ( $jewellery as $j ) {
	zenyo_simple(
		array(
			'sku'   => 'ZY-JW-' . $j[0],
			'name'  => $j[1],
			'cats'  => array( $cat_jewel ),
			'image' => 'jewellery' . $j[0] . '.jpg',
			'price' => $j[2],
			'short' => $j[3],
		)
	);
}

/* ---------- ZENYO: Accessories ---------- */

WP_CLI::log( 'Accessories' );

zenyo_simple(
	array(
		'sku'   => 'ZY-ACC-01',
		'name'  => 'Water Bottle',
		'cats'  => array( $cat_acc ),
		'image' => 'accessory01.jpg',
		'price' => 1490,
		'short' => 'A minimalist bottle for training, travel and everyday use. Inspired by Japanese aesthetics and made for an active lifestyle.',
	)
);
// Cap i Keychain jos nemaju cenu ni opis: ulaze kao nacrti.
zenyo_simple( array( 'sku' => 'ZY-ACC-02', 'name' => 'Cap', 'cats' => array( $cat_acc ), 'image' => 'accessory02.jpg' ) );
zenyo_simple( array( 'sku' => 'ZY-ACC-03', 'name' => 'Keychain', 'cats' => array( $cat_acc ), 'image' => 'accessory03.jpg' ) );

WP_CLI::success( 'Katalog unet.' );

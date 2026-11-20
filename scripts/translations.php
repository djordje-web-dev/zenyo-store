<?php
/**
 * Srpski prevod kataloga: nazivi i opisi proizvoda, kategorije, boje.
 * Pokrece se kroz setup.sh posle kataloga. Upisuje samo tamo gde je srpsko polje prazno,
 * pa ne gazi izmene iz admina (kutija "ZENYO - srpski prevod" na proizvodu, polja "Srpski" na kategoriji).
 */

/** Upisuje srpsko polje proizvoda ako je prazno. */
function zt_product( $sku, $fields ) {
	$id = wc_get_product_id_by_sku( $sku );
	if ( ! $id ) {
		WP_CLI::warning( 'Nema proizvoda: ' . $sku );
		return;
	}
	foreach ( $fields as $key => $value ) {
		if ( '' !== $value && '' === (string) get_post_meta( $id, $key, true ) ) {
			update_post_meta( $id, $key, wp_slash( $value ) );
		}
	}
}

/** Upisuje srpsko polje kategorije ili vrednosti osobine ako je prazno. */
function zt_term( $name, $taxonomy, $fields ) {
	$term = get_term_by( 'name', $name, $taxonomy );
	if ( ! $term ) {
		return;
	}
	foreach ( $fields as $key => $value ) {
		if ( '' !== $value && '' === (string) get_term_meta( $term->term_id, $key, true ) ) {
			update_term_meta( $term->term_id, $key, wp_slash( $value ) );
		}
	}
}

/* ---------- kimona ---------- */

$zt_gis = array(
	'ZY-GI-IJF'  => array( 'IJF takmičarski kimono za džudo', 'IJF ODOBREN', '750 g', array( 165, 170, 175, 180, 185 ) ),
	'ZY-GI-TRN'  => array( 'Trening kimono za džudo', '', '500 g', array( 150, 160, 170 ) ),
	'ZY-GI-KIDS' => array( 'Dečiji kimono za džudo', '', '350 g', array( 120, 130, 140, 150, 160 ) ),
);
foreach ( $zt_gis as $sku => $gi ) {
	$rows = '';
	foreach ( $gi[3] as $size ) {
		$rows .= '<tr><th scope="row">' . $size . '</th><td>—</td><td>—</td><td>—</td><td>—</td></tr>';
	}
	zt_product(
		$sku,
		array(
			'_zenyo_title_sr'      => $gi[0],
			'_zenyo_key_spec_sr'   => $gi[1],
			'_zenyo_content_sr'    => '<p><strong>Težina tkanine:</strong> ' . $gi[2] . '</p>',
			'_zenyo_size_guide_sr' => '<p>Veličine po visini u cm.</p>'
				. '<table><thead><tr><th scope="col">Veličina</th><th scope="col">Visina</th><th scope="col">Gornji deo</th><th scope="col">Rukav</th><th scope="col">Pantalone</th></tr></thead>'
				. '<tbody>' . $rows . '</tbody></table>',
		)
	);
}

/* ---------- pojasevi ---------- */

zt_product( 'ZY-BELT-COL', array( '_zenyo_title_sr' => 'Pojas za džudo u boji' ) );
zt_product( 'ZY-BELT-IJF', array( '_zenyo_title_sr' => 'IJF crni pojas', '_zenyo_key_spec_sr' => 'IJF ODOBREN' ) );

/* ---------- Art ---------- */

$zt_art = array(
	'01' => 'Zašto živiš — ono što ti daje smisao i razlog da ustaneš svakog dana.',
	'02' => 'Kako živiš — put kojim ideš i način na koji nešto živiš, a ne samo krajnji cilj.',
	'03' => 'Kako napreduješ — stalno, postepeno usavršavanje kroz male korake.',
	'04' => 'Kako vidiš lepotu — vrednost i lepota u nesavršenom, istrošenom i prolaznom.',
	'05' => 'Gde pripadaš — mesto na kom osećaš da pripadaš i gde možeš da budeš svoj.',
	'06' => 'Kako se ponašaš — kodeks vrednosti, discipline, časti i karaktera koji vodi tvoje postupke.',
);
foreach ( $zt_art as $no => $short ) {
	zt_product( 'ZY-ART-' . $no, array( '_zenyo_excerpt_sr' => $short, '_zenyo_material_sr' => 'Štampa na platnu' ) );
}
zt_product(
	'ZY-ART-SET',
	array(
		'_zenyo_title_sr'    => 'Komplet — svih 6 radova',
		'_zenyo_excerpt_sr'  => 'Šest japanskih pojmova. Jedna kolekcija.',
		'_zenyo_material_sr' => 'Štampa na platnu',
	)
);

/* ---------- Jewellery i Accessories ---------- */

zt_product( 'ZY-JW-01', array( '_zenyo_title_sr' => 'Ogrlica Kimono', '_zenyo_excerpt_sr' => 'Privezak inspirisan džudoom, u obliku jednog od najprepoznatljivijih simbola ovog sporta.' ) );
zt_product( 'ZY-JW-02', array( '_zenyo_title_sr' => 'Ogrlica Torii', '_zenyo_excerpt_sr' => 'Privezak japanske inspiracije, zasnovan na prepoznatljivom obliku kapije torii.' ) );
zt_product( 'ZY-JW-03', array( '_zenyo_title_sr' => 'Ogrlica Pojas', '_zenyo_excerpt_sr' => 'Privezak inspirisan džudoom: pojas kao simbol napretka i discipline.' ) );
zt_product( 'ZY-JW-04', array( '_zenyo_title_sr' => 'Džudo narukvica', '_zenyo_excerpt_sr' => 'Minimalistička narukvica inspirisana japanskim borilačkim veštinama i kulturom džudoa.' ) );
zt_product( 'ZY-ACC-01', array( '_zenyo_title_sr' => 'Flaša za vodu', '_zenyo_excerpt_sr' => 'Minimalistička flaša za trening, putovanja i svaki dan. Inspirisana japanskom estetikom i napravljena za aktivan život.' ) );
zt_product( 'ZY-ACC-02', array( '_zenyo_title_sr' => 'Kačket' ) );
zt_product( 'ZY-ACC-03', array( '_zenyo_title_sr' => 'Privezak za ključeve' ) );

/* ---------- kategorije ---------- */

// Kategorija sa jednim proizvodom zove se isto kao proizvod, da se naziv u putanji ne ponavlja.
zt_term( 'IJF Competition Judo Gi', 'product_cat', array( 'zenyo_name_sr' => 'IJF takmičarski kimono za džudo' ) );
zt_term( 'Training Judo Gi', 'product_cat', array( 'zenyo_name_sr' => 'Trening kimono za džudo' ) );
zt_term( 'Kids Judo Gi', 'product_cat', array( 'zenyo_name_sr' => 'Dečiji kimono za džudo' ) );
zt_term( 'ZENYO', 'product_cat', array( 'zenyo_description_sr' => 'Predmeti inspirisani džudoom i japanskom kulturom.' ) );
zt_term( 'Art', 'product_cat', array( 'zenyo_name_sr' => 'Umetnost', 'zenyo_description_sr' => 'Japanski pojmovi koji inspirišu svakodnevni život.', 'zenyo_select_label_sr' => 'Izaberite rad' ) );
zt_term( 'Jewellery', 'product_cat', array( 'zenyo_name_sr' => 'Nakit', 'zenyo_description_sr' => 'Nakit japanske inspiracije za svaki dan.', 'zenyo_select_label_sr' => 'Izaberite nakit' ) );
zt_term( 'Accessories', 'product_cat', array( 'zenyo_name_sr' => 'Dodaci', 'zenyo_description_sr' => 'Dodaci japanske inspiracije za svaki dan.', 'zenyo_select_label_sr' => 'Izaberite dodatak' ) );

$zt_rows = '';
foreach ( array( 220 => 'U boji', 240 => 'U boji', 260 => 'U boji / IJF crni', 280 => 'U boji', 300 => 'U boji / IJF crni', 320 => 'IJF crni' ) as $length => $belt ) {
	$zt_rows .= '<tr><th scope="row">' . $length . '</th><td>' . $belt . '</td><td>—</td></tr>';
}
zt_term(
	'Belts',
	'product_cat',
	array(
		'zenyo_name_sr'       => 'Pojasevi',
		'zenyo_size_guide_sr' => '<p>Dužina pojasa u cm.</p><table><thead><tr><th scope="col">Dužina</th><th scope="col">Pojas</th><th scope="col">Preporučeno za</th></tr></thead><tbody>' . $zt_rows . '</tbody></table>',
	)
);

/* ---------- boje ---------- */

foreach ( array( 'White' => 'Bela', 'Blue' => 'Plava', 'Yellow' => 'Žuta', 'Orange' => 'Narandžasta', 'Green' => 'Zelena', 'Brown' => 'Braon', 'Black' => 'Crna' ) as $en => $sr ) {
	zt_term( $en, 'pa_colour', array( 'zenyo_name_sr' => $sr ) );
}

WP_CLI::success( 'Srpski prevod kataloga upisan.' );

<?php
/**
 * Kontakt i podaci firme.
 *
 * Menjaju se u adminu: Appearance -> Customize -> "ZENYO - kontakt i firma".
 * Vrednosti ispod su podaci firme dobijeni 14. 11. 2026. Ako se nesto promeni, menja se u adminu, ne ovde.
 */

function zenyo_settings_fields() {
	return array(
		'call_phone'    => array( 'Telefon u zaglavlju (slusalica)', '+381 62 620 900' ),
		'company_name'  => array( 'Naziv firme', 'ZEUGMA' ),
		'company_pib'   => array( 'PIB', '112796305' ),
		'company_mb'    => array( 'Maticni broj', '66360822' ),
		'address'       => array( 'Adresa', 'Karagačka 134A, 11000 Vrčin, Serbia' ),
		'email'         => array( 'Mejl', 'milivojcuk130@gmail.com' ),
		'office_phone'  => array( 'Telefon u futeru', '+381 62 620 900' ),
		'instagram_url' => array( 'Instagram link', 'https://www.instagram.com/zenyostore/' ),
		'delivery_line'   => array( 'Proizvod: rok isporuke', 'Delivery 1–3 business days' ),
		'returns_summary' => array( 'Proizvod: kratko o povracaju i zameni', '' ),
		'hero_text'     => array( 'Pocetna: recenica iznad dugmeta Shop', 'adidas Combat Sports gear for judo and beyond is on its way. Get ready to gear up.' ),
		// Srpske verzije tekstova iznad. Prazno polje = prikazuje se engleski tekst.
		'address_sr'         => array( 'Adresa (srpski)', 'Karagačka 134A, 11000 Vrčin, Srbija' ),
		'delivery_line_sr'   => array( 'Proizvod: rok isporuke (srpski)', 'Isporuka za 1–3 radna dana' ),
		'returns_summary_sr' => array( 'Proizvod: kratko o povracaju i zameni (srpski)', '' ),
		'hero_text_sr'       => array( 'Pocetna: recenica iznad dugmeta Shop (srpski)', 'adidas Combat Sports oprema za džudo i druge borilačke sportove uskoro stiže. Spremite se.' ),
	);
}

/** Video na pocetnoj: onaj izabran u adminu, ili fajl iz teme ako nista nije izabrano. */
function zenyo_hero_video_url() {
	$id  = (int) get_theme_mod( 'zenyo_hero_video', 0 );
	$url = $id ? wp_get_attachment_url( $id ) : '';
	return $url ? $url : ZENYO_URI . '/assets/video/hero.mp4';
}

/** Slika koja se vidi dok se video ucitava. */
function zenyo_hero_poster_url() {
	$id  = (int) get_theme_mod( 'zenyo_hero_poster', 0 );
	$url = $id ? wp_get_attachment_image_url( $id, 'full' ) : '';
	return $url ? $url : ZENYO_URI . '/assets/img/hero-poster.jpg';
}

/** Uspravan video za telefon. Prazno ako nije izabran: tada i telefon dobija obican video. */
function zenyo_hero_phone_video_url() {
	$id = (int) get_theme_mod( 'zenyo_hero_video_phone', 0 );
	return $id ? (string) wp_get_attachment_url( $id ) : '';
}

/** Uspravna slika za telefon dok se video ucitava. Prazno ako nije izabrana: tada se koristi obicna slika. */
function zenyo_hero_phone_poster_url() {
	$id = (int) get_theme_mod( 'zenyo_hero_poster_phone', 0 );
	return $id ? (string) wp_get_attachment_image_url( $id, 'full' ) : '';
}

/** Vrednost podesavanja, ili primer iz dizajna ako nista nije uneto. */
function zenyo_opt( $key ) {
	$fields = zenyo_settings_fields();
	// Na srpskom prvo gledamo srpsku verziju polja (ako postoji i nije prazna).
	if ( zenyo_is_sr() && isset( $fields[ $key . '_sr' ] ) ) {
		$sr = (string) get_theme_mod( 'zenyo_' . $key . '_sr', $fields[ $key . '_sr' ][1] );
		if ( '' !== $sr ) {
			return $sr;
		}
	}
	$default = isset( $fields[ $key ] ) ? $fields[ $key ][1] : '';
	return (string) get_theme_mod( 'zenyo_' . $key, $default );
}

/** Instagram nalog iz linka: "https://www.instagram.com/zenyostore/" -> "@zenyostore" */
function zenyo_instagram_handle() {
	$path = trim( (string) wp_parse_url( zenyo_opt( 'instagram_url' ), PHP_URL_PATH ), '/' );
	return '' !== $path ? '@' . $path : 'Instagram';
}

/**
 * Kratak kod za tekstove stranica, da se podaci firme pisu na jednom mestu (Customizer):
 *   [zenyo key="company_name"]   naziv firme        [zenyo key="email_link"]  mejl kao link
 *   [zenyo key="address"]        adresa             [zenyo key="phone_link"]  telefon kao link
 *   [zenyo key="company_pib"]    PIB                [zenyo key="instagram_link"]
 *   [zenyo key="company_mb"]     maticni broj
 */
add_shortcode(
	'zenyo',
	function ( $atts ) {
		$key = isset( $atts['key'] ) ? sanitize_key( $atts['key'] ) : '';
		if ( 'email_link' === $key ) {
			$email = zenyo_opt( 'email' );
			return '<a href="' . esc_attr( 'mailto:' . $email ) . '">' . esc_html( $email ) . '</a>';
		}
		if ( 'phone_link' === $key ) {
			$phone = zenyo_opt( 'office_phone' );
			return '<a href="' . esc_attr( zenyo_tel_href( $phone ) ) . '">' . esc_html( $phone ) . '</a>';
		}
		if ( 'instagram_link' === $key ) {
			return '<a href="' . esc_url( zenyo_opt( 'instagram_url' ) ) . '" rel="noopener">' . esc_html( zenyo_instagram_handle() ) . '</a>';
		}
		return array_key_exists( $key, zenyo_settings_fields() ) ? esc_html( zenyo_opt( $key ) ) : '';
	}
);

/** "+381 60 555 0123" -> "tel:+381605550123" */
function zenyo_tel_href( $phone ) {
	return 'tel:' . preg_replace( '/[^0-9+]/', '', $phone );
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'zenyo_contact',
			array(
				'title'    => 'ZENYO - kontakt i firma',
				'priority' => 30,
			)
		);
		foreach ( zenyo_settings_fields() as $key => $field ) {
			$wp_customize->add_setting(
				'zenyo_' . $key,
				array(
					'default'           => $field[1],
					'sanitize_callback' => 'instagram_url' === $key ? 'esc_url_raw' : 'sanitize_text_field',
				)
			);
			$wp_customize->add_control(
				'zenyo_' . $key,
				array(
					'label'   => $field[0],
					'section' => 'zenyo_contact',
					'type'    => 'instagram_url' === $key ? 'url' : 'text',
				)
			);
		}

		// Pocetna strana: video i slika dok se video ucitava.
		$wp_customize->add_setting( 'zenyo_hero_video', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'zenyo_hero_video',
				array(
					'label'       => 'Pocetna: video u pozadini',
					'description' => 'MP4, 1920x1080, bez zvuka, 12-15 sekundi, do 10 MB.',
					'section'     => 'zenyo_contact',
					'mime_type'   => 'video',
				)
			)
		);
		$wp_customize->add_setting( 'zenyo_hero_poster', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'zenyo_hero_poster',
				array(
					'label'       => 'Pocetna: slika dok se video ucitava',
					'section'     => 'zenyo_contact',
					'mime_type'   => 'image',
				)
			)
		);

		// Isto to, ali uspravno, za telefon i uspravno okrenut tablet. Oba polja su neobavezna.
		$wp_customize->add_setting( 'zenyo_hero_video_phone', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'zenyo_hero_video_phone',
				array(
					'label'       => 'Pocetna: video za telefon (uspravan)',
					'description' => 'MP4, 1080x1920 (uspravno), bez zvuka, do 4 MB. Prazno = i telefon prikazuje obican video.',
					'section'     => 'zenyo_contact',
					'mime_type'   => 'video',
				)
			)
		);
		$wp_customize->add_setting( 'zenyo_hero_poster_phone', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'zenyo_hero_poster_phone',
				array(
					'label'       => 'Pocetna: slika za telefon dok se video ucitava (uspravna)',
					'description' => 'Prvi kadar uspravnog videa. Prazno = koristi se obicna slika.',
					'section'     => 'zenyo_contact',
					'mime_type'   => 'image',
				)
			)
		);
	}
);

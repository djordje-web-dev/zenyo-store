<?php
/**
 * Dva jezika: srpski (latinica) i engleski.
 *
 * Kako radi, ukratko:
 *   - Jezik se cita iz adrese. Srpski je osnovni i nema dodatak:  zenyostore.com/cart/
 *     Engleski ima /en/ na pocetku:                                zenyostore.com/en/cart/
 *   - Proizvod postoji JEDNOM. Zato su zaliha, cena i porudzbine iste na oba jezika.
 *   - U adminu se glavna polja (naziv, opis...) pisu na engleskom, a srpski tekst u polja
 *     "Srpski prevod" na istom ekranu. Ako srpsko polje ostane prazno, prikazuje se engleski tekst.
 *   - Stranice (FAQ, Terms...) imaju srpsku verziju kao posebnu stranicu, vezanu za englesku
 *     u kutiji "ZENYO jezik" (vidi inc/lang-admin.php).
 *   - Stalni tekstovi teme (dugmad, natpisi) na engleskom su u sablonima, a srpski u languages/sr.php.
 *   - Tekstove samog WooCommerce-a (poruke o greskama, mejlovi) prevodi njegov srpski paket.
 *     Taj paket je na cirilici, pa ga ovde prebacujemo u latinicu.
 *
 * U adminu i u skriptama se nista ne prevodi: tamo se vidi tacno ono sto je upisano.
 */

/** Jezici sajta. Prvi u nizu je osnovni (bez dodatka u adresi). */
function zenyo_langs() {
	return array(
		'sr' => array( 'locale' => 'sr_RS', 'html' => 'sr-Latn-RS', 'label' => 'SR', 'name' => 'Srpski' ),
		'en' => array( 'locale' => 'en_US', 'html' => 'en', 'label' => 'EN', 'name' => 'English' ),
	);
}

/** Putanja sajta ako nije u korenu domena (npr. "/prodavnica"); obicno prazno. */
function zenyo_home_path() {
	return rtrim( (string) wp_parse_url( get_option( 'home' ), PHP_URL_PATH ), '/' );
}

/** Deo adrese posle domena i posle oznake jezika, npr. "/cart/?x=1". */
function zenyo_request_rest() {
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '/'; // phpcs:ignore
	$home = zenyo_home_path();
	if ( '' !== $home && 0 === strpos( $uri, $home ) ) {
		$uri = substr( $uri, strlen( $home ) );
	}
	$uri = preg_replace( '#^/en(?=[/?\#]|$)#', '', $uri );
	return '' === $uri || '/' !== $uri[0] ? '/' . $uri : $uri;
}

/** Jezik iz adrese. Prazno = admin, WP-CLI i slicno, gde se nista ne prevodi. */
function zenyo_detect_lang() {
	if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) || wp_doing_cron() || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) {
		return '';
	}
	$uri = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '/'; // phpcs:ignore
	if ( false !== strpos( $uri, '/wp-json/' ) || false !== strpos( $uri, 'rest_route=' ) || false !== strpos( $uri, 'wp-login.php' ) ) {
		return '';
	}
	$home = zenyo_home_path();
	if ( '' !== $home && 0 === strpos( $uri, $home ) ) {
		$uri = substr( $uri, strlen( $home ) );
	}
	return preg_match( '#^/en(?=[/?\#]|$)#', $uri ) ? 'en' : 'sr';
}

$GLOBALS['zenyo_lang']       = zenyo_detect_lang();
$GLOBALS['zenyo_lang_stack'] = array();

/** Trenutni jezik: "sr", "en" ili "" (admin). */
function zenyo_lang() {
	return $GLOBALS['zenyo_lang'];
}

function zenyo_is_sr() {
	return 'sr' === $GLOBALS['zenyo_lang'];
}

/* ---------- Adrese ---------- */

// Na engleskom svaka adresa sajta dobija /en. WordPress sam ume da procita takvu adresu,
// jer deo "pocetne adrese" skida pre nego sto trazi stranicu.
add_filter(
	'home_url',
	function ( $url, $path, $scheme ) {
		if ( 'en' !== zenyo_lang() ) {
			return $url;
		}
		if ( is_string( $path ) && preg_match( '#^/?(wp-content|wp-includes|wp-admin|wp-json)(/|$)#', $path ) ) {
			return $url;
		}
		$home = preg_quote( zenyo_home_path(), '#' );
		if ( 'relative' === $scheme ) {
			return preg_replace( '#^' . $home . '(?=[/?\#]|$)#', zenyo_home_path() . '/en', $url, 1 );
		}
		return preg_replace( '#^((?:[a-z]+:)?//[^/?\#]+' . $home . ')(?=[/?\#]|$)#i', '$1/en', $url, 1 );
	},
	10,
	3
);

/** Adresa ove iste stranice na drugom jeziku (za izbor jezika i za pretrazivace). */
function zenyo_lang_url( $lang ) {
	$base = untrailingslashit( set_url_scheme( get_option( 'home' ) ) );
	return $base . ( 'en' === $lang ? '/en' : '' ) . zenyo_request_rest();
}

/* ---------- Prebacivanje jezika (pocetak zahteva i slanje mejlova) ---------- */

/**
 * Privremeno prebacuje jezik, npr. dok se salje mejl kupcu koji je kupovao na engleskom.
 * Posle obavezno pozvati zenyo_restore_lang().
 */
function zenyo_switch_lang( $lang ) {
	$langs = zenyo_langs();
	if ( ! isset( $langs[ $lang ] ) ) {
		return false;
	}
	$GLOBALS['zenyo_lang_stack'][] = $GLOBALS['zenyo_lang'];
	$GLOBALS['zenyo_lang']         = $lang;
	switch_to_locale( $langs[ $lang ]['locale'] );
	return true;
}

function zenyo_restore_lang() {
	if ( ! $GLOBALS['zenyo_lang_stack'] ) {
		return;
	}
	$GLOBALS['zenyo_lang'] = array_pop( $GLOBALS['zenyo_lang_stack'] );
	restore_previous_locale();
}

/* ---------- Tekstovi teme i WooCommerce-a ---------- */

/** Srpski tekstovi teme iz languages/sr.php. */
function zenyo_sr_strings() {
	static $strings = null;
	if ( null === $strings ) {
		$strings = require ZENYO_DIR . '/languages/sr.php';
	}
	return $strings;
}

/** Cirilica -> latinica (WooCommerce-ov srpski paket je na cirilici). */
function zenyo_to_latin( $text ) {
	if ( ! is_string( $text ) || ! preg_match( '/[\x{0400}-\x{04FF}]/u', $text ) ) {
		return $text;
	}
	static $map = array(
		'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Ђ' => 'Đ', 'Е' => 'E', 'Ж' => 'Ž', 'З' => 'Z', 'И' => 'I',
		'Ј' => 'J', 'К' => 'K', 'Л' => 'L', 'Љ' => 'Lj', 'М' => 'M', 'Н' => 'N', 'Њ' => 'Nj', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
		'С' => 'S', 'Т' => 'T', 'Ћ' => 'Ć', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Č', 'Џ' => 'Dž', 'Ш' => 'Š',
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'ђ' => 'đ', 'е' => 'e', 'ж' => 'ž', 'з' => 'z', 'и' => 'i',
		'ј' => 'j', 'к' => 'k', 'л' => 'l', 'љ' => 'lj', 'м' => 'm', 'н' => 'n', 'њ' => 'nj', 'о' => 'o', 'п' => 'p', 'р' => 'r',
		'с' => 's', 'т' => 't', 'ћ' => 'ć', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'č', 'џ' => 'dž', 'ш' => 'š',
	);
	return strtr( $text, $map );
}

/** Koji oblik mnozine ide uz broj: 1 artikal, 2 artikla, 5 artikala. */
function zenyo_sr_plural_index( $n ) {
	$n = abs( (int) $n );
	if ( 1 === $n % 10 && 11 !== $n % 100 ) {
		return 0;
	}
	if ( $n % 10 >= 2 && $n % 10 <= 4 && ( $n % 100 < 12 || $n % 100 > 14 ) ) {
		return 1;
	}
	return 2;
}

add_filter(
	'gettext',
	function ( $translation, $text, $domain ) {
		if ( ! zenyo_is_sr() ) {
			return $translation;
		}
		if ( 'zenyo' === $domain ) {
			$s = zenyo_sr_strings();
			return isset( $s['strings'][ $text ] ) ? $s['strings'][ $text ] : $translation;
		}
		return zenyo_to_latin( $translation );
	},
	10,
	3
);

add_filter(
	'ngettext',
	function ( $translation, $single, $plural, $number, $domain ) {
		if ( ! zenyo_is_sr() ) {
			return $translation;
		}
		if ( 'zenyo' === $domain ) {
			$s = zenyo_sr_strings();
			return isset( $s['plurals'][ $single ] ) ? $s['plurals'][ $single ][ zenyo_sr_plural_index( $number ) ] : $translation;
		}
		return zenyo_to_latin( $translation );
	},
	10,
	5
);

add_filter(
	'gettext_with_context',
	function ( $translation ) {
		return zenyo_is_sr() ? zenyo_to_latin( $translation ) : $translation;
	}
);
add_filter(
	'ngettext_with_context',
	function ( $translation ) {
		return zenyo_is_sr() ? zenyo_to_latin( $translation ) : $translation;
	}
);

/**
 * Prevod teksta koji je upisan u podesavanjima prodavnice (naziv dostave, nacin placanja, naziv osobine).
 * Trazi se u languages/sr.php, odeljak "dynamic". Ako ga tamo nema, ostaje kako je upisan.
 */
function zenyo_t( $text ) {
	if ( ! zenyo_is_sr() || ! is_string( $text ) ) {
		return $text;
	}
	$s = zenyo_sr_strings();
	return isset( $s['dynamic'][ $text ] ) ? $s['dynamic'][ $text ] : $text;
}

// Jezik za ovaj zahtev. Mora posle filtera iznad, da i nazivi meseci budu na latinici.
if ( '' !== $GLOBALS['zenyo_lang'] ) {
	$zenyo_locale = zenyo_langs()[ $GLOBALS['zenyo_lang'] ]['locale'];
	if ( determine_locale() !== $zenyo_locale ) {
		switch_to_locale( $zenyo_locale );
	}
	unset( $zenyo_locale );
}

// <html lang="sr-Latn-RS"> umesto "sr-RS" (koje znaci cirilicu).
add_filter(
	'language_attributes',
	function ( $output ) {
		$langs = zenyo_langs();
		$lang  = zenyo_lang();
		return isset( $langs[ $lang ] ) ? 'lang="' . esc_attr( $langs[ $lang ]['html'] ) . '"' : $output;
	}
);

// Pretrazivacima kazemo da ista stranica postoji na oba jezika.
add_action(
	'wp_head',
	function () {
		if ( '' === zenyo_lang() || is_404() ) {
			return;
		}
		foreach ( zenyo_langs() as $code => $lang ) {
			printf( '<link rel="alternate" hreflang="%s" href="%s">' . "\n", esc_attr( 'sr' === $code ? 'sr' : $code ), esc_url( zenyo_lang_url( $code ) ) );
		}
		printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( zenyo_lang_url( 'sr' ) ) );
	},
	1
);

/** Izbor jezika: "SR | EN". */
function zenyo_lang_switcher() {
	if ( '' === zenyo_lang() ) {
		return;
	}
	echo '<nav class="zy-lang" aria-label="' . esc_attr__( 'Language', 'zenyo' ) . '">';
	foreach ( zenyo_langs() as $code => $lang ) {
		if ( zenyo_lang() === $code ) {
			printf( '<span class="zy-lang__item is-current" aria-current="true" lang="%s" title="%s">%s</span>', esc_attr( $lang['html'] ), esc_attr( $lang['name'] ), esc_html( $lang['label'] ) );
		} else {
			printf( '<a class="zy-lang__item" href="%s" hreflang="%s" lang="%s" title="%s">%s</a>', esc_url( zenyo_lang_url( $code ) ), esc_attr( $code ), esc_attr( $lang['html'] ), esc_attr( $lang['name'] ), esc_html( $lang['label'] ) );
		}
	}
	echo '</nav>';
}

/* ---------- Sadrzaj: proizvodi ---------- */

/** Dodatna polja proizvoda koja imaju srpsku verziju (isti kljuc + "_sr"). */
function zenyo_translatable_product_meta() {
	return array( '_zenyo_key_spec', '_zenyo_size_guide', '_zenyo_care', '_zenyo_material' );
}

/** Srpska vrednost polja proizvoda, ili prazno ako nije upisana. */
function zenyo_product_sr( $product_id, $field ) {
	return (string) get_post_meta( $product_id, '_zenyo_' . $field . '_sr', true );
}

add_filter(
	'woocommerce_product_get_name',
	function ( $name, $product ) {
		$sr = zenyo_is_sr() ? zenyo_product_sr( $product->get_id(), 'title' ) : '';
		return '' !== $sr ? $sr : $name;
	},
	10,
	2
);
// Varijacija (npr. bela, 170) nosi naziv glavnog proizvoda, nekad sa dodatkom "- White, 170".
add_filter(
	'woocommerce_product_variation_get_name',
	function ( $name, $variation ) {
		$sr = zenyo_is_sr() ? zenyo_product_sr( $variation->get_parent_id(), 'title' ) : '';
		if ( '' === $sr ) {
			return $name;
		}
		$parent = get_post_field( 'post_title', $variation->get_parent_id() );
		return ( '' !== $parent && 0 === strpos( $name, $parent ) ) ? $sr . substr( $name, strlen( $parent ) ) : $sr;
	},
	10,
	2
);
foreach ( array( 'description' => 'content', 'short_description' => 'excerpt' ) as $zenyo_prop => $zenyo_field ) {
	add_filter(
		'woocommerce_product_get_' . $zenyo_prop,
		function ( $value, $product ) use ( $zenyo_field ) {
			$sr = zenyo_is_sr() ? zenyo_product_sr( $product->get_id(), $zenyo_field ) : '';
			return '' !== $sr ? $sr : $value;
		},
		10,
		2
	);
}
foreach ( zenyo_translatable_product_meta() as $zenyo_key ) {
	add_filter(
		'woocommerce_product_get_' . $zenyo_key,
		function ( $value, $product ) use ( $zenyo_key ) {
			$sr = zenyo_is_sr() ? (string) get_post_meta( $product->get_id(), $zenyo_key . '_sr', true ) : '';
			return '' !== $sr ? $sr : $value;
		},
		10,
		2
	);
}
unset( $zenyo_prop, $zenyo_field, $zenyo_key );

/* ---------- Sadrzaj: stranice ---------- */

/** Srpska verzija stranice (posebna stranica vezana za englesku), ili null. */
function zenyo_page_sr( $page_id ) {
	static $cache = array();
	$page_id = (int) $page_id;
	if ( ! array_key_exists( $page_id, $cache ) ) {
		$found = get_posts(
			array(
				'post_type'        => 'page',
				'post_status'      => 'publish',
				'meta_key'         => '_zenyo_translation_of', // phpcs:ignore WordPress.DB.SlowDBQuery
				'meta_value'       => $page_id, // phpcs:ignore WordPress.DB.SlowDBQuery
				'numberposts'      => 1,
				'suppress_filters' => true,
			)
		);
		$cache[ $page_id ] = $found ? $found[0] : null;
	}
	return $cache[ $page_id ];
}

/** Srpski naslov proizvoda ili stranice; prazno ako ga nema. */
function zenyo_post_title_sr( $post_id ) {
	$type = get_post_type( $post_id );
	if ( 'product' === $type ) {
		return zenyo_product_sr( $post_id, 'title' );
	}
	if ( 'page' === $type ) {
		$sr = zenyo_page_sr( $post_id );
		return $sr ? $sr->post_title : '';
	}
	return '';
}

add_filter(
	'the_title',
	function ( $title, $post_id = 0 ) {
		$sr = ( zenyo_is_sr() && $post_id ) ? zenyo_post_title_sr( $post_id ) : '';
		return '' !== $sr ? $sr : $title;
	},
	10,
	2
);
add_filter(
	'single_post_title',
	function ( $title, $post = null ) {
		$sr = ( zenyo_is_sr() && $post ) ? zenyo_post_title_sr( $post->ID ) : '';
		return '' !== $sr ? $sr : $title;
	},
	10,
	2
);
add_filter(
	'get_the_excerpt',
	function ( $excerpt, $post = null ) {
		if ( zenyo_is_sr() && $post && 'page' === $post->post_type ) {
			$sr = zenyo_page_sr( $post->ID );
			if ( $sr && '' !== $sr->post_excerpt ) {
				return $sr->post_excerpt;
			}
		}
		return $excerpt;
	},
	10,
	2
);
// Tekst stranice: na srpskom se uzima iz srpske verzije; na engleskom linkovi u tekstu dobijaju /en.
add_filter(
	'the_content',
	function ( $content ) {
		if ( zenyo_is_sr() && is_page() && in_the_loop() && is_main_query() ) {
			$sr = zenyo_page_sr( get_the_ID() );
			// Korpa i naplata imaju srpsku stranicu samo zbog naslova; njihov sadrzaj ostaje isti.
			if ( $sr && '' !== trim( $sr->post_content ) ) {
				return $sr->post_content;
			}
		}
		if ( zenyo_is_sr() && 'product' === get_post_type() ) {
			$sr = zenyo_product_sr( get_the_ID(), 'content' );
			if ( '' !== $sr ) {
				return $sr;
			}
		}
		if ( 'en' === zenyo_lang() ) {
			$home    = zenyo_home_path();
			$content = preg_replace( '#href="' . preg_quote( $home, '#' ) . '/(?!en/|wp-|/)#', 'href="' . $home . '/en/', $content );
		}
		return $content;
	},
	1
);

// Srpska verzija nema svoju adresu: ko je otvori, ide na pravu stranicu.
add_action(
	'template_redirect',
	function () {
		if ( is_page() ) {
			$base = (int) get_post_meta( get_queried_object_id(), '_zenyo_translation_of', true );
			if ( $base && get_post( $base ) ) {
				wp_safe_redirect( get_permalink( $base ), 301 );
				exit;
			}
		}
	},
	1
);
// ...i ne ide u mapu sajta za pretrazivace.
add_filter(
	'wp_sitemaps_posts_query_args',
	function ( $args, $post_type ) {
		if ( 'page' === $post_type ) {
			$args['meta_query'] = array( array( 'key' => '_zenyo_translation_of', 'compare' => 'NOT EXISTS' ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		}
		return $args;
	},
	10,
	2
);

/* ---------- Sadrzaj: kategorije i vrednosti osobina (boje...) ---------- */

add_filter(
	'get_term',
	function ( $term ) {
		if ( ! zenyo_is_sr() || ! ( $term instanceof WP_Term ) ) {
			return $term;
		}
		if ( 'product_cat' !== $term->taxonomy && 0 !== strpos( $term->taxonomy, 'pa_' ) ) {
			return $term;
		}
		$name = (string) get_term_meta( $term->term_id, 'zenyo_name_sr', true );
		$desc = (string) get_term_meta( $term->term_id, 'zenyo_description_sr', true );
		if ( '' !== $name ) {
			$term->name = $name;
		}
		if ( '' !== $desc ) {
			$term->description = $desc;
		}
		return $term;
	}
);

/** Polja kategorije koja imaju srpsku verziju (isti kljuc + "_sr"). */
function zenyo_term_meta( $term_id, $key ) {
	if ( zenyo_is_sr() ) {
		$sr = (string) get_term_meta( $term_id, $key . '_sr', true );
		if ( '' !== $sr ) {
			return $sr;
		}
	}
	return (string) get_term_meta( $term_id, $key, true );
}

/* ---------- Sadrzaj: podesavanja prodavnice ---------- */

// Naziv osobine: Colour -> Boja.
add_filter( 'woocommerce_attribute_label', 'zenyo_t' );
// Naziv dostave: "Standard delivery — Delivery across Serbia".
add_filter( 'woocommerce_shipping_rate_label', 'zenyo_t', 20 );
// Nacin placanja: naziv, opis i uputstvo.
add_filter( 'woocommerce_gateway_title', 'zenyo_t' );
add_filter( 'woocommerce_gateway_description', 'zenyo_t' );
add_filter(
	'option_woocommerce_cod_settings',
	function ( $settings ) {
		if ( zenyo_is_sr() && is_array( $settings ) ) {
			foreach ( array( 'title', 'description', 'instructions' ) as $key ) {
				if ( isset( $settings[ $key ] ) ) {
					$settings[ $key ] = zenyo_t( $settings[ $key ] );
				}
			}
		}
		return $settings;
	}
);

/* ---------- Porudzbine i mejlovi ---------- */

// Uz porudzbinu pamtimo na kom jeziku je kupac kupovao.
add_action(
	'woocommerce_checkout_create_order',
	function ( $order ) {
		if ( '' !== zenyo_lang() ) {
			$order->update_meta_data( '_zenyo_lang', zenyo_lang() );
		}
	}
);

/** Jezik mejla: kupcu na jeziku porudzbine, prodavcu uvek na srpskom. */
function zenyo_email_lang( $email, $object ) {
	if ( ! $email->is_customer_email() ) {
		return 'sr';
	}
	$lang = ( $object instanceof WC_Order ) ? (string) $object->get_meta( '_zenyo_lang' ) : '';
	if ( '' === $lang ) {
		$lang = '' !== zenyo_lang() ? zenyo_lang() : 'sr';
	}
	return $lang;
}

/**
 * Pouzece cuva svoj naziv i uputstvo cim se ucita. Posle promene jezika ih citamo ponovo,
 * da uputstvo u mejlu bude na jeziku mejla.
 */
function zenyo_refresh_gateway_texts() {
	if ( ! function_exists( 'WC' ) || ! did_action( 'woocommerce_init' ) ) {
		return;
	}
	foreach ( WC()->payment_gateways()->payment_gateways() as $gateway ) {
		if ( 'cod' === $gateway->id ) {
			$gateway->init_settings();
			$gateway->title        = $gateway->get_option( 'title' );
			$gateway->description  = $gateway->get_option( 'description' );
			$gateway->instructions = $gateway->get_option( 'instructions' );
		}
	}
}

/** Vraca jezik koji je vazio pre slanja mejla. */
function zenyo_email_lang_done() {
	if ( ! empty( $GLOBALS['zenyo_email_switched'] ) ) {
		$GLOBALS['zenyo_email_switched'] = false;
		zenyo_restore_lang();
		zenyo_refresh_gateway_texts();
	}
}

// WooCommerce pita "kome saljem?" neposredno pre nego sto sastavi mejl. Tu prebacujemo jezik.
add_action(
	'woocommerce_email',
	function ( $mailer ) {
		foreach ( $mailer->get_emails() as $email ) {
			add_filter(
				'woocommerce_email_recipient_' . $email->id,
				function ( $recipient, $object, $wc_email ) {
					zenyo_email_lang_done();
					$lang = zenyo_email_lang( $wc_email, $object );
					if ( $lang !== zenyo_lang() && zenyo_switch_lang( $lang ) ) {
						$GLOBALS['zenyo_email_switched'] = true;
						zenyo_refresh_gateway_texts();
					}
					// WooCommerce zapamti naslov mejla od prethodnog slanja; citamo ga ponovo za ovaj jezik.
					$wc_email->init_settings();
					return $recipient;
				},
				1,
				3
			);
		}
	}
);
add_action( 'woocommerce_email_sent', 'zenyo_email_lang_done' );
// Osiguranje: ako mejl na kraju nije poslat, jezik se vraca kad se zavrsi obrada dogadjaja.
add_action(
	'init',
	function () {
		$actions = apply_filters(
			'woocommerce_email_actions',
			array(
				'woocommerce_order_status_pending_to_processing',
				'woocommerce_order_status_pending_to_completed',
				'woocommerce_order_status_processing_to_cancelled',
				'woocommerce_order_status_pending_to_failed',
				'woocommerce_order_status_pending_to_on-hold',
				'woocommerce_order_status_failed_to_processing',
				'woocommerce_order_status_failed_to_completed',
				'woocommerce_order_status_failed_to_on-hold',
				'woocommerce_order_status_cancelled_to_processing',
				'woocommerce_order_status_cancelled_to_completed',
				'woocommerce_order_status_cancelled_to_on-hold',
				'woocommerce_order_status_on-hold_to_processing',
				'woocommerce_order_status_on-hold_to_cancelled',
				'woocommerce_order_status_on-hold_to_failed',
				'woocommerce_order_status_completed',
				'woocommerce_order_status_failed',
				'woocommerce_order_fully_refunded',
				'woocommerce_order_partially_refunded',
				'woocommerce_new_customer_note',
			)
		);
		foreach ( $actions as $action ) {
			add_action( $action . '_notification', 'zenyo_email_lang_done', 999, 0 );
		}
	},
	20
);

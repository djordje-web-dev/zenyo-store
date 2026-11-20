<?php
/**
 * Korpa, naplata i potvrda porudzbine: polja, provere i pomocne funkcije za sablone
 * u woocommerce/cart/ i woocommerce/checkout/.
 */

/* ---------- Izgled ---------- */

// Sve WooCommerce stranice sada crtaju nasi sabloni, pa njegovi podrazumevani stilovi vise ne trebaju.
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

// Stranica "Hvala": tabelu sa stavkama crtamo sami, pa iskljucujemo WooCommerce-ovu da se ne ponavlja.
remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );

/* ---------- Polja na naplati ---------- */

/**
 * Ostavljamo samo polja iz dizajna: ime, prezime, mejl, telefon, adresa, grad, postanski broj, napomena.
 * Drzava ostaje kao skriveno polje (prodaje se samo u Srbiji).
 */
add_filter(
	'woocommerce_checkout_fields',
	function ( $fields ) {
		unset( $fields['billing']['billing_company'], $fields['billing']['billing_address_2'], $fields['billing']['billing_state'] );

		$set = function ( $key, $label, $placeholder, $wide ) use ( &$fields ) {
			if ( ! isset( $fields['billing'][ $key ] ) ) {
				return;
			}
			$fields['billing'][ $key ]['label']       = $label;
			$fields['billing'][ $key ]['placeholder'] = $placeholder;
			$fields['billing'][ $key ]['class']       = array( $wide ? 'form-row-wide' : 'zy-half' );
		};
		$set( 'billing_first_name', __( 'First name', 'zenyo' ), '', false );
		$set( 'billing_last_name', __( 'Last name', 'zenyo' ), '', false );
		$set( 'billing_email', __( 'Email', 'zenyo' ), 'name@example.com', false );
		$set( 'billing_phone', __( 'Phone', 'zenyo' ), '+381 6x xxx xxxx', false );
		$set( 'billing_address_1', __( 'Address', 'zenyo' ), __( 'Street and number', 'zenyo' ), true );
		$set( 'billing_city', __( 'City', 'zenyo' ), '', false );
		$set( 'billing_postcode', __( 'Postal code', 'zenyo' ), '11000', false );

		$fields['billing']['billing_phone']['required']             = true;
		$fields['billing']['billing_postcode']['maxlength']         = 5;
		$fields['billing']['billing_postcode']['custom_attributes'] = array( 'inputmode' => 'numeric' );

		if ( isset( $fields['order']['order_comments'] ) ) {
			$fields['order']['order_comments']['label']       = __( 'Order note', 'zenyo' );
			$fields['order']['order_comments']['placeholder'] = __( 'e.g. building entrance, intercom, delivery instructions...', 'zenyo' );
			$fields['order']['order_comments']['class']       = array( 'form-row-wide' );
		}
		return $fields;
	}
);

/** Polja prvog odeljka (Contact) i drugog (Delivery address), redom kako se prikazuju. */
function zenyo_checkout_field_groups() {
	return array(
		'contact' => array( 'billing_first_name', 'billing_last_name', 'billing_email', 'billing_phone' ),
		'address' => array( 'billing_address_1', 'billing_city', 'billing_postcode', 'billing_country' ),
	);
}

// Kratke poruke o greskama, kao u dizajnu, uz polje na koje se odnose.
add_filter(
	'woocommerce_checkout_required_field_notice',
	function () {
		return __( 'Required field', 'zenyo' );
	}
);

// Dodatne provere: srpski broj telefona i petocifren postanski broj.
add_action(
	'woocommerce_after_checkout_validation',
	function ( $data, $errors ) {
		if ( ! empty( $data['billing_phone'] ) ) {
			$phone = preg_replace( '/[\s\-\/().]/', '', $data['billing_phone'] );
			if ( ! preg_match( '/^(\+381|00381|0)\d{8,10}$/', $phone ) ) {
				$errors->add( 'billing_phone_validation', __( 'Invalid phone number', 'zenyo' ), array( 'id' => 'billing_phone' ) );
			}
		}
		if ( ! empty( $data['billing_postcode'] ) && ! preg_match( '/^\d{5}$/', trim( $data['billing_postcode'] ) ) ) {
			$errors->add( 'billing_postcode_validation', __( 'Invalid postal code', 'zenyo' ), array( 'id' => 'billing_postcode' ) );
		}
	},
	10,
	2
);

/* ---------- Nacini dostave ---------- */

/**
 * Naziv nacina dostave moze u adminu da nosi i kratku napomenu posle crte:
 *   "Express delivery — 1–2 business days"  ->  [ 'Express delivery', '1–2 business days' ]
 */
function zenyo_shipping_label_parts( $label ) {
	$parts = preg_split( '/\s+[—–-]\s+/u', (string) $label, 2 );
	return array( trim( $parts[0] ), isset( $parts[1] ) ? trim( $parts[1] ) : '' );
}

/** Lista nacina dostave za naplatu (odeljak 3). Kad se promeni izbor, WooCommerce je sam osvezava. */
function zenyo_shipping_methods_html() {
	ob_start();
	$packages = WC()->shipping()->get_packages();
	echo '<div class="zy-options zy-ship-methods">';
	foreach ( $packages as $i => $package ) {
		$chosen = isset( WC()->session->chosen_shipping_methods[ $i ] ) ? WC()->session->chosen_shipping_methods[ $i ] : '';
		foreach ( $package['rates'] as $rate ) {
			list( $label, $note ) = zenyo_shipping_label_parts( $rate->get_label() );
			$cost                 = (float) $rate->get_cost() + array_sum( $rate->get_taxes() );
			printf(
				'<label class="zy-option"><input type="radio" class="shipping_method" name="shipping_method[%1$d]" data-index="%1$d" value="%2$s"%3$s><span class="zy-option__text"><span class="zy-option__label">%4$s</span>%5$s</span><span class="zy-option__price">%6$s</span></label>',
				(int) $i,
				esc_attr( $rate->get_id() ),
				checked( $rate->get_id(), $chosen, false ),
				esc_html( $label ),
				$note ? '<span class="zy-option__note">' . esc_html( $note ) . '</span>' : '',
				$cost > 0 ? wp_kses_post( wc_price( $cost ) ) : esc_html__( 'Free', 'zenyo' )
			);
		}
		if ( empty( $package['rates'] ) ) {
			echo '<p class="zy-options__empty">' . esc_html__( 'No delivery options are available for this address.', 'zenyo' ) . '</p>';
		}
	}
	echo '</div>';
	return ob_get_clean();
}

// Kad WooCommerce osvezava naplatu (npr. posle promene adrese), neka osvezi i nasu listu dostave.
add_filter(
	'woocommerce_update_order_review_fragments',
	function ( $fragments ) {
		$fragments['.zy-ship-methods'] = zenyo_shipping_methods_html();
		return $fragments;
	}
);

/* ---------- Stavke korpe i porudzbine ---------- */

/** Izabrane opcije stavke u korpi kao tekst: "White | 170". */
function zenyo_cart_item_variant( $cart_item ) {
	$parts = array();
	if ( ! empty( $cart_item['variation'] ) ) {
		foreach ( $cart_item['variation'] as $key => $value ) {
			$taxonomy = str_replace( 'attribute_', '', $key );
			$term     = taxonomy_exists( $taxonomy ) ? get_term_by( 'slug', $value, $taxonomy ) : null;
			$parts[]  = $term ? $term->name : $value;
		}
	}
	return implode( ' | ', array_filter( $parts ) );
}

/** Izabrane opcije stavke porudzbine kao tekst: "White | 170". */
function zenyo_order_item_variant( $item ) {
	$parts = array();
	foreach ( $item->get_formatted_meta_data( '_', true ) as $meta ) {
		$parts[] = trim( wp_strip_all_tags( $meta->display_value ) );
	}
	return implode( ' | ', array_filter( $parts ) );
}

/** "Subtotal (3 items)" */
function zenyo_subtotal_label( $count ) {
	/* translators: %d: broj komada */
	return sprintf( _n( 'Subtotal (%d item)', 'Subtotal (%d items)', $count, 'zenyo' ), $count );
}

/**
 * Jedan red u pregledu porudzbine (naplata i stranica "Hvala"): naziv, opcije, kolicina, ukupno.
 * Kad je kolicina veca od 1, ispod iznosa pise i racun: "(1.100 RSD × 2)".
 */
function zenyo_summary_line( $name, $variant, $qty, $line_total, $unit_price ) {
	?>
	<li class="zy-summary__line">
		<span class="zy-summary__product">
			<span class="zy-summary__name"><?php echo esc_html( $name ); ?></span>
			<?php if ( $variant ) : ?>
				<span class="zy-summary__variant"><?php echo esc_html( $variant ); ?></span>
			<?php endif; ?>
		</span>
		<span class="zy-summary__qty"><?php echo (int) $qty; ?></span>
		<span class="zy-summary__total">
			<span><?php echo wp_kses_post( wc_price( $line_total ) ); ?></span>
			<?php if ( $qty > 1 ) : ?>
				<span class="zy-summary__calc">(<?php echo wp_kses_post( wc_price( $unit_price ) ); ?> × <?php echo (int) $qty; ?>)</span>
			<?php endif; ?>
		</span>
	</li>
	<?php
}

/* ---------- Preporuke u korpi ---------- */

/**
 * Do dva proizvoda za odeljak "Things you may also be interested in".
 * Prvo se uzimaju proizvodi koje si u adminu vezao za artikle iz korpe (Linked products -> Cross-sells).
 * Ako ih nema dovoljno, dopunjava se proizvodima iz kategorija kojih u korpi nema.
 */
function zenyo_cart_recommendations( $limit = 2 ) {
	$in_cart      = array();
	$cart_cat_ids = array();
	foreach ( WC()->cart->get_cart() as $item ) {
		$in_cart[]    = (int) $item['product_id'];
		$cart_cat_ids = array_merge( $cart_cat_ids, wc_get_product_cat_ids( $item['product_id'] ) );
	}

	$picked = array();
	foreach ( WC()->cart->get_cross_sells() as $id ) {
		$product = wc_get_product( $id );
		if ( $product && $product->is_visible() && ! in_array( (int) $id, $in_cart, true ) ) {
			$picked[ $id ] = $product;
		}
	}

	if ( count( $picked ) < $limit ) {
		$candidates = wc_get_products(
			array(
				'status'  => 'publish',
				'limit'   => 40,
				'orderby' => 'menu_order',
				'order'   => 'ASC',
				'exclude' => array_merge( $in_cart, array_keys( $picked ) ),
			)
		);
		$fresh      = array(); // iz kategorija kojih nema u korpi
		$rest       = array();
		foreach ( $candidates as $product ) {
			if ( 'yes' === $product->get_meta( '_zenyo_is_set' ) || '' === $product->get_price() || ! $product->is_in_stock() ) {
				continue;
			}
			if ( array_intersect( $product->get_category_ids(), $cart_cat_ids ) ) {
				$rest[] = $product;
			} else {
				$fresh[] = $product;
			}
		}
		// Jeftiniji artikli idu prvi: uz kimono je prirodnije ponuditi flasu ili pojas nego jos jedan kimono.
		usort(
			$fresh,
			function ( $a, $b ) {
				return (float) $a->get_price() <=> (float) $b->get_price();
			}
		);
		// Od "svezih" uzimamo po jedan iz razlicitih kategorija, da preporuke ne budu dva slicna artikla.
		$used_cats = array();
		foreach ( array_merge( $fresh, $rest ) as $product ) {
			if ( count( $picked ) >= $limit ) {
				break;
			}
			$cats = $product->get_category_ids();
			if ( array_intersect( $cats, $used_cats ) && count( $fresh ) > 1 ) {
				continue;
			}
			$picked[ $product->get_id() ] = $product;
			$used_cats                    = array_merge( $used_cats, $cats );
		}
	}

	return array_slice( array_values( $picked ), 0, $limit );
}

/* ---------- Stranica "Hvala" ---------- */

/**
 * Tekstovi o placanju za stranicu "Hvala", zavisno od stanja porudzbine.
 * Vraca: [ 'failed' => bool, 'status' => naziv stanja, 'note' => napomena, 'headline' => glavna recenica ]
 */
function zenyo_order_payment_info( $order ) {
	if ( $order->has_status( array( 'failed', 'cancelled' ) ) ) {
		return array(
			'failed'   => true,
			'status'   => __( 'Payment unsuccessful', 'zenyo' ),
			'note'     => '',
			'headline' => __( 'Your payment could not be completed.', 'zenyo' ),
		);
	}
	if ( 'cod' === $order->get_payment_method() ) {
		$status = __( 'Payment on delivery', 'zenyo' );
		$note   = '';
	} elseif ( $order->is_paid() ) {
		$status = __( 'Payment successful', 'zenyo' );
		$note   = '';
	} elseif ( $order->has_status( 'on-hold' ) ) {
		$status = __( 'Awaiting payment', 'zenyo' );
		$note   = __( 'Payment instructions will follow by email.', 'zenyo' );
	} else {
		$status = __( 'Awaiting payment confirmation', 'zenyo' );
		$note   = '';
	}
	return array(
		'failed'   => false,
		'status'   => $status,
		'note'     => $note,
		'headline' => $order->has_status( 'pending' ) ? __( 'Your order has been received.', 'zenyo' ) : __( 'Your order has been confirmed.', 'zenyo' ),
	);
}

/* ---------- Placanje karticom (RaiAccept, Raiffeisen banka) ---------- */

/**
 * Kupac je na stranici banke odustao od placanja. Dodatak banke tada otkaze porudzbinu,
 * vrati artikle u korpu i posalje kupca nazad na naplatu. Mi pre toga samo dodamo poruku,
 * da kupac zna sta se desilo i da nista nije naplaceno.
 */
add_action(
	'woocommerce_api_raiaccept_recreate_cart',
	function () {
		if ( function_exists( 'wc_load_cart' ) ) {
			wc_load_cart();
		}
		$message = __( 'Payment was cancelled and nothing has been charged. Your cart has been kept, so you can try again or choose another payment method.', 'zenyo' );
		if ( function_exists( 'wc_add_notice' ) && WC()->session && ! wc_has_notice( $message, 'notice' ) ) {
			wc_add_notice( $message, 'notice' );
		}
	},
	5
);

/** Naslov stranice za korpu, naplatu i "Hvala" (umesto naziva WordPress stranice). */
function zenyo_flow_title() {
	if ( is_wc_endpoint_url( 'order-received' ) ) {
		$order = wc_get_order( absint( get_query_var( 'order-received' ) ) );
		return ( $order && $order->has_status( array( 'failed', 'cancelled' ) ) ) ? __( 'Payment unsuccessful', 'zenyo' ) : __( 'Thank you', 'zenyo' );
	}
	if ( is_checkout() ) {
		return __( 'Checkout', 'zenyo' );
	}
	if ( is_cart() ) {
		return __( 'Your cart', 'zenyo' );
	}
	return get_the_title();
}

/* ---------- Recenice o privatnosti i uslovima kupovine iznad dugmeta "Place order" ---------- */

/** U recenici zamenjuje <a>...</a> pravim linkom ka stranici. */
function zenyo_sentence_link( $sentence, $slug ) {
	return str_replace( '<a>', '<a href="' . esc_url( zenyo_page_url( $slug ) ) . '" target="_blank">', $sentence );
}

add_filter(
	'woocommerce_get_privacy_policy_text',
	function ( $text, $type ) {
		if ( 'checkout' !== $type ) {
			return $text;
		}
		return zenyo_sentence_link( __( 'We use your personal data to process your order and for the other purposes described in our <a>Privacy Policy</a>.', 'zenyo' ), 'privacy-policy' );
	},
	10,
	2
);

add_filter(
	'woocommerce_get_terms_and_conditions_checkbox_text',
	function () {
		return zenyo_sentence_link( __( 'I have read and accept the <a>Terms & Conditions</a>', 'zenyo' ), 'terms' );
	}
);

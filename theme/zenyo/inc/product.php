<?php
/**
 * Stranica proizvoda: dodatna polja u adminu i podaci koje stranica salje skripti.
 *
 * Dodatna polja (Products -> izmena proizvoda -> kutija "ZENYO"):
 *   - oznaka ispod naziva, npr. "IJF APPROVED" ili "500 G"
 *   - tabela velicina (otvara se u bocnom panelu)
 *   - uputstvo za odrzavanje
 */

/** Boja kvadratica za poznate nazive boja. Nepoznata boja nema kvadratic, samo naziv. */
function zenyo_colour_hex( $slug ) {
	$map = array(
		'white'  => '#fbfaf8',
		'blue'   => '#2a46b8',
		'yellow' => '#e8c21b',
		'orange' => '#e07a1f',
		'green'  => '#2f8a3e',
		'brown'  => '#6b4226',
		'black'  => '#0a0a0a',
		'red'    => '#d71920',
	);
	return isset( $map[ $slug ] ) ? $map[ $slug ] : '';
}

/** Cena kao obican tekst: "35.900 RSD". */
function zenyo_price_text( $amount ) {
	return trim( html_entity_decode( wp_strip_all_tags( wc_price( $amount ) ), ENT_QUOTES, 'UTF-8' ) );
}

/**
 * Osobine koje kupac bira, redom kako su podesene na proizvodu.
 * Vraca: [ 'pa_colour' => [ 'label' => 'Colour', 'options' => [ [ 'slug' => 'blue', 'name' => 'Blue' ], ... ] ], ... ]
 */
function zenyo_product_choices( $product ) {
	$choices = array();
	if ( ! $product->is_type( 'variable' ) ) {
		return $choices;
	}
	foreach ( $product->get_variation_attributes() as $taxonomy => $slugs ) {
		$options = array();
		if ( taxonomy_exists( $taxonomy ) ) {
			// Redosled iz admina (Products -> Attributes -> Configure terms).
			foreach ( wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'all' ) ) as $term ) {
				if ( in_array( $term->slug, $slugs, true ) ) {
					$options[] = array( 'slug' => $term->slug, 'name' => $term->name );
				}
			}
		} else {
			foreach ( $slugs as $slug ) {
				$options[] = array( 'slug' => $slug, 'name' => $slug );
			}
		}
		$choices[ $taxonomy ] = array(
			'label'   => wc_attribute_label( $taxonomy, $product ),
			'options' => $options,
		);
	}
	return $choices;
}

/**
 * Sve sto skripti na stranici treba: varijacije sa cenom i stanjem, adresa za dodavanje u korpu, poruke.
 */
function zenyo_product_script_data( $product ) {
	$variations = array();
	$from       = '';

	if ( $product->is_type( 'variable' ) ) {
		foreach ( $product->get_available_variations() as $v ) {
			$attributes = array();
			foreach ( $v['attributes'] as $key => $value ) {
				$attributes[ str_replace( 'attribute_', '', $key ) ] = $value;
			}
			$variations[] = array(
				'id'         => (int) $v['variation_id'],
				'attributes' => $attributes,
				'price'      => (float) $v['display_price'],
				'priceText'  => zenyo_price_text( $v['display_price'] ),
				'inStock'    => (bool) $v['is_in_stock'] && (bool) $v['is_purchasable'],
				'image'      => ! empty( $v['image']['full_src'] ) && (int) $v['image_id'] !== (int) $product->get_image_id() ? $v['image']['full_src'] : '',
			);
		}
	}

	$labels = array();
	foreach ( zenyo_product_choices( $product ) as $taxonomy => $choice ) {
		$labels[ $taxonomy ] = $choice['label'];
	}

	return array(
		'productId'  => $product->get_id(),
		'isVariable' => $product->is_type( 'variable' ),
		'canBuy'     => $product->is_type( 'variable' ) ? true : ( $product->is_purchasable() && $product->is_in_stock() ),
		'priceText'  => $product->is_type( 'variable' ) ? '' : zenyo_price_text( wc_get_price_to_display( $product ) ),
		'variations' => $variations,
		'labels'     => $labels,
		'ajaxUrl'    => WC_AJAX::get_endpoint( 'add_to_cart' ),
		'i18n'       => array(
			/* translators: %s: cena */
			'from'    => __( 'From %s', 'zenyo' ),
			/* translators: %s: sta treba izabrati, npr. "colour and size" */
			'select'  => __( 'Please select a %s.', 'zenyo' ),
			'and'     => __( 'and', 'zenyo' ),
			/* translators: %s: izabrane opcije i cena */
			'added'   => __( 'Added to cart — %s.', 'zenyo' ),
			'soldOut' => __( 'Sold out', 'zenyo' ),
			'error'   => __( 'Could not add to cart. Please try again.', 'zenyo' ),
			'adding'  => __( 'Adding…', 'zenyo' ),
		),
	);
}

/** Naziv sa dodatkom, ako ga ima: "IKIGAI (生き甲斐)". */
function zenyo_product_label( $product ) {
	$extra = (string) $product->get_meta( '_zenyo_japanese' );
	return $product->get_name() . ( '' !== $extra ? ' (' . $extra . ')' : '' );
}

/**
 * Podaci za stranicu sa galerijom (assets/js/gallery.js).
 * Za proizvod sa varijacijama koristi se prva osobina (npr. velicina otiska).
 */
function zenyo_gallery_script_data( $products ) {
	$out = array();
	foreach ( $products as $product ) {
		$variations = array();
		$size_label = '';
		if ( $product->is_type( 'variable' ) ) {
			$choices  = zenyo_product_choices( $product );
			$taxonomy = (string) array_key_first( $choices );
			if ( $taxonomy ) {
				$size_label = $choices[ $taxonomy ]['label'];
				$names      = array();
				foreach ( $choices[ $taxonomy ]['options'] as $option ) {
					$names[ $option['slug'] ] = $option['name'];
				}
				$by_value = array();
				foreach ( $product->get_available_variations() as $v ) {
					$value = isset( $v['attributes'][ 'attribute_' . $taxonomy ] ) ? $v['attributes'][ 'attribute_' . $taxonomy ] : '';
					if ( '' === $value ) {
						continue;
					}
					$by_value[ $value ] = array(
						'id'      => (int) $v['variation_id'],
						'value'   => $value,
						'name'    => isset( $names[ $value ] ) ? $names[ $value ] : $value,
						'price'   => (float) $v['display_price'],
						'inStock' => (bool) $v['is_in_stock'] && (bool) $v['is_purchasable'],
					);
				}
				// Redosled kao u adminu.
				foreach ( array_keys( $names ) as $slug ) {
					if ( isset( $by_value[ $slug ] ) ) {
						$variations[] = $by_value[ $slug ];
					}
				}
			}
		}

		// Specifikacije: osobine proizvoda koje nisu za izbor (npr. Material, Chain length).
		$details = array();
		foreach ( $product->get_attributes() as $attribute ) {
			if ( $attribute->get_variation() || ! $attribute->get_visible() ) {
				continue;
			}
			$values = $attribute->is_taxonomy()
				? wc_get_product_terms( $product->get_id(), $attribute->get_name(), array( 'fields' => 'names' ) )
				: $attribute->get_options();
			$details[] = array( wc_attribute_label( $attribute->get_name(), $product ), implode( ', ', $values ) );
		}

		$simple_price = $product->is_type( 'variable' ) || '' === $product->get_price() ? null : (float) wc_get_price_to_display( $product );

		$out[] = array(
			'id'         => $product->get_id(),
			'name'       => zenyo_product_label( $product ),
			'text'       => trim( wp_strip_all_tags( $product->get_short_description() ) ),
			'material'   => (string) $product->get_meta( '_zenyo_material' ),
			'details'    => $details,
			'price'      => $simple_price,
			'canBuy'     => ! $product->is_type( 'variable' ) && $product->is_purchasable() && $product->is_in_stock(),
			'sizeLabel'  => $size_label,
			'variations' => $variations,
		);
	}

	$pattern = array(
		'left'        => '%s%v',
		'right'       => '%v%s',
		'left_space'  => '%s %v',
		'right_space' => '%v %s',
	);
	$pos     = get_option( 'woocommerce_currency_pos', 'right_space' );

	return array(
		'products' => $out,
		'ajaxUrl'  => WC_AJAX::get_endpoint( 'add_to_cart' ),
		'format'   => array(
			'decimals' => wc_get_price_decimals(),
			'thousand' => wc_get_price_thousand_separator(),
			'decimal'  => wc_get_price_decimal_separator(),
			'symbol'   => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
			'pattern'  => isset( $pattern[ $pos ] ) ? $pattern[ $pos ] : '%v %s',
		),
		'i18n'     => array(
			'add'     => __( 'Add to cart', 'zenyo' ),
			'soldOut' => __( 'Sold out', 'zenyo' ),
			'adding'  => __( 'Adding…', 'zenyo' ),
			/* translators: %s: sta je dodato */
			'added'   => __( 'Added to cart — %s.', 'zenyo' ),
			/* translators: %d: kolicina */
			'qty'     => __( 'qty %d', 'zenyo' ),
			'error'   => __( 'Could not add to cart. Please try again.', 'zenyo' ),
		),
	);
}

/* ---------- Kutija "ZENYO" na stranici za izmenu proizvoda ---------- */

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'zenyo_product', 'ZENYO', 'zenyo_product_meta_box', 'product', 'normal', 'default' );
	}
);

function zenyo_product_meta_box( $post ) {
	wp_nonce_field( 'zenyo_product_save', 'zenyo_product_nonce' );
	$spec  = get_post_meta( $post->ID, '_zenyo_key_spec', true );
	$guide = get_post_meta( $post->ID, '_zenyo_size_guide', true );
	$care  = get_post_meta( $post->ID, '_zenyo_care', true );
	$focus = get_post_meta( $post->ID, '_zenyo_focus', true );
	$extra = get_post_meta( $post->ID, '_zenyo_japanese', true );
	$mat   = get_post_meta( $post->ID, '_zenyo_material', true );
	$set   = get_post_meta( $post->ID, '_zenyo_is_set', true );
	?>
	<p>
		<label for="zenyo_key_spec"><strong>Oznaka ispod naziva</strong></label><br>
		<input type="text" id="zenyo_key_spec" name="zenyo_key_spec" value="<?php echo esc_attr( $spec ); ?>" class="widefat" placeholder="npr. IJF APPROVED ili 500 G">
	</p>
	<p>
		<label for="zenyo_size_guide"><strong>Tabela velicina</strong> (otvara se klikom na „Size guide"; moze HTML tabela)</label><br>
		<textarea id="zenyo_size_guide" name="zenyo_size_guide" rows="8" class="widefat code"><?php echo esc_textarea( $guide ); ?></textarea>
	</p>
	<p>
		<label for="zenyo_care"><strong>Uputstvo za odrzavanje</strong></label><br>
		<textarea id="zenyo_care" name="zenyo_care" rows="4" class="widefat"><?php echo esc_textarea( $care ); ?></textarea>
	</p>
	<p>
		<label for="zenyo_focus"><strong>Kadriranje glavne slike</strong> (prvo levo-desno, pa gore-dole)</label><br>
		<input type="text" id="zenyo_focus" name="zenyo_focus" value="<?php echo esc_attr( $focus ); ?>" placeholder="50% 50%">
	</p>
	<hr>
	<p><strong>Za stranice sa galerijom (Art, Jewellery, Accessories)</strong></p>
	<p>
		<label for="zenyo_japanese">Dodatak uz naziv (npr. japanski zapis: 生き甲斐)</label><br>
		<input type="text" id="zenyo_japanese" name="zenyo_japanese" value="<?php echo esc_attr( $extra ); ?>" class="widefat">
	</p>
	<p>
		<label for="zenyo_material">Kratka napomena ispod opisa (npr. Printed on canvas)</label><br>
		<input type="text" id="zenyo_material" name="zenyo_material" value="<?php echo esc_attr( $mat ); ?>" class="widefat">
	</p>
	<p>
		<label><input type="checkbox" name="zenyo_is_set" value="yes" <?php checked( $set, 'yes' ); ?>> Ovo je komplet (nema svoju sliku u galeriji, nudi se samo u padajucoj listi)</label>
	</p>
	<p class="description">Specifikacije na tim stranicama (Material, Chain length…) su osobine proizvoda iz kartice „Attributes" koje nisu oznacene za varijacije.</p>
	<p class="description">„Product details" na sajtu je glavni opis proizvoda (veliko polje za tekst na vrhu). Kratak opis ispod naziva je „Product short description".</p>
	<?php
}

add_action(
	'save_post_product',
	function ( $post_id ) {
		if ( ! isset( $_POST['zenyo_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['zenyo_product_nonce'] ), 'zenyo_product_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( isset( $_POST['zenyo_key_spec'] ) ) {
			update_post_meta( $post_id, '_zenyo_key_spec', sanitize_text_field( wp_unslash( $_POST['zenyo_key_spec'] ) ) );
		}
		if ( isset( $_POST['zenyo_size_guide'] ) ) {
			update_post_meta( $post_id, '_zenyo_size_guide', wp_kses_post( wp_unslash( $_POST['zenyo_size_guide'] ) ) );
		}
		if ( isset( $_POST['zenyo_focus'] ) ) {
			$focus = trim( sanitize_text_field( wp_unslash( $_POST['zenyo_focus'] ) ) );
			if ( '' === $focus || preg_match( '/^[0-9]{1,3}% [0-9]{1,3}%$/', $focus ) ) {
				update_post_meta( $post_id, '_zenyo_focus', $focus );
			}
		}
		if ( isset( $_POST['zenyo_japanese'] ) ) {
			update_post_meta( $post_id, '_zenyo_japanese', sanitize_text_field( wp_unslash( $_POST['zenyo_japanese'] ) ) );
		}
		if ( isset( $_POST['zenyo_material'] ) ) {
			update_post_meta( $post_id, '_zenyo_material', sanitize_text_field( wp_unslash( $_POST['zenyo_material'] ) ) );
		}
		// Polje za potvrdu se ne salje kad nije oznaceno, pa ga uvek upisujemo.
		update_post_meta( $post_id, '_zenyo_is_set', isset( $_POST['zenyo_is_set'] ) ? 'yes' : 'no' );
		if ( isset( $_POST['zenyo_care'] ) ) {
			update_post_meta( $post_id, '_zenyo_care', wp_kses_post( wp_unslash( $_POST['zenyo_care'] ) ) );
		}
	}
);

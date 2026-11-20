<?php
/**
 * Polja za srpski prevod u adminu.
 *
 *   Proizvod   - kutija "ZENYO - srpski prevod" ispod opisa proizvoda
 *   Kategorija i vrednost osobine (npr. boja) - polja "Srpski" na ekranu za izmenu
 *   Stranica   - kutija "ZENYO jezik": srpska verzija je posebna stranica vezana za englesku
 *
 * Pravilo svuda: prazno srpsko polje znaci da se na srpskom sajtu prikazuje engleski tekst.
 */

/* ---------- Proizvod ---------- */

/** Polja proizvoda sa srpskom verzijom: [ kljuc u bazi, natpis, vrsta polja ]. */
function zenyo_product_sr_fields() {
	return array(
		array( '_zenyo_title_sr', 'Naziv', 'text' ),
		array( '_zenyo_excerpt_sr', 'Kratak opis (ispod naziva)', 'textarea' ),
		array( '_zenyo_content_sr', 'Opis („Product details"; moze HTML)', 'html' ),
		array( '_zenyo_key_spec_sr', 'Oznaka ispod naziva', 'text' ),
		array( '_zenyo_size_guide_sr', 'Tabela velicina (moze HTML tabela)', 'html' ),
		array( '_zenyo_care_sr', 'Uputstvo za odrzavanje', 'html' ),
		array( '_zenyo_material_sr', 'Kratka napomena ispod opisa (galerije)', 'text' ),
	);
}

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'zenyo_product_sr', 'ZENYO - srpski prevod', 'zenyo_product_sr_meta_box', 'product', 'normal', 'default' );
	}
);

function zenyo_product_sr_meta_box( $post ) {
	wp_nonce_field( 'zenyo_product_sr_save', 'zenyo_product_sr_nonce' );
	echo '<p class="description">Glavna polja proizvoda (naziv, opisi, kutija „ZENYO") pisu se na engleskom. Ovde ide srpski tekst za ista polja. Prazno polje = na srpskom sajtu se prikazuje engleski tekst.</p>';
	foreach ( zenyo_product_sr_fields() as $field ) {
		list( $key, $label, $type ) = $field;
		$id    = ltrim( $key, '_' );
		$value = get_post_meta( $post->ID, $key, true );
		echo '<p><label for="' . esc_attr( $id ) . '"><strong>' . esc_html( $label ) . '</strong></label><br>';
		if ( 'text' === $type ) {
			echo '<input type="text" class="widefat" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '">';
		} else {
			echo '<textarea class="widefat' . ( 'html' === $type ? ' code' : '' ) . '" rows="' . ( 'html' === $type ? 6 : 3 ) . '" id="' . esc_attr( $id ) . '" name="' . esc_attr( $id ) . '">' . esc_textarea( $value ) . '</textarea>';
		}
		echo '</p>';
	}
}

add_action(
	'save_post_product',
	function ( $post_id ) {
		if ( ! isset( $_POST['zenyo_product_sr_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['zenyo_product_sr_nonce'] ), 'zenyo_product_sr_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		foreach ( zenyo_product_sr_fields() as $field ) {
			list( $key, , $type ) = $field;
			$id = ltrim( $key, '_' );
			if ( ! isset( $_POST[ $id ] ) ) {
				continue;
			}
			$raw = wp_unslash( $_POST[ $id ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			if ( 'html' === $type ) {
				$value = wp_kses_post( $raw );
			} elseif ( 'textarea' === $type ) {
				$value = sanitize_textarea_field( $raw );
			} else {
				$value = sanitize_text_field( $raw );
			}
			update_post_meta( $post_id, $key, $value );
		}
	}
);

/* ---------- Kategorije i vrednosti osobina ---------- */

/** Ispis jednog reda u obrascu za izmenu kategorije/vrednosti. */
function zenyo_term_sr_row( $term, $key, $label, $type = 'text', $note = '' ) {
	$value = get_term_meta( $term->term_id, $key, true );
	echo '<tr class="form-field"><th scope="row"><label for="' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
	if ( 'text' === $type ) {
		echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" value="' . esc_attr( $value ) . '">';
	} else {
		echo '<textarea id="' . esc_attr( $key ) . '" name="' . esc_attr( $key ) . '" rows="' . ( 'html' === $type ? 8 : 3 ) . '" class="large-text' . ( 'html' === $type ? ' code' : '' ) . '">' . esc_textarea( $value ) . '</textarea>';
	}
	if ( $note ) {
		echo '<p class="description">' . esc_html( $note ) . '</p>';
	}
	echo '</td></tr>';
}

/** Polja sa srpskom verzijom po vrsti: kategorija ima vise, vrednost osobine samo naziv. */
function zenyo_term_sr_fields( $taxonomy ) {
	$fields = array( array( 'zenyo_name_sr', 'Srpski: naziv', 'text', 'Prazno = prikazuje se naziv iznad.' ) );
	if ( 'product_cat' === $taxonomy ) {
		$fields[] = array( 'zenyo_description_sr', 'Srpski: opis', 'textarea', '' );
		$fields[] = array( 'zenyo_select_label_sr', 'Srpski: natpis iznad izbora proizvoda', 'text', 'Samo za izgled sa galerijom.' );
		$fields[] = array( 'zenyo_size_guide_sr', 'Srpski: tabela velicina', 'html', '' );
	}
	return $fields;
}

add_action(
	'admin_init',
	function () {
		$taxonomies = array( 'product_cat' );
		if ( function_exists( 'wc_get_attribute_taxonomy_names' ) ) {
			$taxonomies = array_merge( $taxonomies, wc_get_attribute_taxonomy_names() );
		}
		foreach ( $taxonomies as $taxonomy ) {
			add_action(
				$taxonomy . '_edit_form_fields',
				function ( $term ) use ( $taxonomy ) {
					foreach ( zenyo_term_sr_fields( $taxonomy ) as $f ) {
						zenyo_term_sr_row( $term, $f[0], $f[1], $f[2], $f[3] );
					}
				},
				30
			);
			add_action(
				'edited_' . $taxonomy,
				function ( $term_id ) use ( $taxonomy ) {
					// phpcs:disable WordPress.Security.NonceVerification -- WordPress proverava nonce za ceo obrazac.
					foreach ( zenyo_term_sr_fields( $taxonomy ) as $f ) {
						if ( ! isset( $_POST[ $f[0] ] ) ) {
							continue;
						}
						$raw = wp_unslash( $_POST[ $f[0] ] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
						if ( 'html' === $f[2] ) {
							$value = wp_kses_post( $raw );
						} elseif ( 'textarea' === $f[2] ) {
							$value = sanitize_textarea_field( $raw );
						} else {
							$value = sanitize_text_field( $raw );
						}
						update_term_meta( $term_id, $f[0], $value );
					}
					// phpcs:enable
				}
			);
		}
	}
);

/* ---------- Stranice ---------- */

add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'zenyo_page_lang', 'ZENYO jezik', 'zenyo_page_lang_meta_box', 'page', 'side', 'default' );
	}
);

function zenyo_page_lang_meta_box( $post ) {
	wp_nonce_field( 'zenyo_page_lang_save', 'zenyo_page_lang_nonce' );
	$base = (int) get_post_meta( $post->ID, '_zenyo_translation_of', true );
	$sr   = zenyo_page_sr( $post->ID );

	if ( ! $base && $sr ) {
		echo '<p>Ovo je engleska stranica. Srpska verzija: <a href="' . esc_url( get_edit_post_link( $sr->ID ) ) . '">' . esc_html( $sr->post_title ) . '</a></p>';
		return;
	}

	$pages = get_pages( array( 'exclude' => array( $post->ID ), 'sort_column' => 'post_title' ) );
	echo '<p><label for="zenyo_translation_of">Ova stranica je <strong>srpska verzija</strong> stranice:</label></p>';
	echo '<select id="zenyo_translation_of" name="zenyo_translation_of" class="widefat"><option value="0">— nije prevod (engleska stranica) —</option>';
	foreach ( $pages as $page ) {
		// Stranica koja je i sama prevod ne moze biti original.
		if ( get_post_meta( $page->ID, '_zenyo_translation_of', true ) ) {
			continue;
		}
		echo '<option value="' . (int) $page->ID . '" ' . selected( $base, $page->ID, false ) . '>' . esc_html( $page->post_title ) . '</option>';
	}
	echo '</select>';
	echo '<p class="description">Srpska verzija nema svoju adresu. Njen naslov, tekst i uvod (Excerpt) prikazuju se na adresi engleske stranice kad je sajt na srpskom.</p>';
}

add_action(
	'save_post_page',
	function ( $post_id ) {
		if ( ! isset( $_POST['zenyo_page_lang_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['zenyo_page_lang_nonce'] ), 'zenyo_page_lang_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) || ! isset( $_POST['zenyo_translation_of'] ) ) {
			return;
		}
		$base = absint( $_POST['zenyo_translation_of'] );
		if ( $base && $base !== $post_id && 'page' === get_post_type( $base ) ) {
			update_post_meta( $post_id, '_zenyo_translation_of', $base );
		} else {
			delete_post_meta( $post_id, '_zenyo_translation_of' );
		}
	}
);

// U spisku stranica uz srpsku verziju pise cija je.
add_filter(
	'display_post_states',
	function ( $states, $post ) {
		if ( 'page' === $post->post_type ) {
			$base = (int) get_post_meta( $post->ID, '_zenyo_translation_of', true );
			if ( $base ) {
				$states['zenyo_sr'] = 'srpski: ' . get_post_field( 'post_title', $base );
			}
		}
		return $states;
	},
	10,
	2
);

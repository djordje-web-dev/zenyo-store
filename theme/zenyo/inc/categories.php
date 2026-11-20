<?php
/**
 * Kategorije proizvoda: slika, kadriranje, kuda vodi klik, i polja u adminu.
 *
 * Pravila kuda vodi kategorija (da bi novi proizvodi radili bez programera):
 *   - ima potkategorije            -> stranica sa plocicama potkategorija (kao ZENYO: Art / Jewellery / Accessories)
 *   - ima tacno jedan proizvod     -> pravo na taj proizvod (kao kimona)
 *   - ima vise proizvoda           -> lista proizvoda
 *
 * Izuzetak: kategoriji se u adminu moze zadati izgled "svi proizvodi na jednoj stranici" (Belts).
 */

/**
 * Izgled stranice kategorije:
 *   'auto'    - pravila iznad
 *   'stack'   - svi proizvodi jedan ispod drugog, svaki sa svojim izborom i dugmetom (Belts)
 *   'gallery' - slike gore, jedan zajednicki deo za kupovinu dole (Art, Jewellery, Accessories)
 */
function zenyo_category_layout( $term_id ) {
	$layout = (string) get_term_meta( $term_id, 'zenyo_layout', true );
	return in_array( $layout, array( 'stack', 'gallery' ), true ) ? $layout : 'auto';
}

/** Izgled kategorije koja se upravo prikazuje, ili '' ako ovo nije stranica kategorije. */
function zenyo_current_category_layout() {
	return ( function_exists( 'is_product_category' ) && is_product_category() ) ? zenyo_category_layout( get_queried_object_id() ) : '';
}

/** Glavne kategorije za Shop Index, po redosledu iz admina (prevlacenjem u Products -> Categories). */
function zenyo_top_categories() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'orderby'    => 'menu_order',
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}

function zenyo_child_categories( $term_id ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => $term_id,
			'hide_empty' => true,
			'orderby'    => 'menu_order',
		)
	);
	return is_wp_error( $terms ) ? array() : $terms;
}

/** Objavljeni proizvodi neposredno u kategoriji (najvise $limit ID-jeva). */
function zenyo_category_product_ids( $term_id, $limit = 2 ) {
	return wc_get_products(
		array(
			'status'   => 'publish',
			'limit'    => $limit,
			'return'   => 'ids',
			'tax_query' => array(
				array(
					'taxonomy'         => 'product_cat',
					'field'            => 'term_id',
					'terms'            => array( $term_id ),
					'include_children' => false,
				),
			),
		)
	);
}

/** Ako kategorija ima tacno jedan proizvod i nema potkategorije, vraca njegov ID; inace 0. */
function zenyo_category_single_product( $term_id ) {
	if ( 'auto' !== zenyo_category_layout( $term_id ) || zenyo_child_categories( $term_id ) ) {
		return 0;
	}
	$ids = zenyo_category_product_ids( $term_id, 2 );
	return 1 === count( $ids ) ? (int) $ids[0] : 0;
}

/** Adresa na koju vodi klik na kategoriju. */
function zenyo_category_url( $term ) {
	$product_id = zenyo_category_single_product( $term->term_id );
	return $product_id ? get_permalink( $product_id ) : get_term_link( $term );
}

/** Slika kategorije (Products -> Categories -> Thumbnail). */
function zenyo_category_image_url( $term_id, $size = 'full' ) {
	$image_id = (int) get_term_meta( $term_id, 'thumbnail_id', true );
	return $image_id ? (string) wp_get_attachment_image_url( $image_id, $size ) : '';
}

/** Tacka kadriranja slike, npr. "50% 30%" (levo-desno, gore-dole). */
function zenyo_category_focus( $term_id ) {
	$focus = (string) get_term_meta( $term_id, 'zenyo_focus', true );
	return preg_match( '/^\d{1,3}% \d{1,3}%$/', $focus ) ? $focus : '50% 50%';
}

/** Boja natpisa preko slike na plocici: "dark" ili "light". */
function zenyo_category_label_tone( $term_id ) {
	return 'dark' === get_term_meta( $term_id, 'zenyo_label_tone', true ) ? 'dark' : 'light';
}

/** Da li je ovo "View all" (svi proizvodi) ili pretraga, a ne Shop Index. */
function zenyo_is_view_all() {
	return isset( $_GET['view'] ) && 'all' === $_GET['view']; // phpcs:ignore WordPress.Security.NonceVerification
}

function zenyo_view_all_url() {
	return add_query_arg( 'view', 'all', zenyo_shop_url() );
}

// Ako neko otvori adresu kategorije sa jednim proizvodom, saljemo ga pravo na proizvod.
add_action(
	'template_redirect',
	function () {
		if ( function_exists( 'is_product_category' ) && is_product_category() ) {
			$product_id = zenyo_category_single_product( get_queried_object_id() );
			if ( $product_id ) {
				wp_safe_redirect( get_permalink( $product_id ), 302 );
				exit;
			}
		}
	}
);

// Lista proizvoda: do 24 po strani.
add_filter(
	'loop_shop_per_page',
	function () {
		return 24;
	}
);

/* ---------- Polja u adminu: Products -> Categories -> (izmena kategorije) ---------- */

add_action(
	'product_cat_edit_form_fields',
	function ( $term ) {
		$focus = get_term_meta( $term->term_id, 'zenyo_focus', true );
		$tone   = zenyo_category_label_tone( $term->term_id );
		$layout = zenyo_category_layout( $term->term_id );
		$guide  = get_term_meta( $term->term_id, 'zenyo_size_guide', true );
		?>
		<tr class="form-field">
			<th scope="row"><label for="zenyo_layout">ZENYO: izgled stranice</label></th>
			<td>
				<select id="zenyo_layout" name="zenyo_layout">
					<option value="auto" <?php selected( $layout, 'auto' ); ?>>Automatski (jedan proizvod: pravo na njega; vise: lista)</option>
					<option value="stack" <?php selected( $layout, 'stack' ); ?>>Svi proizvodi se kupuju sa ove stranice (kao Belts)</option>
					<option value="gallery" <?php selected( $layout, 'gallery' ); ?>>Galerija gore, kupovina dole (kao Art, Jewellery, Accessories)</option>
				</select>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="zenyo_select_label">ZENYO: natpis iznad izbora proizvoda</label></th>
			<td>
				<input type="text" id="zenyo_select_label" name="zenyo_select_label" value="<?php echo esc_attr( get_term_meta( $term->term_id, 'zenyo_select_label', true ) ); ?>" placeholder="Select product">
				<p class="description">Samo za izgled sa galerijom, npr. „Select artwork".</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="zenyo_size_guide">ZENYO: tabela velicina</label></th>
			<td>
				<textarea id="zenyo_size_guide" name="zenyo_size_guide" rows="8" class="large-text code"><?php echo esc_textarea( $guide ); ?></textarea>
				<p class="description">Koristi se kad se svi proizvodi kupuju sa ove stranice. Otvara se klikom na „Size guide". Moze HTML tabela.</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="zenyo_focus">ZENYO: kadriranje slike</label></th>
			<td>
				<input type="text" id="zenyo_focus" name="zenyo_focus" value="<?php echo esc_attr( $focus ); ?>" placeholder="50% 50%">
				<p class="description">Koji deo slike ostaje u kadru kad se slika isece: prvo levo-desno, pa gore-dole. Npr. „50% 30%" drzi gornji deo, „90% 50%" desnu stranu.</p>
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row"><label for="zenyo_label_tone">ZENYO: boja natpisa preko slike</label></th>
			<td>
				<select id="zenyo_label_tone" name="zenyo_label_tone">
					<option value="light" <?php selected( $tone, 'light' ); ?>>Bela (za tamne slike)</option>
					<option value="dark" <?php selected( $tone, 'dark' ); ?>>Crna (za svetle slike)</option>
				</select>
				<p class="description">Vazi za plocice potkategorija (npr. Art / Jewellery / Accessories).</p>
			</td>
		</tr>
		<?php
	},
	20
);

add_action(
	'edited_product_cat',
	function ( $term_id ) {
		// phpcs:disable WordPress.Security.NonceVerification -- WordPress proverava nonce za ceo obrazac kategorije.
		if ( isset( $_POST['zenyo_focus'] ) ) {
			$focus = trim( sanitize_text_field( wp_unslash( $_POST['zenyo_focus'] ) ) );
			if ( '' === $focus || preg_match( '/^\d{1,3}% \d{1,3}%$/', $focus ) ) {
				update_term_meta( $term_id, 'zenyo_focus', $focus );
			}
		}
		if ( isset( $_POST['zenyo_layout'] ) ) {
			$layout = sanitize_key( $_POST['zenyo_layout'] );
			update_term_meta( $term_id, 'zenyo_layout', in_array( $layout, array( 'stack', 'gallery' ), true ) ? $layout : 'auto' );
		}
		if ( isset( $_POST['zenyo_select_label'] ) ) {
			update_term_meta( $term_id, 'zenyo_select_label', sanitize_text_field( wp_unslash( $_POST['zenyo_select_label'] ) ) );
		}
		if ( isset( $_POST['zenyo_size_guide'] ) ) {
			update_term_meta( $term_id, 'zenyo_size_guide', wp_kses_post( wp_unslash( $_POST['zenyo_size_guide'] ) ) );
		}
		if ( isset( $_POST['zenyo_label_tone'] ) ) {
			update_term_meta( $term_id, 'zenyo_label_tone', 'dark' === $_POST['zenyo_label_tone'] ? 'dark' : 'light' );
		}
		// phpcs:enable
	}
);

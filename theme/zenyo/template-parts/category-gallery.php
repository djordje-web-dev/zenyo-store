<?php
/**
 * Kategorija sa galerijom i jednim zajednickim delom za kupovinu (dizajn: Art, Jewellery, Accessories).
 * Ukljucuje se u adminu: Products -> Categories -> izmena kategorije -> "ZENYO: izgled stranice".
 *
 * Gore su slike proizvoda; dole se bira proizvod (i velicina, ako je ima), kolicina, i dodaje u korpu.
 * Proizvod oznacen kao komplet ne dobija sliku u galeriji, ali se nudi u padajucoj listi.
 * Mrezu racunamo po broju proizvoda, pa nov proizvod sam dobija svoje mesto.
 */

$zenyo_term = get_queried_object();

$zenyo_products = array();
while ( have_posts() ) {
	the_post();
	$zenyo_p = wc_get_product( get_the_ID() );
	if ( $zenyo_p ) {
		$zenyo_products[] = $zenyo_p;
	}
}
$zenyo_gallery = array_values(
	array_filter(
		$zenyo_products,
		function ( $p ) {
			return 'yes' !== $p->get_meta( '_zenyo_is_set' );
		}
	)
);
// U padajucoj listi: prvo obicni proizvodi redom iz galerije, pa kompleti.
$zenyo_options = array_merge(
	$zenyo_gallery,
	array_filter(
		$zenyo_products,
		function ( $p ) {
			return 'yes' === $p->get_meta( '_zenyo_is_set' );
		}
	)
);

// Mreza: do 4 slike u jednom redu; 5-6 slika u dva reda po tri; vise od toga po cetiri u redu.
$zenyo_count = count( $zenyo_gallery );
if ( $zenyo_count <= 4 ) {
	$zenyo_cols = max( 1, $zenyo_count );
} elseif ( $zenyo_count <= 6 ) {
	$zenyo_cols = 3;
} else {
	$zenyo_cols = 4;
}
$zenyo_rows = max( 1, (int) ceil( $zenyo_count / $zenyo_cols ) );
// Do dva reda sve staje u jedan ekran, kao u dizajnu; sa vise redova stranica se skroluje.
$zenyo_fits = $zenyo_rows <= 2;

$zenyo_select_label = zenyo_term_meta( $zenyo_term->term_id, 'zenyo_select_label' );
if ( '' === $zenyo_select_label ) {
	$zenyo_select_label = __( 'Select product', 'zenyo' );
}

get_header( null, array( 'screen' => $zenyo_fits ) );
?>
<div class="zy-gal<?php echo $zenyo_fits ? '' : ' zy-gal--long'; ?>" data-zy-gallery>

	<section class="zy-gal__top" aria-label="<?php esc_attr_e( 'Collection', 'zenyo' ); ?>">
		<div class="zy-gal__intro">
			<h1><?php echo esc_html( $zenyo_term->name ); ?></h1>
			<div class="zy-gal__dash" aria-hidden="true"></div>
			<?php if ( $zenyo_term->description ) : ?>
				<p><?php echo esc_html( $zenyo_term->description ); ?></p>
			<?php endif; ?>
		</div>

		<ul class="zy-gal__grid" style="grid-template-columns: repeat(<?php echo (int) $zenyo_cols; ?>, minmax(0, 1fr)); grid-template-rows: repeat(<?php echo (int) $zenyo_rows; ?>, minmax(0, 1fr));">
			<?php foreach ( $zenyo_gallery as $i => $zenyo_p ) : ?>
				<li>
					<button type="button" class="zy-gal__item" data-zy-item="<?php echo (int) $zenyo_p->get_id(); ?>" aria-pressed="false" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: naziv proizvoda */ __( 'Select %s', 'zenyo' ), $zenyo_p->get_name() ) ); ?>">
						<span class="zy-gal__media">
							<?php
							if ( $zenyo_p->get_image_id() ) {
								echo wp_get_attachment_image( $zenyo_p->get_image_id(), 'large', false, array( 'class' => 'zy-gal__img', 'alt' => $zenyo_p->get_name() ) );
							}
							?>
						</span>
						<span class="zy-gal__caption"><span><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span><span><?php echo esc_html( zenyo_product_label( $zenyo_p ) ); ?></span></span>
					</button>
				</li>
			<?php endforeach; ?>
		</ul>
	</section>

	<div class="zy-gal__rule" aria-hidden="true"></div>

	<section class="zy-gal__buy" data-zy-buy aria-label="<?php esc_attr_e( 'Buy', 'zenyo' ); ?>">
		<div></div>
		<div class="zy-gal__buy-grid">

			<div class="zy-gal__form">
				<label class="zy-field">
					<span class="zy-field__label"><?php echo esc_html( $zenyo_select_label ); ?></span>
					<span class="zy-select">
						<select data-zy-pick>
							<?php foreach ( $zenyo_options as $zenyo_p ) : ?>
								<option value="<?php echo (int) $zenyo_p->get_id(); ?>"><?php echo esc_html( zenyo_product_label( $zenyo_p ) ); ?></option>
							<?php endforeach; ?>
						</select>
						<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="square"><path d="M6 9l6 6 6-6"></path></svg>
					</span>
				</label>

				<label class="zy-field" data-zy-size-wrap hidden>
					<span class="zy-field__label" data-zy-size-label></span>
					<span class="zy-select">
						<select data-zy-size></select>
						<svg aria-hidden="true" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="square"><path d="M6 9l6 6 6-6"></path></svg>
					</span>
				</label>

				<div class="zy-gal__row">
					<span class="zy-field__label" id="zy-gal-qty-label"><?php esc_html_e( 'Quantity', 'zenyo' ); ?></span>
					<div class="zy-stepper" role="group" aria-labelledby="zy-gal-qty-label">
						<button type="button" data-zy-dec aria-label="<?php esc_attr_e( 'Decrease quantity', 'zenyo' ); ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14"></path></svg></button>
						<span data-zy-qty aria-live="polite">1</span>
						<button type="button" data-zy-inc aria-label="<?php esc_attr_e( 'Increase quantity', 'zenyo' ); ?>"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg></button>
					</div>
				</div>

				<div class="zy-gal__row">
					<span class="zy-field__label"><?php esc_html_e( 'Price', 'zenyo' ); ?></span>
					<span class="zy-gal__price" data-zy-price aria-live="polite"></span>
				</div>
			</div>

			<div class="zy-gal__info">
				<h2 data-zy-info-title></h2>
				<p data-zy-info-text></p>
				<span class="zy-field__label zy-field__label--soft" data-zy-info-material hidden></span>
				<div class="zy-gal__info-rule" data-zy-info-rule aria-hidden="true" hidden></div>
				<dl data-zy-info-details hidden></dl>
				<button type="button" class="zy-gal__add" data-zy-add><?php esc_html_e( 'Add to cart', 'zenyo' ); ?></button>
				<p class="zy-gal__status" role="status" data-zy-status></p>
			</div>

		</div>
	</section>

	<script type="application/json" id="zy-gallery-data"><?php echo wp_json_encode( zenyo_gallery_script_data( $zenyo_options ) ); ?></script>

</div>
<?php
get_footer();

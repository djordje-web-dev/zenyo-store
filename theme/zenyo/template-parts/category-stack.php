<?php
/**
 * Kategorija u kojoj se svi proizvodi kupuju sa iste stranice, jedan ispod drugog (dizajn: Belts).
 * Ukljucuje se u adminu: Products -> Categories -> izmena kategorije -> "ZENYO: izgled stranice".
 * Svaki proizvod ima sliku levo i izbor, cenu i dugme desno; nema odlaska na stranicu proizvoda.
 */

$zenyo_term     = get_queried_object();
$zenyo_guide    = zenyo_term_meta( $zenyo_term->term_id, 'zenyo_size_guide' );
$zenyo_delivery = zenyo_opt( 'delivery_line' );
$zenyo_ship_url = zenyo_page_url( 'shipping-returns' );

get_header();
?>
<div class="zy-stack">

	<div class="zy-stack__head">
		<h1 class="zy-buy__title"><?php echo esc_html( $zenyo_term->name ); ?></h1>
		<div class="zy-buy__dash" aria-hidden="true"></div>
	</div>

	<div class="zy-stack__items">
		<?php
		while ( have_posts() ) :
			the_post();
			$zenyo_product = wc_get_product( get_the_ID() );
			if ( ! $zenyo_product ) {
				continue;
			}
			$zenyo_image_id = (int) $zenyo_product->get_image_id();
			$zenyo_focus    = (string) $zenyo_product->get_meta( '_zenyo_focus' );
			?>
			<section class="zy-stack__item" data-zy-product aria-labelledby="zy-buy-title-<?php echo (int) $zenyo_product->get_id(); ?>">
				<div class="zy-stack__media">
					<?php if ( $zenyo_image_id ) : ?>
						<img data-zy-main src="<?php echo esc_url( wp_get_attachment_image_url( $zenyo_image_id, 'full' ) ); ?>" alt="<?php echo esc_attr( get_post_meta( $zenyo_image_id, '_wp_attachment_image_alt', true ) ?: $zenyo_product->get_name() ); ?>"<?php echo $zenyo_focus ? ' style="object-position: ' . esc_attr( $zenyo_focus ) . ';"' : ''; ?>>
					<?php endif; ?>
				</div>
				<?php
				get_template_part(
					'template-parts/product-buy',
					null,
					array(
						'product'      => $zenyo_product,
						'heading'      => 'h2',
						'guide_id'     => $zenyo_guide ? 'zy-guide' : '',
						'colour_style' => 'row',
					)
				);
				?>
			</section>
		<?php endwhile; ?>
	</div>

	<?php if ( $zenyo_delivery ) : ?>
		<div class="zy-stack__foot">
			<span></span>
			<p class="zy-buy__meta">
				<a href="<?php echo esc_url( $zenyo_ship_url ); ?>"><?php echo esc_html( $zenyo_delivery ); ?></a>
				<span aria-hidden="true">|</span>
				<a href="<?php echo esc_url( $zenyo_ship_url ); ?>"><?php esc_html_e( 'Returns & exchanges', 'zenyo' ); ?></a>
			</p>
		</div>
	<?php endif; ?>

	<?php
	if ( $zenyo_guide ) {
		get_template_part(
			'template-parts/size-guide-dialog',
			null,
			array(
				'id'      => 'zy-guide',
				/* translators: %s: naziv kategorije */
				'title'   => sprintf( __( '%s size guide', 'zenyo' ), $zenyo_term->name ),
				'content' => $zenyo_guide,
			)
		);
	}
	?>

</div>
<?php
get_footer();

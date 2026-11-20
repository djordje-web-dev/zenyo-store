<?php
/**
 * Stranica proizvoda (dizajn: stranice kimona).
 *
 * Levo galerija, desno naziv, izbor boje i velicine, cena i dugme; ispod sekcije koje se otvaraju.
 * Sve dolazi iz admina: slike, varijacije sa cenom i zalihom, opisi, tabela velicina.
 * Kolona za kupovinu je u template-parts/product-buy.php, a izbor, cenu i korpu vodi assets/js/product.js.
 */

defined( 'ABSPATH' ) || exit;

the_post();
$zenyo_product = wc_get_product( get_the_ID() );

$zenyo_images   = array_values( array_filter( array_merge( array( $zenyo_product->get_image_id() ), $zenyo_product->get_gallery_image_ids() ) ) );
$zenyo_guide    = (string) $zenyo_product->get_meta( '_zenyo_size_guide' );
$zenyo_care     = (string) $zenyo_product->get_meta( '_zenyo_care' );
$zenyo_focus    = (string) $zenyo_product->get_meta( '_zenyo_focus' );
$zenyo_details  = (string) apply_filters( 'the_content', get_the_content() );
$zenyo_delivery = zenyo_opt( 'delivery_line' );
$zenyo_returns  = zenyo_opt( 'returns_summary' );
$zenyo_ship_url = zenyo_page_url( 'shipping-returns' );

// Podaci za pretrazivace (naziv, cena, dostupnost) koje WooCommerce inace sam dodaje.
WC()->structured_data->generate_product_data( $zenyo_product );

$zenyo_arrow = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="square" aria-hidden="true"><path d="M3 12h17"></path><path d="M14 6l6 6-6 6"></path></svg>';

get_header( null, array( 'left' => 'logo' ) );
?>
<div class="zy-product">

	<?php wc_print_notices(); ?>

	<div class="zy-product__top" data-zy-product>

		<section class="zy-gallery" aria-label="<?php esc_attr_e( 'Product images', 'zenyo' ); ?>">
			<?php if ( count( $zenyo_images ) > 1 ) : ?>
				<div class="zy-gallery__thumbs" role="group" aria-label="<?php esc_attr_e( 'Choose image', 'zenyo' ); ?>">
					<?php foreach ( $zenyo_images as $i => $image_id ) : ?>
						<button type="button" class="zy-gallery__thumb" data-zy-thumb data-full="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'full' ) ); ?>" data-alt="<?php echo esc_attr( get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ); ?>" aria-pressed="<?php echo 0 === $i ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %d: redni broj slike */ __( 'Image %d', 'zenyo' ), $i + 1 ) ); ?>">
							<?php echo wp_get_attachment_image( $image_id, 'woocommerce_thumbnail', false, array( 'alt' => '' ) ); ?>
						</button>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<div class="zy-gallery__main">
				<?php if ( $zenyo_images ) : ?>
					<img class="zy-gallery__img" data-zy-main src="<?php echo esc_url( wp_get_attachment_image_url( $zenyo_images[0], 'full' ) ); ?>" alt="<?php echo esc_attr( get_post_meta( $zenyo_images[0], '_wp_attachment_image_alt', true ) ?: get_the_title() ); ?>"<?php echo $zenyo_focus ? ' style="object-position: ' . esc_attr( $zenyo_focus ) . ';"' : ''; ?>>
					<button type="button" class="zy-gallery__zoom" data-zy-open="zy-zoom" aria-label="<?php esc_attr_e( 'Enlarge image', 'zenyo' ); ?>">
						<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"></circle><path d="M15.5 15.5L21 21"></path></svg>
					</button>
				<?php endif; ?>
			</div>
		</section>

		<?php
		get_template_part(
			'template-parts/product-buy',
			null,
			array(
				'product'       => $zenyo_product,
				'heading'       => 'h1',
				'guide_id'      => $zenyo_guide ? 'zy-guide' : '',
				'colour_style'  => 'tile',
				'show_delivery' => true,
			)
		);
		?>
	</div>

	<section class="zy-acc" aria-label="<?php esc_attr_e( 'Product information sections', 'zenyo' ); ?>">
		<?php if ( trim( wp_strip_all_tags( $zenyo_details ) ) ) : ?>
			<details class="zy-acc__item">
				<summary><h2><?php esc_html_e( 'Product details', 'zenyo' ); ?></h2></summary>
				<div class="zy-acc__body"><?php echo wp_kses_post( $zenyo_details ); ?></div>
			</details>
		<?php endif; ?>
		<?php if ( $zenyo_guide ) : ?>
			<details class="zy-acc__item">
				<summary><h2><?php esc_html_e( 'Size guide', 'zenyo' ); ?></h2></summary>
				<div class="zy-acc__body">
					<button type="button" class="zy-textlink" data-zy-open="zy-guide"><?php esc_html_e( 'Open size guide', 'zenyo' ); ?><?php echo $zenyo_arrow; // phpcs:ignore ?></button>
				</div>
			</details>
		<?php endif; ?>
		<?php if ( $zenyo_care ) : ?>
			<details class="zy-acc__item">
				<summary><h2><?php esc_html_e( 'Care instructions', 'zenyo' ); ?></h2></summary>
				<div class="zy-acc__body"><?php echo wp_kses_post( wpautop( $zenyo_care ) ); ?></div>
			</details>
		<?php endif; ?>
		<details class="zy-acc__item">
			<summary><h2><?php esc_html_e( 'Shipping & returns', 'zenyo' ); ?></h2></summary>
			<div class="zy-acc__body">
				<?php if ( $zenyo_delivery ) : ?>
					<p><?php echo esc_html( $zenyo_delivery ); ?>.</p>
				<?php endif; ?>
				<?php if ( $zenyo_returns ) : ?>
					<p><?php echo esc_html( $zenyo_returns ); ?></p>
				<?php endif; ?>
				<?php if ( '#' !== $zenyo_ship_url ) : ?>
					<p><a class="zy-textlink" href="<?php echo esc_url( $zenyo_ship_url ); ?>"><?php esc_html_e( 'Shipping & Returns', 'zenyo' ); ?><?php echo $zenyo_arrow; // phpcs:ignore ?></a></p>
				<?php endif; ?>
			</div>
		</details>
	</section>

	<?php // Uvecana slika. ?>
	<dialog class="zy-dialog zy-dialog--zoom" id="zy-zoom" aria-label="<?php esc_attr_e( 'Enlarged image', 'zenyo' ); ?>">
		<img data-zy-zoom-img src="" alt="">
		<button type="button" class="zy-dialog__close zy-dialog__close--corner" data-zy-close aria-label="<?php esc_attr_e( 'Close enlarged image', 'zenyo' ); ?>">
			<svg width="16" height="16" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 2L16 16M16 2L2 16"></path></svg>
		</button>
	</dialog>

	<?php
	if ( $zenyo_guide ) {
		get_template_part(
			'template-parts/size-guide-dialog',
			null,
			array(
				'id'      => 'zy-guide',
				'title'   => __( 'Size guide', 'zenyo' ),
				'content' => $zenyo_guide,
			)
		);
	}
	?>

</div>
<?php
get_footer();

<?php
/**
 * Kolona za kupovinu jednog proizvoda: naziv, oznaka, opis, izbor, cena, dugme "Add to cart".
 * Koristi je stranica proizvoda, a i stranice na kojima se vise proizvoda kupuje odjednom (Belts).
 *
 * Mora da stoji unutar elementa sa atributom data-zy-product; assets/js/product.js ga tako nalazi.
 *
 * Argumenti ($args):
 *   product       WC_Product (obavezno)
 *   heading       'h1' ili 'h2' - koji naslov je naziv proizvoda
 *   guide_id      ID panela sa tabelom velicina; prazno = bez linka "Size guide"
 *   colour_style  'tile' (kvadratic iznad naziva, kao kod kimona) ili 'row' (trakica pored naziva, kao kod pojaseva)
 *   show_delivery da li se ispod dugmeta ispisuje rok isporuke
 */

$zenyo_product = $args['product'];
$zenyo_heading = isset( $args['heading'] ) && 'h2' === $args['heading'] ? 'h2' : 'h1';
$zenyo_guide   = isset( $args['guide_id'] ) ? (string) $args['guide_id'] : '';
$zenyo_row     = isset( $args['colour_style'] ) && 'row' === $args['colour_style'];
$zenyo_show_d  = ! empty( $args['show_delivery'] );

$zenyo_id       = $zenyo_product->get_id();
$zenyo_choices  = zenyo_product_choices( $zenyo_product );
$zenyo_spec     = (string) $zenyo_product->get_meta( '_zenyo_key_spec' );
$zenyo_short    = (string) $zenyo_product->get_short_description();
$zenyo_delivery = zenyo_opt( 'delivery_line' );
$zenyo_ship_url = zenyo_page_url( 'shipping-returns' );
$zenyo_can_buy  = $zenyo_product->is_type( 'variable' ) || ( $zenyo_product->is_purchasable() && $zenyo_product->is_in_stock() );
$zenyo_guide_shown = false;

$zenyo_arrow = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="square" aria-hidden="true"><path d="M3 12h17"></path><path d="M14 6l6 6-6 6"></path></svg>';
?>
<div class="zy-buy">

	<div class="zy-buy__head">
		<<?php echo $zenyo_heading; // phpcs:ignore ?> class="zy-buy__title" id="zy-buy-title-<?php echo (int) $zenyo_id; ?>"><?php echo esc_html( $zenyo_product->get_name() ); ?></<?php echo $zenyo_heading; // phpcs:ignore ?>>
		<div class="zy-buy__dash" aria-hidden="true"></div>
	</div>

	<?php if ( $zenyo_spec ) : ?>
		<p class="zy-buy__spec"><?php echo esc_html( $zenyo_spec ); ?></p>
	<?php endif; ?>

	<?php if ( $zenyo_short ) : ?>
		<div class="zy-buy__desc"><?php echo wp_kses_post( wpautop( $zenyo_short ) ); ?></div>
	<?php endif; ?>

	<?php
	foreach ( $zenyo_choices as $taxonomy => $choice ) :
		$zenyo_is_colour = ( 'pa_colour' === $taxonomy );
		$zenyo_label_id  = 'zy-choice-' . (int) $zenyo_id . '-' . sanitize_html_class( $taxonomy );
		$zenyo_opt_class = 'zy-choice__options';
		if ( $zenyo_is_colour ) {
			$zenyo_opt_class .= $zenyo_row ? ' zy-choice__options--colour-row' : ' zy-choice__options--colour';
		}
		?>
		<div class="zy-choice" role="group" aria-labelledby="<?php echo esc_attr( $zenyo_label_id ); ?>" data-zy-choice="<?php echo esc_attr( $taxonomy ); ?>">
			<div class="zy-choice__head">
				<span class="zy-choice__label" id="<?php echo esc_attr( $zenyo_label_id ); ?>"><?php echo esc_html( $choice['label'] ); ?></span>
				<span class="zy-choice__value" data-zy-value></span>
			</div>
			<div class="<?php echo esc_attr( $zenyo_opt_class ); ?>">
				<?php foreach ( $choice['options'] as $option ) : ?>
					<?php if ( $zenyo_is_colour ) : ?>
						<?php $zenyo_hex = zenyo_colour_hex( $option['slug'] ); ?>
						<button type="button" class="zy-swatch<?php echo $zenyo_row ? ' zy-swatch--row' : ''; ?>" data-zy-option="<?php echo esc_attr( $option['slug'] ); ?>" data-name="<?php echo esc_attr( $option['name'] ); ?>" aria-pressed="false">
							<?php if ( $zenyo_hex ) : ?>
								<span class="zy-swatch__chip" style="background: <?php echo esc_attr( $zenyo_hex ); ?>;" aria-hidden="true"></span>
							<?php endif; ?>
							<span><?php echo esc_html( $option['name'] ); ?></span>
						</button>
					<?php else : ?>
						<button type="button" class="zy-size" data-zy-option="<?php echo esc_attr( $option['slug'] ); ?>" data-name="<?php echo esc_attr( $option['name'] ); ?>" aria-pressed="false"><?php echo esc_html( $option['name'] ); ?></button>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
			<?php if ( $zenyo_guide && ! $zenyo_is_colour && ! $zenyo_guide_shown ) : ?>
				<button type="button" class="zy-textlink" data-zy-open="<?php echo esc_attr( $zenyo_guide ); ?>"><?php esc_html_e( 'Size guide', 'zenyo' ); ?><?php echo $zenyo_arrow; // phpcs:ignore ?></button>
				<?php $zenyo_guide_shown = true; ?>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>

	<div class="zy-buy__action">
		<p class="zy-buy__price" data-zy-price><?php echo wp_kses_post( zenyo_price_label( $zenyo_product ) ); ?></p>
		<button type="button" class="zy-buy__add" data-zy-add<?php disabled( ! $zenyo_can_buy ); ?>>
			<?php echo $zenyo_can_buy ? esc_html__( 'Add to cart', 'zenyo' ) : esc_html__( 'Sold out', 'zenyo' ); ?>
			<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter" aria-hidden="true"><path d="M4.5 8.5h15l-1 12h-13z"></path><path d="M8.5 8.5V7a3.5 3.5 0 0 1 7 0v1.5"></path></svg>
		</button>
		<p class="zy-buy__status" role="status" aria-live="polite" data-zy-status></p>
		<?php if ( $zenyo_show_d && $zenyo_delivery ) : ?>
			<p class="zy-buy__meta">
				<a href="<?php echo esc_url( $zenyo_ship_url ); ?>"><?php echo esc_html( $zenyo_delivery ); ?></a>
				<span aria-hidden="true">|</span>
				<a href="<?php echo esc_url( $zenyo_ship_url ); ?>"><?php esc_html_e( 'Returns & exchanges', 'zenyo' ); ?></a>
			</p>
		<?php endif; ?>
	</div>

	<script type="application/json" data-zy-product-data><?php echo wp_json_encode( zenyo_product_script_data( $zenyo_product ) ); ?></script>

</div>

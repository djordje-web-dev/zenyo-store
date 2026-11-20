<?php
/**
 * Korpa.
 *
 * Nazivi klasa "woocommerce-cart-form", "cart_totals", "product-remove" i dugme "update_cart"
 * su namerno isti kao u WooCommerce-u: njegova skripta ih trazi i zahvaljujuci tome promena kolicine
 * i uklanjanje stavke rade bez ponovnog ucitavanja stranice. Ne menjati ih.
 */

defined( 'ABSPATH' ) || exit;

$zenyo_cart  = WC()->cart;
$zenyo_count = $zenyo_cart->get_cart_contents_count();
$zenyo_recs  = zenyo_cart_recommendations( 2 );
$zenyo_minus = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14"></path></svg>';
$zenyo_plus  = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="M5 12h14"></path><path d="M12 5v14"></path></svg>';

do_action( 'woocommerce_before_cart' );
?>
<div class="zy-cartx">

	<form class="woocommerce-cart-form zy-cartx__items" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post" aria-label="<?php esc_attr_e( 'Cart items', 'zenyo' ); ?>">
		<div class="zy-cartx__cols" aria-hidden="true">
			<span><?php esc_html_e( 'Product', 'zenyo' ); ?></span>
			<span><?php esc_html_e( 'Price', 'zenyo' ); ?></span>
			<span><?php esc_html_e( 'Quantity', 'zenyo' ); ?></span>
			<span><?php esc_html_e( 'Total', 'zenyo' ); ?></span>
		</div>

		<ul class="zy-cartx__list woocommerce-cart-form__contents">
			<?php
			foreach ( $zenyo_cart->get_cart() as $cart_item_key => $cart_item ) :
				$_product = $cart_item['data'];
				if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 ) {
					continue;
				}
				$zenyo_parent  = wc_get_product( $cart_item['product_id'] );
				$zenyo_name    = $zenyo_parent ? $zenyo_parent->get_name() : $_product->get_name();
				$zenyo_link    = $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '';
				$zenyo_variant = zenyo_cart_item_variant( $cart_item );
				$zenyo_image   = $_product->get_image_id() ? wp_get_attachment_image( $_product->get_image_id(), 'woocommerce_thumbnail', false, array( 'alt' => '' ) ) : '';
				$zenyo_max     = $_product->get_max_purchase_quantity();
				?>
				<li class="zy-cartx__row woocommerce-cart-form__cart-item cart_item">
					<div class="zy-cartx__product">
						<span class="zy-cartx__thumb"><?php echo $zenyo_image; // phpcs:ignore ?></span>
						<div class="zy-cartx__info">
							<?php if ( $zenyo_link ) : ?>
								<a class="zy-cartx__name" href="<?php echo esc_url( $zenyo_link ); ?>"><?php echo esc_html( $zenyo_name ); ?></a>
							<?php else : ?>
								<span class="zy-cartx__name"><?php echo esc_html( $zenyo_name ); ?></span>
							<?php endif; ?>
							<?php if ( $zenyo_variant ) : ?>
								<span class="zy-cartx__variant"><?php echo esc_html( $zenyo_variant ); ?></span>
							<?php endif; ?>
							<span class="product-remove">
								<a class="remove zy-cartx__remove" href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: naziv proizvoda */ __( 'Remove %s', 'zenyo' ), $zenyo_name ) ); ?>" data-product_id="<?php echo esc_attr( $cart_item['product_id'] ); ?>"><?php esc_html_e( 'Remove', 'zenyo' ); ?></a>
							</span>
						</div>
					</div>

					<span class="zy-cartx__price"><?php echo wp_kses_post( $zenyo_cart->get_product_price( $_product ) ); ?></span>

					<div class="zy-cartx__qty">
						<div class="zy-stepper" role="group" aria-label="<?php esc_attr_e( 'Quantity', 'zenyo' ); ?>">
							<button type="button" data-zy-step="-1" aria-label="<?php esc_attr_e( 'Decrease quantity', 'zenyo' ); ?>"<?php disabled( $cart_item['quantity'] <= 1 ); ?>><?php echo $zenyo_minus; // phpcs:ignore ?></button>
							<input type="number" class="qty" name="cart[<?php echo esc_attr( $cart_item_key ); ?>][qty]" value="<?php echo esc_attr( $cart_item['quantity'] ); ?>" min="1"<?php echo $zenyo_max > 0 ? ' max="' . esc_attr( $zenyo_max ) . '"' : ''; ?> step="1" inputmode="numeric" aria-label="<?php esc_attr_e( 'Quantity', 'zenyo' ); ?>">
							<button type="button" data-zy-step="1" aria-label="<?php esc_attr_e( 'Increase quantity', 'zenyo' ); ?>"<?php disabled( $zenyo_max > 0 && $cart_item['quantity'] >= $zenyo_max ); ?>><?php echo $zenyo_plus; // phpcs:ignore ?></button>
						</div>
					</div>

					<span class="zy-cartx__total"><?php echo wp_kses_post( $zenyo_cart->get_product_subtotal( $_product, $cart_item['quantity'] ) ); ?></span>
				</li>
			<?php endforeach; ?>
		</ul>

		<div class="zy-cartx__back">
			<a href="<?php echo esc_url( zenyo_shop_url() ); ?>">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="square" aria-hidden="true"><path d="M21 12H4"></path><path d="M10 6l-6 6 6 6"></path></svg>
				<?php esc_html_e( 'Continue shopping', 'zenyo' ); ?>
			</a>
		</div>

		<?php // Dugme koje WooCommerce-ova skripta "klikne" kad se promeni kolicina; kupac ga ne vidi. ?>
		<button type="submit" class="screen-reader-text" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'zenyo' ); ?>" tabindex="-1"><?php esc_html_e( 'Update cart', 'zenyo' ); ?></button>
		<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
	</form>

	<aside class="cart_totals zy-summary" aria-label="<?php esc_attr_e( 'Order summary', 'zenyo' ); ?>">
		<h2 class="zy-summary__title"><?php esc_html_e( 'Order summary', 'zenyo' ); ?></h2>
		<dl class="zy-summary__rows">
			<div><dt><?php echo esc_html( zenyo_subtotal_label( $zenyo_count ) ); ?></dt><dd><?php echo wp_kses_post( $zenyo_cart->get_cart_subtotal() ); ?></dd></div>
			<?php if ( $zenyo_cart->needs_shipping() ) : ?>
				<div><dt><?php esc_html_e( 'Shipping', 'zenyo' ); ?></dt><dd><?php echo wp_kses_post( $zenyo_cart->get_cart_shipping_total() ); ?></dd></div>
			<?php endif; ?>
		</dl>
		<div class="zy-summary__grand">
			<span><?php esc_html_e( 'Total', 'zenyo' ); ?></span>
			<span class="zy-summary__amount"><?php echo wp_kses_post( wc_price( $zenyo_cart->get_total( 'edit' ) ) ); ?></span>
		</div>
		<a class="zy-summary__btn checkout-button" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Proceed to checkout', 'zenyo' ); ?></a>
	</aside>

</div>

<?php if ( $zenyo_recs ) : ?>
	<div class="zy-flow__rule" aria-hidden="true"></div>
	<section class="zy-recs" aria-label="<?php esc_attr_e( 'Things you may also be interested in', 'zenyo' ); ?>">
		<div class="zy-recs__head">
			<h2><?php esc_html_e( 'Things you may also be interested in', 'zenyo' ); ?></h2>
			<div class="zy-recs__dash" aria-hidden="true"></div>
		</div>
		<div class="zy-recs__grid">
			<?php
			foreach ( $zenyo_recs as $zenyo_rec ) :
				$zenyo_direct = $zenyo_rec->is_type( 'simple' ) && $zenyo_rec->is_purchasable() && $zenyo_rec->is_in_stock();
				$zenyo_desc   = trim( wp_strip_all_tags( $zenyo_rec->get_short_description() ) );
				?>
				<div class="zy-rec">
					<a class="zy-rec__media" href="<?php echo esc_url( $zenyo_rec->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
						<?php echo $zenyo_rec->get_image_id() ? wp_get_attachment_image( $zenyo_rec->get_image_id(), 'woocommerce_single', false, array( 'alt' => '' ) ) : ''; // phpcs:ignore ?>
					</a>
					<div class="zy-rec__body">
						<h3><a href="<?php echo esc_url( $zenyo_rec->get_permalink() ); ?>"><?php echo esc_html( $zenyo_rec->get_name() ); ?></a></h3>
						<?php if ( $zenyo_desc ) : ?>
							<p class="zy-rec__desc"><?php echo esc_html( $zenyo_desc ); ?></p>
						<?php endif; ?>
						<p class="zy-rec__price"><?php echo wp_kses_post( zenyo_price_label( $zenyo_rec ) ); ?></p>
						<?php if ( $zenyo_direct ) : ?>
							<button type="button" class="zy-rec__btn" data-zy-rec-add="<?php echo (int) $zenyo_rec->get_id(); ?>" data-zy-url="<?php echo esc_url( WC_AJAX::get_endpoint( 'add_to_cart' ) ); ?>"><?php esc_html_e( 'Add to cart', 'zenyo' ); ?></button>
						<?php else : ?>
							<a class="zy-rec__btn" href="<?php echo esc_url( $zenyo_rec->get_permalink() ); ?>"><?php esc_html_e( 'Select options', 'zenyo' ); ?></a>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</section>
<?php endif; ?>

<?php do_action( 'woocommerce_after_cart' ); ?>

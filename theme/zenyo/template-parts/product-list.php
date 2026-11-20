<?php
/**
 * Lista proizvoda: kategorija sa vise proizvoda, "View all" i pretraga.
 *
 * NAPOMENA: ova stranica nema svoj dizajn. Slozena je od elemenata sa ostalih stranica
 * (naslov sa crvenom crticom, slika, naziv, cena) i ceka Sretenovu doradu.
 */

if ( is_product_category() ) {
	$zenyo_heading = single_term_title( '', false );
} elseif ( is_search() ) {
	/* translators: %s: trazeni pojam */
	$zenyo_heading = sprintf( __( 'Search: %s', 'zenyo' ), get_search_query() );
} else {
	$zenyo_heading = __( 'All products', 'zenyo' );
}

get_header();
?>
<div class="zy-list">

	<h1 class="zy-title"><?php echo esc_html( $zenyo_heading ); ?></h1>

	<?php if ( have_posts() ) : ?>
		<ul class="zy-list__grid">
			<?php
			while ( have_posts() ) :
				the_post();
				$zenyo_product = wc_get_product( get_the_ID() );
				if ( ! $zenyo_product ) {
					continue;
				}
				$zenyo_price = zenyo_price_label( $zenyo_product );
				?>
				<li>
					<a class="zy-card" href="<?php the_permalink(); ?>">
						<span class="zy-card__media">
							<?php
							if ( has_post_thumbnail() ) {
								the_post_thumbnail( 'woocommerce_single', array( 'class' => 'zy-card__img', 'alt' => '' ) );
							}
							?>
						</span>
						<span class="zy-card__name"><?php the_title(); ?></span>
						<span class="zy-card__dash" aria-hidden="true"></span>
						<?php if ( ! $zenyo_product->is_in_stock() ) : ?>
							<span class="zy-card__price"><?php esc_html_e( 'Sold out', 'zenyo' ); ?></span>
						<?php elseif ( $zenyo_price ) : ?>
							<span class="zy-card__price"><?php echo wp_kses_post( $zenyo_price ); ?></span>
						<?php endif; ?>
					</a>
				</li>
			<?php endwhile; ?>
		</ul>
		<?php woocommerce_pagination(); ?>
	<?php else : ?>
		<p class="zy-list__empty"><?php esc_html_e( 'No products found.', 'zenyo' ); ?></p>
		<p><a class="zy-btn" href="<?php echo esc_url( zenyo_shop_url() ); ?>"><?php esc_html_e( 'Return to shop', 'zenyo' ); ?></a></p>
	<?php endif; ?>

</div>
<?php
get_footer();

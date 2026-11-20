<?php
/**
 * Obicna WordPress stranica.
 * Korpa, naplata i "Hvala" su takodje WordPress stranice, ali dobijaju siri raspored i svoj naslov;
 * njihov sadrzaj crtaju sabloni u woocommerce/cart/ i woocommerce/checkout/.
 */

$zenyo_is_flow = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() );

get_header();

while ( have_posts() ) {
	the_post();
	if ( $zenyo_is_flow ) {
		?>
		<div class="zy-flow">
			<div class="zy-flow__head">
				<h1><?php echo esc_html( zenyo_flow_title() ); ?></h1>
				<div class="zy-flow__dash" aria-hidden="true"></div>
			</div>
			<?php the_content(); ?>
		</div>
		<?php
	} else {
		?>
		<article class="zy-content">
			<h1 class="zy-title"><?php the_title(); ?></h1>
			<div class="zy-prose"><?php the_content(); ?></div>
		</article>
		<?php
	}
}

get_footer();

<?php
/**
 * Opsti sablon: koristi se za svaku stranicu koja jos nema svoj.
 */

get_header();

if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		?>
		<article class="zy-content">
			<h1 class="zy-title"><?php the_title(); ?></h1>
			<div class="zy-prose"><?php the_content(); ?></div>
		</article>
		<?php
	}
} else {
	?>
	<article class="zy-content">
		<h1 class="zy-title"><?php esc_html_e( 'Page not found', 'zenyo' ); ?></h1>
		<div class="zy-prose">
			<p><a href="<?php echo esc_url( zenyo_shop_url() ); ?>"><?php esc_html_e( 'Return to shop', 'zenyo' ); ?></a></p>
		</div>
	</article>
	<?php
}

get_footer();

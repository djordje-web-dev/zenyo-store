<?php
/**
 * FAQ (stranica sa adresom /faq/).
 *
 * Sve se uredjuje u adminu, na stranici "FAQ":
 *   - uvodna recenica = polje "Excerpt"
 *   - slika gore desno = "Featured image"
 *   - pitanja i odgovori = blokovi "Details" u tekstu stranice (naslov bloka je pitanje)
 */

$zenyo_quick = array(
	'shipping-returns' => __( 'Shipping & Returns', 'zenyo' ),
	'terms'            => __( 'Terms & Conditions', 'zenyo' ),
	'privacy-policy'   => __( 'Privacy Policy', 'zenyo' ),
	'contact'          => __( 'Contact', 'zenyo' ),
);

get_header();
the_post();
?>
<div class="zy-faq">

	<section class="zy-faq__intro" aria-label="<?php esc_attr_e( 'FAQ introduction', 'zenyo' ); ?>">
		<div class="zy-faq__lead">
			<h1><?php the_title(); ?></h1>
			<div class="zy-faq__dash" aria-hidden="true"></div>
			<?php if ( has_excerpt() ) : ?>
				<p><?php echo esc_html( get_the_excerpt() ); ?></p>
			<?php endif; ?>
		</div>
		<?php if ( has_post_thumbnail() ) : ?>
			<div class="zy-faq__hero"><?php the_post_thumbnail( 'full', array( 'alt' => '' ) ); ?></div>
		<?php endif; ?>
	</section>

	<div class="zy-faq__body">
		<section class="zy-faq__list" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'zenyo' ); ?>">
			<?php the_content(); ?>
		</section>

		<aside class="zy-faq__side">
			<?php get_template_part( 'template-parts/contact-box', null, array( 'button' => true ) ); ?>
			<nav class="zy-box" aria-labelledby="zy-box-links">
				<h2 id="zy-box-links"><?php esc_html_e( 'Quick links', 'zenyo' ); ?></h2>
				<div class="zy-box__dash" aria-hidden="true"></div>
				<ul class="zy-box__links">
					<?php foreach ( $zenyo_quick as $slug => $label ) : ?>
						<li><a href="<?php echo esc_url( zenyo_page_url( $slug ) ); ?>"><span><?php echo esc_html( $label ); ?></span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="square" aria-hidden="true"><path d="M3 12h17"></path><path d="M14 6l6 6-6 6"></path></svg></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</aside>
	</div>

</div>
<?php
get_footer();

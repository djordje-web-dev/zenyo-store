<?php
/**
 * Futer (Dark Zenyo). Isti je na svakoj stranici.
 * Linkovi vode na stranice po "slug"-u; dok stranica ne postoji (faza 9), link je "#".
 */

// Stranica bez okvira (pocetna sa videom): samo zatvaramo dokument.
if ( ! empty( $args['bare'] ) ) {
	wp_footer();
	echo '</body></html>';
	return;
}

$zenyo_help  = array(
	'faq'              => __( 'FAQ', 'zenyo' ),
	'shipping-returns' => __( 'Shipping & Returns', 'zenyo' ),
	'contact'          => __( 'Contact', 'zenyo' ),
);
$zenyo_legal = array(
	'terms'               => __( 'Terms & Conditions', 'zenyo' ),
	'privacy-policy'      => __( 'Privacy Policy', 'zenyo' ),
	'cookie-policy'       => __( 'Cookie Policy', 'zenyo' ),
	'company-information' => __( 'Company Information', 'zenyo' ),
);
$zenyo_email = zenyo_opt( 'email' );
$zenyo_phone = zenyo_opt( 'office_phone' );
$zenyo_sep   = '<span class="zy-footer__sep" aria-hidden="true">|</span>';
?>
</main>

</div><!-- .zy-light -->

<footer class="zy-footer">

	<div class="zy-footer__top">

		<div class="zy-footer__brand">
			<a class="zy-footer__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'ZENYO — home', 'zenyo' ); ?>"><?php zenyo_logo( '#ffffff', 148 ); ?></a>
			<p class="zy-footer__slogan">
				<span>Minimum Effort.</span>
				<span>Maximum Efficiency.</span>
			</p>
			<a class="zy-footer__about" href="<?php echo esc_url( zenyo_page_url( 'about' ) ); ?>">
				<span class="zy-footer__about-text"><?php esc_html_e( 'About ZENYO', 'zenyo' ); ?><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="square" aria-hidden="true"><path d="M3 12h17"></path><path d="M14 6l6 6-6 6"></path></svg></span>
				<span class="zy-footer__about-dash" aria-hidden="true"></span>
			</a>
		</div>

		<div class="zy-footer__cols">
			<nav class="zy-footer__col" aria-labelledby="zy-footer-help">
				<h2 id="zy-footer-help"><?php esc_html_e( 'Help', 'zenyo' ); ?></h2>
				<ul>
					<?php foreach ( $zenyo_help as $slug => $label ) : ?>
						<li><a href="<?php echo esc_url( zenyo_page_url( $slug ) ); ?>"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
			<nav class="zy-footer__col" aria-labelledby="zy-footer-legal">
				<h2 id="zy-footer-legal"><?php esc_html_e( 'Legal', 'zenyo' ); ?></h2>
				<ul>
					<?php foreach ( $zenyo_legal as $slug => $label ) : ?>
						<li><a href="<?php echo esc_url( zenyo_page_url( $slug ) ); ?>"><?php echo esc_html( $label ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>

	</div>

	<div class="zy-footer__bottom">
		<address>
			<span class="zy-footer__row">
				<span>© <?php echo esc_html( wp_date( 'Y' ) ); ?> ZENYO</span><?php echo $zenyo_sep; // phpcs:ignore ?>
				<span><?php echo esc_html( zenyo_opt( 'company_name' ) ); ?></span><?php echo $zenyo_sep; // phpcs:ignore ?>
				<span>PIB: <?php echo esc_html( zenyo_opt( 'company_pib' ) ); ?></span><?php echo $zenyo_sep; // phpcs:ignore ?>
				<span>MB: <?php echo esc_html( zenyo_opt( 'company_mb' ) ); ?></span>
			</span>
			<span class="zy-footer__row">
				<span><?php echo esc_html( zenyo_opt( 'address' ) ); ?></span><?php echo $zenyo_sep; // phpcs:ignore ?>
				<a href="<?php echo esc_attr( 'mailto:' . $zenyo_email ); ?>"><?php echo esc_html( $zenyo_email ); ?></a><?php echo $zenyo_sep; // phpcs:ignore ?>
				<a href="<?php echo esc_attr( zenyo_tel_href( $zenyo_phone ) ); ?>"><?php echo esc_html( $zenyo_phone ); ?></a>
			</span>
		</address>
		<div class="zy-footer__row">
			<a class="zy-footer__instagram" href="<?php echo esc_url( zenyo_opt( 'instagram_url' ) ); ?>" rel="noopener">
				<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"></rect><circle cx="12" cy="12" r="4"></circle><circle cx="17.3" cy="6.7" r="0.6" fill="currentColor"></circle></svg>Instagram
			</a><?php echo $zenyo_sep; // phpcs:ignore ?>
			<span><?php esc_html_e( 'Serbia', 'zenyo' ); ?></span><?php echo $zenyo_sep; // phpcs:ignore ?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>">zenyostore.com</a>
		</div>
	</div>

</footer>

<?php wp_footer(); ?>
</body>
</html>

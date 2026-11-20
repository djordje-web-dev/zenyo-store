<?php
/**
 * Kutija "Still have a question?" sa mejlom, telefonom i Instagramom (FAQ).
 * Podaci dolaze iz admina: Appearance -> Customize -> "ZENYO - kontakt i firma".
 *
 * Argumenti ($args): button - da li se prikazuje dugme "Contact us".
 */

$zenyo_email = zenyo_opt( 'email' );
$zenyo_phone = zenyo_opt( 'office_phone' );
?>
<section class="zy-box" aria-labelledby="zy-box-help">
	<h2 id="zy-box-help"><?php esc_html_e( 'Still have a question?', 'zenyo' ); ?></h2>
	<div class="zy-box__dash" aria-hidden="true"></div>
	<p><?php esc_html_e( "We're here to help.", 'zenyo' ); ?></p>
	<dl class="zy-box__contacts">
		<dt><?php esc_html_e( 'Email', 'zenyo' ); ?></dt>
		<dd><a href="<?php echo esc_attr( 'mailto:' . $zenyo_email ); ?>"><?php echo esc_html( $zenyo_email ); ?></a></dd>
		<dt><?php esc_html_e( 'Phone', 'zenyo' ); ?></dt>
		<dd><a href="<?php echo esc_attr( zenyo_tel_href( $zenyo_phone ) ); ?>"><?php echo esc_html( $zenyo_phone ); ?></a></dd>
		<dt>Instagram</dt>
		<dd><a href="<?php echo esc_url( zenyo_opt( 'instagram_url' ) ); ?>" rel="noopener"><?php echo esc_html( zenyo_instagram_handle() ); ?></a></dd>
	</dl>
	<?php if ( ! empty( $args['button'] ) ) : ?>
		<a class="zy-box__btn" href="<?php echo esc_url( zenyo_page_url( 'contact' ) ); ?>"><?php esc_html_e( 'Contact us', 'zenyo' ); ?></a>
	<?php endif; ?>
</section>

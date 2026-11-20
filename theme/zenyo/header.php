<?php
/**
 * Zaglavlje: levo putanja ili logo, desno slusalica i korpa.
 *
 * Sablon moze da izabere sta stoji levo:
 *   get_header();                                  // putanja "Shop / ..."
 *   get_header( null, array( 'left' => 'logo' ) ); // logo, a putanja u redu ispod (stranice kimona)
 *   get_header( null, array( 'left' => 'none' ) ); // nista (Shop Index)
 *
 * Jos dve mogucnosti:
 *   'screen' => true  // svetli deo je visok tacno jedan ekran (Shop Index, ZENYO)
 *   'bare'   => true  // bez zaglavlja i okvira; stranica sve crta sama (pocetna sa videom)
 */

$zenyo_left   = isset( $args['left'] ) ? $args['left'] : 'breadcrumb';
$zenyo_screen = ! empty( $args['screen'] );
$zenyo_bare   = ! empty( $args['bare'] );
$zenyo_phone = zenyo_opt( 'call_phone' );
$zenyo_count = zenyo_cart_count();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<?php
if ( $zenyo_bare ) {
	return;
}
?>
<a class="zy-skip" href="#main"><?php esc_html_e( 'Skip to content', 'zenyo' ); ?></a>

<div class="zy-light<?php echo $zenyo_screen ? ' zy-light--screen' : ''; ?>">

<header class="zy-header zy-header--<?php echo esc_attr( $zenyo_left ); ?>">
	<div class="zy-header__left">
		<?php if ( 'logo' === $zenyo_left ) : ?>
			<a class="zy-header__logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'ZENYO — home', 'zenyo' ); ?>"><?php zenyo_logo( '#0a0a0a', 170 ); ?></a>
		<?php else : ?>
			<?php // Na telefonu levo uvek stoji logo; putanja prelazi u red ispod (vidi base.css). ?>
			<a class="zy-header__logo zy-header__logo--mobile" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'ZENYO — home', 'zenyo' ); ?>"><?php zenyo_logo( '#0a0a0a', 0 ); ?></a>
			<?php if ( 'breadcrumb' === $zenyo_left ) : ?>
				<?php zenyo_breadcrumb(); ?>
			<?php endif; ?>
		<?php endif; ?>
	</div>

	<div class="zy-header__right">
		<?php zenyo_lang_switcher(); ?>
		<div class="zy-phone">
			<button type="button" class="zy-icon-btn" data-zy-toggle="zy-phone-panel" aria-expanded="false" aria-controls="zy-phone-panel" aria-label="<?php esc_attr_e( 'Phone — show number', 'zenyo' ); ?>">
				<svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
			</button>
			<div id="zy-phone-panel" class="zy-phone__panel" role="dialog" aria-label="<?php esc_attr_e( 'Call ZENYO', 'zenyo' ); ?>" hidden>
				<span class="zy-phone__label"><?php esc_html_e( 'Call us', 'zenyo' ); ?></span>
				<a class="zy-phone__number" href="<?php echo esc_attr( zenyo_tel_href( $zenyo_phone ) ); ?>"><?php echo esc_html( $zenyo_phone ); ?></a>
				<a class="zy-btn zy-btn--small" href="<?php echo esc_attr( zenyo_tel_href( $zenyo_phone ) ); ?>">
					<?php esc_html_e( 'Call', 'zenyo' ); ?>
					<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
				</a>
			</div>
		</div>

		<a class="zy-cart" href="<?php echo esc_url( function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' ) ); ?>"<?php echo ( function_exists( 'is_cart' ) && is_cart() ) ? ' aria-current="page"' : ''; ?>>
			<span class="zy-cart__text"><span class="zy-cart__word"><?php esc_html_e( 'Cart', 'zenyo' ); ?> </span>(<?php echo zenyo_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>)</span>
			<svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="square" stroke-linejoin="miter" aria-hidden="true"><path d="M4.5 8.5h15l-1 12h-13z"></path><path d="M8.5 8.5V7a3.5 3.5 0 0 1 7 0v1.5"></path></svg>
		</a>
	</div>
</header>

<?php if ( 'logo' === $zenyo_left ) : ?>
	<div class="zy-subhead"><?php zenyo_breadcrumb(); ?></div>
<?php endif; ?>

<main id="main" class="zy-main">

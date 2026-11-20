<?php
/**
 * Pocetna: video preko celog ekrana, ZENYO logo, jedna recenica i dugme Shop.
 * Video i recenica se menjaju u adminu: Appearance -> Customize -> "ZENYO - kontakt i firma".
 * Tamo su i dva neobavezna polja za telefon: uspravan video i uspravna slika.
 */

get_header( null, array( 'bare' => true ) );

$zenyo_cart_url     = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/' );
$zenyo_phone_video  = zenyo_hero_phone_video_url();
$zenyo_phone_poster = zenyo_hero_phone_poster_url();
?>
<main class="zy-hero">

	<?php if ( '' === $zenyo_phone_video && '' === $zenyo_phone_poster ) : ?>
		<video class="zy-hero__video" aria-hidden="true" autoplay muted loop playsinline preload="auto"
			poster="<?php echo esc_url( zenyo_hero_poster_url() ); ?>"
			src="<?php echo esc_url( zenyo_hero_video_url() ); ?>"></video>
	<?php else : ?>
		<?php
		// Postoji uspravan video ili slika za telefon. Video zato nema adresu u HTML-u, nego je
		// kratka skripta ispod upise prema ekranu. Tako telefon skida samo uspravan video,
		// a racunar samo obican; niko ne skida oba.
		?>
		<video class="zy-hero__video" id="zy-hero-video" aria-hidden="true" autoplay muted loop playsinline preload="auto"
			data-video="<?php echo esc_url( zenyo_hero_video_url() ); ?>"
			data-poster="<?php echo esc_url( zenyo_hero_poster_url() ); ?>"
			data-video-phone="<?php echo esc_url( $zenyo_phone_video ); ?>"
			data-poster-phone="<?php echo esc_url( $zenyo_phone_poster ); ?>"></video>
		<script>
		(function () {
			var v = document.getElementById('zy-hero-video');
			// "Telefon" = uspravan ekran do 1000 px sirine (telefon i uspravno okrenut tablet).
			var phone = window.matchMedia('(orientation: portrait) and (max-width: 1000px)').matches;
			var d = v.dataset;
			v.poster = (phone && d.posterPhone) || d.poster;
			v.src = (phone && d.videoPhone) || d.video;
		})();
		</script>
		<noscript><img class="zy-hero__video" src="<?php echo esc_url( zenyo_hero_poster_url() ); ?>" alt=""></noscript>
	<?php endif; ?>

	<div class="zy-hero__scrim" aria-hidden="true"></div>

	<header class="zy-hero__header">
		<?php zenyo_lang_switcher(); ?>
		<a class="zy-hero__cart" href="<?php echo esc_url( $zenyo_cart_url ); ?>" aria-label="<?php esc_attr_e( 'Cart', 'zenyo' ); ?>">
			<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" stroke-linejoin="miter" aria-hidden="true"><path d="M4.5 8.5h15l-1 12h-13z"></path><path d="M8.5 8.5V7a3.5 3.5 0 0 1 7 0v1.5"></path></svg>
		</a>
	</header>

	<div class="zy-hero__logo">
		<h1>
			<span class="screen-reader-text">ZENYO</span>
			<?php zenyo_logo( '#ffffff', 0 ); ?>
		</h1>
	</div>

	<div class="zy-hero__bottom">
		<p class="zy-hero__text"><?php echo esc_html( zenyo_opt( 'hero_text' ) ); ?></p>
		<a class="zy-hero__shop" href="<?php echo esc_url( zenyo_shop_url() ); ?>">
			<?php esc_html_e( 'Shop', 'zenyo' ); ?>
			<span class="zy-hero__shop-dash" aria-hidden="true"></span>
		</a>
	</div>

</main>
<?php
get_footer( null, array( 'bare' => true ) );

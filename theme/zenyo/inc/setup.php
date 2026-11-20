<?php
/**
 * Osnovna podesavanja teme i ucitavanje stilova i skripti.
 */

add_action(
	'after_setup_theme',
	function () {
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
		add_theme_support( 'woocommerce' );
		load_theme_textdomain( 'zenyo', ZENYO_DIR . '/languages' );
	}
);

// "Excerpt" (kratak opis) i na stranicama: FAQ ga koristi kao uvodnu recenicu.
add_action(
	'init',
	function () {
		add_post_type_support( 'page', 'excerpt' );
	}
);

/** Verzija fajla = vreme poslednje izmene, da pregledac ne drzi staru kopiju. */
function zenyo_asset_version( $relative_path ) {
	$file = ZENYO_DIR . $relative_path;
	return file_exists( $file ) ? (string) filemtime( $file ) : '1';
}

add_action(
	'wp_enqueue_scripts',
	function () {
		// Albert Sans: font za obican tekst. Privremeno sa Google-a; pred pustanje ga selimo na nas server.
		wp_enqueue_style(
			'zenyo-albert-sans',
			'https://fonts.googleapis.com/css2?family=Albert+Sans:wght@400;600&display=swap',
			array(),
			null
		);
		wp_enqueue_style( 'zenyo-base', ZENYO_URI . '/assets/css/base.css', array(), zenyo_asset_version( '/assets/css/base.css' ) );
		wp_enqueue_script(
			'zenyo-site',
			ZENYO_URI . '/assets/js/site.js',
			array(),
			zenyo_asset_version( '/assets/js/site.js' ),
			array( 'in_footer' => true, 'strategy' => 'defer' )
		);

		// Stilovi i skripte pojedinih stranica ucitavaju se samo tamo gde trebaju.
		if ( is_front_page() ) {
			wp_enqueue_style( 'zenyo-hero', ZENYO_URI . '/assets/css/hero.css', array( 'zenyo-base' ), zenyo_asset_version( '/assets/css/hero.css' ) );
		}
		// Zajednicka funkcija za dodavanje u korpu; ucitava se samo uz skripte koje je traze.
		wp_register_script(
			'zenyo-cart-api',
			ZENYO_URI . '/assets/js/cart-api.js',
			array(),
			zenyo_asset_version( '/assets/js/cart-api.js' ),
			array( 'in_footer' => true, 'strategy' => 'defer' )
		);
		if ( is_page() && ! ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) ) {
			wp_enqueue_style( 'zenyo-pages', ZENYO_URI . '/assets/css/pages.css', array( 'zenyo-base' ), zenyo_asset_version( '/assets/css/pages.css' ) );
		}
		if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
			wp_enqueue_style( 'zenyo-flow', ZENYO_URI . '/assets/css/flow.css', array( 'zenyo-base' ), zenyo_asset_version( '/assets/css/flow.css' ) );
			wp_enqueue_script(
				'zenyo-flow',
				ZENYO_URI . '/assets/js/flow.js',
				array( 'jquery', 'zenyo-cart-api' ),
				zenyo_asset_version( '/assets/js/flow.js' ),
				array( 'in_footer' => true )
			);
		}
		if ( 'gallery' === zenyo_current_category_layout() ) {
			wp_enqueue_style( 'zenyo-gallery', ZENYO_URI . '/assets/css/gallery.css', array( 'zenyo-base' ), zenyo_asset_version( '/assets/css/gallery.css' ) );
			wp_enqueue_script(
				'zenyo-gallery',
				ZENYO_URI . '/assets/js/gallery.js',
				array( 'zenyo-cart-api' ),
				zenyo_asset_version( '/assets/js/gallery.js' ),
				array( 'in_footer' => true, 'strategy' => 'defer' )
			);
		}
		if ( function_exists( 'is_product' ) && ( is_product() || 'stack' === zenyo_current_category_layout() ) ) {
			wp_enqueue_style( 'zenyo-product', ZENYO_URI . '/assets/css/product.css', array( 'zenyo-base' ), zenyo_asset_version( '/assets/css/product.css' ) );
			wp_enqueue_script(
				'zenyo-product',
				ZENYO_URI . '/assets/js/product.js',
				array( 'zenyo-cart-api' ),
				zenyo_asset_version( '/assets/js/product.js' ),
				array( 'in_footer' => true, 'strategy' => 'defer' )
			);
		}
		if ( function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() ) ) {
			wp_enqueue_style( 'zenyo-shop', ZENYO_URI . '/assets/css/shop.css', array( 'zenyo-base' ), zenyo_asset_version( '/assets/css/shop.css' ) );
			if ( is_shop() && ! zenyo_is_view_all() ) {
				wp_enqueue_script(
					'zenyo-shop-index',
					ZENYO_URI . '/assets/js/shop-index.js',
					array(),
					zenyo_asset_version( '/assets/js/shop-index.js' ),
					array( 'in_footer' => true, 'strategy' => 'defer' )
				);
			}
		}
	}
);

// Brze povezivanje sa Google Fonts serverima.
add_filter(
	'wp_resource_hints',
	function ( $urls, $relation ) {
		if ( 'preconnect' === $relation ) {
			$urls[] = 'https://fonts.googleapis.com';
			$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' );
		}
		return $urls;
	},
	10,
	2
);

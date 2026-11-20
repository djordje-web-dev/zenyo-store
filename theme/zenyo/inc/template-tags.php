<?php
/**
 * Male funkcije za ispis delova stranice.
 */

/**
 * ZENYO logo: slova u fontu Vito Wide i crvena crtica ispod N.
 * Privremeno je slozen iz fonta, kao u dizajnu. Kad stigne originalni SVG logo, menja se samo ova funkcija.
 *
 * @param string $fill  Boja slova.
 * @param int    $width Sirina u pikselima; 0 = sirinu odredjuje CSS.
 */
function zenyo_logo( $fill = '#0a0a0a', $width = 170 ) {
	printf(
		'<svg class="zy-logo" aria-hidden="true" viewBox="354 575 544 109"' . ( $width ? ' style="width:%2$dpx"' : '' ) . '><g transform="translate(0 639) scale(1 0.862)"><text y="0" font-family="\'Vito Wide\', system-ui, sans-serif" font-weight="500" font-size="110" fill="%1$s"><tspan x="347.6">Z</tspan><tspan x="463.4">E</tspan><tspan x="573.8">N</tspan><tspan x="699.2">Y</tspan><tspan x="801.9">O</tspan></text></g><rect x="579" y="674" width="95" height="10" fill="#d71920"></rect></svg>',
		esc_attr( $fill ),
		(int) $width
	);
}

/** Adresa stranice "Shop" (izbor kategorija). */
function zenyo_shop_url() {
	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

/** Adresa stranice po njenom "slug"-u, ili "#" dok stranica ne postoji (faza 9). */
function zenyo_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	return $page ? get_permalink( $page ) : '#';
}

/**
 * Stavke putanje "Shop / ... / trenutna stranica".
 * Vraca niz parova [ naziv, adresa ]; poslednja stavka nema adresu.
 */
function zenyo_breadcrumb_items() {
	$items = array( array( __( 'Shop', 'zenyo' ), zenyo_shop_url() ) );

	$add_category_chain = function ( $term, $link_last ) use ( &$items ) {
		$chain   = array_reverse( get_ancestors( $term->term_id, 'product_cat' ) );
		$chain[] = $term->term_id;
		$last    = end( $chain );
		foreach ( $chain as $term_id ) {
			$t       = get_term( $term_id, 'product_cat' );
			$is_last = ( $term_id === $last );
			$items[] = array( $t->name, ( $is_last && ! $link_last ) ? '' : get_term_link( $t ) );
		}
	};

	if ( function_exists( 'is_shop' ) && is_shop() ) {
		if ( function_exists( 'zenyo_is_view_all' ) && zenyo_is_view_all() ) {
			$items[] = array( __( 'All products', 'zenyo' ), '' );
		} else {
			$items[0][1] = '';
		}
	} elseif ( function_exists( 'is_product_category' ) && is_product_category() ) {
		$add_category_chain( get_queried_object(), false );
	} elseif ( function_exists( 'is_product' ) && is_product() ) {
		$terms = get_the_terms( get_the_ID(), 'product_cat' );
		$title = get_the_title();
		if ( $terms && ! is_wp_error( $terms ) ) {
			// Uzimamo najdublju kategoriju (npr. Art, a ne ZENYO).
			usort(
				$terms,
				function ( $a, $b ) {
					return count( get_ancestors( $b->term_id, 'product_cat' ) ) - count( get_ancestors( $a->term_id, 'product_cat' ) );
				}
			);
			$same_name = ( 0 === strcasecmp( $terms[0]->name, $title ) );
			// Kad se kategorija i proizvod zovu isto (kimona), ne ponavljamo naziv.
			$add_category_chain( $terms[0], ! $same_name );
			if ( ! $same_name ) {
				$items[] = array( $title, '' );
			}
		} else {
			$items[] = array( $title, '' );
		}
	} elseif ( is_singular() ) {
		$items[] = array( get_the_title(), '' );
	} elseif ( is_404() ) {
		$items[] = array( __( 'Page not found', 'zenyo' ), '' );
	}

	return $items;
}

function zenyo_breadcrumb() {
	$items = zenyo_breadcrumb_items();
	$last  = count( $items ) - 1;
	echo '<nav class="zy-crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'zenyo' ) . '"><ol>';
	foreach ( $items as $i => $item ) {
		if ( $i > 0 ) {
			echo '<li class="zy-crumbs__sep" aria-hidden="true">/</li>';
		}
		if ( $i === $last || '' === $item[1] ) {
			printf( '<li class="zy-crumbs__current"%s>%s</li>', $i === $last ? ' aria-current="page"' : '', esc_html( $item[0] ) );
		} else {
			printf( '<li><a href="%s">%s</a></li>', esc_url( $item[1] ), esc_html( $item[0] ) );
		}
	}
	echo '</ol></nav>';
}

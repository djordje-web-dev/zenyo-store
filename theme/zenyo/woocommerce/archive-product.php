<?php
/**
 * Sve stranice koje prikazuju vise proizvoda ili kategorija. Bira jedan od pet izgleda:
 *
 *   /shop/                      -> Shop Index: glavne kategorije i velika slika
 *   kategorija sa izgledom "stack" -> svi proizvodi se kupuju sa te stranice (Belts)
 *   kategorija sa izgledom "gallery" -> slike gore, kupovina dole (Art, Jewellery, Accessories)
 *   kategorija sa potkategorijama -> plocice potkategorija (ZENYO: Art / Jewellery / Accessories)
 *   sve ostalo                  -> lista proizvoda (kategorija sa vise proizvoda, "View all", pretraga)
 *
 * Kategorija sa tacno jednim proizvodom ne stize dovde: inc/categories.php je salje pravo na proizvod.
 */

defined( 'ABSPATH' ) || exit;

if ( is_shop() && ! zenyo_is_view_all() && ! is_search() ) {
	get_template_part( 'template-parts/shop-index' );
} elseif ( 'stack' === zenyo_current_category_layout() ) {
	get_template_part( 'template-parts/category-stack' );
} elseif ( 'gallery' === zenyo_current_category_layout() ) {
	get_template_part( 'template-parts/category-gallery' );
} elseif ( is_product_category() && zenyo_child_categories( get_queried_object_id() ) ) {
	get_template_part( 'template-parts/category-tiles' );
} else {
	get_template_part( 'template-parts/product-list' );
}

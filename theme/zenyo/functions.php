<?php
/**
 * ZENYO tema.
 *
 * Ovaj fajl samo ucitava delove iz foldera inc/. Svaki deo radi jednu stvar:
 *   lang.php          - dva jezika: srpski i engleski (/en/)
 *   lang-admin.php    - polja za srpski prevod u adminu
 *   setup.php         - sta tema podrzava, koje CSS/JS fajlove ucitava
 *   settings.php      - kontakt i podaci firme koji se menjaju iz admina
 *   template-tags.php - male funkcije za ispis (logo, putanja "Shop / ...")
 *   cart.php          - brojac korpe u zaglavlju
 *   woocommerce.php   - sitna prilagodjavanja prodavnice (npr. oznaka "RSD", ispis cene)
 *   categories.php    - kategorije: slika, kadriranje, kuda vodi klik
 *   product.php       - stranica proizvoda: dodatna polja u adminu, podaci za skriptu
 *   checkout.php      - korpa, naplata i potvrda: polja, provere, pomocne funkcije
 */

define( 'ZENYO_DIR', get_template_directory() );
define( 'ZENYO_URI', get_template_directory_uri() );

require ZENYO_DIR . '/inc/lang.php';
require ZENYO_DIR . '/inc/lang-admin.php';
require ZENYO_DIR . '/inc/setup.php';
require ZENYO_DIR . '/inc/settings.php';
require ZENYO_DIR . '/inc/template-tags.php';
require ZENYO_DIR . '/inc/cart.php';
require ZENYO_DIR . '/inc/woocommerce.php';
require ZENYO_DIR . '/inc/categories.php';
require ZENYO_DIR . '/inc/product.php';
require ZENYO_DIR . '/inc/checkout.php';

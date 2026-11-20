<?php
/**
 * Pomocne funkcije za pisanje teksta stranica u obliku koji editor u adminu ume da uredjuje (blokovi).
 * Koriste ih pages.php (engleski) i pages-sr.php (srpski).
 */

function zp_h2( $text ) {
	return "<!-- wp:heading -->\n<h2 class=\"wp-block-heading\">{$text}</h2>\n<!-- /wp:heading -->\n\n";
}
function zp_p( $html, $class = '' ) {
	$attr = $class ? " {\"className\":\"{$class}\"}" : '';
	$cls  = $class ? " class=\"{$class}\"" : '';
	return "<!-- wp:paragraph{$attr} -->\n<p{$cls}>{$html}</p>\n<!-- /wp:paragraph -->\n\n";
}
function zp_ul( $items ) {
	$out = "<!-- wp:list -->\n<ul class=\"wp-block-list\">";
	foreach ( $items as $item ) {
		$out .= "<!-- wp:list-item -->\n<li>{$item}</li>\n<!-- /wp:list-item -->\n\n";
	}
	return $out . "</ul>\n<!-- /wp:list -->\n\n";
}
function zp_html( $html ) {
	return "<!-- wp:html -->\n{$html}\n<!-- /wp:html -->\n\n";
}
/** Pitanje i odgovor: blok "Details" (naslov = pitanje). */
function zp_qa( $question, $answer_html, $open = false ) {
	$attr = $open ? ' {"showContent":true}' : '';
	$flag = $open ? ' open' : '';
	return "<!-- wp:details{$attr} -->\n<details class=\"wp-block-details\"{$flag}><summary>{$question}</summary>" . trim( zp_p( $answer_html ) ) . "</details>\n<!-- /wp:details -->\n\n";
}

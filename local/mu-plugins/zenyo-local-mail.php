<?php
/**
 * Plugin Name: ZENYO - lokalni mejlovi
 * Description: Samo za lokalni rad. Sve mejlove salje u Mailpit (http://localhost:8091), nista ne ide napolje.
 *
 * "mu-plugin" je dodatak koji WordPress ucitava sam, bez ukljucivanja u adminu.
 * Ovaj folder se ne postavlja na pravi sajt; tamo se slanje mejlova podesava posebno (faza 14).
 */

add_action(
	'phpmailer_init',
	function ( $phpmailer ) {
		$phpmailer->isSMTP();
		$phpmailer->Host     = 'mail';
		$phpmailer->Port     = 1025;
		$phpmailer->SMTPAuth = false;
		$phpmailer->SMTPAutoTLS = false;
	}
);

// Na lokalu WordPress kao posiljaoca stavlja "wordpress@localhost", sto nije ispravna adresa.
add_filter(
	'wp_mail_from',
	function ( $from ) {
		return false !== strpos( $from, '@localhost' ) ? 'shop@zenyo.test' : $from;
	}
);

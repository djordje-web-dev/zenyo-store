<?php
/**
 * Zajednicka funkcija za skripte: ubacuje sliku iz seed/images u biblioteku medija.
 */

require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';

if ( ! defined( 'ZENYO_SEED_DIR' ) ) {
	define( 'ZENYO_SEED_DIR', '/seed/images/' );
}

/** Ubacuje sliku jednom (pamti je po nazivu fajla). Vraca ID ili 0. */
function zenyo_image( $file ) {
	if ( ! $file ) {
		return 0;
	}
	$existing = get_posts(
		array(
			'post_type'   => 'attachment',
			'meta_key'    => '_zenyo_seed',
			'meta_value'  => $file,
			'numberposts' => 1,
			'fields'      => 'ids',
		)
	);
	if ( $existing ) {
		return (int) $existing[0];
	}
	$path = ZENYO_SEED_DIR . $file;
	if ( ! file_exists( $path ) ) {
		WP_CLI::warning( 'Nema slike: ' . $file );
		return 0;
	}
	$upload = wp_upload_bits( 'zenyo-' . $file, null, file_get_contents( $path ) );
	if ( ! empty( $upload['error'] ) ) {
		WP_CLI::warning( $file . ': ' . $upload['error'] );
		return 0;
	}
	$id = wp_insert_attachment(
		array(
			'post_mime_type' => wp_check_filetype( $upload['file'] )['type'],
			'post_title'     => pathinfo( $file, PATHINFO_FILENAME ),
			'post_status'    => 'inherit',
		),
		$upload['file']
	);
	wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );
	update_post_meta( $id, '_zenyo_seed', $file );
	return (int) $id;
}

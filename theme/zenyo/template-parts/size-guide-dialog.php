<?php
/**
 * Bocni panel sa tabelom velicina. Otvara ga svako dugme sa data-zy-open="ID panela".
 *
 * Argumenti ($args): id, title, content (HTML).
 */

$zenyo_dialog_id = isset( $args['id'] ) ? $args['id'] : 'zy-guide';
?>
<dialog class="zy-dialog zy-dialog--side" id="<?php echo esc_attr( $zenyo_dialog_id ); ?>" aria-labelledby="<?php echo esc_attr( $zenyo_dialog_id ); ?>-title">
	<div class="zy-dialog__head">
		<div>
			<h2 id="<?php echo esc_attr( $zenyo_dialog_id ); ?>-title"><?php echo esc_html( $args['title'] ); ?></h2>
			<div class="zy-dialog__dash" aria-hidden="true"></div>
		</div>
		<button type="button" class="zy-dialog__close" data-zy-close aria-label="<?php esc_attr_e( 'Close size guide', 'zenyo' ); ?>">
			<svg width="16" height="16" viewBox="0 0 18 18" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M2 2L16 16M16 2L2 16"></path></svg>
		</button>
	</div>
	<div class="zy-dialog__body"><?php echo wp_kses_post( $args['content'] ); ?></div>
</dialog>

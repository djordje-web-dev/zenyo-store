<?php
/**
 * Kategorija koja ima potkategorije: naslov levo, velike plocice sa slikama desno.
 * Dizajnirano za ZENYO (Art / Jewellery / Accessories), ali radi za svaku takvu kategoriju.
 * Sa tri potkategorije prva je visoka, a druge dve su jedna iznad druge, kao u dizajnu.
 */

$zenyo_term  = get_queried_object();
$zenyo_tiles = zenyo_child_categories( $zenyo_term->term_id );
// Kategorija "ZENYO" umesto obicnog naslova dobija logo.
$zenyo_is_brand = ( 0 === strcasecmp( $zenyo_term->name, 'ZENYO' ) );

get_header( null, array( 'screen' => true ) );
?>
<div class="zy-tiles">

	<div class="zy-tiles__intro">
		<h1<?php echo $zenyo_is_brand ? ' class="zy-tiles__brand"' : ' class="zy-title"'; ?>>
			<?php if ( $zenyo_is_brand ) : ?>
				<span class="screen-reader-text">ZENYO</span>
				<?php zenyo_logo( '#0a0a0a', 190 ); ?>
			<?php else : ?>
				<?php echo esc_html( $zenyo_term->name ); ?>
			<?php endif; ?>
		</h1>
		<?php if ( $zenyo_term->description ) : ?>
			<p class="zy-tiles__desc"><?php echo esc_html( $zenyo_term->description ); ?></p>
		<?php endif; ?>
	</div>

	<nav class="zy-tiles__grid zy-tiles__grid--<?php echo (int) count( $zenyo_tiles ); ?>" aria-label="<?php echo esc_attr( $zenyo_term->name ); ?>">
		<?php
		foreach ( $zenyo_tiles as $tile ) :
			$zenyo_img = zenyo_category_image_url( $tile->term_id );
			?>
			<a class="zy-tile zy-tile--<?php echo esc_attr( zenyo_category_label_tone( $tile->term_id ) ); ?>" href="<?php echo esc_url( zenyo_category_url( $tile ) ); ?>">
				<?php if ( $zenyo_img ) : ?>
					<img class="zy-tile__img" src="<?php echo esc_url( $zenyo_img ); ?>" alt="" style="object-position: <?php echo esc_attr( zenyo_category_focus( $tile->term_id ) ); ?>;">
				<?php endif; ?>
				<span class="zy-tile__label">
					<span class="zy-tile__name"><?php echo esc_html( $tile->name ); ?></span>
					<span class="zy-tile__dash" aria-hidden="true"></span>
					<svg class="zy-tile__arrow" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square" aria-hidden="true"><path d="M3 12h17"></path><path d="M14 6l6 6-6 6"></path></svg>
				</span>
			</a>
		<?php endforeach; ?>
	</nav>

</div>
<?php
get_footer();

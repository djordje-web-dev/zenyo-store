<?php
/**
 * Shop Index: spisak glavnih kategorija levo, velika slika desno.
 * Prelazak misem (ili Tab) preko naziva menja sliku; klik na naziv ili na sliku vodi u kategoriju.
 * Kategorije, njihov redosled, slike i kadriranje dolaze iz admina (Products -> Categories).
 */

$zenyo_cats = zenyo_top_categories();

get_header( null, array( 'left' => 'none', 'screen' => true ) );
?>
<div class="zy-shopx">

	<div class="zy-shopx__left">
		<div class="zy-shopx__head">
			<h1><?php esc_html_e( 'Shop', 'zenyo' ); ?></h1>
			<div class="zy-shopx__dash" aria-hidden="true"></div>
		</div>

		<nav class="zy-shopx__nav" aria-label="<?php esc_attr_e( 'Categories', 'zenyo' ); ?>">
			<ul>
				<?php foreach ( $zenyo_cats as $i => $cat ) : ?>
					<li>
						<a class="zy-shopx__row<?php echo 0 === $i ? ' is-active' : ''; ?>" href="<?php echo esc_url( zenyo_category_url( $cat ) ); ?>" data-zy-index="<?php echo (int) $i; ?>"<?php echo 0 === $i ? ' aria-current="true"' : ''; ?>>
							<span class="zy-shopx__num"><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></span>
							<span class="zy-shopx__label">
								<span class="zy-shopx__name"><?php echo esc_html( $cat->name ); ?></span>
								<span class="zy-shopx__row-dash" aria-hidden="true"></span>
							</span>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
			<a class="zy-shopx__all" href="<?php echo esc_url( zenyo_view_all_url() ); ?>">
				<?php esc_html_e( 'View all', 'zenyo' ); ?>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="square" aria-hidden="true"><path d="M3 12h17"></path><path d="M14 6l6 6-6 6"></path></svg>
			</a>
		</nav>
	</div>

	<?php if ( $zenyo_cats ) : ?>
		<a class="zy-shopx__panel" href="<?php echo esc_url( zenyo_category_url( $zenyo_cats[0] ) ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: naziv kategorije */ __( 'Shop %s', 'zenyo' ), $zenyo_cats[0]->name ) ); ?>">
			<?php
			foreach ( $zenyo_cats as $i => $cat ) :
				$zenyo_img   = zenyo_category_image_url( $cat->term_id );
				$zenyo_focus = zenyo_category_focus( $cat->term_id );
				?>
				<div class="zy-shopx__layer<?php echo 0 === $i ? ' is-active is-in' : ''; ?>" aria-hidden="true" data-zy-index="<?php echo (int) $i; ?>" data-zy-label="<?php echo esc_attr( sprintf( __( 'Shop %s', 'zenyo' ), $cat->name ) ); ?>">
					<?php if ( $zenyo_img ) : ?>
						<?php for ( $s = 0; $s < 3; $s++ ) : // Tri trake iste slike: svaka se otkriva sa malim zakasnjenjem. ?>
							<div class="zy-shopx__strip zy-shopx__strip--<?php echo (int) $s; ?>"><img src="<?php echo esc_url( $zenyo_img ); ?>" alt="" style="object-position: <?php echo esc_attr( $zenyo_focus ); ?>;"<?php echo 0 === $i ? '' : ' loading="lazy"'; ?>></div>
						<?php endfor; ?>
					<?php else : ?>
						<div class="zy-shopx__empty"><?php echo esc_html( $cat->name ); ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
			<span class="zy-shopx__go" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"><path d="M3 12h17"></path><path d="M14 6l6 6-6 6"></path></svg></span>
		</a>
	<?php endif; ?>

</div>
<?php
get_footer();

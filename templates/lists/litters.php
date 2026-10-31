<?php
/**
 * Litters, grouped into born and expected litters.
 *
 * Override: {theme}/rudelwerk/lists/litters.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $sections      Key => array{title: string, posts: WP_Post[]}.
 *     @type bool  $show_headings Whether to print the section headings.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<?php foreach ( $args['sections'] as $rw_key => $rw_section ) : ?>
	<section class="rudelwerk-litters__section rudelwerk-litters__section--<?php echo esc_attr( $rw_key ); ?>" id="rudelwerk-litters-<?php echo esc_attr( $rw_key ); ?>">
		<?php if ( $args['show_headings'] ) : ?>
			<h2><?php echo esc_html( $rw_section['title'] ); ?></h2>
		<?php endif; ?>

		<?php if ( $rw_section['posts'] ) : ?>
			<div class="rudelwerk-cards">
				<?php foreach ( $rw_section['posts'] as $rw_post ) : ?>
					<?php rudelwerk_template( 'parts/litter-card.php', array( 'post' => $rw_post ) ); ?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="rudelwerk-empty"><?php esc_html_e( 'There are no litters at the moment.', 'rudelwerk' ); ?></p>
		<?php endif; ?>
	</section>
<?php endforeach; ?>

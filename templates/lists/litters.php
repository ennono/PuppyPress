<?php
/**
 * Litters, grouped into born and expected litters.
 *
 * Override: {theme}/puppypress/lists/litters.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $sections      Key => array{title: string, posts: WP_Post[]}.
 *     @type bool  $show_headings Whether to print the section headings.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<?php foreach ( $args['sections'] as $pp_key => $pp_section ) : ?>
	<section class="puppypress-litters__section puppypress-litters__section--<?php echo esc_attr( $pp_key ); ?>" id="puppypress-litters-<?php echo esc_attr( $pp_key ); ?>">
		<?php if ( $args['show_headings'] ) : ?>
			<h2><?php echo esc_html( $pp_section['title'] ); ?></h2>
		<?php endif; ?>

		<?php if ( $pp_section['posts'] ) : ?>
			<div class="puppypress-cards">
				<?php foreach ( $pp_section['posts'] as $pp_post ) : ?>
					<?php puppypress_template( 'parts/litter-card.php', array( 'post' => $pp_post ) ); ?>
				<?php endforeach; ?>
			</div>
		<?php else : ?>
			<p class="puppypress-empty"><?php esc_html_e( 'There are no litters at the moment.', 'puppypress' ); ?></p>
		<?php endif; ?>
	</section>
<?php endforeach; ?>

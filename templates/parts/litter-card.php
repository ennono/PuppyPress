<?php
/**
 * A litter in a list.
 *
 * Override: {theme}/puppypress/parts/litter-card.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type WP_Post $post Litter.
 * }
 */

use PuppyPress\Data\Dogs;

defined( 'ABSPATH' ) || exit;

$pp_post     = $args['post'];
$pp_id       = $pp_post->ID;
$pp_expected = Dogs::is_expected_litter( $pp_id );
$pp_counts   = Dogs::litter_counts( $pp_id );
$pp_location = trim( get_post_meta( $pp_id, 'zip', true ) . ' ' . get_post_meta( $pp_id, 'city', true ) );
$pp_born     = (string) get_post_meta( $pp_id, 'date_of_birth', true );
$pp_mother   = (string) get_post_meta( $pp_id, 'mother_name', true );
$pp_father   = (string) get_post_meta( $pp_id, 'father_name', true );
$pp_to_give  = $pp_expected ? '' : Dogs::format_sexes( $pp_counts['available_male'], $pp_counts['available_female'] );
?>
<article class="puppypress-card puppypress-card--litter<?php echo $pp_expected ? ' is-expected' : ''; ?>">
	<?php if ( has_post_thumbnail( $pp_post ) ) : ?>
		<a class="puppypress-card__image" href="<?php echo esc_url( get_permalink( $pp_post ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php echo get_the_post_thumbnail( $pp_post, 'medium_large' ); ?>
		</a>
	<?php endif; ?>

	<div class="puppypress-card__body">
		<h3 class="puppypress-card__title"><a href="<?php echo esc_url( get_permalink( $pp_post ) ); ?>"><?php echo esc_html( Dogs::litter_title( $pp_id, $pp_post->post_title ) ); ?></a></h3>

		<div class="puppypress-card__meta">
			<?php if ( '' !== $pp_location ) : ?>
				<p><?php echo esc_html( $pp_location ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $pp_born ) : ?>
				<p>
					<?php
					/* translators: %s: date of birth */
					echo esc_html( sprintf( $pp_expected ? __( 'Expected: %s', 'puppypress' ) : __( 'Date born: %s', 'puppypress' ), $pp_born ) );
					?>
				</p>
			<?php endif; ?>

			<?php if ( '' !== $pp_to_give ) : ?>
				<p>
					<?php
					/* translators: %s: e.g. "2 males, 1 female" */
					echo esc_html( sprintf( __( 'Still available: %s', 'puppypress' ), $pp_to_give ) );
					?>
				</p>
			<?php endif; ?>

			<?php if ( '' !== $pp_mother || '' !== $pp_father ) : ?>
				<p>
					<?php if ( '' !== $pp_mother ) : ?>
						<?php
						/* translators: %s: name of the mother */
						echo esc_html( sprintf( __( 'Mother: %s', 'puppypress' ), $pp_mother ) );
						?>
						<br>
					<?php endif; ?>
					<?php if ( '' !== $pp_father ) : ?>
						<?php
						/* translators: %s: name of the father */
						echo esc_html( sprintf( __( 'Father: %s', 'puppypress' ), $pp_father ) );
						?>
					<?php endif; ?>
				</p>
			<?php endif; ?>
		</div>

		<a class="puppypress-card__more" href="<?php echo esc_url( get_permalink( $pp_post ) ); ?>"><?php esc_html_e( 'Show puppies', 'puppypress' ); ?></a>
	</div>
</article>

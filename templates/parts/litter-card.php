<?php
/**
 * A litter in a list.
 *
 * Override: {theme}/rudelwerk/parts/litter-card.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type WP_Post $post Litter.
 * }
 */

use Rudelwerk\Data\Dogs;

defined( 'ABSPATH' ) || exit;

$rw_post     = $args['post'];
$rw_id       = $rw_post->ID;
$rw_expected = Dogs::is_expected_litter( $rw_id );
$rw_counts   = Dogs::litter_counts( $rw_id );
$rw_location = trim( get_post_meta( $rw_id, 'zip', true ) . ' ' . get_post_meta( $rw_id, 'city', true ) );
$rw_born     = (string) get_post_meta( $rw_id, 'date_of_birth', true );
$rw_mother   = (string) get_post_meta( $rw_id, 'mother_name', true );
$rw_father   = (string) get_post_meta( $rw_id, 'father_name', true );
$rw_to_give  = $rw_expected ? '' : Dogs::format_sexes( $rw_counts['available_male'], $rw_counts['available_female'] );
?>
<article class="rudelwerk-card rudelwerk-card--litter<?php echo $rw_expected ? ' is-expected' : ''; ?>">
	<?php if ( has_post_thumbnail( $rw_post ) ) : ?>
		<a class="rudelwerk-card__image" href="<?php echo esc_url( get_permalink( $rw_post ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php echo get_the_post_thumbnail( $rw_post, 'medium_large' ); ?>
		</a>
	<?php endif; ?>

	<div class="rudelwerk-card__body">
		<h3 class="rudelwerk-card__title"><a href="<?php echo esc_url( get_permalink( $rw_post ) ); ?>"><?php echo esc_html( Dogs::litter_title( $rw_id, $rw_post->post_title ) ); ?></a></h3>

		<div class="rudelwerk-card__meta">
			<?php if ( '' !== $rw_location ) : ?>
				<p><?php echo esc_html( $rw_location ); ?></p>
			<?php endif; ?>

			<?php if ( '' !== $rw_born ) : ?>
				<p>
					<?php
					/* translators: %s: date of birth */
					echo esc_html( sprintf( $rw_expected ? __( 'Expected: %s', 'rudelwerk' ) : __( 'Date born: %s', 'rudelwerk' ), $rw_born ) );
					?>
				</p>
			<?php endif; ?>

			<?php if ( '' !== $rw_to_give ) : ?>
				<p>
					<?php
					/* translators: %s: e.g. "2 males, 1 female" */
					echo esc_html( sprintf( __( 'Still available: %s', 'rudelwerk' ), $rw_to_give ) );
					?>
				</p>
			<?php endif; ?>

			<?php if ( '' !== $rw_mother || '' !== $rw_father ) : ?>
				<p>
					<?php if ( '' !== $rw_mother ) : ?>
						<?php
						/* translators: %s: name of the mother */
						echo esc_html( sprintf( __( 'Mother: %s', 'rudelwerk' ), $rw_mother ) );
						?>
						<br>
					<?php endif; ?>
					<?php if ( '' !== $rw_father ) : ?>
						<?php
						/* translators: %s: name of the father */
						echo esc_html( sprintf( __( 'Father: %s', 'rudelwerk' ), $rw_father ) );
						?>
					<?php endif; ?>
				</p>
			<?php endif; ?>
		</div>

		<a class="rudelwerk-card__more" href="<?php echo esc_url( get_permalink( $rw_post ) ); ?>"><?php esc_html_e( 'Show puppies', 'rudelwerk' ); ?></a>
	</div>
</article>

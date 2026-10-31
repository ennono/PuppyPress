<?php
/**
 * Detail view of a litter (wraps the post content).
 *
 * Override: {theme}/rudelwerk/single/litter.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type WP_Post $post    Litter.
 *     @type string  $content Filtered post content.
 * }
 */

use Rudelwerk\Data\Contact;
use Rudelwerk\Data\Dogs;

defined( 'ABSPATH' ) || exit;

$rw_post     = $args['post'];
$rw_id       = $rw_post->ID;
$rw_expected = Dogs::is_expected_litter( $rw_id );
$rw_counts   = Dogs::litter_counts( $rw_id );
$rw_kennel   = Dogs::display_kennel( $rw_id );
$rw_born     = (string) get_post_meta( $rw_id, 'date_of_birth', true );
$rw_breeders = get_the_terms( $rw_id, 'breeder' );
$rw_breeders = ( $rw_breeders && ! is_wp_error( $rw_breeders ) ) ? array_map( array( Contact::class, 'for_term' ), $rw_breeders ) : array();

$rw_litter_size = Dogs::format_sexes( $rw_counts['male'], $rw_counts['female'] );
$rw_to_give     = Dogs::format_sexes( $rw_counts['available_male'], $rw_counts['available_female'] );

$rw_parents = array(
	'father' => array(
		'label'    => __( 'Father', 'rudelwerk' ),
		'name'     => (string) get_post_meta( $rw_id, 'father_name', true ),
		'desc'     => (string) get_post_meta( $rw_id, 'father_desc', true ),
		'photos'   => Dogs::attachment_ids( $rw_id, 'father_photo' ),
		'stud_dog' => absint( get_post_meta( $rw_id, 'father_stud_dog_id', true ) ),
	),
	'mother' => array(
		'label'    => __( 'Mother', 'rudelwerk' ),
		'name'     => (string) get_post_meta( $rw_id, 'mother_name', true ),
		'desc'     => (string) get_post_meta( $rw_id, 'mother_desc', true ),
		'photos'   => Dogs::attachment_ids( $rw_id, 'mother_photo' ),
		'stud_dog' => 0,
	),
);
?>
<div class="rudelwerk rudelwerk-single rudelwerk-single--litter<?php echo $rw_expected ? ' is-expected' : ''; ?>">

	<div class="rudelwerk-summary">
		<?php if ( $rw_kennel && '' !== $rw_born ) : ?>
			<p>
				<?php
				if ( $rw_expected ) {
					/* translators: 1: kennel name, 2: expected date of birth */
					$rw_sentence = __( 'The kennel %1$s expects these puppies to be born %2$s.', 'rudelwerk' );
					printf( esc_html( $rw_sentence ), '<strong>' . esc_html( $rw_kennel->name ) . '</strong>', '<strong>' . esc_html( $rw_born ) . '</strong>' );
				} else {
					/* translators: 1: kennel name, 2: date of birth */
					$rw_sentence = __( 'The puppies of the kennel %1$s were born on %2$s.', 'rudelwerk' );
					printf( esc_html( $rw_sentence ), '<strong>' . esc_html( $rw_kennel->name ) . '</strong>', '<strong>' . esc_html( $rw_born ) . '</strong>' );
				}
				?>
			</p>
		<?php endif; ?>

		<?php if ( ! $rw_expected && ( '' !== $rw_litter_size || '' !== $rw_to_give ) ) : ?>
			<p class="rudelwerk-litter-numbers">
				<?php if ( '' !== $rw_litter_size ) : ?>
					<strong><?php esc_html_e( 'Litter size', 'rudelwerk' ); ?>:</strong> <?php echo esc_html( $rw_litter_size ); ?><br>
				<?php endif; ?>
				<?php if ( '' !== $rw_to_give ) : ?>
					<strong><?php esc_html_e( 'Still available', 'rudelwerk' ); ?>:</strong> <?php echo esc_html( $rw_to_give ); ?><br>
				<?php endif; ?>
				<?php if ( '' !== $rw_counts['last_update'] ) : ?>
					<small>
						<?php
						/* translators: %s: date of the last update */
						echo esc_html( sprintf( __( 'Last update: %s', 'rudelwerk' ), $rw_counts['last_update'] ) );
						?>
					</small>
				<?php endif; ?>
			</p>
		<?php endif; ?>
	</div>

	<?php if ( $rw_kennel || $rw_breeders ) : ?>
		<div class="rudelwerk-columns">
			<?php if ( $rw_kennel ) : ?>
				<div>
					<h2><?php esc_html_e( 'Kennel', 'rudelwerk' ); ?></h2>
					<?php
					rudelwerk_template(
						'parts/contact.php',
						array(
							'contact' => array(
								'name'    => $rw_kennel->name,
								'address' => '',
								'phone'   => '',
								'mobile'  => '',
								'fax'     => '',
								'email'   => '',
								'website' => (string) get_term_meta( $rw_kennel->term_id, 'kennel_website', true ),
							),
						)
					);
					?>
				</div>
			<?php endif; ?>

			<?php if ( $rw_breeders ) : ?>
				<div>
					<h2><?php echo esc_html( _n( 'Breeder', 'Breeders', count( $rw_breeders ), 'rudelwerk' ) ); ?></h2>
					<?php foreach ( $rw_breeders as $rw_contact ) : ?>
						<?php rudelwerk_template( 'parts/contact.php', array( 'contact' => $rw_contact ) ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( '' !== trim( $args['content'] ) ) : ?>
		<h2><?php esc_html_e( 'About the puppies', 'rudelwerk' ); ?></h2>
		<div class="rudelwerk-content">
			<?php echo $args['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered post content. ?>
		</div>
	<?php endif; ?>

	<?php foreach ( $rw_parents as $rw_key => $rw_parent ) : ?>
		<?php if ( '' === $rw_parent['name'] && '' === $rw_parent['desc'] && ! $rw_parent['photos'] ) : ?>
			<?php continue; ?>
		<?php endif; ?>
		<section class="rudelwerk-parent rudelwerk-parent--<?php echo esc_attr( $rw_key ); ?>">
			<h3><?php echo esc_html( trim( $rw_parent['label'] . ': ' . $rw_parent['name'], ': ' ) ); ?></h3>

			<?php if ( $rw_parent['stud_dog'] && 'publish' === get_post_status( $rw_parent['stud_dog'] ) ) : ?>
				<p>
					<a href="<?php echo esc_url( get_permalink( $rw_parent['stud_dog'] ) ); ?>">
						<?php
						/* translators: %s: name of the stud dog */
						echo esc_html( sprintf( __( 'More about the stud dog %s', 'rudelwerk' ), Dogs::stud_dog_name( $rw_parent['stud_dog'] ) ) );
						?>
					</a>
				</p>
			<?php endif; ?>

			<?php if ( '' !== $rw_parent['desc'] ) : ?>
				<?php echo wp_kses_post( wpautop( esc_html( $rw_parent['desc'] ) ) ); ?>
			<?php endif; ?>

			<?php if ( $rw_parent['photos'] ) : ?>
				<div class="rudelwerk-gallery">
					<?php foreach ( $rw_parent['photos'] as $rw_photo ) : ?>
						<?php $rw_full = wp_get_attachment_image_url( $rw_photo, 'large' ); ?>
						<?php if ( $rw_full ) : ?>
							<a href="<?php echo esc_url( $rw_full ); ?>"><?php echo wp_get_attachment_image( $rw_photo, 'medium' ); ?></a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>
</div>

<?php
/**
 * Detail view of a litter (wraps the post content).
 *
 * Override: {theme}/puppypress/single/litter.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type WP_Post $post    Litter.
 *     @type string  $content Filtered post content.
 * }
 */

use PuppyPress\Data\Contact;
use PuppyPress\Data\Dogs;

defined( 'ABSPATH' ) || exit;

$pp_post     = $args['post'];
$pp_id       = $pp_post->ID;
$pp_expected = Dogs::is_expected_litter( $pp_id );
$pp_counts   = Dogs::litter_counts( $pp_id );
$pp_kennel   = Dogs::display_kennel( $pp_id );
$pp_born     = (string) get_post_meta( $pp_id, 'date_of_birth', true );
$pp_breeders = get_the_terms( $pp_id, 'breeder' );
$pp_breeders = ( $pp_breeders && ! is_wp_error( $pp_breeders ) ) ? array_map( array( Contact::class, 'for_term' ), $pp_breeders ) : array();

$pp_litter_size = Dogs::format_sexes( $pp_counts['male'], $pp_counts['female'] );
$pp_to_give     = Dogs::format_sexes( $pp_counts['available_male'], $pp_counts['available_female'] );

$pp_parents = array(
	'father' => array(
		'label'    => __( 'Father', 'puppypress' ),
		'name'     => (string) get_post_meta( $pp_id, 'father_name', true ),
		'desc'     => (string) get_post_meta( $pp_id, 'father_desc', true ),
		'photos'   => Dogs::attachment_ids( $pp_id, 'father_photo' ),
		'stud_dog' => absint( get_post_meta( $pp_id, 'father_stud_dog_id', true ) ),
	),
	'mother' => array(
		'label'    => __( 'Mother', 'puppypress' ),
		'name'     => (string) get_post_meta( $pp_id, 'mother_name', true ),
		'desc'     => (string) get_post_meta( $pp_id, 'mother_desc', true ),
		'photos'   => Dogs::attachment_ids( $pp_id, 'mother_photo' ),
		'stud_dog' => 0,
	),
);
?>
<div class="puppypress puppypress-single puppypress-single--litter<?php echo $pp_expected ? ' is-expected' : ''; ?>">

	<div class="puppypress-summary">
		<?php if ( $pp_kennel && '' !== $pp_born ) : ?>
			<p>
				<?php
				if ( $pp_expected ) {
					/* translators: 1: kennel name, 2: expected date of birth */
					$pp_sentence = __( 'The kennel %1$s expects these puppies to be born %2$s.', 'puppypress' );
					printf( esc_html( $pp_sentence ), '<strong>' . esc_html( $pp_kennel->name ) . '</strong>', '<strong>' . esc_html( $pp_born ) . '</strong>' );
				} else {
					/* translators: 1: kennel name, 2: date of birth */
					$pp_sentence = __( 'The puppies of the kennel %1$s were born on %2$s.', 'puppypress' );
					printf( esc_html( $pp_sentence ), '<strong>' . esc_html( $pp_kennel->name ) . '</strong>', '<strong>' . esc_html( $pp_born ) . '</strong>' );
				}
				?>
			</p>
		<?php endif; ?>

		<?php if ( ! $pp_expected && ( '' !== $pp_litter_size || '' !== $pp_to_give ) ) : ?>
			<p class="puppypress-litter-numbers">
				<?php if ( '' !== $pp_litter_size ) : ?>
					<strong><?php esc_html_e( 'Litter size', 'puppypress' ); ?>:</strong> <?php echo esc_html( $pp_litter_size ); ?><br>
				<?php endif; ?>
				<?php if ( '' !== $pp_to_give ) : ?>
					<strong><?php esc_html_e( 'Still available', 'puppypress' ); ?>:</strong> <?php echo esc_html( $pp_to_give ); ?><br>
				<?php endif; ?>
				<?php if ( '' !== $pp_counts['last_update'] ) : ?>
					<small>
						<?php
						/* translators: %s: date of the last update */
						echo esc_html( sprintf( __( 'Last update: %s', 'puppypress' ), $pp_counts['last_update'] ) );
						?>
					</small>
				<?php endif; ?>
			</p>
		<?php endif; ?>
	</div>

	<?php if ( $pp_kennel || $pp_breeders ) : ?>
		<div class="puppypress-columns">
			<?php if ( $pp_kennel ) : ?>
				<div>
					<h2><?php esc_html_e( 'Kennel', 'puppypress' ); ?></h2>
					<?php
					puppypress_template(
						'parts/contact.php',
						array(
							'contact' => array(
								'name'    => $pp_kennel->name,
								'address' => '',
								'phone'   => '',
								'mobile'  => '',
								'fax'     => '',
								'email'   => '',
								'website' => (string) get_term_meta( $pp_kennel->term_id, 'kennel_website', true ),
							),
						)
					);
					?>
				</div>
			<?php endif; ?>

			<?php if ( $pp_breeders ) : ?>
				<div>
					<h2><?php echo esc_html( _n( 'Breeder', 'Breeders', count( $pp_breeders ), 'puppypress' ) ); ?></h2>
					<?php foreach ( $pp_breeders as $pp_contact ) : ?>
						<?php puppypress_template( 'parts/contact.php', array( 'contact' => $pp_contact ) ); ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( '' !== trim( $args['content'] ) ) : ?>
		<h2><?php esc_html_e( 'About the puppies', 'puppypress' ); ?></h2>
		<div class="puppypress-content">
			<?php echo $args['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered post content. ?>
		</div>
	<?php endif; ?>

	<?php foreach ( $pp_parents as $pp_key => $pp_parent ) : ?>
		<?php if ( '' === $pp_parent['name'] && '' === $pp_parent['desc'] && ! $pp_parent['photos'] ) : ?>
			<?php continue; ?>
		<?php endif; ?>
		<section class="puppypress-parent puppypress-parent--<?php echo esc_attr( $pp_key ); ?>">
			<h3><?php echo esc_html( trim( $pp_parent['label'] . ': ' . $pp_parent['name'], ': ' ) ); ?></h3>

			<?php if ( $pp_parent['stud_dog'] && 'publish' === get_post_status( $pp_parent['stud_dog'] ) ) : ?>
				<p>
					<a href="<?php echo esc_url( get_permalink( $pp_parent['stud_dog'] ) ); ?>">
						<?php
						/* translators: %s: name of the stud dog */
						echo esc_html( sprintf( __( 'More about the stud dog %s', 'puppypress' ), Dogs::stud_dog_name( $pp_parent['stud_dog'] ) ) );
						?>
					</a>
				</p>
			<?php endif; ?>

			<?php if ( '' !== $pp_parent['desc'] ) : ?>
				<?php echo wp_kses_post( wpautop( esc_html( $pp_parent['desc'] ) ) ); ?>
			<?php endif; ?>

			<?php if ( $pp_parent['photos'] ) : ?>
				<div class="puppypress-gallery">
					<?php foreach ( $pp_parent['photos'] as $pp_photo ) : ?>
						<?php $pp_full = wp_get_attachment_image_url( $pp_photo, 'large' ); ?>
						<?php if ( $pp_full ) : ?>
							<a href="<?php echo esc_url( $pp_full ); ?>"><?php echo wp_get_attachment_image( $pp_photo, 'medium' ); ?></a>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</section>
	<?php endforeach; ?>
</div>

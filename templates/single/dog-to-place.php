<?php
/**
 * Detail view of a dog to place (wraps the post content).
 *
 * Override: {theme}/rudelwerk/single/dog-to-place.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type WP_Post $post    Dog to place.
 *     @type string  $content Filtered post content.
 * }
 */

use Rudelwerk\Data\Dogs;

defined( 'ABSPATH' ) || exit;

$rw_post   = $args['post'];
$rw_gender = Dogs::gender_label( (string) get_post_meta( $rw_post->ID, 'gender', true ) );
$rw_born   = (string) get_post_meta( $rw_post->ID, 'date_of_birth', true );

$rw_meta = array();
if ( '' !== $rw_gender ) {
	$rw_meta[] = $rw_gender;
}
if ( '' !== $rw_born ) {
	/* translators: %s: date of birth */
	$rw_meta[] = sprintf( __( 'born %s', 'rudelwerk' ), $rw_born );
}
?>
<div class="rudelwerk rudelwerk-single rudelwerk-single--dog-to-place">
	<?php if ( $rw_meta ) : ?>
		<p class="rudelwerk-summary"><?php echo esc_html( implode( ', ', $rw_meta ) ); ?></p>
	<?php endif; ?>

	<?php if ( '' !== trim( $args['content'] ) ) : ?>
		<h2>
			<?php
			/* translators: %s: name of the dog */
			echo esc_html( sprintf( __( 'About %s', 'rudelwerk' ), $rw_post->post_title ) );
			?>
		</h2>
		<div class="rudelwerk-content">
			<?php echo $args['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered post content. ?>
		</div>
	<?php endif; ?>
</div>

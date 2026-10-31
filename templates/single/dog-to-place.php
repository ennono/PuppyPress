<?php
/**
 * Detail view of a dog to place (wraps the post content).
 *
 * Override: {theme}/puppypress/single/dog-to-place.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type WP_Post $post    Dog to place.
 *     @type string  $content Filtered post content.
 * }
 */

use PuppyPress\Data\Dogs;

defined( 'ABSPATH' ) || exit;

$pp_post   = $args['post'];
$pp_gender = Dogs::gender_label( (string) get_post_meta( $pp_post->ID, 'gender', true ) );
$pp_born   = (string) get_post_meta( $pp_post->ID, 'date_of_birth', true );

$pp_meta = array();
if ( '' !== $pp_gender ) {
	$pp_meta[] = $pp_gender;
}
if ( '' !== $pp_born ) {
	/* translators: %s: date of birth */
	$pp_meta[] = sprintf( __( 'born %s', 'puppypress' ), $pp_born );
}
?>
<div class="puppypress puppypress-single puppypress-single--dog-to-place">
	<?php if ( $pp_meta ) : ?>
		<p class="puppypress-summary"><?php echo esc_html( implode( ', ', $pp_meta ) ); ?></p>
	<?php endif; ?>

	<?php if ( '' !== trim( $args['content'] ) ) : ?>
		<h2>
			<?php
			/* translators: %s: name of the dog */
			echo esc_html( sprintf( __( 'About %s', 'puppypress' ), $pp_post->post_title ) );
			?>
		</h2>
		<div class="puppypress-content">
			<?php echo $args['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered post content. ?>
		</div>
	<?php endif; ?>
</div>

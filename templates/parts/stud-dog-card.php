<?php
/**
 * A stud dog in a list.
 *
 * Override: {theme}/puppypress/parts/stud-dog-card.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type WP_Post      $post   Stud dog.
 *     @type WP_Term|null $kennel Kennel shown with the name (default: visible kennel).
 * }
 */

use PuppyPress\Data\Dogs;

defined( 'ABSPATH' ) || exit;

$pp_post    = $args['post'];
$pp_name    = Dogs::stud_dog_name( $pp_post->ID, $pp_post->post_title, isset( $args['kennel'] ) ? $args['kennel'] : null );
$pp_born    = Dogs::format_date( (string) get_post_meta( $pp_post->ID, 'date_born', true ) );
$pp_withers = (string) get_post_meta( $pp_post->ID, 'withers', true );

$pp_meta = array();
if ( '' !== $pp_born ) {
	/* translators: %s: date of birth */
	$pp_meta[] = sprintf( __( 'Date born: %s', 'puppypress' ), $pp_born );
}
if ( '' !== $pp_withers ) {
	/* translators: %s: withers height in cm */
	$pp_meta[] = sprintf( __( 'Withers: %s cm', 'puppypress' ), Dogs::format_withers( $pp_withers ) );
}
?>
<article class="puppypress-card puppypress-card--stud-dog">
	<?php if ( has_post_thumbnail( $pp_post ) ) : ?>
		<a class="puppypress-card__image" href="<?php echo esc_url( get_permalink( $pp_post ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php echo get_the_post_thumbnail( $pp_post, 'medium_large' ); ?>
		</a>
	<?php endif; ?>

	<div class="puppypress-card__body">
		<h3 class="puppypress-card__title"><a href="<?php echo esc_url( get_permalink( $pp_post ) ); ?>"><?php echo esc_html( $pp_name ); ?></a></h3>

		<?php if ( $pp_meta ) : ?>
			<p class="puppypress-card__meta"><?php echo esc_html( implode( ', ', $pp_meta ) ); ?></p>
		<?php endif; ?>

		<a class="puppypress-card__more" href="<?php echo esc_url( get_permalink( $pp_post ) ); ?>"><?php esc_html_e( 'Show dog', 'puppypress' ); ?></a>
	</div>
</article>

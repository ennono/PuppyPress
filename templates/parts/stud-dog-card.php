<?php
/**
 * A stud dog in a list.
 *
 * Override: {theme}/rudelwerk/parts/stud-dog-card.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type WP_Post      $post   Stud dog.
 *     @type WP_Term|null $kennel Kennel shown with the name (default: visible kennel).
 * }
 */

use Rudelwerk\Data\Dogs;

defined( 'ABSPATH' ) || exit;

$rw_post    = $args['post'];
$rw_name    = Dogs::stud_dog_name( $rw_post->ID, $rw_post->post_title, isset( $args['kennel'] ) ? $args['kennel'] : null );
$rw_born    = Dogs::format_date( (string) get_post_meta( $rw_post->ID, 'date_born', true ) );
$rw_withers = (string) get_post_meta( $rw_post->ID, 'withers', true );

$rw_meta = array();
if ( '' !== $rw_born ) {
	/* translators: %s: date of birth */
	$rw_meta[] = sprintf( __( 'Date born: %s', 'rudelwerk' ), $rw_born );
}
if ( '' !== $rw_withers ) {
	/* translators: %s: withers height in cm */
	$rw_meta[] = sprintf( __( 'Withers: %s cm', 'rudelwerk' ), Dogs::format_withers( $rw_withers ) );
}
?>
<article class="rudelwerk-card rudelwerk-card--stud-dog">
	<?php if ( has_post_thumbnail( $rw_post ) ) : ?>
		<a class="rudelwerk-card__image" href="<?php echo esc_url( get_permalink( $rw_post ) ); ?>" tabindex="-1" aria-hidden="true">
			<?php echo get_the_post_thumbnail( $rw_post, 'medium_large' ); ?>
		</a>
	<?php endif; ?>

	<div class="rudelwerk-card__body">
		<h3 class="rudelwerk-card__title"><a href="<?php echo esc_url( get_permalink( $rw_post ) ); ?>"><?php echo esc_html( $rw_name ); ?></a></h3>

		<?php if ( $rw_meta ) : ?>
			<p class="rudelwerk-card__meta"><?php echo esc_html( implode( ', ', $rw_meta ) ); ?></p>
		<?php endif; ?>

		<a class="rudelwerk-card__more" href="<?php echo esc_url( get_permalink( $rw_post ) ); ?>"><?php esc_html_e( 'Show dog', 'rudelwerk' ); ?></a>
	</div>
</article>

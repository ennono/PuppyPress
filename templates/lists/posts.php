<?php
/**
 * Alphabetical list of posts with their kennel.
 *
 * Override: {theme}/rudelwerk/lists/posts.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type WP_Post[] $posts Posts.
 * }
 */

use Rudelwerk\Data\Dogs;

defined( 'ABSPATH' ) || exit;
?>
<?php if ( $args['posts'] ) : ?>
	<ul class="rudelwerk-posts__list">
		<?php foreach ( $args['posts'] as $rw_post ) : ?>
			<?php $rw_kennel = Dogs::display_kennel( $rw_post->ID ); ?>
			<li>
				<a href="<?php echo esc_url( get_permalink( $rw_post ) ); ?>"><?php echo esc_html( trim( $rw_post->post_title . ' ' . ( $rw_kennel ? $rw_kennel->name : '' ) ) ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<p class="rudelwerk-empty"><?php esc_html_e( 'No entries found.', 'rudelwerk' ); ?></p>
<?php endif; ?>

<?php
/**
 * Alphabetical list of posts with their kennel.
 *
 * Override: {theme}/puppypress/lists/posts.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type WP_Post[] $posts Posts.
 * }
 */

use PuppyPress\Data\Dogs;

defined( 'ABSPATH' ) || exit;
?>
<?php if ( $args['posts'] ) : ?>
	<ul class="puppypress-posts__list">
		<?php foreach ( $args['posts'] as $pp_post ) : ?>
			<?php $pp_kennel = Dogs::display_kennel( $pp_post->ID ); ?>
			<li>
				<a href="<?php echo esc_url( get_permalink( $pp_post ) ); ?>"><?php echo esc_html( trim( $pp_post->post_title . ' ' . ( $pp_kennel ? $pp_kennel->name : '' ) ) ); ?></a>
			</li>
		<?php endforeach; ?>
	</ul>
<?php else : ?>
	<p class="puppypress-empty"><?php esc_html_e( 'No entries found.', 'puppypress' ); ?></p>
<?php endif; ?>

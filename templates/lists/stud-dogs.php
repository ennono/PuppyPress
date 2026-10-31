<?php
/**
 * Stud dogs, optionally grouped by kennel.
 *
 * Override: {theme}/puppypress/lists/stud-dogs.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $groups List of array{kennel: WP_Term|null, posts: WP_Post[]}.
 * }
 */

defined( 'ABSPATH' ) || exit;

$pp_found = false;
?>
<div class="puppypress-cards">
	<?php foreach ( $args['groups'] as $pp_group ) : ?>
		<?php foreach ( $pp_group['posts'] as $pp_post ) : ?>
			<?php $pp_found = true; ?>
			<?php
			puppypress_template(
				'parts/stud-dog-card.php',
				array(
					'post'   => $pp_post,
					'kennel' => $pp_group['kennel'],
				)
			);
			?>
		<?php endforeach; ?>
	<?php endforeach; ?>
</div>

<?php if ( ! $pp_found ) : ?>
	<p class="puppypress-empty"><?php esc_html_e( 'No stud dogs found.', 'puppypress' ); ?></p>
<?php endif; ?>

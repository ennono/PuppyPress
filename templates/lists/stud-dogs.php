<?php
/**
 * Stud dogs, optionally grouped by kennel.
 *
 * Override: {theme}/rudelwerk/lists/stud-dogs.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $groups List of array{kennel: WP_Term|null, posts: WP_Post[]}.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rw_found = false;
?>
<div class="rudelwerk-cards">
	<?php foreach ( $args['groups'] as $rw_group ) : ?>
		<?php foreach ( $rw_group['posts'] as $rw_post ) : ?>
			<?php $rw_found = true; ?>
			<?php
			rudelwerk_template(
				'parts/stud-dog-card.php',
				array(
					'post'   => $rw_post,
					'kennel' => $rw_group['kennel'],
				)
			);
			?>
		<?php endforeach; ?>
	<?php endforeach; ?>
</div>

<?php if ( ! $rw_found ) : ?>
	<p class="rudelwerk-empty"><?php esc_html_e( 'No stud dogs found.', 'rudelwerk' ); ?></p>
<?php endif; ?>

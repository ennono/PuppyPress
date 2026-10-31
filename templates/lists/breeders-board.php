<?php
/**
 * Breeders' board: kennels by ZIP code with their breeders.
 *
 * Override: {theme}/puppypress/lists/breeders-board.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $rows List of array{kennel: WP_Term, zip: string, breeders: array[]}.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( $args['rows'] ) : ?>
	<div class="puppypress-table-scroll">
		<table class="puppypress-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'ZIP code', 'puppypress' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Kennel', 'puppypress' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Breeders', 'puppypress' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $args['rows'] as $pp_row ) : ?>
					<tr>
						<td><?php echo esc_html( '' !== $pp_row['zip'] ? $pp_row['zip'] : __( 'Unknown', 'puppypress' ) ); ?></td>
						<td><strong><?php echo esc_html( $pp_row['kennel']->name ); ?></strong></td>
						<td>
							<?php foreach ( $pp_row['breeders'] as $pp_contact ) : ?>
								<?php puppypress_template( 'parts/contact.php', array( 'contact' => $pp_contact ) ); ?>
							<?php endforeach; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php else : ?>
	<p class="puppypress-empty"><?php esc_html_e( 'No kennels found.', 'puppypress' ); ?></p>
<?php endif; ?>

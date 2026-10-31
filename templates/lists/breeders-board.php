<?php
/**
 * Breeders' board: kennels by ZIP code with their breeders.
 *
 * Override: {theme}/rudelwerk/lists/breeders-board.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $rows List of array{kennel: WP_Term, zip: string, breeders: array[]}.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( $args['rows'] ) : ?>
	<div class="rudelwerk-table-scroll">
		<table class="rudelwerk-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'ZIP code', 'rudelwerk' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Kennel', 'rudelwerk' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Breeders', 'rudelwerk' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $args['rows'] as $rw_row ) : ?>
					<tr>
						<td><?php echo esc_html( '' !== $rw_row['zip'] ? $rw_row['zip'] : __( 'Unknown', 'rudelwerk' ) ); ?></td>
						<td><strong><?php echo esc_html( $rw_row['kennel']->name ); ?></strong></td>
						<td>
							<?php foreach ( $rw_row['breeders'] as $rw_contact ) : ?>
								<?php rudelwerk_template( 'parts/contact.php', array( 'contact' => $rw_contact ) ); ?>
							<?php endforeach; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php else : ?>
	<p class="rudelwerk-empty"><?php esc_html_e( 'No kennels found.', 'rudelwerk' ); ?></p>
<?php endif; ?>

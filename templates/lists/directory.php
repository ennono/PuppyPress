<?php
/**
 * Contact directory (trimming proofs, dog sitters) grouped by ZIP region.
 *
 * Override: {theme}/rudelwerk/lists/directory.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array  $regions List of array{region: WP_Term, rows: array[]}; each row has zip, city, post, contact.
 *     @type string $empty   Message if nothing was found.
 * }
 */

use Rudelwerk\Frontend\Icons;

defined( 'ABSPATH' ) || exit;
?>
<?php if ( ! $args['regions'] ) : ?>
	<p class="rudelwerk-empty"><?php echo esc_html( $args['empty'] ); ?></p>
<?php endif; ?>

<?php foreach ( $args['regions'] as $rw_region ) : ?>
	<h2><?php echo esc_html( $rw_region['region']->name ); ?></h2>
	<div class="rudelwerk-table-scroll">
		<table class="rudelwerk-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'ZIP code', 'rudelwerk' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Location', 'rudelwerk' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Name', 'rudelwerk' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Phone', 'rudelwerk' ); ?></th>
					<th scope="col" class="rudelwerk-table__wide"><?php esc_html_e( 'Details', 'rudelwerk' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rw_region['rows'] as $rw_row ) : ?>
					<?php
					$rw_contact = $rw_row['contact'];
					$rw_email   = is_email( $rw_contact['email'] ) ? antispambot( $rw_contact['email'] ) : '';
					?>
					<tr>
						<td><?php echo esc_html( $rw_row['zip'] ); ?></td>
						<td><?php echo esc_html( $rw_row['city'] ); ?></td>
						<td>
							<?php if ( $rw_email ) : ?>
								<a href="<?php echo esc_url( 'mailto:' . $rw_email ); ?>"><?php echo esc_html( $rw_contact['name'] ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $rw_contact['name'] ); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php foreach ( array( 'phone', 'mobile' ) as $rw_key ) : ?>
								<?php if ( '' !== $rw_contact[ $rw_key ] ) : ?>
									<a class="rudelwerk-nowrap" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $rw_contact[ $rw_key ] ) ); ?>"><?php echo esc_html( $rw_contact[ $rw_key ] ); ?></a><br>
								<?php endif; ?>
							<?php endforeach; ?>
						</td>
						<td>
							<?php if ( '' !== trim( $rw_row['post']->post_content ) ) : ?>
								<?php echo wp_kses_post( wpautop( $rw_row['post']->post_content ) ); ?>
							<?php endif; ?>

							<?php if ( '' !== $rw_contact['address'] ) : ?>
								<p><?php echo nl2br( esc_html( $rw_contact['address'] ) ); ?></p>
							<?php endif; ?>

							<?php if ( $rw_email || '' !== $rw_contact['website'] ) : ?>
								<p class="rudelwerk-icons">
									<?php if ( $rw_email ) : ?>
										<a href="<?php echo esc_url( 'mailto:' . $rw_email ); ?>" aria-label="<?php esc_attr_e( 'Email', 'rudelwerk' ); ?>"><?php echo Icons::svg( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
									<?php endif; ?>
									<?php if ( '' !== $rw_contact['website'] ) : ?>
										<a href="<?php echo esc_url( $rw_contact['website'] ); ?>" target="_blank" rel="nofollow noopener" aria-label="<?php esc_attr_e( 'Website', 'rudelwerk' ); ?>"><?php echo Icons::svg( 'website' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
									<?php endif; ?>
								</p>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endforeach; ?>

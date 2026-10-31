<?php
/**
 * Contact directory (trimming proofs, dog sitters) grouped by ZIP region.
 *
 * Override: {theme}/puppypress/lists/directory.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array  $regions List of array{region: WP_Term, rows: array[]}; each row has zip, city, post, contact.
 *     @type string $empty   Message if nothing was found.
 * }
 */

use PuppyPress\Frontend\Icons;

defined( 'ABSPATH' ) || exit;
?>
<?php if ( ! $args['regions'] ) : ?>
	<p class="puppypress-empty"><?php echo esc_html( $args['empty'] ); ?></p>
<?php endif; ?>

<?php foreach ( $args['regions'] as $pp_region ) : ?>
	<h2><?php echo esc_html( $pp_region['region']->name ); ?></h2>
	<div class="puppypress-table-scroll">
		<table class="puppypress-table">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'ZIP code', 'puppypress' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Location', 'puppypress' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Name', 'puppypress' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Phone', 'puppypress' ); ?></th>
					<th scope="col" class="puppypress-table__wide"><?php esc_html_e( 'Details', 'puppypress' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $pp_region['rows'] as $pp_row ) : ?>
					<?php
					$pp_contact = $pp_row['contact'];
					$pp_email   = is_email( $pp_contact['email'] ) ? antispambot( $pp_contact['email'] ) : '';
					?>
					<tr>
						<td><?php echo esc_html( $pp_row['zip'] ); ?></td>
						<td><?php echo esc_html( $pp_row['city'] ); ?></td>
						<td>
							<?php if ( $pp_email ) : ?>
								<a href="<?php echo esc_url( 'mailto:' . $pp_email ); ?>"><?php echo esc_html( $pp_contact['name'] ); ?></a>
							<?php else : ?>
								<?php echo esc_html( $pp_contact['name'] ); ?>
							<?php endif; ?>
						</td>
						<td>
							<?php foreach ( array( 'phone', 'mobile' ) as $pp_key ) : ?>
								<?php if ( '' !== $pp_contact[ $pp_key ] ) : ?>
									<a class="puppypress-nowrap" href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $pp_contact[ $pp_key ] ) ); ?>"><?php echo esc_html( $pp_contact[ $pp_key ] ); ?></a><br>
								<?php endif; ?>
							<?php endforeach; ?>
						</td>
						<td>
							<?php if ( '' !== trim( $pp_row['post']->post_content ) ) : ?>
								<?php echo wp_kses_post( wpautop( $pp_row['post']->post_content ) ); ?>
							<?php endif; ?>

							<?php if ( '' !== $pp_contact['address'] ) : ?>
								<p><?php echo nl2br( esc_html( $pp_contact['address'] ) ); ?></p>
							<?php endif; ?>

							<?php if ( $pp_email || '' !== $pp_contact['website'] ) : ?>
								<p class="puppypress-icons">
									<?php if ( $pp_email ) : ?>
										<a href="<?php echo esc_url( 'mailto:' . $pp_email ); ?>" aria-label="<?php esc_attr_e( 'Email', 'puppypress' ); ?>"><?php echo Icons::svg( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
									<?php endif; ?>
									<?php if ( '' !== $pp_contact['website'] ) : ?>
										<a href="<?php echo esc_url( $pp_contact['website'] ); ?>" target="_blank" rel="nofollow noopener" aria-label="<?php esc_attr_e( 'Website', 'puppypress' ); ?>"><?php echo Icons::svg( 'website' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
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

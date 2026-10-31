<?php
/**
 * Literature grouped by category.
 *
 * Override: {theme}/puppypress/lists/literature.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $categories List of array{category: WP_Term, books: WP_Post[]}.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( ! $args['categories'] ) : ?>
	<p class="puppypress-empty"><?php esc_html_e( 'No literature found.', 'puppypress' ); ?></p>
<?php endif; ?>

<?php foreach ( $args['categories'] as $pp_category ) : ?>
	<h2><?php echo esc_html( $pp_category['category']->name ); ?></h2>
	<div class="puppypress-table-scroll">
		<table class="puppypress-table puppypress-table--literature">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Cover', 'puppypress' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Author', 'puppypress' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Title', 'puppypress' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Price', 'puppypress' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $pp_category['books'] as $pp_book ) : ?>
					<?php
					$pp_authors  = get_the_terms( $pp_book, 'literature-author' );
					$pp_authors  = ( $pp_authors && ! is_wp_error( $pp_authors ) ) ? implode( ', ', wp_list_pluck( $pp_authors, 'name' ) ) : '';
					$pp_subtitle = (string) get_post_meta( $pp_book->ID, 'subtitle', true );
					$pp_isbn     = (string) get_post_meta( $pp_book->ID, 'isbn', true );
					?>
					<tr>
						<td class="puppypress-table__cover"><?php echo get_the_post_thumbnail( $pp_book, 'thumbnail' ); ?></td>
						<td><?php echo esc_html( $pp_authors ); ?></td>
						<td>
							<p>
								<strong><?php echo esc_html( $pp_book->post_title ); ?></strong>
								<?php if ( '' !== $pp_subtitle ) : ?>
									<br><?php echo esc_html( $pp_subtitle ); ?>
								<?php endif; ?>
								<?php if ( '' !== $pp_isbn ) : ?>
									<br><small><?php echo esc_html( sprintf( 'ISBN %s', $pp_isbn ) ); ?></small>
								<?php endif; ?>
							</p>
							<?php echo wp_kses_post( wpautop( $pp_book->post_content ) ); ?>
						</td>
						<td class="puppypress-nowrap"><?php echo esc_html( (string) get_post_meta( $pp_book->ID, 'price', true ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endforeach; ?>

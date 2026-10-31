<?php
/**
 * Literature grouped by category.
 *
 * Override: {theme}/rudelwerk/lists/literature.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $categories List of array{category: WP_Term, books: WP_Post[]}.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<?php if ( ! $args['categories'] ) : ?>
	<p class="rudelwerk-empty"><?php esc_html_e( 'No literature found.', 'rudelwerk' ); ?></p>
<?php endif; ?>

<?php foreach ( $args['categories'] as $rw_category ) : ?>
	<h2><?php echo esc_html( $rw_category['category']->name ); ?></h2>
	<div class="rudelwerk-table-scroll">
		<table class="rudelwerk-table rudelwerk-table--literature">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'Cover', 'rudelwerk' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Author', 'rudelwerk' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Title', 'rudelwerk' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Price', 'rudelwerk' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $rw_category['books'] as $rw_book ) : ?>
					<?php
					$rw_authors  = get_the_terms( $rw_book, 'literature-author' );
					$rw_authors  = ( $rw_authors && ! is_wp_error( $rw_authors ) ) ? implode( ', ', wp_list_pluck( $rw_authors, 'name' ) ) : '';
					$rw_subtitle = (string) get_post_meta( $rw_book->ID, 'subtitle', true );
					$rw_isbn     = (string) get_post_meta( $rw_book->ID, 'isbn', true );
					?>
					<tr>
						<td class="rudelwerk-table__cover"><?php echo get_the_post_thumbnail( $rw_book, 'thumbnail' ); ?></td>
						<td><?php echo esc_html( $rw_authors ); ?></td>
						<td>
							<p>
								<strong><?php echo esc_html( $rw_book->post_title ); ?></strong>
								<?php if ( '' !== $rw_subtitle ) : ?>
									<br><?php echo esc_html( $rw_subtitle ); ?>
								<?php endif; ?>
								<?php if ( '' !== $rw_isbn ) : ?>
									<br><small><?php echo esc_html( sprintf( 'ISBN %s', $rw_isbn ) ); ?></small>
								<?php endif; ?>
							</p>
							<?php echo wp_kses_post( wpautop( $rw_book->post_content ) ); ?>
						</td>
						<td class="rudelwerk-nowrap"><?php echo esc_html( (string) get_post_meta( $rw_book->ID, 'price', true ) ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>
<?php endforeach; ?>

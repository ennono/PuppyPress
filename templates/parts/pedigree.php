<?php
/**
 * Pedigree table (three generations).
 *
 * Override: {theme}/rudelwerk/parts/pedigree.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $pedigree Result of Rudelwerk\Content\Pedigree::get().
 * }
 */

use Rudelwerk\Content\Pedigree;

defined( 'ABSPATH' ) || exit;

$rw_pedigree = $args['pedigree'];
?>
<div class="rudelwerk-pedigree">
	<table class="rudelwerk-pedigree__table" aria-label="<?php esc_attr_e( 'Pedigree', 'rudelwerk' ); ?>">
		<tbody>
			<?php foreach ( Pedigree::rows() as $rw_row ) : ?>
				<tr>
					<?php foreach ( $rw_row as $rw_cell ) : ?>
						<?php $rw_ancestor = $rw_pedigree[ $rw_cell['position'] ]; ?>
						<td rowspan="<?php echo (int) $rw_cell['rowspan']; ?>" class="rudelwerk-pedigree__generation-<?php echo (int) $rw_cell['generation']; ?>">
							<?php if ( 1 === $rw_cell['generation'] ) : ?>
								<span class="rudelwerk-pedigree__role"><?php echo esc_html( Pedigree::label( $rw_cell['position'] ) ); ?></span>
							<?php endif; ?>

							<?php if ( '' !== trim( $rw_ancestor['description'] ) ) : ?>
								<div class="rudelwerk-pedigree__description"><?php echo nl2br( esc_html( $rw_ancestor['description'] ) ); ?></div>
							<?php endif; ?>

							<?php if ( $rw_ancestor['approval'] && 'publish' === get_post_status( $rw_ancestor['approval'] ) ) : ?>
								<a class="rudelwerk-pedigree__approval" href="<?php echo esc_url( get_permalink( $rw_ancestor['approval'] ) ); ?>"><?php esc_html_e( 'Approval', 'rudelwerk' ); ?></a>
							<?php endif; ?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

<?php
/**
 * Pedigree table (three generations).
 *
 * Override: {theme}/puppypress/parts/pedigree.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $pedigree Result of PuppyPress\Content\Pedigree::get().
 * }
 */

use PuppyPress\Content\Pedigree;

defined( 'ABSPATH' ) || exit;

$pp_pedigree = $args['pedigree'];
?>
<div class="puppypress-pedigree">
	<table class="puppypress-pedigree__table" aria-label="<?php esc_attr_e( 'Pedigree', 'puppypress' ); ?>">
		<tbody>
			<?php foreach ( Pedigree::rows() as $pp_row ) : ?>
				<tr>
					<?php foreach ( $pp_row as $pp_cell ) : ?>
						<?php $pp_ancestor = $pp_pedigree[ $pp_cell['position'] ]; ?>
						<td rowspan="<?php echo (int) $pp_cell['rowspan']; ?>" class="puppypress-pedigree__generation-<?php echo (int) $pp_cell['generation']; ?>">
							<?php if ( 1 === $pp_cell['generation'] ) : ?>
								<span class="puppypress-pedigree__role"><?php echo esc_html( Pedigree::label( $pp_cell['position'] ) ); ?></span>
							<?php endif; ?>

							<?php if ( '' !== trim( $pp_ancestor['description'] ) ) : ?>
								<div class="puppypress-pedigree__description"><?php echo nl2br( esc_html( $pp_ancestor['description'] ) ); ?></div>
							<?php endif; ?>

							<?php if ( $pp_ancestor['approval'] && 'publish' === get_post_status( $pp_ancestor['approval'] ) ) : ?>
								<a class="puppypress-pedigree__approval" href="<?php echo esc_url( get_permalink( $pp_ancestor['approval'] ) ); ?>"><?php esc_html_e( 'Approval', 'puppypress' ); ?></a>
							<?php endif; ?>
						</td>
					<?php endforeach; ?>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</div>

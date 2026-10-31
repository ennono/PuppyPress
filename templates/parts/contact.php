<?php
/**
 * Contact details of a person or business.
 *
 * Override: {theme}/rudelwerk/parts/contact.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $contact   Contact details (name, address, phone, mobile, fax, email, website).
 *     @type bool  $show_name Whether to show the name. Default true.
 * }
 */

use Rudelwerk\Frontend\Icons;

defined( 'ABSPATH' ) || exit;

$rw_contact   = $args['contact'];
$rw_show_name = ! isset( $args['show_name'] ) || $args['show_name'];
?>
<ul class="rudelwerk-contact">
	<?php if ( $rw_show_name && '' !== $rw_contact['name'] ) : ?>
		<li class="rudelwerk-contact__name"><strong><?php echo esc_html( $rw_contact['name'] ); ?></strong></li>
	<?php endif; ?>

	<?php if ( '' !== $rw_contact['address'] ) : ?>
		<li class="rudelwerk-contact__address"><?php echo nl2br( esc_html( $rw_contact['address'] ) ); ?></li>
	<?php endif; ?>

	<?php foreach ( array( 'phone', 'mobile', 'fax' ) as $rw_key ) : ?>
		<?php if ( '' !== $rw_contact[ $rw_key ] ) : ?>
			<li class="rudelwerk-contact__<?php echo esc_attr( $rw_key ); ?>">
				<?php echo Icons::svg( $rw_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $rw_contact[ $rw_key ] ) ); ?>"><?php echo esc_html( $rw_contact[ $rw_key ] ); ?></a>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>

	<?php if ( is_email( $rw_contact['email'] ) ) : ?>
		<li class="rudelwerk-contact__email">
			<?php echo Icons::svg( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<a href="<?php echo esc_url( 'mailto:' . antispambot( $rw_contact['email'] ) ); ?>"><?php echo esc_html( antispambot( $rw_contact['email'] ) ); ?></a>
		</li>
	<?php endif; ?>

	<?php if ( '' !== $rw_contact['website'] ) : ?>
		<li class="rudelwerk-contact__website">
			<?php echo Icons::svg( 'website' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<a href="<?php echo esc_url( $rw_contact['website'] ); ?>" target="_blank" rel="nofollow noopener"><?php echo esc_html( preg_replace( '#^https?://#', '', untrailingslashit( $rw_contact['website'] ) ) ); ?></a>
		</li>
	<?php endif; ?>
</ul>

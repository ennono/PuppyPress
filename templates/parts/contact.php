<?php
/**
 * Contact details of a person or business.
 *
 * Override: {theme}/puppypress/parts/contact.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $contact   Contact details (name, address, phone, mobile, fax, email, website).
 *     @type bool  $show_name Whether to show the name. Default true.
 * }
 */

use PuppyPress\Frontend\Icons;

defined( 'ABSPATH' ) || exit;

$pp_contact   = $args['contact'];
$pp_show_name = ! isset( $args['show_name'] ) || $args['show_name'];
?>
<ul class="puppypress-contact">
	<?php if ( $pp_show_name && '' !== $pp_contact['name'] ) : ?>
		<li class="puppypress-contact__name"><strong><?php echo esc_html( $pp_contact['name'] ); ?></strong></li>
	<?php endif; ?>

	<?php if ( '' !== $pp_contact['address'] ) : ?>
		<li class="puppypress-contact__address"><?php echo nl2br( esc_html( $pp_contact['address'] ) ); ?></li>
	<?php endif; ?>

	<?php foreach ( array( 'phone', 'mobile', 'fax' ) as $pp_key ) : ?>
		<?php if ( '' !== $pp_contact[ $pp_key ] ) : ?>
			<li class="puppypress-contact__<?php echo esc_attr( $pp_key ); ?>">
				<?php echo Icons::svg( $pp_key ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
				<a href="<?php echo esc_url( 'tel:' . preg_replace( '/[^\d+]/', '', $pp_contact[ $pp_key ] ) ); ?>"><?php echo esc_html( $pp_contact[ $pp_key ] ); ?></a>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>

	<?php if ( is_email( $pp_contact['email'] ) ) : ?>
		<li class="puppypress-contact__email">
			<?php echo Icons::svg( 'email' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<a href="<?php echo esc_url( 'mailto:' . antispambot( $pp_contact['email'] ) ); ?>"><?php echo esc_html( antispambot( $pp_contact['email'] ) ); ?></a>
		</li>
	<?php endif; ?>

	<?php if ( '' !== $pp_contact['website'] ) : ?>
		<li class="puppypress-contact__website">
			<?php echo Icons::svg( 'website' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<a href="<?php echo esc_url( $pp_contact['website'] ); ?>" target="_blank" rel="nofollow noopener"><?php echo esc_html( preg_replace( '#^https?://#', '', untrailingslashit( $pp_contact['website'] ) ) ); ?></a>
		</li>
	<?php endif; ?>
</ul>

<?php
/**
 * Detail view of a stud dog (wraps the post content).
 *
 * Override: {theme}/puppypress/single/stud-dog.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type WP_Post $post    Stud dog.
 *     @type string  $content Filtered post content.
 * }
 */

use PuppyPress\Content\Pedigree;
use PuppyPress\Data\Contact;
use PuppyPress\Data\Dogs;

defined( 'ABSPATH' ) || exit;

$pp_post = $args['post'];
$pp_id   = $pp_post->ID;

$pp_terms = static function ( $taxonomy ) use ( $pp_id ) {
	$terms = get_the_terms( $pp_id, $taxonomy );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms : array();
};

$pp_kennels  = array_values( array_filter( $pp_terms( 'kennel' ), static function ( $kennel ) {
	return ! Dogs::is_kennel_hidden( $kennel );
} ) );
$pp_breeders = array_map( array( Contact::class, 'for_term' ), $pp_terms( 'breeder' ) );
$pp_owners   = array_map( array( Contact::class, 'for_term' ), $pp_terms( 'owner' ) );

$pp_born     = Dogs::format_date( (string) get_post_meta( $pp_id, 'date_born', true ) );
$pp_withers  = (string) get_post_meta( $pp_id, 'withers', true );
$pp_studbook = (string) get_post_meta( $pp_id, 'studbook_no', true );
$pp_tests    = (string) get_post_meta( $pp_id, 'tests', true );
$pp_license  = absint( get_post_meta( $pp_id, 'license', true ) );
$pp_approval = absint( get_post_meta( $pp_id, 'approval', true ) );
$pp_pedigree = Pedigree::get( $pp_id );

/*
 * Summary line: kennel | breeder | owner | date born.
 */
$pp_summary = array();

foreach ( $pp_kennels as $pp_kennel ) {
	$pp_summary[] = array(
		/* translators: %s: kennel name */
		'text' => sprintf( __( 'Kennel: %s', 'puppypress' ), $pp_kennel->name ),
		'url'  => (string) get_term_meta( $pp_kennel->term_id, 'kennel_website', true ),
	);
}

foreach ( $pp_breeders as $pp_contact ) {
	$pp_summary[] = array(
		/* translators: %s: breeder name */
		'text' => sprintf( __( 'Breeder: %s', 'puppypress' ), $pp_contact['name'] ),
		'url'  => Contact::link( $pp_contact ),
	);
}

foreach ( $pp_owners as $pp_contact ) {
	$pp_summary[] = array(
		/* translators: %s: owner name */
		'text' => sprintf( __( 'Owner: %s', 'puppypress' ), $pp_contact['name'] ),
		'url'  => Contact::link( $pp_contact, true ),
	);
}

if ( '' !== $pp_born ) {
	$pp_summary[] = array(
		/* translators: %s: date of birth */
		'text' => sprintf( __( 'Date born: %s', 'puppypress' ), $pp_born ),
		'url'  => '',
	);
}

/*
 * Stud dog data.
 */
$pp_data = array();

if ( '' !== $pp_studbook ) {
	$pp_data[] = array( __( 'Stud book no.', 'puppypress' ), esc_html( $pp_studbook ) );
}
if ( '' !== $pp_born ) {
	$pp_data[] = array( __( 'Date born', 'puppypress' ), esc_html( $pp_born ) );
}
if ( '' !== $pp_withers ) {
	/* translators: %s: withers height */
	$pp_data[] = array( __( 'Withers', 'puppypress' ), esc_html( sprintf( __( '%s cm', 'puppypress' ), Dogs::format_withers( $pp_withers ) ) ) );
}
if ( $pp_license && 'publish' === get_post_status( $pp_license ) ) {
	$pp_data[] = array( __( 'Breeding license', 'puppypress' ), sprintf( '<a href="%1$s">%2$s</a>', esc_url( get_permalink( $pp_license ) ), esc_html( get_post_field( 'post_title', $pp_license ) ) ) );
}
if ( $pp_approval && 'publish' === get_post_status( $pp_approval ) ) {
	$pp_data[] = array( __( 'Approval', 'puppypress' ), sprintf( '<a href="%1$s">%2$s</a>', esc_url( get_permalink( $pp_approval ) ), esc_html( get_post_field( 'post_title', $pp_approval ) ) ) );
}
if ( '' !== $pp_tests ) {
	$pp_data[] = array( __( 'Tests and titles', 'puppypress' ), esc_html( $pp_tests ) );
}

$pp_kennel_websites = array_filter(
	array_map(
		static function ( $kennel ) {
			return (string) get_term_meta( $kennel->term_id, 'kennel_website', true );
		},
		$pp_kennels
	)
);
?>
<div class="puppypress puppypress-single puppypress-single--stud-dog">

	<?php if ( $pp_summary ) : ?>
		<p class="puppypress-summary">
			<?php foreach ( $pp_summary as $pp_index => $pp_item ) : ?>
				<?php echo $pp_index ? '<span class="puppypress-summary__sep" aria-hidden="true"> | </span>' : ''; ?>
				<?php if ( $pp_item['url'] ) : ?>
					<?php $pp_external = 0 !== strpos( $pp_item['url'], 'mailto:' ); ?>
					<a href="<?php echo esc_url( $pp_item['url'] ); ?>"<?php echo $pp_external ? ' target="_blank" rel="nofollow noopener"' : ''; ?>><?php echo esc_html( $pp_item['text'] ); ?></a>
				<?php else : ?>
					<span><?php echo esc_html( $pp_item['text'] ); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</p>
	<?php endif; ?>

	<?php if ( $pp_data ) : ?>
		<h2><?php esc_html_e( 'Stud dog data', 'puppypress' ); ?></h2>
		<dl class="puppypress-data">
			<?php foreach ( $pp_data as $pp_row ) : ?>
				<dt><?php echo esc_html( $pp_row[0] ); ?></dt>
				<dd><?php echo wp_kses_post( $pp_row[1] ); ?></dd>
			<?php endforeach; ?>
		</dl>
	<?php endif; ?>

	<?php if ( $pp_breeders ) : ?>
		<h2><?php echo esc_html( _n( 'Breeder', 'Breeders', count( $pp_breeders ), 'puppypress' ) ); ?></h2>
		<?php foreach ( $pp_breeders as $pp_contact ) : ?>
			<?php puppypress_template( 'parts/contact.php', array( 'contact' => $pp_contact ) ); ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( $pp_kennel_websites ) : ?>
		<h2><?php echo esc_html( _n( 'Kennel', 'Kennels', count( $pp_kennels ), 'puppypress' ) ); ?></h2>
		<?php foreach ( $pp_kennels as $pp_kennel ) : ?>
			<?php
			puppypress_template(
				'parts/contact.php',
				array(
					'contact' => array(
						'name'    => $pp_kennel->name,
						'address' => '',
						'phone'   => '',
						'mobile'  => '',
						'fax'     => '',
						'email'   => '',
						'website' => (string) get_term_meta( $pp_kennel->term_id, 'kennel_website', true ),
					),
				)
			);
			?>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( $pp_owners ) : ?>
		<h2><?php echo esc_html( _n( 'Owner', 'Owners', count( $pp_owners ), 'puppypress' ) ); ?></h2>
		<?php foreach ( $pp_owners as $pp_contact ) : ?>
			<?php puppypress_template( 'parts/contact.php', array( 'contact' => $pp_contact ) ); ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( ! Pedigree::is_empty( $pp_pedigree ) ) : ?>
		<h2><?php esc_html_e( 'Pedigree', 'puppypress' ); ?></h2>
		<?php puppypress_template( 'parts/pedigree.php', array( 'pedigree' => $pp_pedigree ) ); ?>
	<?php endif; ?>

	<?php if ( '' !== trim( $args['content'] ) ) : ?>
		<h2>
			<?php
			/* translators: %s: name of the stud dog */
			echo esc_html( sprintf( __( 'About %s', 'puppypress' ), $pp_post->post_title ) );
			?>
		</h2>
		<div class="puppypress-content">
			<?php echo $args['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered post content. ?>
		</div>
	<?php endif; ?>
</div>

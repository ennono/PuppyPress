<?php
/**
 * Detail view of a stud dog (wraps the post content).
 *
 * Override: {theme}/rudelwerk/single/stud-dog.php
 *
 * @package Rudelwerk
 * @version 1.0.0
 *
 * @var array $args {
 *     @type WP_Post $post    Stud dog.
 *     @type string  $content Filtered post content.
 * }
 */

use Rudelwerk\Content\Pedigree;
use Rudelwerk\Data\Contact;
use Rudelwerk\Data\Dogs;

defined( 'ABSPATH' ) || exit;

$rw_post = $args['post'];
$rw_id   = $rw_post->ID;

$rw_terms = static function ( $taxonomy ) use ( $rw_id ) {
	$terms = get_the_terms( $rw_id, $taxonomy );
	return ( $terms && ! is_wp_error( $terms ) ) ? $terms : array();
};

$rw_kennels  = array_values( array_filter( $rw_terms( 'kennel' ), static function ( $kennel ) {
	return ! Dogs::is_kennel_hidden( $kennel );
} ) );
$rw_breeders = array_map( array( Contact::class, 'for_term' ), $rw_terms( 'breeder' ) );
$rw_owners   = array_map( array( Contact::class, 'for_term' ), $rw_terms( 'owner' ) );

$rw_born     = Dogs::format_date( (string) get_post_meta( $rw_id, 'date_born', true ) );
$rw_withers  = (string) get_post_meta( $rw_id, 'withers', true );
$rw_studbook = (string) get_post_meta( $rw_id, 'studbook_no', true );
$rw_tests    = (string) get_post_meta( $rw_id, 'tests', true );
$rw_license  = absint( get_post_meta( $rw_id, 'license', true ) );
$rw_approval = absint( get_post_meta( $rw_id, 'approval', true ) );
$rw_pedigree = Pedigree::get( $rw_id );

/*
 * Summary line: kennel | breeder | owner | date born.
 */
$rw_summary = array();

foreach ( $rw_kennels as $rw_kennel ) {
	$rw_summary[] = array(
		/* translators: %s: kennel name */
		'text' => sprintf( __( 'Kennel: %s', 'rudelwerk' ), $rw_kennel->name ),
		'url'  => (string) get_term_meta( $rw_kennel->term_id, 'kennel_website', true ),
	);
}

foreach ( $rw_breeders as $rw_contact ) {
	$rw_summary[] = array(
		/* translators: %s: breeder name */
		'text' => sprintf( __( 'Breeder: %s', 'rudelwerk' ), $rw_contact['name'] ),
		'url'  => Contact::link( $rw_contact ),
	);
}

foreach ( $rw_owners as $rw_contact ) {
	$rw_summary[] = array(
		/* translators: %s: owner name */
		'text' => sprintf( __( 'Owner: %s', 'rudelwerk' ), $rw_contact['name'] ),
		'url'  => Contact::link( $rw_contact, true ),
	);
}

if ( '' !== $rw_born ) {
	$rw_summary[] = array(
		/* translators: %s: date of birth */
		'text' => sprintf( __( 'Date born: %s', 'rudelwerk' ), $rw_born ),
		'url'  => '',
	);
}

/*
 * Stud dog data.
 */
$rw_data = array();

if ( '' !== $rw_studbook ) {
	$rw_data[] = array( __( 'Stud book no.', 'rudelwerk' ), esc_html( $rw_studbook ) );
}
if ( '' !== $rw_born ) {
	$rw_data[] = array( __( 'Date born', 'rudelwerk' ), esc_html( $rw_born ) );
}
if ( '' !== $rw_withers ) {
	/* translators: %s: withers height */
	$rw_data[] = array( __( 'Withers', 'rudelwerk' ), esc_html( sprintf( __( '%s cm', 'rudelwerk' ), Dogs::format_withers( $rw_withers ) ) ) );
}
if ( $rw_license && 'publish' === get_post_status( $rw_license ) ) {
	$rw_data[] = array( __( 'Breeding license', 'rudelwerk' ), sprintf( '<a href="%1$s">%2$s</a>', esc_url( get_permalink( $rw_license ) ), esc_html( get_post_field( 'post_title', $rw_license ) ) ) );
}
if ( $rw_approval && 'publish' === get_post_status( $rw_approval ) ) {
	$rw_data[] = array( __( 'Approval', 'rudelwerk' ), sprintf( '<a href="%1$s">%2$s</a>', esc_url( get_permalink( $rw_approval ) ), esc_html( get_post_field( 'post_title', $rw_approval ) ) ) );
}
if ( '' !== $rw_tests ) {
	$rw_data[] = array( __( 'Tests and titles', 'rudelwerk' ), esc_html( $rw_tests ) );
}

$rw_kennel_websites = array_filter(
	array_map(
		static function ( $kennel ) {
			return (string) get_term_meta( $kennel->term_id, 'kennel_website', true );
		},
		$rw_kennels
	)
);
?>
<div class="rudelwerk rudelwerk-single rudelwerk-single--stud-dog">

	<?php if ( $rw_summary ) : ?>
		<p class="rudelwerk-summary">
			<?php foreach ( $rw_summary as $rw_index => $rw_item ) : ?>
				<?php echo $rw_index ? '<span class="rudelwerk-summary__sep" aria-hidden="true"> | </span>' : ''; ?>
				<?php if ( $rw_item['url'] ) : ?>
					<?php $rw_external = 0 !== strpos( $rw_item['url'], 'mailto:' ); ?>
					<a href="<?php echo esc_url( $rw_item['url'] ); ?>"<?php echo $rw_external ? ' target="_blank" rel="nofollow noopener"' : ''; ?>><?php echo esc_html( $rw_item['text'] ); ?></a>
				<?php else : ?>
					<span><?php echo esc_html( $rw_item['text'] ); ?></span>
				<?php endif; ?>
			<?php endforeach; ?>
		</p>
	<?php endif; ?>

	<?php if ( $rw_data ) : ?>
		<h2><?php esc_html_e( 'Stud dog data', 'rudelwerk' ); ?></h2>
		<dl class="rudelwerk-data">
			<?php foreach ( $rw_data as $rw_row ) : ?>
				<dt><?php echo esc_html( $rw_row[0] ); ?></dt>
				<dd><?php echo wp_kses_post( $rw_row[1] ); ?></dd>
			<?php endforeach; ?>
		</dl>
	<?php endif; ?>

	<?php if ( $rw_breeders ) : ?>
		<h2><?php echo esc_html( _n( 'Breeder', 'Breeders', count( $rw_breeders ), 'rudelwerk' ) ); ?></h2>
		<?php foreach ( $rw_breeders as $rw_contact ) : ?>
			<?php rudelwerk_template( 'parts/contact.php', array( 'contact' => $rw_contact ) ); ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( $rw_kennel_websites ) : ?>
		<h2><?php echo esc_html( _n( 'Kennel', 'Kennels', count( $rw_kennels ), 'rudelwerk' ) ); ?></h2>
		<?php foreach ( $rw_kennels as $rw_kennel ) : ?>
			<?php
			rudelwerk_template(
				'parts/contact.php',
				array(
					'contact' => array(
						'name'    => $rw_kennel->name,
						'address' => '',
						'phone'   => '',
						'mobile'  => '',
						'fax'     => '',
						'email'   => '',
						'website' => (string) get_term_meta( $rw_kennel->term_id, 'kennel_website', true ),
					),
				)
			);
			?>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( $rw_owners ) : ?>
		<h2><?php echo esc_html( _n( 'Owner', 'Owners', count( $rw_owners ), 'rudelwerk' ) ); ?></h2>
		<?php foreach ( $rw_owners as $rw_contact ) : ?>
			<?php rudelwerk_template( 'parts/contact.php', array( 'contact' => $rw_contact ) ); ?>
		<?php endforeach; ?>
	<?php endif; ?>

	<?php if ( ! Pedigree::is_empty( $rw_pedigree ) ) : ?>
		<h2><?php esc_html_e( 'Pedigree', 'rudelwerk' ); ?></h2>
		<?php rudelwerk_template( 'parts/pedigree.php', array( 'pedigree' => $rw_pedigree ) ); ?>
	<?php endif; ?>

	<?php if ( '' !== trim( $args['content'] ) ) : ?>
		<h2>
			<?php
			/* translators: %s: name of the stud dog */
			echo esc_html( sprintf( __( 'About %s', 'rudelwerk' ), $rw_post->post_title ) );
			?>
		</h2>
		<div class="rudelwerk-content">
			<?php echo $args['content']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- filtered post content. ?>
		</div>
	<?php endif; ?>
</div>

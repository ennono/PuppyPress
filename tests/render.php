<?php
/**
 * Renders every template with sample data to catch runtime errors.
 * Included by tests/run.php after the unit checks.
 *
 * @package Rudelwerk
 */

use Rudelwerk\Frontend\StudDogFilter;
use Rudelwerk\Frontend\Templates;

/*
 * Fake storage.
 */
$GLOBALS['rw_meta'] = array(
	10 => array(
		'date_born'          => '2019-04-12',
		'withers'            => '61.5',
		'studbook_no'        => 'ZB 4711',
		'tests'              => 'ZTP, BH',
		'license'            => '42',
		'approval'           => '42',
		'kennel_before_name' => '',
		'father'             => "Arko\nZTP",
		'father_approval'    => '42',
	),
	20 => array(
		'expected_puppies'            => '',
		'date_of_birth'               => '01.05.2026',
		'male_count'                  => '3',
		'female_count'                => '1',
		'still_available_male'        => '1',
		'still_available_female'      => '0',
		'still_available_last_update' => '20.05.2026',
		'zip'                         => '28195',
		'city'                        => 'Bremen',
		'father_name'                 => 'Arko',
		'father_desc'                 => 'Lovely dog',
		'father_stud_dog_id'          => '10',
		'father_photo'                => ',5,6',
		'mother_name'                 => 'Bella',
		'mother_desc'                 => '',
		'mother_photo'                => '',
	),
	30 => array(
		'gender'        => 'female',
		'date_of_birth' => '2021',
	),
	40 => array(
		'subtitle' => 'Everything about terriers',
		'isbn'     => '978-3-00-000000-0',
		'price'    => '19,90 €',
		'address'  => "Street 1\n28195 Bremen",
		'email'    => 'groomer@example.org',
		'phone'    => '0421 123',
		'website'  => 'https://example.org',
	),
);

$GLOBALS['rw_term_meta'] = array(
	1 => array( 'kennel_website' => 'https://kennel.example.org', 'kennel_hidden' => '0', 'kennel_zip' => '28195' ),
	2 => array( 'breeder_email' => 'breeder@example.org', 'breeder_phone' => '+49 421 1', 'breeder_address' => "Line 1\nLine 2" ),
	3 => array( 'owner_website' => 'https://owner.example.org' ),
);

$GLOBALS['rw_terms_of'] = array(
	10 => array(
		'kennel'  => array( (object) array( 'term_id' => 1, 'name' => 'vom Waldrand', 'taxonomy' => 'kennel' ) ),
		'breeder' => array( (object) array( 'term_id' => 2, 'name' => 'Erika Muster', 'taxonomy' => 'breeder' ) ),
		'owner'   => array( (object) array( 'term_id' => 3, 'name' => 'Otto Owner', 'taxonomy' => 'owner' ) ),
	),
	20 => array(
		'kennel'  => array( (object) array( 'term_id' => 1, 'name' => 'vom Waldrand', 'taxonomy' => 'kennel' ) ),
		'breeder' => array( (object) array( 'term_id' => 2, 'name' => 'Erika Muster', 'taxonomy' => 'breeder' ) ),
	),
	40 => array(
		'literature-author' => array( (object) array( 'term_id' => 9, 'name' => 'A. Author', 'taxonomy' => 'literature-author' ) ),
	),
);

class WP_Term {
	public $term_id;
	public $name;
	public $taxonomy;
	public function __construct( $data ) {
		foreach ( (array) $data as $key => $value ) {
			$this->$key = $value;
		}
	}
}

class WP_Post {
	public $ID;
	public $post_title;
	public $post_content = '';
	public $post_type    = 'post';
	public function __construct( $data ) {
		foreach ( $data as $key => $value ) {
			$this->$key = $value;
		}
	}
}

foreach ( $GLOBALS['rw_terms_of'] as $post_id => $taxonomies ) {
	foreach ( $taxonomies as $taxonomy => $terms ) {
		$GLOBALS['rw_terms_of'][ $post_id ][ $taxonomy ] = array_map(
			static function ( $term ) {
				return new WP_Term( $term );
			},
			$terms
		);
	}
}

function get_post_meta( $id, $key ) { return isset( $GLOBALS['rw_meta'][ $id ][ $key ] ) ? $GLOBALS['rw_meta'][ $id ][ $key ] : ''; }
function get_term_meta( $id, $key ) { return isset( $GLOBALS['rw_term_meta'][ $id ][ $key ] ) ? $GLOBALS['rw_term_meta'][ $id ][ $key ] : ''; }
function get_the_terms( $post, $taxonomy ) {
	$id = is_object( $post ) ? $post->ID : (int) $post;
	return isset( $GLOBALS['rw_terms_of'][ $id ][ $taxonomy ] ) ? $GLOBALS['rw_terms_of'][ $id ][ $taxonomy ] : false;
}
function get_post_field( $field, $id ) { return 42 === (int) $id ? 'Approval Arko' : 'Arko'; }
function get_the_title( $post ) { return is_object( $post ) ? $post->post_title : 'Title'; }
function get_option( $name ) { return 'd.m.Y'; }
function wp_timezone() { return new DateTimeZone( 'Europe/Berlin' ); }
function wp_date( $format, $timestamp ) { return gmdate( $format, $timestamp ); }
function has_post_thumbnail() { return false; }
function get_the_post_thumbnail() { return ''; }
function wp_kses_post( $html ) { return $html; }
function wpautop( $text ) { return '<p>' . $text . '</p>'; }
function antispambot( $email ) { return $email; }
function is_email( $email ) { return (bool) filter_var( $email, FILTER_VALIDATE_EMAIL ); }
function untrailingslashit( $value ) { return rtrim( $value, '/\\' ); }
function wp_get_attachment_image_url( $id ) { return 'https://example.org/img-' . $id . '.jpg'; }
function wp_get_attachment_image( $id ) { return '<img src="img-' . $id . '.jpg">'; }
function selected( $a, $b, $echo = true ) { $out = (string) $a === (string) $b ? ' selected="selected"' : ''; if ( $echo ) { echo $out; } return $out; }
function checked( $a, $b = true, $echo = true ) { $out = (string) $a === (string) $b ? ' checked="checked"' : ''; if ( $echo ) { echo $out; } return $out; }
function remove_query_arg() { return 'https://example.org/deckrueden/'; }
function wp_enqueue_style() {}
function wp_style_is() { return true; }
function wp_strip_all_tags( $text ) { return strip_tags( $text ); }

require RUDELWERK_DIR . 'includes/template-functions.php';

$rw_render = static function ( $name, array $args ) {
	set_error_handler(
		static function ( $severity, $message, $file, $line ) {
			throw new ErrorException( $message, 0, $severity, $file, $line );
		}
	);

	try {
		$html = Templates::html( $name, $args );
		$ok   = true;
	} catch ( Throwable $e ) {
		ob_end_clean();
		fwrite( STDERR, "  $name: " . $e->getMessage() . ' (' . basename( $e->getFile() ) . ':' . $e->getLine() . ")\n" );
		$html = '';
		$ok   = false;
	}

	restore_error_handler();
	check( "render $name", $ok );

	return $html;
};

$stud_dog = new WP_Post( array( 'ID' => 10, 'post_title' => 'Arko', 'post_type' => 'stud-dogs' ) );
$litter   = new WP_Post( array( 'ID' => 20, 'post_title' => 'vom Waldrand', 'post_type' => 'puppies' ) );
$dog      = new WP_Post( array( 'ID' => 30, 'post_title' => 'Lucky', 'post_type' => 'dogs-to-place' ) );
$book     = new WP_Post( array( 'ID' => 40, 'post_title' => 'Terrier Book', 'post_content' => 'Text', 'post_type' => 'literature' ) );

$html = $rw_render( 'single/stud-dog.php', array( 'post' => $stud_dog, 'content' => '<p>Story</p>' ) );
check( 'stud dog: summary with kennel link', false !== strpos( $html, 'href="https://kennel.example.org"' ) && false !== strpos( $html, 'Kennel: vom Waldrand' ) );
check( 'stud dog: breeder links to email', false !== strpos( $html, 'mailto:breeder@example.org' ) );
check( 'stud dog: owner links to website', false !== strpos( $html, 'Owner: Otto Owner' ) );
check( 'stud dog: formatted date', false !== strpos( $html, '12.04.2019' ) );
check( 'stud dog: withers', false !== strpos( $html, '61.5 cm' ) );
check( 'stud dog: license link', false !== strpos( $html, '?p=42' ) );
check( 'stud dog: pedigree', false !== strpos( $html, 'rudelwerk-pedigree' ) );
check( 'stud dog: content', false !== strpos( $html, '<p>Story</p>' ) && false !== strpos( $html, 'About Arko' ) );

$html = $rw_render( 'single/litter.php', array( 'post' => $litter, 'content' => '' ) );
check( 'litter: born sentence', false !== strpos( $html, 'were born on' ) );
check( 'litter: litter size', false !== strpos( $html, '3 males, 1 female' ) );
check( 'litter: still available', false !== strpos( $html, '1 male, 0 females' ) );
check( 'litter: father photos (legacy leading comma)', 2 === substr_count( $html, '<img src="img-' ) );
check( 'litter: stud dog link with kennel', false !== strpos( $html, 'More about the stud dog Arko vom Waldrand' ) );
check( 'litter: no empty content heading', false === strpos( $html, 'About the puppies' ) );

$html = $rw_render( 'single/dog-to-place.php', array( 'post' => $dog, 'content' => '<p>x</p>' ) );
check( 'dog to place: gender label', false !== strpos( $html, 'female, born 2021' ) );

$html = $rw_render( 'parts/stud-dog-card.php', array( 'post' => $stud_dog, 'kennel' => null ) );
check( 'card: name with kennel', false !== strpos( $html, 'Arko vom Waldrand' ) );

$html = $rw_render( 'parts/litter-card.php', array( 'post' => $litter ) );
check( 'litter card: title prefix', false !== strpos( $html, 'Puppies vom Waldrand' ) );
check( 'litter card: location', false !== strpos( $html, '28195 Bremen' ) );

$filter      = new StudDogFilter();
$filter->hd  = 'a1';
$html = $rw_render(
	'parts/stud-dog-filter.php',
	array(
		'filter' => $filter,
		'action' => '',
		'sizes'  => array( 'medium' => 'Medium' ),
		'hds'    => array( 'a1' => 'A1', 'a2' => 'A2' ),
		'eds'    => array(),
		'exams'  => array( 'ztp' => 'ZTP' ),
		'titles' => array(),
	)
);
check( 'filter: open when active', false !== strpos( $html, '<details class="rudelwerk-filter" open' ) );
check( 'filter: selected value', false !== strpos( $html, 'value="a1"  selected="selected"' ) );
check( 'filter: empty taxonomies skipped', false === strpos( $html, 'rw_ed' ) );

$rw_render( 'lists/stud-dogs.php', array( 'groups' => array( array( 'kennel' => null, 'posts' => array( $stud_dog ) ) ) ) );
$html = $rw_render( 'lists/stud-dogs.php', array( 'groups' => array() ) );
check( 'stud dogs: empty message', false !== strpos( $html, 'No stud dogs found.' ) );
$rw_render( 'lists/posts.php', array( 'posts' => array( $stud_dog ) ) );
$rw_render( 'lists/litters.php', array( 'sections' => array( 'born' => array( 'title' => 'Born', 'posts' => array( $litter ) ), 'expected' => array( 'title' => 'Expected', 'posts' => array() ) ), 'show_headings' => true ) );
$html = $rw_render(
	'lists/breeders-board.php',
	array(
		'rows' => array(
			array(
				'kennel'   => new WP_Term( array( 'term_id' => 1, 'name' => 'vom Waldrand' ) ),
				'zip'      => '',
				'breeders' => array( array( 'name' => 'Erika', 'address' => '', 'phone' => '1', 'mobile' => '', 'fax' => '2', 'email' => '', 'website' => '' ) ),
			),
		),
	)
);
check( 'breeders board: unknown zip', false !== strpos( $html, 'Unknown' ) );
check( 'breeders board: fax icon', false !== strpos( $html, 'rudelwerk-icon--fax' ) );

$html = $rw_render(
	'lists/directory.php',
	array(
		'regions' => array(
			array(
				'region' => new WP_Term( array( 'name' => '2xxxx' ) ),
				'rows'   => array(
					array(
						'zip'     => '28195',
						'city'    => 'Bremen',
						'post'    => $book,
						'contact' => array( 'name' => 'Groomer', 'address' => "Street 1\nBremen", 'phone' => '0421 123', 'mobile' => '', 'fax' => '', 'email' => 'groomer@example.org', 'website' => 'https://example.org' ),
					),
				),
			),
		),
		'empty'   => 'none',
	)
);
check( 'directory: tel link', false !== strpos( $html, 'tel:0421123' ) );
check( 'directory: mailto name', false !== strpos( $html, 'mailto:groomer@example.org">Groomer' ) );

$html = $rw_render( 'lists/literature.php', array( 'categories' => array( array( 'category' => new WP_Term( array( 'name' => 'Breed' ) ), 'books' => array( $book ) ) ) ) );
check( 'literature: author and isbn', false !== strpos( $html, 'A. Author' ) && false !== strpos( $html, 'ISBN 978-3-00-000000-0' ) );

/*
 * Admin controls.
 */
function esc_textarea( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function get_posts() { return array( new WP_Post( array( 'ID' => 42, 'post_title' => 'Approval Arko' ) ) ); }
function get_post_type_object() { return (object) array( 'cap' => (object) array( 'create_posts' => 'edit_posts' ), 'labels' => (object) array( 'add_new_item' => 'Add new approval' ) ); }
function current_user_can() { return true; }
function admin_url( $path ) { return 'https://example.org/wp-admin/' . $path; }

foreach ( Rudelwerk\Content\Fields::post_groups() as $post_type => $groups ) {
	foreach ( $groups as $group ) {
		foreach ( $group['fields'] as $key => $field ) {
			$value = 'media' === $field['type'] ? '5,6' : ( 'post' === $field['type'] ? '42' : 'x' );

			ob_start();
			try {
				Rudelwerk\Admin\FieldRenderer::control( $key, $field, $value );
				$html = ob_get_clean();
				$ok   = '' !== $html && false !== strpos( $html, 'rudelwerk_fields[' . $key . ']' );
			} catch ( Throwable $e ) {
				ob_end_clean();
				$ok = false;
			}
			check( "admin control $post_type/$key", $ok );
		}
	}
}

ob_start();
Rudelwerk\Admin\FieldRenderer::control( 'approval', array( 'type' => 'post', 'post_type' => 'approvals', 'add_new' => true ), '42' );
$html = ob_get_clean();
check( 'admin post select marks current value', false !== strpos( $html, 'value="42"  selected="selected"' ) );
check( 'admin post select offers "add new"', false !== strpos( $html, 'post-new.php?post_type=approvals' ) );

ob_start();
Rudelwerk\Admin\FieldRenderer::control( 'gender', array( 'type' => 'select', 'options' => Rudelwerk\Content\Fields::gender_options() ), 'weiblich' );
$html = ob_get_clean();
check( 'admin select keeps unknown legacy value', false !== strpos( $html, 'value="weiblich"  selected="selected"' ) );

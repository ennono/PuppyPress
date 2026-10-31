<?php
/**
 * Dependency free test runner: php tests/run.php
 *
 * @package Rudelwerk
 */

require __DIR__ . '/bootstrap.php';

use Rudelwerk\Content\Fields;
use Rudelwerk\Content\Pedigree;
use Rudelwerk\Content\PostTypes;
use Rudelwerk\Content\Taxonomies;
use Rudelwerk\Frontend\StudDogFilter;

$failures = 0;
$checks   = 0;

function check( $label, $condition ) {
	global $failures, $checks;
	$checks++;
	if ( ! $condition ) {
		$failures++;
		fwrite( STDERR, "FAIL: $label\n" );
	}
}

/*
 * 1. Data model is identical to the former theme.
 */
( new PostTypes() )->register();
( new Taxonomies() )->register();

$expected_post_types = array( 'puppies', 'dogs-to-place', 'stud-dogs', 'approvals', 'breeding-license', 'results', 'shows', 'trimming-proofs', 'dog-sitters', 'literature', 'vacation-deals' );
check( 'post type keys', array() === array_diff( $expected_post_types, array_keys( $GLOBALS['rw_test']['post_types'] ) ) );

$expected_taxonomies = array(
	'placement-category'  => array( 'dogs-to-place' ),
	'kennel'              => array( 'stud-dogs', 'approvals', 'breeding-license', 'puppies' ),
	'breeder'             => array( 'stud-dogs', 'puppies' ),
	'owner'               => array( 'stud-dogs' ),
	'event'               => array( 'results' ),
	'period'              => array( 'shows' ),
	'zip'                 => array( 'trimming-proofs' ),
	'city'                => array( 'trimming-proofs' ),
	'type'                => array( 'dog-sitters' ),
	'dog-sitters-zip'     => array( 'dog-sitters' ),
	'dog-sitters-city'    => array( 'dog-sitters' ),
	'literature-category' => array( 'literature' ),
	'literature-author'   => array( 'literature' ),
	'puppies-city'        => array( 'puppies' ),
	'exam'                => array( 'stud-dogs' ),
	'hd'                  => array( 'stud-dogs' ),
	'ed'                  => array( 'stud-dogs' ),
	'size'                => array( 'stud-dogs' ),
	'title'               => array( 'stud-dogs' ),
);
foreach ( $expected_taxonomies as $taxonomy => $types ) {
	check( "taxonomy $taxonomy registered", isset( $GLOBALS['rw_test']['taxonomies'][ $taxonomy ] ) );
	check( "taxonomy $taxonomy object types", isset( $GLOBALS['rw_test']['taxonomies'][ $taxonomy ] ) && $types === $GLOBALS['rw_test']['taxonomies'][ $taxonomy ]['types'] );
}

check( 'approvals live in the stud dog menu', 'edit.php?post_type=stud-dogs' === $GLOBALS['rw_test']['post_types']['approvals']['show_in_menu'] );
check( 'trimming proofs are not publicly queryable', false === $GLOBALS['rw_test']['post_types']['trimming-proofs']['publicly_queryable'] );
check( 'title taxonomy uses a safe query var', 'stud_dog_title' === $GLOBALS['rw_test']['taxonomies']['title']['args']['query_var'] );

/*
 * 2. All meta keys of the former theme are still handled.
 */
$legacy_post_meta = array(
	'stud-dogs'       => array( 'date_born', 'studbook_no', 'tests', 'withers', 'license', 'approval', 'kennel_before_name', 'father', 'father_approval', 'mother', 'mother_approval', 'grandfather_1', 'grandfather_1_approval', 'grandmother_1', 'grandmother_1_approval', 'grandfather_2', 'grandfather_2_approval', 'grandmother_2', 'grandmother_2_approval', 'greatgrandfather_1', 'greatgrandfather_1_approval', 'greatgrandmother_1', 'greatgrandmother_1_approval', 'greatgrandfather_2', 'greatgrandfather_2_approval', 'greatgrandmother_2', 'greatgrandmother_2_approval', 'greatgrandfather_3', 'greatgrandfather_3_approval', 'greatgrandmother_3', 'greatgrandmother_3_approval', 'greatgrandfather_4', 'greatgrandfather_4_approval', 'greatgrandmother_4', 'greatgrandmother_4_approval' ),
	'puppies'         => array( 'expected_puppies', 'date_of_birth', 'male_count', 'female_count', 'still_available_male', 'still_available_female', 'still_available_last_update', 'zip', 'city', 'father_name', 'father_desc', 'father_stud_dog_id', 'father_photo', 'mother_name', 'mother_desc', 'mother_photo' ),
	'dogs-to-place'   => array( 'date_of_birth', 'gender' ),
	'trimming-proofs' => array( 'address', 'email', 'phone', 'website' ),
	'dog-sitters'     => array( 'address', 'email', 'phone', 'mobile', 'website' ),
	'literature'      => array( 'subtitle', 'isbn', 'price' ),
);
foreach ( $legacy_post_meta as $post_type => $keys ) {
	$missing = array_diff( $keys, array_keys( Fields::for_post_type( $post_type ) ) );
	check( "meta keys of $post_type (missing: " . implode( ', ', $missing ) . ')', ! $missing );
}

$legacy_term_meta = array(
	'kennel'  => array( 'kennel_website', 'kennel_zip', 'kennel_hidden' ),
	'breeder' => array( 'breeder_website', 'breeder_phone', 'breeder_mobile', 'breeder_fax', 'breeder_email', 'breeder_address', 'breeder_kennel' ),
	'owner'   => array( 'owner_website', 'owner_phone', 'owner_email', 'owner_address' ),
);
$term_fields = Fields::term_fields();
foreach ( $legacy_term_meta as $taxonomy => $keys ) {
	check( "term meta of $taxonomy", ! array_diff( $keys, array_keys( $term_fields[ $taxonomy ] ) ) );
}

/*
 * 3. Sanitization.
 */
check( 'checkbox on', '1' === Fields::sanitize( 'on', array( 'type' => 'checkbox' ) ) );
check( 'checkbox off', '' === Fields::sanitize( '', array( 'type' => 'checkbox' ) ) );
check( 'checkbox off with explicit value', '0' === Fields::sanitize( '', array( 'type' => 'checkbox', 'unchecked' => '0' ) ) );
check( 'number with comma', '61.5' === Fields::sanitize( '61,5', array( 'type' => 'number' ) ) );
check( 'number invalid', '' === Fields::sanitize( 'abc', array( 'type' => 'number' ) ) );
check( 'date valid', '2020-05-01' === Fields::sanitize( '2020-05-01', array( 'type' => 'date' ) ) );
check( 'date invalid', '' === Fields::sanitize( '01.05.2020', array( 'type' => 'date' ) ) );
check( 'post id', '12' === Fields::sanitize( '12abc', array( 'type' => 'post' ) ) );
check( 'post id empty', '' === Fields::sanitize( '', array( 'type' => 'post' ) ) );
check( 'media ids (legacy leading comma)', '4,7' === Fields::sanitize( ',4,7,4,x', array( 'type' => 'media' ) ) );
check( 'textarea keeps newlines', "a\nb" === Fields::sanitize( "a\nb", array( 'type' => 'textarea' ) ) );
check( 'text strips tags', 'Arko' === Fields::sanitize( '<b>Arko</b>', array( 'type' => 'text' ) ) );
check( 'select keeps legacy value', 'weiblich' === Fields::sanitize( 'weiblich', array( 'type' => 'select', 'options' => array( 'female' => 'f' ) ) ) );

/*
 * 4. Pedigree layout.
 */
$rows = Pedigree::rows();
check( 'pedigree has 8 rows', 8 === count( $rows ) );
check( 'pedigree has 14 positions', 14 === count( Pedigree::positions() ) );
$cells = array();
foreach ( $rows as $row ) {
	foreach ( $row as $cell ) {
		$cells[] = $cell['position'];
	}
}
$positions = Pedigree::positions();
sort( $cells );
sort( $positions );
check( 'pedigree renders every position exactly once', $positions === $cells );
check( 'pedigree first row', array( 'father', 'grandfather_1', 'greatgrandfather_1' ) === array_column( $rows[0], 'position' ) );
check( 'pedigree fifth row', array( 'mother', 'grandfather_2', 'greatgrandfather_3' ) === array_column( $rows[4], 'position' ) );
check( 'pedigree last row', array( 'greatgrandmother_4' ) === array_column( $rows[7], 'position' ) );

/*
 * 5. Stud dog filter.
 */
$filter         = new StudDogFilter();
check( 'empty filter inactive', ! $filter->is_active() );
check( 'empty filter leaves args alone', array( 'post_type' => 'stud-dogs' ) === $filter->apply( array( 'post_type' => 'stud-dogs' ) ) );

$filter->hd       = 'a1';
$filter->exams    = array( 'ztp', 'bh' );
$filter->approval = true;
$args             = $filter->apply( array() );
check( 'filter active', $filter->is_active() );
check( 'filter relation', 'AND' === $args['tax_query']['relation'] );
check( 'filter hd clause', array( 'taxonomy' => 'hd', 'field' => 'slug', 'terms' => 'a1' ) === $args['tax_query'][0] );
check( 'filter exams need all', 'AND' === $args['tax_query'][1]['operator'] && array( 'ztp', 'bh' ) === $args['tax_query'][1]['terms'] );
check( 'filter approval meta', 'approval' === $args['meta_query'][0]['key'] && 'NOT IN' === $args['meta_query'][0]['compare'] );

$GLOBALS['rw_test']['terms'] = array();
$filter                      = new StudDogFilter();
$filter->zip                 = '28';
$args                        = $filter->apply( array() );
check( 'zip without kennels matches nothing', array( 0 ) === $args['tax_query'][0]['terms'] );

$GLOBALS['rw_test']['terms'] = array( (object) array( 'term_id' => 5 ), (object) array( 'term_id' => 9 ) );
$args                        = $filter->apply( array() );
check( 'zip limits kennels', array( 5, 9 ) === $args['tax_query'][0]['terms'] );

/*
 * 6. German translation keeps the URLs of the former theme.
 */
function rw_read_mo( $file ) {
	$data = file_get_contents( $file );
	$head = unpack( 'Vmagic/Vrevision/Vcount/Vorig/Vtrans', substr( $data, 0, 20 ) );
	$out  = array();
	for ( $i = 0; $i < $head['count']; $i++ ) {
		$o = unpack( 'Vlen/Voff', substr( $data, $head['orig'] + 8 * $i, 8 ) );
		$t = unpack( 'Vlen/Voff', substr( $data, $head['trans'] + 8 * $i, 8 ) );
		$out[ substr( $data, $o['off'], $o['len'] ) ] = substr( $data, $t['off'], $t['len'] );
	}
	return array( $head['magic'], $out );
}

list( $magic, $mo ) = rw_read_mo( RUDELWERK_DIR . 'languages/rudelwerk-de_DE.mo' );
check( 'mo magic', 0x950412de === $magic );

$legacy_slugs = array(
	'puppies'                => 'welpen',
	'dogs-to-place'          => 'vermittlungshunde',
	'stud-dogs'              => 'deckrueden',
	'breeding/approvals'     => 'zucht/koerberichte',
	'breedinglicenses'       => 'zuchtzulassungen',
	'exam-results'           => 'pruefungen',
	'shows'                  => 'ausstellungen',
	'trimming-proofs'        => 'trimmnachweise',
	'dog-sitters'            => 'tierbetreuungen',
	'literature'             => 'literatur',
	'vacationdeals'          => 'urlaubsangebote',
	'dogs-to-place-category' => 'vermittlungshunde-kategorie',
	'kennels'                => 'zwinger',
	'puppies-by-city'        => 'airedale-terrier-welpen-nach-staedten',
	'by-exam'                => 'nach-pruefung',
	'by-hd-value'            => 'nach-hd-wert',
	'by-ed-value'            => 'nach-ed-wert',
	'by-size'                => 'nach-groesse',
	'by-title'               => 'by-title',
);
foreach ( $legacy_slugs as $slug => $german ) {
	$key = "URL slug\x04" . $slug;
	check( "slug $slug => $german", isset( $mo[ $key ] ) && $german === $mo[ $key ] );
}
check( 'plural forms', isset( $mo[ "%d male\0%d males" ] ) && "%d Rüde\0%d Rüden" === $mo[ "%d male\0%d males" ] );

$php = include RUDELWERK_DIR . 'languages/rudelwerk-de_DE.l10n.php';
check( 'l10n.php plural forms header', 'nplurals=2; plural=n != 1;' === $php['plural-forms'] );
check( 'l10n.php slug', 'deckrueden' === $php['messages'][ "URL slug\x04stud-dogs" ] );
check( 'l10n.php plural', "%d Rüde\0%d Rüden" === $php['messages']['%d male'] );

/*
 * 7. Templates render.
 */
function esc_url( $url ) { return htmlspecialchars( (string) $url, ENT_QUOTES ); }
function esc_attr_e( $text ) { echo esc_attr( $text ); }
function esc_html_e( $text ) { echo esc_html( $text ); }
function number_format_i18n( $number ) { return (string) $number; }
function get_post_status( $id ) { return in_array( (int) $id, array( 10, 42 ), true ) ? 'publish' : false; }
function get_permalink( $post ) { return 'https://example.org/?p=' . ( is_object( $post ) ? $post->ID : (int) $post ); }
function locate_template() { return ''; }

$pedigree = array();
foreach ( Pedigree::positions() as $position ) {
	$pedigree[ $position ] = array( 'description' => '', 'approval' => 0 );
}
$pedigree['father'] = array( 'description' => "Arko <script>\nZTP", 'approval' => 42 );
$pedigree['greatgrandmother_4'] = array( 'description' => 'Bella', 'approval' => 7 );

$html = Rudelwerk\Frontend\Templates::html( 'parts/pedigree.php', array( 'pedigree' => $pedigree ) );
check( 'pedigree escapes descriptions', false !== strpos( $html, 'Arko &lt;script&gt;<br />' ) );
check( 'pedigree links published approvals', false !== strpos( $html, '?p=42' ) );
check( 'pedigree skips unpublished approvals', false === strpos( $html, '?p=7' ) );
check( 'pedigree has 8 rows', 8 === substr_count( $html, '<tr>' ) );
check( 'pedigree has 14 cells', 14 === substr_count( $html, '<td ' ) );

$html = Rudelwerk\Frontend\Templates::html(
	'lists/terms.php',
	array(
		'items'      => array(
			array( 'name' => 'A & B', 'url' => 'https://example.org/a', 'count' => 2, 'children' => array(
				array( 'name' => 'Child', 'url' => 'https://example.org/c', 'count' => 0, 'children' => array() ),
			) ),
		),
		'show_count' => true,
		'link_empty' => false,
	)
);
check( 'terms list nests children', 2 === substr_count( $html, '<ul' ) );
check( 'terms list escapes names', false !== strpos( $html, 'A &amp; B' ) );
check( 'terms list does not link empty terms', false === strpos( $html, 'example.org/c' ) );
check( 'terms list shows counts', false !== strpos( $html, '(2)' ) );

require __DIR__ . '/render.php';

fwrite( STDOUT, sprintf( "%d checks, %d failures\n", $checks, $failures ) );
exit( $failures ? 1 : 0 );

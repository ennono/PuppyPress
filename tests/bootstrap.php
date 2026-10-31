<?php
/**
 * Minimal WordPress stubs for the dependency free test runner.
 *
 * @package PuppyPress
 */

define( 'ABSPATH', __DIR__ . '/' );
define( 'PUPPYPRESS_DIR', dirname( __DIR__ ) . '/' );
define( 'PUPPYPRESS_URL', 'https://example.org/wp-content/plugins/puppypress/' );
define( 'PUPPYPRESS_VERSION', 'test' );
define( 'PUPPYPRESS_FILE', PUPPYPRESS_DIR . 'puppypress.php' );

$GLOBALS['pp_test'] = array(
	'filters'    => array(),
	'terms'      => array(),
	'post_types' => array(),
	'taxonomies' => array(),
);

function __( $text ) { return $text; }
function _x( $text ) { return $text; }
function _n( $single, $plural, $number ) { return 1 === (int) $number ? $single : $plural; }
function esc_html__( $text ) { return $text; }
function esc_attr__( $text ) { return $text; }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function esc_attr( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES ); }
function esc_url_raw( $url ) { return filter_var( $url, FILTER_VALIDATE_URL ) ? $url : ''; }
function sanitize_text_field( $text ) { return trim( preg_replace( '/[\r\n\t ]+/', ' ', strip_tags( (string) $text ) ) ); }
function sanitize_textarea_field( $text ) { return trim( strip_tags( (string) $text ) ); }
function sanitize_email( $email ) { return filter_var( $email, FILTER_VALIDATE_EMAIL ) ? $email : ''; }
function sanitize_title( $title ) { return strtolower( trim( preg_replace( '/[^A-Za-z0-9]+/', '-', (string) $title ), '-' ) ); }
function wp_unslash( $value ) { return is_array( $value ) ? array_map( 'wp_unslash', $value ) : stripslashes( (string) $value ); }
function absint( $value ) { return abs( (int) $value ); }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, (array) $args ); }
function wp_list_pluck( $list, $field, $index = null ) {
	$out = array();
	foreach ( $list as $item ) {
		$item = (array) $item;
		if ( null === $index ) {
			$out[] = $item[ $field ];
		} else {
			$out[ $item[ $index ] ] = $item[ $field ];
		}
	}
	return $out;
}
function is_wp_error( $thing ) { return false; }
function add_action() {}
function add_filter() {}
function apply_filters( $hook, $value ) { return $value; }
function register_post_type( $type, $args ) { $GLOBALS['pp_test']['post_types'][ $type ] = $args; }
function register_taxonomy( $taxonomy, $types, $args ) { $GLOBALS['pp_test']['taxonomies'][ $taxonomy ] = array( 'types' => $types, 'args' => $args ); }
function get_terms( $args ) { return $GLOBALS['pp_test']['terms']; }

spl_autoload_register(
	static function ( $class ) {
		if ( 0 === strpos( $class, 'PuppyPress\\' ) ) {
			require PUPPYPRESS_DIR . 'includes/' . str_replace( '\\', '/', substr( $class, strlen( 'PuppyPress\\' ) ) ) . '.php';
		}
	}
);

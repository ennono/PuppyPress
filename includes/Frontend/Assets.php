<?php
/**
 * Front end assets.
 *
 * @package PuppyPress
 */

namespace PuppyPress\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the stylesheet; it is only enqueued when PuppyPress output is rendered.
 */
final class Assets {

	const HANDLE = 'puppypress';

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'register' ) );
	}

	/**
	 * Registers the stylesheet and enqueues it on PuppyPress views.
	 */
	public function register() {
		wp_register_style( self::HANDLE, PUPPYPRESS_URL . 'assets/css/puppypress.css', array(), PUPPYPRESS_VERSION );

		if ( is_singular( array( 'stud-dogs', 'puppies', 'dogs-to-place' ) ) || is_post_type_archive( 'stud-dogs' ) ) {
			self::enqueue_frontend();
		}
	}

	/**
	 * Enqueues the stylesheet (also works late, e.g. from shortcodes).
	 */
	public static function enqueue_frontend() {
		/**
		 * Filters whether PuppyPress loads its stylesheet.
		 *
		 * @param bool $load Default true.
		 */
		if ( ! apply_filters( 'puppypress_load_styles', true ) ) {
			return;
		}

		if ( ! wp_style_is( self::HANDLE, 'registered' ) ) {
			wp_register_style( self::HANDLE, PUPPYPRESS_URL . 'assets/css/puppypress.css', array(), PUPPYPRESS_VERSION );
		}

		wp_enqueue_style( self::HANDLE );
	}
}

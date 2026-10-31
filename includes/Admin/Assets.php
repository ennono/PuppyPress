<?php
/**
 * Admin assets.
 *
 * @package PuppyPress
 */

namespace PuppyPress\Admin;

use PuppyPress\Content\Fields;

defined( 'ABSPATH' ) || exit;

/**
 * Loads admin styles and the media picker where needed.
 */
final class Assets {

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
	}

	/**
	 * Enqueues assets on PuppyPress edit screens.
	 */
	public function enqueue() {
		$screen = get_current_screen();

		if ( ! $screen ) {
			return;
		}

		$is_post_screen = 'post' === $screen->base && Fields::for_post_type( $screen->post_type );
		$is_term_screen = in_array( $screen->base, array( 'edit-tags', 'term' ), true ) && array_key_exists( $screen->taxonomy, Fields::term_fields() );

		if ( ! $is_post_screen && ! $is_term_screen ) {
			return;
		}

		wp_enqueue_style( 'puppypress-admin', PUPPYPRESS_URL . 'assets/css/admin.css', array(), PUPPYPRESS_VERSION );

		if ( ! $is_post_screen || ! wp_list_filter( Fields::for_post_type( $screen->post_type ), array( 'type' => 'media' ) ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( 'puppypress-admin-media', PUPPYPRESS_URL . 'assets/js/admin-media.js', array( 'jquery' ), PUPPYPRESS_VERSION, true );
		wp_localize_script(
			'puppypress-admin-media',
			'puppyPressMedia',
			array(
				'title'  => __( 'Choose or upload photos', 'puppypress' ),
				'button' => __( 'Use these photos', 'puppypress' ),
				'remove' => __( 'Remove', 'puppypress' ),
			)
		);
	}
}

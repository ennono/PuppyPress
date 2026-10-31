<?php
/**
 * Admin assets.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Admin;

use Rudelwerk\Content\Fields;

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
	 * Enqueues assets on Rudelwerk edit screens.
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

		wp_enqueue_style( 'rudelwerk-admin', RUDELWERK_URL . 'assets/css/admin.css', array(), RUDELWERK_VERSION );

		if ( ! $is_post_screen || ! wp_list_filter( Fields::for_post_type( $screen->post_type ), array( 'type' => 'media' ) ) ) {
			return;
		}

		wp_enqueue_media();
		wp_enqueue_script( 'rudelwerk-admin-media', RUDELWERK_URL . 'assets/js/admin-media.js', array( 'jquery' ), RUDELWERK_VERSION, true );
		wp_localize_script(
			'rudelwerk-admin-media',
			'rudelwerkMedia',
			array(
				'title'  => __( 'Choose or upload photos', 'rudelwerk' ),
				'button' => __( 'Use these photos', 'rudelwerk' ),
				'remove' => __( 'Remove', 'rudelwerk' ),
			)
		);
	}
}

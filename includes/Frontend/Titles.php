<?php
/**
 * Display titles of stud dogs and litters.
 *
 * @package PuppyPress
 */

namespace PuppyPress\Frontend;

use PuppyPress\Content\PostTypes;
use PuppyPress\Data\Dogs;

defined( 'ABSPATH' ) || exit;

/**
 * Adds the kennel to stud dog names and a prefix to litter titles on the front end.
 *
 * Disable with `add_filter( 'puppypress_display_titles', '__return_false' );`.
 */
final class Titles {

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_filter( 'the_title', array( $this, 'filter_title' ), 10, 2 );
		add_filter( 'single_post_title', array( $this, 'filter_single_post_title' ), 10, 2 );
	}

	/**
	 * Whether titles should be changed in the current request.
	 *
	 * @return bool
	 */
	private function enabled() {
		if ( is_admin() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return false;
		}

		/**
		 * Filters whether PuppyPress changes front end titles of stud dogs and litters.
		 *
		 * @param bool $enabled Default true.
		 */
		return (bool) apply_filters( 'puppypress_display_titles', true );
	}

	/**
	 * Filters `the_title`.
	 *
	 * @param string $title   Title.
	 * @param int    $post_id Post ID.
	 * @return string
	 */
	public function filter_title( $title, $post_id = 0 ) {
		if ( ! $post_id || ! $this->enabled() ) {
			return $title;
		}

		return $this->display_title( $title, (int) $post_id );
	}

	/**
	 * Filters `single_post_title` (document title).
	 *
	 * @param string   $title Title.
	 * @param \WP_Post $post  Post.
	 * @return string
	 */
	public function filter_single_post_title( $title, $post ) {
		if ( ! $post || ! $this->enabled() ) {
			return $title;
		}

		return $this->display_title( $title, (int) $post->ID );
	}

	/**
	 * Applies the display rules.
	 *
	 * @param string $title   Title.
	 * @param int    $post_id Post ID.
	 * @return string
	 */
	private function display_title( $title, $post_id ) {
		switch ( get_post_type( $post_id ) ) {
			case PostTypes::STUD_DOG:
				return Dogs::stud_dog_name( $post_id, $title );

			case PostTypes::PUPPIES:
				return Dogs::litter_title( $post_id, $title );
		}

		return $title;
	}
}

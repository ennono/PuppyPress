<?php
/**
 * Public template tags for themes.
 *
 * @package PuppyPress
 */

use PuppyPress\Content\Pedigree;
use PuppyPress\Data\Dogs;
use PuppyPress\Frontend\Assets;
use PuppyPress\Frontend\StudDogFilter;
use PuppyPress\Frontend\Templates;

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'puppypress_template' ) ) {
	/**
	 * Renders a PuppyPress template (theme overrides in `{theme}/puppypress/`).
	 *
	 * @param string $name Template name, e.g. "parts/pedigree.php".
	 * @param array  $args Variables, available as `$args` in the template.
	 */
	function puppypress_template( $name, array $args = array() ) {
		Templates::render( $name, $args );
	}
}

if ( ! function_exists( 'puppypress_display_kennel' ) ) {
	/**
	 * The visible kennel of a dog.
	 *
	 * @param int|WP_Post|null $post Post; defaults to the current post.
	 * @return WP_Term|null
	 */
	function puppypress_display_kennel( $post = null ) {
		$post = get_post( $post );

		return $post ? Dogs::display_kennel( $post->ID ) : null;
	}
}

if ( ! function_exists( 'puppypress_stud_dog_name' ) ) {
	/**
	 * Name of a stud dog including its kennel.
	 *
	 * @param int|WP_Post|null $post Post; defaults to the current post.
	 * @return string
	 */
	function puppypress_stud_dog_name( $post = null ) {
		$post = get_post( $post );

		return $post ? Dogs::stud_dog_name( $post->ID ) : '';
	}
}

if ( ! function_exists( 'puppypress_the_pedigree' ) ) {
	/**
	 * Echoes the pedigree table of a stud dog.
	 *
	 * @param int|WP_Post|null $post Post; defaults to the current post.
	 */
	function puppypress_the_pedigree( $post = null ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return;
		}

		Assets::enqueue_frontend();
		Templates::render( 'parts/pedigree.php', array( 'pedigree' => Pedigree::get( $post->ID ) ) );
	}
}

if ( ! function_exists( 'puppypress_stud_dog_filter_form' ) ) {
	/**
	 * Echoes the stud dog filter form.
	 *
	 * @param string $action Form action URL; empty for the current page.
	 */
	function puppypress_stud_dog_filter_form( $action = '' ) {
		Assets::enqueue_frontend();
		Templates::render( 'parts/stud-dog-filter.php', StudDogFilter::from_request()->form_data() + array( 'action' => $action ) );
	}
}

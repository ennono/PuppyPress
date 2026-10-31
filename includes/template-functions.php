<?php
/**
 * Public template tags for themes.
 *
 * @package Rudelwerk
 */

use Rudelwerk\Content\Pedigree;
use Rudelwerk\Data\Dogs;
use Rudelwerk\Frontend\Assets;
use Rudelwerk\Frontend\StudDogFilter;
use Rudelwerk\Frontend\Templates;

defined( 'ABSPATH' ) || exit;

if ( ! function_exists( 'rudelwerk_template' ) ) {
	/**
	 * Renders a Rudelwerk template (theme overrides in `{theme}/rudelwerk/`).
	 *
	 * @param string $name Template name, e.g. "parts/pedigree.php".
	 * @param array  $args Variables, available as `$args` in the template.
	 */
	function rudelwerk_template( $name, array $args = array() ) {
		Templates::render( $name, $args );
	}
}

if ( ! function_exists( 'rudelwerk_display_kennel' ) ) {
	/**
	 * The visible kennel of a dog.
	 *
	 * @param int|WP_Post|null $post Post; defaults to the current post.
	 * @return WP_Term|null
	 */
	function rudelwerk_display_kennel( $post = null ) {
		$post = get_post( $post );

		return $post ? Dogs::display_kennel( $post->ID ) : null;
	}
}

if ( ! function_exists( 'rudelwerk_stud_dog_name' ) ) {
	/**
	 * Name of a stud dog including its kennel.
	 *
	 * @param int|WP_Post|null $post Post; defaults to the current post.
	 * @return string
	 */
	function rudelwerk_stud_dog_name( $post = null ) {
		$post = get_post( $post );

		return $post ? Dogs::stud_dog_name( $post->ID ) : '';
	}
}

if ( ! function_exists( 'rudelwerk_the_pedigree' ) ) {
	/**
	 * Echoes the pedigree table of a stud dog.
	 *
	 * @param int|WP_Post|null $post Post; defaults to the current post.
	 */
	function rudelwerk_the_pedigree( $post = null ) {
		$post = get_post( $post );

		if ( ! $post ) {
			return;
		}

		Assets::enqueue_frontend();
		Templates::render( 'parts/pedigree.php', array( 'pedigree' => Pedigree::get( $post->ID ) ) );
	}
}

if ( ! function_exists( 'rudelwerk_stud_dog_filter_form' ) ) {
	/**
	 * Echoes the stud dog filter form.
	 *
	 * @param string $action Form action URL; empty for the current page.
	 */
	function rudelwerk_stud_dog_filter_form( $action = '' ) {
		Assets::enqueue_frontend();
		Templates::render( 'parts/stud-dog-filter.php', StudDogFilter::from_request()->form_data() + array( 'action' => $action ) );
	}
}

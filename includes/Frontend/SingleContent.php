<?php
/**
 * Detail views of stud dogs, litters and dogs to place.
 *
 * @package PuppyPress
 */

namespace PuppyPress\Frontend;

use PuppyPress\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Wraps the post content of single views with the dog's data.
 *
 * This works with any theme: the theme renders its normal single layout and
 * PuppyPress adds data, contacts, pedigree and photos to the content. The
 * markup comes from overridable templates in templates/single/.
 */
final class SingleContent {

	/**
	 * Post type => template.
	 *
	 * @var array<string, string>
	 */
	private $templates = array(
		PostTypes::STUD_DOG     => 'single/stud-dog.php',
		PostTypes::PUPPIES      => 'single/litter.php',
		PostTypes::DOG_TO_PLACE => 'single/dog-to-place.php',
	);

	/**
	 * Prevents recursion when templates call the_content filters.
	 *
	 * @var bool
	 */
	private $rendering = false;

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_filter( 'the_content', array( $this, 'filter_content' ), 20 );
	}

	/**
	 * Filters the content of the main single view.
	 *
	 * @param string $content Content (already processed by earlier filters).
	 * @return string
	 */
	public function filter_content( $content ) {
		if ( $this->rendering || ! is_singular( array_keys( $this->templates ) ) || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post = get_post();

		if ( ! $post || ! isset( $this->templates[ $post->post_type ] ) || (int) get_queried_object_id() !== (int) $post->ID ) {
			return $content;
		}

		/**
		 * Filters whether PuppyPress adds its data to the single view.
		 *
		 * @param bool     $enabled Default true.
		 * @param \WP_Post $post    Post.
		 */
		if ( ! apply_filters( 'puppypress_single_content', true, $post ) ) {
			return $content;
		}

		$this->rendering = true;
		Assets::enqueue_frontend();

		$html = Templates::html(
			$this->templates[ $post->post_type ],
			array(
				'post'    => $post,
				'content' => $content,
			)
		);

		$this->rendering = false;

		return $html;
	}
}

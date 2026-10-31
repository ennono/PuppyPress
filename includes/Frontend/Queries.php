<?php
/**
 * Adjustments of the main query on PuppyPress archives.
 *
 * @package PuppyPress
 */

namespace PuppyPress\Frontend;

use PuppyPress\Content\PostTypes;
use PuppyPress\Content\Taxonomies;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * Replaces the custom WP_Query loops of the former archive templates.
 */
final class Queries {

	/**
	 * Whether the filter form has already been printed.
	 *
	 * @var bool
	 */
	private $form_printed = false;

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'pre_get_posts', array( $this, 'adjust_main_query' ) );
		add_action( 'loop_start', array( $this, 'maybe_print_filter_form' ) );
		add_action( 'loop_no_results', array( $this, 'maybe_print_filter_form' ) );
	}

	/**
	 * Adjusts the main query.
	 *
	 * @param WP_Query $query Query.
	 */
	public function adjust_main_query( WP_Query $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		if ( $query->is_post_type_archive( PostTypes::STUD_DOG ) ) {
			$this->list_all_by_title( $query );

			$filter = StudDogFilter::from_request();
			if ( $filter->is_active() ) {
				foreach ( $filter->apply( array() ) as $key => $value ) {
					$query->set( $key, $value );
				}
			}
			return;
		}

		// Kennels are shared by several post types; kennel archives list stud dogs only.
		if ( $query->is_tax( Taxonomies::KENNEL ) ) {
			$query->set( 'post_type', PostTypes::STUD_DOG );
			$this->list_all_by_title( $query );
			return;
		}

		foreach ( array( Taxonomies::EXAM, Taxonomies::HD, Taxonomies::ED, Taxonomies::SIZE, Taxonomies::TITLE ) as $taxonomy ) {
			if ( $query->is_tax( $taxonomy ) ) {
				$this->list_all_by_title( $query );
				return;
			}
		}

		if ( $query->is_post_type_archive( PostTypes::PUPPIES ) || $query->is_tax( Taxonomies::PUPPIES_CITY ) ) {
			$query->set( 'posts_per_page', -1 );
			$query->set( 'meta_query', Shortcodes::zip_order_meta_query() ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$query->set(
				'orderby',
				array(
					'zip_clause' => 'ASC',
					'date'       => 'DESC',
				)
			);
		}
	}

	/**
	 * Shows all posts alphabetically.
	 *
	 * @param WP_Query $query Query.
	 */
	private function list_all_by_title( WP_Query $query ) {
		/**
		 * Filters whether stud dog archives show all dogs on one page.
		 *
		 * @param bool $all Default true.
		 */
		if ( apply_filters( 'puppypress_stud_dog_archive_show_all', true ) ) {
			$query->set( 'posts_per_page', -1 );
		}

		$query->set( 'orderby', 'title' );
		$query->set( 'order', 'ASC' );
	}

	/**
	 * Prints the filter form above the stud dog archive loop.
	 *
	 * @param WP_Query $query Query.
	 */
	public function maybe_print_filter_form( $query ) {
		if ( $this->form_printed || ! $query instanceof WP_Query || ! $query->is_main_query() || ! $query->is_post_type_archive( PostTypes::STUD_DOG ) ) {
			return;
		}

		/**
		 * Filters whether the filter form is printed on the stud dog archive.
		 *
		 * @param bool $print Default true.
		 */
		if ( ! apply_filters( 'puppypress_archive_filter_form', true ) ) {
			return;
		}

		$this->form_printed = true;

		Assets::enqueue_frontend();
		Templates::render( 'parts/stud-dog-filter.php', StudDogFilter::from_request()->form_data() + array( 'action' => get_post_type_archive_link( PostTypes::STUD_DOG ) ) );
	}
}

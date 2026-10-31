<?php
/**
 * Custom post types.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Content;

defined( 'ABSPATH' ) || exit;

/**
 * Registers all custom post types.
 *
 * The post type keys are identical to the ones of the former "Extra Child"
 * theme, so existing content keeps working without any migration.
 */
final class PostTypes {

	const STUD_DOG         = 'stud-dogs';
	const PUPPIES          = 'puppies';
	const DOG_TO_PLACE     = 'dogs-to-place';
	const APPROVAL         = 'approvals';
	const BREEDING_LICENSE = 'breeding-license';
	const RESULT           = 'results';
	const SHOW             = 'shows';
	const TRIMMING_PROOF   = 'trimming-proofs';
	const DOG_SITTER       = 'dog-sitters';
	const LITERATURE       = 'literature';
	const VACATION_DEAL    = 'vacation-deals';

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register' ), 5 );
	}

	/**
	 * Registers all post types.
	 */
	public function register() {
		foreach ( $this->definitions() as $post_type => $args ) {
			/**
			 * Filters the arguments of a Rudelwerk post type.
			 *
			 * @param array  $args      register_post_type() arguments.
			 * @param string $post_type Post type key.
			 */
			register_post_type( $post_type, apply_filters( 'rudelwerk_post_type_args', $args, $post_type ) );
		}
	}

	/**
	 * Returns the rewrite slug for a post type.
	 *
	 * @param string $post_type Post type key.
	 * @param string $default   Default (already translated) slug.
	 * @return string
	 */
	private function slug( $post_type, $default ) {
		/**
		 * Filters the rewrite slug of a Rudelwerk post type.
		 *
		 * @param string $slug      Slug.
		 * @param string $post_type Post type key.
		 */
		return (string) apply_filters( 'rudelwerk_post_type_slug', $default, $post_type );
	}

	/**
	 * Shared defaults.
	 *
	 * @return array
	 */
	private function defaults() {
		return array(
			'public'              => true,
			'hierarchical'        => false,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'post',
		);
	}

	/**
	 * Generic "not found" labels shared by all post types.
	 *
	 * @return array
	 */
	private function common_labels() {
		return array(
			'add_new'            => __( 'Add New', 'rudelwerk' ),
			'not_found'          => __( 'Not found', 'rudelwerk' ),
			'not_found_in_trash' => __( 'Not found in Trash', 'rudelwerk' ),
		);
	}

	/**
	 * Post type definitions.
	 *
	 * @return array<string, array>
	 */
	public function definitions() {
		$defaults = $this->defaults();
		$common   = $this->common_labels();

		$types = array();

		$types[ self::PUPPIES ] = array(
			'labels'        => $common + array(
				'name'           => _x( 'Puppies', 'post type general name', 'rudelwerk' ),
				'singular_name'  => _x( 'Litter', 'post type singular name', 'rudelwerk' ),
				'menu_name'      => __( 'Puppies', 'rudelwerk' ),
				'all_items'      => __( 'All litters', 'rudelwerk' ),
				'add_new_item'   => __( 'Add new litter', 'rudelwerk' ),
				'edit_item'      => __( 'Edit litter', 'rudelwerk' ),
				'new_item'       => __( 'New litter', 'rudelwerk' ),
				'view_item'      => __( 'View litter', 'rudelwerk' ),
				'search_items'   => __( 'Search litters', 'rudelwerk' ),
			),
			'description'   => __( 'Litters of puppies and expected litters.', 'rudelwerk' ),
			'menu_icon'     => 'dashicons-heart',
			'menu_position' => 5,
			'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'revisions' ),
			'has_archive'   => true,
			'rewrite'       => array(
				'slug'       => $this->slug( self::PUPPIES, _x( 'puppies', 'URL slug', 'rudelwerk' ) ),
				'with_front' => false,
			),
		);

		$types[ self::DOG_TO_PLACE ] = array(
			'labels'        => $common + array(
				'name'          => _x( 'Dogs to place', 'post type general name', 'rudelwerk' ),
				'singular_name' => _x( 'Dog to place', 'post type singular name', 'rudelwerk' ),
				'menu_name'     => __( 'Dogs to place', 'rudelwerk' ),
				'all_items'     => __( 'All dogs', 'rudelwerk' ),
				'add_new_item'  => __( 'Add new dog', 'rudelwerk' ),
				'edit_item'     => __( 'Edit dog', 'rudelwerk' ),
				'new_item'      => __( 'New dog', 'rudelwerk' ),
				'view_item'     => __( 'View dog', 'rudelwerk' ),
				'search_items'  => __( 'Search dogs', 'rudelwerk' ),
			),
			'description'   => __( 'Dogs looking for a new home.', 'rudelwerk' ),
			'menu_icon'     => 'dashicons-admin-home',
			'menu_position' => 6,
			'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'revisions' ),
			'has_archive'   => true,
			'rewrite'       => array(
				'slug'       => $this->slug( self::DOG_TO_PLACE, _x( 'dogs-to-place', 'URL slug', 'rudelwerk' ) ),
				'with_front' => false,
			),
		);

		$types[ self::STUD_DOG ] = array(
			'labels'        => $common + array(
				'name'          => _x( 'Stud dogs', 'post type general name', 'rudelwerk' ),
				'singular_name' => _x( 'Stud dog', 'post type singular name', 'rudelwerk' ),
				'menu_name'     => __( 'Stud dogs', 'rudelwerk' ),
				'all_items'     => __( 'All stud dogs', 'rudelwerk' ),
				'add_new_item'  => __( 'Add new stud dog', 'rudelwerk' ),
				'edit_item'     => __( 'Edit stud dog', 'rudelwerk' ),
				'new_item'      => __( 'New stud dog', 'rudelwerk' ),
				'view_item'     => __( 'View stud dog', 'rudelwerk' ),
				'search_items'  => __( 'Search stud dogs', 'rudelwerk' ),
			),
			'description'   => __( 'Stud dogs including pedigree.', 'rudelwerk' ),
			'menu_icon'     => 'dashicons-pets',
			'menu_position' => 7,
			'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'revisions' ),
			'has_archive'   => true,
			'rewrite'       => array(
				'slug'       => $this->slug( self::STUD_DOG, _x( 'stud-dogs', 'URL slug', 'rudelwerk' ) ),
				'with_front' => false,
			),
		);

		$types[ self::APPROVAL ] = array(
			'labels'       => $common + array(
				'name'          => _x( 'Approvals', 'post type general name', 'rudelwerk' ),
				'singular_name' => _x( 'Approval', 'post type singular name', 'rudelwerk' ),
				'menu_name'     => __( 'Approvals', 'rudelwerk' ),
				'all_items'     => __( 'Approvals', 'rudelwerk' ),
				'add_new_item'  => __( 'Add new approval', 'rudelwerk' ),
				'edit_item'     => __( 'Edit approval', 'rudelwerk' ),
				'new_item'      => __( 'New approval', 'rudelwerk' ),
				'view_item'     => __( 'View approval', 'rudelwerk' ),
				'search_items'  => __( 'Search approvals', 'rudelwerk' ),
			),
			'description'  => __( 'Approval reports of breeding dogs.', 'rudelwerk' ),
			'show_in_menu' => 'edit.php?post_type=' . self::STUD_DOG,
			'supports'     => array( 'title', 'editor', 'author', 'revisions' ),
			'rewrite'      => array(
				'slug'       => $this->slug( self::APPROVAL, _x( 'breeding/approvals', 'URL slug', 'rudelwerk' ) ),
				'with_front' => false,
			),
		);

		$types[ self::BREEDING_LICENSE ] = array(
			'labels'       => $common + array(
				'name'          => _x( 'Breeding licenses', 'post type general name', 'rudelwerk' ),
				'singular_name' => _x( 'Breeding license', 'post type singular name', 'rudelwerk' ),
				'menu_name'     => __( 'Breeding licenses', 'rudelwerk' ),
				'all_items'     => __( 'Breeding licenses', 'rudelwerk' ),
				'add_new_item'  => __( 'Add new breeding license', 'rudelwerk' ),
				'edit_item'     => __( 'Edit breeding license', 'rudelwerk' ),
				'new_item'      => __( 'New breeding license', 'rudelwerk' ),
				'view_item'     => __( 'View breeding license', 'rudelwerk' ),
				'search_items'  => __( 'Search breeding licenses', 'rudelwerk' ),
			),
			'description'  => __( 'Breeding licenses.', 'rudelwerk' ),
			'show_in_menu' => 'edit.php?post_type=' . self::STUD_DOG,
			'supports'     => array( 'title', 'editor', 'author', 'revisions' ),
			'has_archive'  => true,
			'rewrite'      => array(
				'slug'       => $this->slug( self::BREEDING_LICENSE, _x( 'breedinglicenses', 'URL slug', 'rudelwerk' ) ),
				'with_front' => false,
			),
		);

		$types[ self::RESULT ] = array(
			'labels'        => $common + array(
				'name'          => _x( 'Results', 'post type general name', 'rudelwerk' ),
				'singular_name' => _x( 'Result', 'post type singular name', 'rudelwerk' ),
				'menu_name'     => __( 'Results', 'rudelwerk' ),
				'all_items'     => __( 'All results', 'rudelwerk' ),
				'add_new_item'  => __( 'Add new result', 'rudelwerk' ),
				'edit_item'     => __( 'Edit result', 'rudelwerk' ),
				'new_item'      => __( 'New result', 'rudelwerk' ),
				'view_item'     => __( 'View result', 'rudelwerk' ),
				'search_items'  => __( 'Search results', 'rudelwerk' ),
			),
			'description'   => __( 'Results of exams and events.', 'rudelwerk' ),
			'menu_icon'     => 'dashicons-awards',
			'menu_position' => 8,
			'supports'      => array( 'title', 'editor', 'author', 'revisions' ),
			'rewrite'       => array(
				// German translation is "pruefungen", which keeps the URLs of the former theme.
				'slug'       => $this->slug( self::RESULT, _x( 'exam-results', 'URL slug', 'rudelwerk' ) ),
				'with_front' => false,
			),
		);

		$types[ self::SHOW ] = array(
			'labels'        => $common + array(
				'name'          => _x( 'Shows', 'post type general name', 'rudelwerk' ),
				'singular_name' => _x( 'Show', 'post type singular name', 'rudelwerk' ),
				'menu_name'     => __( 'Shows', 'rudelwerk' ),
				'all_items'     => __( 'All shows', 'rudelwerk' ),
				'add_new_item'  => __( 'Add new show', 'rudelwerk' ),
				'edit_item'     => __( 'Edit show', 'rudelwerk' ),
				'new_item'      => __( 'New show', 'rudelwerk' ),
				'view_item'     => __( 'View show', 'rudelwerk' ),
				'search_items'  => __( 'Search shows', 'rudelwerk' ),
			),
			'description'   => __( 'Dog shows.', 'rudelwerk' ),
			'menu_icon'     => 'dashicons-star-filled',
			'menu_position' => 9,
			'supports'      => array( 'title', 'editor', 'author', 'revisions' ),
			'rewrite'       => array(
				'slug'       => $this->slug( self::SHOW, _x( 'shows', 'URL slug', 'rudelwerk' ) ),
				'with_front' => false,
			),
		);

		$types[ self::TRIMMING_PROOF ] = array(
			'labels'             => $common + array(
				'name'          => _x( 'Trimming proofs', 'post type general name', 'rudelwerk' ),
				'singular_name' => _x( 'Trimming proof', 'post type singular name', 'rudelwerk' ),
				'menu_name'     => __( 'Trimming proofs', 'rudelwerk' ),
				'all_items'     => __( 'All trimming proofs', 'rudelwerk' ),
				'add_new_item'  => __( 'Add new trimming proof', 'rudelwerk' ),
				'edit_item'     => __( 'Edit trimming proof', 'rudelwerk' ),
				'new_item'      => __( 'New trimming proof', 'rudelwerk' ),
				'view_item'     => __( 'View trimming proof', 'rudelwerk' ),
				'search_items'  => __( 'Search trimming proofs', 'rudelwerk' ),
			),
			'description'        => __( 'Groomers with a trimming proof.', 'rudelwerk' ),
			'menu_icon'          => 'dashicons-admin-tools',
			'menu_position'      => 5,
			'supports'           => array( 'title', 'author', 'editor' ),
			'publicly_queryable' => false,
			'rewrite'            => array(
				'slug'       => $this->slug( self::TRIMMING_PROOF, _x( 'trimming-proofs', 'URL slug', 'rudelwerk' ) ),
				'with_front' => false,
			),
		);

		$types[ self::DOG_SITTER ] = array(
			'labels'             => $common + array(
				'name'          => _x( 'Dog sitters', 'post type general name', 'rudelwerk' ),
				'singular_name' => _x( 'Dog sitter', 'post type singular name', 'rudelwerk' ),
				'menu_name'     => __( 'Dog sitters', 'rudelwerk' ),
				'all_items'     => __( 'All dog sitters', 'rudelwerk' ),
				'add_new_item'  => __( 'Add new dog sitter', 'rudelwerk' ),
				'edit_item'     => __( 'Edit dog sitter', 'rudelwerk' ),
				'new_item'      => __( 'New dog sitter', 'rudelwerk' ),
				'view_item'     => __( 'View dog sitter', 'rudelwerk' ),
				'search_items'  => __( 'Search dog sitters', 'rudelwerk' ),
			),
			'description'        => __( 'Dog sitters and boarding.', 'rudelwerk' ),
			'menu_icon'          => 'dashicons-groups',
			'menu_position'      => 5,
			'supports'           => array( 'title', 'author', 'editor' ),
			'publicly_queryable' => false,
			'rewrite'            => array(
				'slug'       => $this->slug( self::DOG_SITTER, _x( 'dog-sitters', 'URL slug', 'rudelwerk' ) ),
				'with_front' => false,
			),
		);

		$types[ self::LITERATURE ] = array(
			'labels'             => $common + array(
				'name'          => _x( 'Literature', 'post type general name', 'rudelwerk' ),
				'singular_name' => _x( 'Book', 'post type singular name', 'rudelwerk' ),
				'menu_name'     => __( 'Literature', 'rudelwerk' ),
				'all_items'     => __( 'All literature', 'rudelwerk' ),
				'add_new_item'  => __( 'Add new book', 'rudelwerk' ),
				'edit_item'     => __( 'Edit book', 'rudelwerk' ),
				'new_item'      => __( 'New book', 'rudelwerk' ),
				'view_item'     => __( 'View book', 'rudelwerk' ),
				'search_items'  => __( 'Search literature', 'rudelwerk' ),
			),
			'description'        => __( 'Recommended literature.', 'rudelwerk' ),
			'menu_icon'          => 'dashicons-book',
			'menu_position'      => 5,
			'supports'           => array( 'title', 'author', 'editor', 'thumbnail' ),
			'publicly_queryable' => false,
			'rewrite'            => array(
				'slug'       => $this->slug( self::LITERATURE, _x( 'literature', 'URL slug', 'rudelwerk' ) ),
				'with_front' => false,
			),
		);

		$types[ self::VACATION_DEAL ] = array(
			'labels'        => $common + array(
				'name'          => _x( 'Vacation deals', 'post type general name', 'rudelwerk' ),
				'singular_name' => _x( 'Vacation deal', 'post type singular name', 'rudelwerk' ),
				'menu_name'     => __( 'Vacation deals', 'rudelwerk' ),
				'all_items'     => __( 'All vacation deals', 'rudelwerk' ),
				'add_new_item'  => __( 'Add new vacation deal', 'rudelwerk' ),
				'edit_item'     => __( 'Edit vacation deal', 'rudelwerk' ),
				'new_item'      => __( 'New vacation deal', 'rudelwerk' ),
				'view_item'     => __( 'View vacation deal', 'rudelwerk' ),
				'search_items'  => __( 'Search vacation deals', 'rudelwerk' ),
			),
			'description'   => __( 'Dog friendly vacation deals.', 'rudelwerk' ),
			'menu_icon'     => 'dashicons-palmtree',
			'menu_position' => 5,
			'supports'      => array( 'title', 'author', 'editor', 'thumbnail' ),
			'has_archive'   => true,
			'rewrite'       => array(
				'slug'       => $this->slug( self::VACATION_DEAL, _x( 'vacationdeals', 'URL slug', 'rudelwerk' ) ),
				'with_front' => false,
			),
		);

		foreach ( $types as $key => $args ) {
			$types[ $key ] = array_merge( $defaults, $args );
		}

		return $types;
	}
}

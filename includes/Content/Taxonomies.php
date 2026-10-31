<?php
/**
 * Custom taxonomies.
 *
 * @package PuppyPress
 */

namespace PuppyPress\Content;

defined( 'ABSPATH' ) || exit;

/**
 * Registers all custom taxonomies.
 *
 * Taxonomy keys are identical to the ones of the former "Extra Child" theme.
 */
final class Taxonomies {

	const PLACEMENT_CATEGORY  = 'placement-category';
	const KENNEL              = 'kennel';
	const BREEDER             = 'breeder';
	const OWNER               = 'owner';
	const EVENT               = 'event';
	const PERIOD              = 'period';
	const TRIMMING_ZIP        = 'zip';
	const TRIMMING_CITY       = 'city';
	const DOG_SITTER_TYPE     = 'type';
	const DOG_SITTER_ZIP      = 'dog-sitters-zip';
	const DOG_SITTER_CITY     = 'dog-sitters-city';
	const LITERATURE_CATEGORY = 'literature-category';
	const LITERATURE_AUTHOR   = 'literature-author';
	const PUPPIES_CITY        = 'puppies-city';
	const EXAM                = 'exam';
	const HD                  = 'hd';
	const ED                  = 'ed';
	const SIZE                = 'size';
	const TITLE               = 'title';

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register' ), 5 );
	}

	/**
	 * Registers all taxonomies.
	 */
	public function register() {
		foreach ( $this->definitions() as $taxonomy => $definition ) {
			$args = array_merge(
				array(
					'hierarchical'      => true,
					'show_ui'           => true,
					'show_admin_column' => true,
					'query_var'         => true,
				),
				$definition['args']
			);

			/**
			 * Filters the object types a PuppyPress taxonomy is attached to.
			 *
			 * @param string[] $object_types Post types.
			 * @param string   $taxonomy     Taxonomy key.
			 */
			$object_types = apply_filters( 'puppypress_taxonomy_object_types', $definition['object_types'], $taxonomy );

			/**
			 * Filters the arguments of a PuppyPress taxonomy.
			 *
			 * @param array  $args     register_taxonomy() arguments.
			 * @param string $taxonomy Taxonomy key.
			 */
			register_taxonomy( $taxonomy, $object_types, apply_filters( 'puppypress_taxonomy_args', $args, $taxonomy ) );
		}
	}

	/**
	 * Builds a rewrite argument with a filterable slug.
	 *
	 * @param string $taxonomy Taxonomy key.
	 * @param string $slug     Default (already translated) slug.
	 * @return array
	 */
	private function rewrite( $taxonomy, $slug ) {
		return array(
			/**
			 * Filters the rewrite slug of a PuppyPress taxonomy.
			 *
			 * @param string $slug     Slug.
			 * @param string $taxonomy Taxonomy key.
			 */
			'slug'       => (string) apply_filters( 'puppypress_taxonomy_slug', $slug, $taxonomy ),
			'with_front' => false,
		);
	}

	/**
	 * Taxonomy definitions.
	 *
	 * @return array<string, array{object_types: string[], args: array}>
	 */
	public function definitions() {
		return array(
			self::PLACEMENT_CATEGORY  => array(
				'object_types' => array( PostTypes::DOG_TO_PLACE ),
				'args'         => array(
					'labels'  => array(
						'name'              => _x( 'Placement categories', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'Placement category', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'Categories', 'puppypress' ),
						'search_items'      => __( 'Search categories', 'puppypress' ),
						'all_items'         => __( 'All categories', 'puppypress' ),
						'parent_item'       => __( 'Parent category', 'puppypress' ),
						'parent_item_colon' => __( 'Parent category:', 'puppypress' ),
						'edit_item'         => __( 'Edit category', 'puppypress' ),
						'update_item'       => __( 'Update category', 'puppypress' ),
						'add_new_item'      => __( 'Add new category', 'puppypress' ),
						'new_item_name'     => __( 'New category name', 'puppypress' ),
					),
					'rewrite' => $this->rewrite( self::PLACEMENT_CATEGORY, _x( 'dogs-to-place-category', 'URL slug', 'puppypress' ) ),
				),
			),

			self::KENNEL              => array(
				'object_types' => array( PostTypes::STUD_DOG, PostTypes::APPROVAL, PostTypes::BREEDING_LICENSE, PostTypes::PUPPIES ),
				'args'         => array(
					'labels'  => array(
						'name'              => _x( 'Kennels', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'Kennel', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'Kennels', 'puppypress' ),
						'search_items'      => __( 'Search kennels', 'puppypress' ),
						'all_items'         => __( 'All kennels', 'puppypress' ),
						'parent_item'       => __( 'Parent kennel', 'puppypress' ),
						'parent_item_colon' => __( 'Parent kennel:', 'puppypress' ),
						'edit_item'         => __( 'Edit kennel', 'puppypress' ),
						'update_item'       => __( 'Update kennel', 'puppypress' ),
						'add_new_item'      => __( 'Add new kennel', 'puppypress' ),
						'new_item_name'     => __( 'New kennel name', 'puppypress' ),
					),
					'rewrite' => $this->rewrite( self::KENNEL, _x( 'kennels', 'URL slug', 'puppypress' ) ),
				),
			),

			self::BREEDER             => array(
				'object_types' => array( PostTypes::STUD_DOG, PostTypes::PUPPIES ),
				'args'         => array(
					'labels' => array(
						'name'              => _x( 'Breeders', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'Breeder', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'Breeders', 'puppypress' ),
						'search_items'      => __( 'Search breeders', 'puppypress' ),
						'all_items'         => __( 'All breeders', 'puppypress' ),
						'parent_item'       => __( 'Parent breeder', 'puppypress' ),
						'parent_item_colon' => __( 'Parent breeder:', 'puppypress' ),
						'edit_item'         => __( 'Edit breeder', 'puppypress' ),
						'update_item'       => __( 'Update breeder', 'puppypress' ),
						'add_new_item'      => __( 'Add new breeder', 'puppypress' ),
						'new_item_name'     => __( 'New breeder name', 'puppypress' ),
					),
				),
			),

			self::OWNER               => array(
				'object_types' => array( PostTypes::STUD_DOG ),
				'args'         => array(
					'labels' => array(
						'name'              => _x( 'Owners', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'Owner', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'Owners', 'puppypress' ),
						'search_items'      => __( 'Search owners', 'puppypress' ),
						'all_items'         => __( 'All owners', 'puppypress' ),
						'parent_item'       => __( 'Parent owner', 'puppypress' ),
						'parent_item_colon' => __( 'Parent owner:', 'puppypress' ),
						'edit_item'         => __( 'Edit owner', 'puppypress' ),
						'update_item'       => __( 'Update owner', 'puppypress' ),
						'add_new_item'      => __( 'Add new owner', 'puppypress' ),
						'new_item_name'     => __( 'New owner name', 'puppypress' ),
					),
				),
			),

			self::EVENT               => array(
				'object_types' => array( PostTypes::RESULT ),
				'args'         => array(
					'labels' => array(
						'name'              => _x( 'Events', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'Event', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'Events', 'puppypress' ),
						'search_items'      => __( 'Search events', 'puppypress' ),
						'all_items'         => __( 'All events', 'puppypress' ),
						'parent_item'       => __( 'Parent event', 'puppypress' ),
						'parent_item_colon' => __( 'Parent event:', 'puppypress' ),
						'edit_item'         => __( 'Edit event', 'puppypress' ),
						'update_item'       => __( 'Update event', 'puppypress' ),
						'add_new_item'      => __( 'Add new event', 'puppypress' ),
						'new_item_name'     => __( 'New event name', 'puppypress' ),
					),
				),
			),

			self::PERIOD              => array(
				'object_types' => array( PostTypes::SHOW ),
				'args'         => array(
					'labels' => array(
						'name'              => _x( 'Periods', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'Period', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'Periods', 'puppypress' ),
						'search_items'      => __( 'Search periods', 'puppypress' ),
						'all_items'         => __( 'All periods', 'puppypress' ),
						'parent_item'       => __( 'Parent period', 'puppypress' ),
						'parent_item_colon' => __( 'Parent period:', 'puppypress' ),
						'edit_item'         => __( 'Edit period', 'puppypress' ),
						'update_item'       => __( 'Update period', 'puppypress' ),
						'add_new_item'      => __( 'Add new period', 'puppypress' ),
						'new_item_name'     => __( 'New period name', 'puppypress' ),
					),
				),
			),

			self::TRIMMING_ZIP        => array(
				'object_types' => array( PostTypes::TRIMMING_PROOF ),
				'args'         => array( 'labels' => $this->zip_labels() ),
			),

			self::TRIMMING_CITY       => array(
				'object_types' => array( PostTypes::TRIMMING_PROOF ),
				'args'         => array(
					'labels'             => $this->city_labels(),
					'publicly_queryable' => false,
				),
			),

			self::DOG_SITTER_TYPE     => array(
				'object_types' => array( PostTypes::DOG_SITTER ),
				'args'         => array(
					'labels'             => array(
						'name'              => _x( 'Types', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'Type', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'Types', 'puppypress' ),
						'search_items'      => __( 'Search types', 'puppypress' ),
						'all_items'         => __( 'All types', 'puppypress' ),
						'parent_item'       => __( 'Parent type', 'puppypress' ),
						'parent_item_colon' => __( 'Parent type:', 'puppypress' ),
						'edit_item'         => __( 'Edit type', 'puppypress' ),
						'update_item'       => __( 'Update type', 'puppypress' ),
						'add_new_item'      => __( 'Add new type', 'puppypress' ),
						'new_item_name'     => __( 'New type name', 'puppypress' ),
					),
					'publicly_queryable' => false,
					// "type" is a reserved query variable in WordPress.
					'query_var'          => 'dog_sitter_type',
				),
			),

			self::DOG_SITTER_ZIP      => array(
				'object_types' => array( PostTypes::DOG_SITTER ),
				'args'         => array(
					'labels'             => $this->zip_labels(),
					'publicly_queryable' => false,
				),
			),

			self::DOG_SITTER_CITY     => array(
				'object_types' => array( PostTypes::DOG_SITTER ),
				'args'         => array(
					'labels'             => $this->city_labels(),
					'publicly_queryable' => false,
				),
			),

			self::LITERATURE_CATEGORY => array(
				'object_types' => array( PostTypes::LITERATURE ),
				'args'         => array(
					'labels'             => array(
						'name'              => _x( 'Literature categories', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'Literature category', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'Categories', 'puppypress' ),
						'search_items'      => __( 'Search categories', 'puppypress' ),
						'all_items'         => __( 'All categories', 'puppypress' ),
						'parent_item'       => __( 'Parent category', 'puppypress' ),
						'parent_item_colon' => __( 'Parent category:', 'puppypress' ),
						'edit_item'         => __( 'Edit category', 'puppypress' ),
						'update_item'       => __( 'Update category', 'puppypress' ),
						'add_new_item'      => __( 'Add new category', 'puppypress' ),
						'new_item_name'     => __( 'New category name', 'puppypress' ),
					),
					'publicly_queryable' => false,
				),
			),

			self::LITERATURE_AUTHOR   => array(
				'object_types' => array( PostTypes::LITERATURE ),
				'args'         => array(
					'labels'             => array(
						'name'              => _x( 'Authors', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'Author', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'Authors', 'puppypress' ),
						'search_items'      => __( 'Search authors', 'puppypress' ),
						'all_items'         => __( 'All authors', 'puppypress' ),
						'parent_item'       => __( 'Parent author', 'puppypress' ),
						'parent_item_colon' => __( 'Parent author:', 'puppypress' ),
						'edit_item'         => __( 'Edit author', 'puppypress' ),
						'update_item'       => __( 'Update author', 'puppypress' ),
						'add_new_item'      => __( 'Add new author', 'puppypress' ),
						'new_item_name'     => __( 'New author name', 'puppypress' ),
					),
					'publicly_queryable' => false,
				),
			),

			self::PUPPIES_CITY        => array(
				'object_types' => array( PostTypes::PUPPIES ),
				'args'         => array(
					'labels'  => $this->city_labels(),
					'rewrite' => $this->rewrite( self::PUPPIES_CITY, _x( 'puppies-by-city', 'URL slug', 'puppypress' ) ),
				),
			),

			self::EXAM                => array(
				'object_types' => array( PostTypes::STUD_DOG ),
				'args'         => array(
					'labels'  => array(
						'name'              => _x( 'Exams', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'Exam', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'Exams', 'puppypress' ),
						'search_items'      => __( 'Search exams', 'puppypress' ),
						'all_items'         => __( 'All exams', 'puppypress' ),
						'parent_item'       => __( 'Parent exam', 'puppypress' ),
						'parent_item_colon' => __( 'Parent exam:', 'puppypress' ),
						'edit_item'         => __( 'Edit exam', 'puppypress' ),
						'update_item'       => __( 'Update exam', 'puppypress' ),
						'add_new_item'      => __( 'Add new exam', 'puppypress' ),
						'new_item_name'     => __( 'New exam name', 'puppypress' ),
					),
					'rewrite' => $this->rewrite( self::EXAM, _x( 'by-exam', 'URL slug', 'puppypress' ) ),
				),
			),

			self::HD                  => array(
				'object_types' => array( PostTypes::STUD_DOG ),
				'args'         => array(
					'labels'  => array(
						'name'              => _x( 'HD values', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'HD value', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'HD', 'puppypress' ),
						'search_items'      => __( 'Search HD values', 'puppypress' ),
						'all_items'         => __( 'All HD values', 'puppypress' ),
						'parent_item'       => __( 'Parent HD value', 'puppypress' ),
						'parent_item_colon' => __( 'Parent HD value:', 'puppypress' ),
						'edit_item'         => __( 'Edit HD value', 'puppypress' ),
						'update_item'       => __( 'Update HD value', 'puppypress' ),
						'add_new_item'      => __( 'Add new HD value', 'puppypress' ),
						'new_item_name'     => __( 'New HD value', 'puppypress' ),
					),
					'rewrite' => $this->rewrite( self::HD, _x( 'by-hd-value', 'URL slug', 'puppypress' ) ),
				),
			),

			self::ED                  => array(
				'object_types' => array( PostTypes::STUD_DOG ),
				'args'         => array(
					'labels'  => array(
						'name'              => _x( 'ED values', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'ED value', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'ED', 'puppypress' ),
						'search_items'      => __( 'Search ED values', 'puppypress' ),
						'all_items'         => __( 'All ED values', 'puppypress' ),
						'parent_item'       => __( 'Parent ED value', 'puppypress' ),
						'parent_item_colon' => __( 'Parent ED value:', 'puppypress' ),
						'edit_item'         => __( 'Edit ED value', 'puppypress' ),
						'update_item'       => __( 'Update ED value', 'puppypress' ),
						'add_new_item'      => __( 'Add new ED value', 'puppypress' ),
						'new_item_name'     => __( 'New ED value', 'puppypress' ),
					),
					'rewrite' => $this->rewrite( self::ED, _x( 'by-ed-value', 'URL slug', 'puppypress' ) ),
				),
			),

			self::SIZE                => array(
				'object_types' => array( PostTypes::STUD_DOG ),
				'args'         => array(
					'labels'  => array(
						'name'              => _x( 'Sizes', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'Size', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'Sizes', 'puppypress' ),
						'search_items'      => __( 'Search sizes', 'puppypress' ),
						'all_items'         => __( 'All sizes', 'puppypress' ),
						'parent_item'       => __( 'Parent size', 'puppypress' ),
						'parent_item_colon' => __( 'Parent size:', 'puppypress' ),
						'edit_item'         => __( 'Edit size', 'puppypress' ),
						'update_item'       => __( 'Update size', 'puppypress' ),
						'add_new_item'      => __( 'Add new size', 'puppypress' ),
						'new_item_name'     => __( 'New size', 'puppypress' ),
					),
					'rewrite' => $this->rewrite( self::SIZE, _x( 'by-size', 'URL slug', 'puppypress' ) ),
				),
			),

			self::TITLE               => array(
				'object_types' => array( PostTypes::STUD_DOG ),
				'args'         => array(
					'labels'    => array(
						'name'              => _x( 'Titles', 'taxonomy general name', 'puppypress' ),
						'singular_name'     => _x( 'Title', 'taxonomy singular name', 'puppypress' ),
						'menu_name'         => __( 'Titles', 'puppypress' ),
						'search_items'      => __( 'Search titles', 'puppypress' ),
						'all_items'         => __( 'All titles', 'puppypress' ),
						'parent_item'       => __( 'Parent title', 'puppypress' ),
						'parent_item_colon' => __( 'Parent title:', 'puppypress' ),
						'edit_item'         => __( 'Edit title', 'puppypress' ),
						'update_item'       => __( 'Update title', 'puppypress' ),
						'add_new_item'      => __( 'Add new title', 'puppypress' ),
						'new_item_name'     => __( 'New title', 'puppypress' ),
					),
					// "title" is a WP_Query variable (post title); a custom query var keeps term archives working.
					'query_var' => 'stud_dog_title',
					'rewrite'   => $this->rewrite( self::TITLE, _x( 'by-title', 'URL slug', 'puppypress' ) ),
				),
			),
		);
	}

	/**
	 * Labels shared by all ZIP code taxonomies.
	 *
	 * @return array
	 */
	private function zip_labels() {
		return array(
			'name'              => _x( 'ZIP codes', 'taxonomy general name', 'puppypress' ),
			'singular_name'     => _x( 'ZIP code', 'taxonomy singular name', 'puppypress' ),
			'menu_name'         => __( 'ZIP codes', 'puppypress' ),
			'search_items'      => __( 'Search ZIP codes', 'puppypress' ),
			'all_items'         => __( 'All ZIP codes', 'puppypress' ),
			'parent_item'       => __( 'Parent ZIP code', 'puppypress' ),
			'parent_item_colon' => __( 'Parent ZIP code:', 'puppypress' ),
			'edit_item'         => __( 'Edit ZIP code', 'puppypress' ),
			'update_item'       => __( 'Update ZIP code', 'puppypress' ),
			'add_new_item'      => __( 'Add new ZIP code', 'puppypress' ),
			'new_item_name'     => __( 'New ZIP code', 'puppypress' ),
		);
	}

	/**
	 * Labels shared by all city taxonomies.
	 *
	 * @return array
	 */
	private function city_labels() {
		return array(
			'name'              => _x( 'Cities', 'taxonomy general name', 'puppypress' ),
			'singular_name'     => _x( 'City', 'taxonomy singular name', 'puppypress' ),
			'menu_name'         => __( 'Cities', 'puppypress' ),
			'search_items'      => __( 'Search cities', 'puppypress' ),
			'all_items'         => __( 'All cities', 'puppypress' ),
			'parent_item'       => __( 'Parent city', 'puppypress' ),
			'parent_item_colon' => __( 'Parent city:', 'puppypress' ),
			'edit_item'         => __( 'Edit city', 'puppypress' ),
			'update_item'       => __( 'Update city', 'puppypress' ),
			'add_new_item'      => __( 'Add new city', 'puppypress' ),
			'new_item_name'     => __( 'New city name', 'puppypress' ),
		);
	}
}

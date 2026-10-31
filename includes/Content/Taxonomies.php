<?php
/**
 * Custom taxonomies.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Content;

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
			 * Filters the object types a Rudelwerk taxonomy is attached to.
			 *
			 * @param string[] $object_types Post types.
			 * @param string   $taxonomy     Taxonomy key.
			 */
			$object_types = apply_filters( 'rudelwerk_taxonomy_object_types', $definition['object_types'], $taxonomy );

			/**
			 * Filters the arguments of a Rudelwerk taxonomy.
			 *
			 * @param array  $args     register_taxonomy() arguments.
			 * @param string $taxonomy Taxonomy key.
			 */
			register_taxonomy( $taxonomy, $object_types, apply_filters( 'rudelwerk_taxonomy_args', $args, $taxonomy ) );
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
			 * Filters the rewrite slug of a Rudelwerk taxonomy.
			 *
			 * @param string $slug     Slug.
			 * @param string $taxonomy Taxonomy key.
			 */
			'slug'       => (string) apply_filters( 'rudelwerk_taxonomy_slug', $slug, $taxonomy ),
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
						'name'              => _x( 'Placement categories', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'Placement category', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'Categories', 'rudelwerk' ),
						'search_items'      => __( 'Search categories', 'rudelwerk' ),
						'all_items'         => __( 'All categories', 'rudelwerk' ),
						'parent_item'       => __( 'Parent category', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent category:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit category', 'rudelwerk' ),
						'update_item'       => __( 'Update category', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new category', 'rudelwerk' ),
						'new_item_name'     => __( 'New category name', 'rudelwerk' ),
					),
					'rewrite' => $this->rewrite( self::PLACEMENT_CATEGORY, _x( 'dogs-to-place-category', 'URL slug', 'rudelwerk' ) ),
				),
			),

			self::KENNEL              => array(
				'object_types' => array( PostTypes::STUD_DOG, PostTypes::APPROVAL, PostTypes::BREEDING_LICENSE, PostTypes::PUPPIES ),
				'args'         => array(
					'labels'  => array(
						'name'              => _x( 'Kennels', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'Kennel', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'Kennels', 'rudelwerk' ),
						'search_items'      => __( 'Search kennels', 'rudelwerk' ),
						'all_items'         => __( 'All kennels', 'rudelwerk' ),
						'parent_item'       => __( 'Parent kennel', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent kennel:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit kennel', 'rudelwerk' ),
						'update_item'       => __( 'Update kennel', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new kennel', 'rudelwerk' ),
						'new_item_name'     => __( 'New kennel name', 'rudelwerk' ),
					),
					'rewrite' => $this->rewrite( self::KENNEL, _x( 'kennels', 'URL slug', 'rudelwerk' ) ),
				),
			),

			self::BREEDER             => array(
				'object_types' => array( PostTypes::STUD_DOG, PostTypes::PUPPIES ),
				'args'         => array(
					'labels' => array(
						'name'              => _x( 'Breeders', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'Breeder', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'Breeders', 'rudelwerk' ),
						'search_items'      => __( 'Search breeders', 'rudelwerk' ),
						'all_items'         => __( 'All breeders', 'rudelwerk' ),
						'parent_item'       => __( 'Parent breeder', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent breeder:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit breeder', 'rudelwerk' ),
						'update_item'       => __( 'Update breeder', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new breeder', 'rudelwerk' ),
						'new_item_name'     => __( 'New breeder name', 'rudelwerk' ),
					),
				),
			),

			self::OWNER               => array(
				'object_types' => array( PostTypes::STUD_DOG ),
				'args'         => array(
					'labels' => array(
						'name'              => _x( 'Owners', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'Owner', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'Owners', 'rudelwerk' ),
						'search_items'      => __( 'Search owners', 'rudelwerk' ),
						'all_items'         => __( 'All owners', 'rudelwerk' ),
						'parent_item'       => __( 'Parent owner', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent owner:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit owner', 'rudelwerk' ),
						'update_item'       => __( 'Update owner', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new owner', 'rudelwerk' ),
						'new_item_name'     => __( 'New owner name', 'rudelwerk' ),
					),
				),
			),

			self::EVENT               => array(
				'object_types' => array( PostTypes::RESULT ),
				'args'         => array(
					'labels' => array(
						'name'              => _x( 'Events', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'Event', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'Events', 'rudelwerk' ),
						'search_items'      => __( 'Search events', 'rudelwerk' ),
						'all_items'         => __( 'All events', 'rudelwerk' ),
						'parent_item'       => __( 'Parent event', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent event:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit event', 'rudelwerk' ),
						'update_item'       => __( 'Update event', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new event', 'rudelwerk' ),
						'new_item_name'     => __( 'New event name', 'rudelwerk' ),
					),
				),
			),

			self::PERIOD              => array(
				'object_types' => array( PostTypes::SHOW ),
				'args'         => array(
					'labels' => array(
						'name'              => _x( 'Periods', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'Period', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'Periods', 'rudelwerk' ),
						'search_items'      => __( 'Search periods', 'rudelwerk' ),
						'all_items'         => __( 'All periods', 'rudelwerk' ),
						'parent_item'       => __( 'Parent period', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent period:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit period', 'rudelwerk' ),
						'update_item'       => __( 'Update period', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new period', 'rudelwerk' ),
						'new_item_name'     => __( 'New period name', 'rudelwerk' ),
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
						'name'              => _x( 'Types', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'Type', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'Types', 'rudelwerk' ),
						'search_items'      => __( 'Search types', 'rudelwerk' ),
						'all_items'         => __( 'All types', 'rudelwerk' ),
						'parent_item'       => __( 'Parent type', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent type:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit type', 'rudelwerk' ),
						'update_item'       => __( 'Update type', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new type', 'rudelwerk' ),
						'new_item_name'     => __( 'New type name', 'rudelwerk' ),
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
						'name'              => _x( 'Literature categories', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'Literature category', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'Categories', 'rudelwerk' ),
						'search_items'      => __( 'Search categories', 'rudelwerk' ),
						'all_items'         => __( 'All categories', 'rudelwerk' ),
						'parent_item'       => __( 'Parent category', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent category:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit category', 'rudelwerk' ),
						'update_item'       => __( 'Update category', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new category', 'rudelwerk' ),
						'new_item_name'     => __( 'New category name', 'rudelwerk' ),
					),
					'publicly_queryable' => false,
				),
			),

			self::LITERATURE_AUTHOR   => array(
				'object_types' => array( PostTypes::LITERATURE ),
				'args'         => array(
					'labels'             => array(
						'name'              => _x( 'Authors', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'Author', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'Authors', 'rudelwerk' ),
						'search_items'      => __( 'Search authors', 'rudelwerk' ),
						'all_items'         => __( 'All authors', 'rudelwerk' ),
						'parent_item'       => __( 'Parent author', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent author:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit author', 'rudelwerk' ),
						'update_item'       => __( 'Update author', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new author', 'rudelwerk' ),
						'new_item_name'     => __( 'New author name', 'rudelwerk' ),
					),
					'publicly_queryable' => false,
				),
			),

			self::PUPPIES_CITY        => array(
				'object_types' => array( PostTypes::PUPPIES ),
				'args'         => array(
					'labels'  => $this->city_labels(),
					'rewrite' => $this->rewrite( self::PUPPIES_CITY, _x( 'puppies-by-city', 'URL slug', 'rudelwerk' ) ),
				),
			),

			self::EXAM                => array(
				'object_types' => array( PostTypes::STUD_DOG ),
				'args'         => array(
					'labels'  => array(
						'name'              => _x( 'Exams', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'Exam', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'Exams', 'rudelwerk' ),
						'search_items'      => __( 'Search exams', 'rudelwerk' ),
						'all_items'         => __( 'All exams', 'rudelwerk' ),
						'parent_item'       => __( 'Parent exam', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent exam:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit exam', 'rudelwerk' ),
						'update_item'       => __( 'Update exam', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new exam', 'rudelwerk' ),
						'new_item_name'     => __( 'New exam name', 'rudelwerk' ),
					),
					'rewrite' => $this->rewrite( self::EXAM, _x( 'by-exam', 'URL slug', 'rudelwerk' ) ),
				),
			),

			self::HD                  => array(
				'object_types' => array( PostTypes::STUD_DOG ),
				'args'         => array(
					'labels'  => array(
						'name'              => _x( 'HD values', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'HD value', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'HD', 'rudelwerk' ),
						'search_items'      => __( 'Search HD values', 'rudelwerk' ),
						'all_items'         => __( 'All HD values', 'rudelwerk' ),
						'parent_item'       => __( 'Parent HD value', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent HD value:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit HD value', 'rudelwerk' ),
						'update_item'       => __( 'Update HD value', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new HD value', 'rudelwerk' ),
						'new_item_name'     => __( 'New HD value', 'rudelwerk' ),
					),
					'rewrite' => $this->rewrite( self::HD, _x( 'by-hd-value', 'URL slug', 'rudelwerk' ) ),
				),
			),

			self::ED                  => array(
				'object_types' => array( PostTypes::STUD_DOG ),
				'args'         => array(
					'labels'  => array(
						'name'              => _x( 'ED values', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'ED value', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'ED', 'rudelwerk' ),
						'search_items'      => __( 'Search ED values', 'rudelwerk' ),
						'all_items'         => __( 'All ED values', 'rudelwerk' ),
						'parent_item'       => __( 'Parent ED value', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent ED value:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit ED value', 'rudelwerk' ),
						'update_item'       => __( 'Update ED value', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new ED value', 'rudelwerk' ),
						'new_item_name'     => __( 'New ED value', 'rudelwerk' ),
					),
					'rewrite' => $this->rewrite( self::ED, _x( 'by-ed-value', 'URL slug', 'rudelwerk' ) ),
				),
			),

			self::SIZE                => array(
				'object_types' => array( PostTypes::STUD_DOG ),
				'args'         => array(
					'labels'  => array(
						'name'              => _x( 'Sizes', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'Size', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'Sizes', 'rudelwerk' ),
						'search_items'      => __( 'Search sizes', 'rudelwerk' ),
						'all_items'         => __( 'All sizes', 'rudelwerk' ),
						'parent_item'       => __( 'Parent size', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent size:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit size', 'rudelwerk' ),
						'update_item'       => __( 'Update size', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new size', 'rudelwerk' ),
						'new_item_name'     => __( 'New size', 'rudelwerk' ),
					),
					'rewrite' => $this->rewrite( self::SIZE, _x( 'by-size', 'URL slug', 'rudelwerk' ) ),
				),
			),

			self::TITLE               => array(
				'object_types' => array( PostTypes::STUD_DOG ),
				'args'         => array(
					'labels'    => array(
						'name'              => _x( 'Titles', 'taxonomy general name', 'rudelwerk' ),
						'singular_name'     => _x( 'Title', 'taxonomy singular name', 'rudelwerk' ),
						'menu_name'         => __( 'Titles', 'rudelwerk' ),
						'search_items'      => __( 'Search titles', 'rudelwerk' ),
						'all_items'         => __( 'All titles', 'rudelwerk' ),
						'parent_item'       => __( 'Parent title', 'rudelwerk' ),
						'parent_item_colon' => __( 'Parent title:', 'rudelwerk' ),
						'edit_item'         => __( 'Edit title', 'rudelwerk' ),
						'update_item'       => __( 'Update title', 'rudelwerk' ),
						'add_new_item'      => __( 'Add new title', 'rudelwerk' ),
						'new_item_name'     => __( 'New title', 'rudelwerk' ),
					),
					// "title" is a WP_Query variable (post title); a custom query var keeps term archives working.
					'query_var' => 'stud_dog_title',
					'rewrite'   => $this->rewrite( self::TITLE, _x( 'by-title', 'URL slug', 'rudelwerk' ) ),
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
			'name'              => _x( 'ZIP codes', 'taxonomy general name', 'rudelwerk' ),
			'singular_name'     => _x( 'ZIP code', 'taxonomy singular name', 'rudelwerk' ),
			'menu_name'         => __( 'ZIP codes', 'rudelwerk' ),
			'search_items'      => __( 'Search ZIP codes', 'rudelwerk' ),
			'all_items'         => __( 'All ZIP codes', 'rudelwerk' ),
			'parent_item'       => __( 'Parent ZIP code', 'rudelwerk' ),
			'parent_item_colon' => __( 'Parent ZIP code:', 'rudelwerk' ),
			'edit_item'         => __( 'Edit ZIP code', 'rudelwerk' ),
			'update_item'       => __( 'Update ZIP code', 'rudelwerk' ),
			'add_new_item'      => __( 'Add new ZIP code', 'rudelwerk' ),
			'new_item_name'     => __( 'New ZIP code', 'rudelwerk' ),
		);
	}

	/**
	 * Labels shared by all city taxonomies.
	 *
	 * @return array
	 */
	private function city_labels() {
		return array(
			'name'              => _x( 'Cities', 'taxonomy general name', 'rudelwerk' ),
			'singular_name'     => _x( 'City', 'taxonomy singular name', 'rudelwerk' ),
			'menu_name'         => __( 'Cities', 'rudelwerk' ),
			'search_items'      => __( 'Search cities', 'rudelwerk' ),
			'all_items'         => __( 'All cities', 'rudelwerk' ),
			'parent_item'       => __( 'Parent city', 'rudelwerk' ),
			'parent_item_colon' => __( 'Parent city:', 'rudelwerk' ),
			'edit_item'         => __( 'Edit city', 'rudelwerk' ),
			'update_item'       => __( 'Update city', 'rudelwerk' ),
			'add_new_item'      => __( 'Add new city', 'rudelwerk' ),
			'new_item_name'     => __( 'New city name', 'rudelwerk' ),
		);
	}
}

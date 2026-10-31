<?php
/**
 * Field schema for post meta and term meta.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Content;

defined( 'ABSPATH' ) || exit;

/**
 * Single source of truth for all custom fields.
 *
 * The schema drives meta registration, the admin meta boxes, the term forms
 * and sanitization. Meta keys are identical to the former theme.
 *
 * Supported field types: text, textarea, date, number, email, url, checkbox,
 * select, post (ID of another post), term (ID of a term), media (comma
 * separated attachment IDs).
 */
final class Fields {

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register_meta' ), 20 );
	}

	/**
	 * Registers all meta keys with WordPress, including sanitization.
	 */
	public function register_meta() {
		foreach ( self::post_groups() as $post_type => $groups ) {
			foreach ( self::flatten( $groups ) as $key => $field ) {
				register_post_meta(
					$post_type,
					$key,
					array(
						'type'              => 'string',
						'single'            => true,
						'description'       => $field['label'],
						'sanitize_callback' => static function ( $value ) use ( $field ) {
							return Fields::sanitize( $value, $field );
						},
						'auth_callback'     => static function ( $allowed, $meta_key, $object_id ) {
							return current_user_can( 'edit_post', $object_id );
						},
					)
				);
			}
		}

		foreach ( self::term_fields() as $taxonomy => $fields ) {
			foreach ( $fields as $key => $field ) {
				register_term_meta(
					$taxonomy,
					$key,
					array(
						'type'              => 'string',
						'single'            => true,
						'description'       => $field['label'],
						'sanitize_callback' => static function ( $value ) use ( $field ) {
							return Fields::sanitize( $value, $field );
						},
						'auth_callback'     => static function ( $allowed, $meta_key, $object_id ) {
							return current_user_can( 'edit_term', $object_id );
						},
					)
				);
			}
		}
	}

	/**
	 * Meta box groups per post type.
	 *
	 * @return array<string, array<string, array>>
	 */
	public static function post_groups() {
		$groups = array(
			PostTypes::STUD_DOG       => array(
				'stud_dog_details' => array(
					'title'    => __( 'Stud dog data', 'rudelwerk' ),
					'context'  => 'side',
					'priority' => 'high',
					'fields'   => array(
						'date_born'          => array(
							'type'  => 'date',
							'label' => __( 'Date born', 'rudelwerk' ),
						),
						'studbook_no'        => array(
							'type'  => 'text',
							'label' => __( 'Stud book no.', 'rudelwerk' ),
						),
						'tests'              => array(
							'type'  => 'text',
							'label' => __( 'Tests and titles', 'rudelwerk' ),
						),
						'withers'            => array(
							'type'  => 'number',
							'step'  => '0.1',
							'label' => __( 'Withers in cm', 'rudelwerk' ),
						),
						'license'            => array(
							'type'      => 'post',
							'post_type' => PostTypes::BREEDING_LICENSE,
							'label'     => __( 'Breeding license', 'rudelwerk' ),
						),
						'approval'           => array(
							'type'      => 'post',
							'post_type' => PostTypes::APPROVAL,
							'add_new'   => true,
							'label'     => __( 'Approval', 'rudelwerk' ),
						),
						'kennel_before_name' => array(
							'type'  => 'checkbox',
							'label' => __( 'Show kennel name before the stud dog name', 'rudelwerk' ),
						),
					),
				),
				'stud_dog_pedigree' => array(
					'title'    => __( 'Pedigree', 'rudelwerk' ),
					'context'  => 'normal',
					'priority' => 'high',
					'renderer' => 'pedigree',
					'fields'   => self::pedigree_fields(),
				),
			),

			PostTypes::PUPPIES        => array(
				'litter_details' => array(
					'title'  => __( 'Litter', 'rudelwerk' ),
					'fields' => array(
						'expected_puppies'            => array(
							'type'  => 'checkbox',
							'label' => __( 'Expected litter (puppies are not born yet)', 'rudelwerk' ),
						),
						'date_of_birth'               => array(
							'type'        => 'text',
							'label'       => __( 'Date of birth', 'rudelwerk' ),
							'description' => __( 'Free text, e.g. "12.05.2026" or "end of May".', 'rudelwerk' ),
						),
						'male_count'                  => array(
							'type'  => 'text',
							'label' => __( 'Number of male puppies', 'rudelwerk' ),
						),
						'female_count'                => array(
							'type'  => 'text',
							'label' => __( 'Number of female puppies', 'rudelwerk' ),
						),
						'still_available_male'        => array(
							'type'  => 'text',
							'label' => __( 'Male puppies still available', 'rudelwerk' ),
						),
						'still_available_female'      => array(
							'type'  => 'text',
							'label' => __( 'Female puppies still available', 'rudelwerk' ),
						),
						'still_available_last_update' => array(
							'type'  => 'text',
							'label' => __( 'Last update', 'rudelwerk' ),
						),
						'zip'                         => array(
							'type'  => 'text',
							'label' => __( 'ZIP code', 'rudelwerk' ),
						),
						'city'                        => array(
							'type'  => 'text',
							'label' => __( 'City', 'rudelwerk' ),
						),
					),
				),
				'litter_father'  => array(
					'title'  => __( 'Father', 'rudelwerk' ),
					'fields' => array(
						'father_name'        => array(
							'type'  => 'text',
							'label' => __( 'Name', 'rudelwerk' ),
						),
						'father_desc'        => array(
							'type'  => 'textarea',
							'label' => __( 'Description', 'rudelwerk' ),
						),
						'father_stud_dog_id' => array(
							'type'      => 'post',
							'post_type' => PostTypes::STUD_DOG,
							'label'     => __( 'Matching stud dog entry', 'rudelwerk' ),
						),
						'father_photo'       => array(
							'type'  => 'media',
							'label' => __( 'Photos', 'rudelwerk' ),
						),
					),
				),
				'litter_mother'  => array(
					'title'  => __( 'Mother', 'rudelwerk' ),
					'fields' => array(
						'mother_name'  => array(
							'type'  => 'text',
							'label' => __( 'Name', 'rudelwerk' ),
						),
						'mother_desc'  => array(
							'type'  => 'textarea',
							'label' => __( 'Description', 'rudelwerk' ),
						),
						'mother_photo' => array(
							'type'  => 'media',
							'label' => __( 'Photos', 'rudelwerk' ),
						),
					),
				),
			),

			PostTypes::DOG_TO_PLACE   => array(
				'dog_to_place_details' => array(
					'title'   => __( 'Details', 'rudelwerk' ),
					'context' => 'side',
					'fields'  => array(
						'date_of_birth' => array(
							'type'  => 'text',
							'label' => __( 'Date of birth', 'rudelwerk' ),
						),
						'gender'        => array(
							'type'    => 'select',
							'label'   => __( 'Gender', 'rudelwerk' ),
							'options' => self::gender_options(),
						),
					),
				),
			),

			PostTypes::TRIMMING_PROOF => array(
				'trimming_proof_details' => array(
					'title'   => __( 'Contact', 'rudelwerk' ),
					'context' => 'side',
					'fields'  => self::contact_fields( array( 'address', 'email', 'phone', 'website' ) ),
				),
			),

			PostTypes::DOG_SITTER     => array(
				'dog_sitter_details' => array(
					'title'   => __( 'Contact', 'rudelwerk' ),
					'context' => 'side',
					'fields'  => self::contact_fields( array( 'address', 'email', 'phone', 'mobile', 'website' ) ),
				),
			),

			PostTypes::LITERATURE     => array(
				'literature_details' => array(
					'title'   => __( 'Details', 'rudelwerk' ),
					'context' => 'side',
					'fields'  => array(
						'subtitle' => array(
							'type'  => 'text',
							'label' => __( 'Subtitle', 'rudelwerk' ),
						),
						'isbn'     => array(
							'type'  => 'text',
							'label' => __( 'ISBN', 'rudelwerk' ),
						),
						'price'    => array(
							'type'  => 'text',
							'label' => __( 'Price', 'rudelwerk' ),
						),
					),
				),
			),
		);

		/**
		 * Filters the meta box groups and fields of all post types.
		 *
		 * @param array $groups Groups keyed by post type, then by group ID.
		 */
		return apply_filters( 'rudelwerk_post_fields', $groups );
	}

	/**
	 * Term meta fields per taxonomy.
	 *
	 * @return array<string, array<string, array>>
	 */
	public static function term_fields() {
		$fields = array(
			Taxonomies::KENNEL  => array(
				'kennel_zip'     => array(
					'type'  => 'text',
					'label' => __( 'ZIP code', 'rudelwerk' ),
				),
				'kennel_website' => array(
					'type'  => 'url',
					'label' => __( 'Website', 'rudelwerk' ),
				),
				'kennel_hidden'  => array(
					'type'        => 'checkbox',
					// The breeders' board historically queries for "0", so store it explicitly.
					'unchecked'   => '0',
					'label'       => __( 'Hidden', 'rudelwerk' ),
					'description' => __( 'Hide this kennel on the website (name, website and breeders\' board).', 'rudelwerk' ),
				),
			),
			Taxonomies::BREEDER => self::person_fields( 'breeder', array( 'address', 'website', 'phone', 'mobile', 'fax', 'email' ) ) + array(
				'breeder_kennel' => array(
					'type'        => 'term',
					'taxonomy'    => Taxonomies::KENNEL,
					'label'       => __( 'Kennel', 'rudelwerk' ),
					'description' => __( 'Connects the breeder with a kennel (used by the breeders\' board).', 'rudelwerk' ),
				),
			),
			Taxonomies::OWNER   => self::person_fields( 'owner', array( 'address', 'website', 'phone', 'email' ) ),
		);

		/**
		 * Filters the term meta fields of all taxonomies.
		 *
		 * @param array $fields Fields keyed by taxonomy, then by meta key.
		 */
		return apply_filters( 'rudelwerk_term_fields', $fields );
	}

	/**
	 * Fields of a post type, regardless of their meta box group.
	 *
	 * @param string $post_type Post type.
	 * @return array<string, array>
	 */
	public static function for_post_type( $post_type ) {
		$groups = self::post_groups();

		return isset( $groups[ $post_type ] ) ? self::flatten( $groups[ $post_type ] ) : array();
	}

	/**
	 * Sanitizes a value according to its field definition.
	 *
	 * Expects unslashed input (update_metadata() unslashes before sanitizing).
	 *
	 * @param mixed $value Raw value.
	 * @param array $field Field definition.
	 * @return string
	 */
	public static function sanitize( $value, array $field ) {
		if ( is_array( $value ) ) {
			$value = implode( ',', $value );
		}

		$value = trim( (string) $value );

		switch ( $field['type'] ) {
			case 'textarea':
				return sanitize_textarea_field( $value );

			case 'email':
				return sanitize_email( $value );

			case 'url':
				return esc_url_raw( $value );

			case 'number':
				$value = str_replace( ',', '.', $value );
				return is_numeric( $value ) ? $value : '';

			case 'date':
				return preg_match( '/^\d{4}-\d{2}-\d{2}$/', $value ) ? $value : '';

			case 'checkbox':
				if ( '' !== $value && '0' !== $value ) {
					return '1';
				}
				return isset( $field['unchecked'] ) ? (string) $field['unchecked'] : '';

			case 'post':
			case 'term':
				$id = absint( $value );
				return $id ? (string) $id : '';

			case 'media':
				$ids = array_filter( array_map( 'absint', explode( ',', $value ) ) );
				return implode( ',', array_unique( $ids ) );

			case 'select':
				$options = isset( $field['options'] ) ? $field['options'] : array();
				return array_key_exists( $value, $options ) ? $value : sanitize_text_field( $value );

			case 'text':
			default:
				return sanitize_text_field( $value );
		}
	}

	/**
	 * Pedigree fields: description and approval for every ancestor.
	 *
	 * @return array<string, array>
	 */
	private static function pedigree_fields() {
		$fields = array();

		foreach ( Pedigree::positions() as $position ) {
			$fields[ $position ]               = array(
				'type'  => 'textarea',
				'label' => Pedigree::label( $position ),
			);
			$fields[ $position . '_approval' ] = array(
				'type'      => 'post',
				'post_type' => PostTypes::APPROVAL,
				'add_new'   => true,
				/* translators: %s: ancestor, e.g. "Father". */
				'label'     => sprintf( __( 'Approval (%s)', 'rudelwerk' ), Pedigree::label( $position ) ),
			);
		}

		return $fields;
	}

	/**
	 * Gender options of dogs to place. Keys are the stored values.
	 *
	 * @return array<string, string>
	 */
	public static function gender_options() {
		return array(
			''       => '—',
			'female' => __( 'female', 'rudelwerk' ),
			'male'   => __( 'male', 'rudelwerk' ),
		);
	}

	/**
	 * Contact field definitions for posts (unprefixed meta keys).
	 *
	 * @param string[] $keys Contact keys.
	 * @return array<string, array>
	 */
	private static function contact_fields( array $keys ) {
		$fields = array();

		foreach ( $keys as $key ) {
			$fields[ $key ] = self::contact_field( $key );
		}

		return $fields;
	}

	/**
	 * Contact field definitions for person terms (prefixed meta keys).
	 *
	 * @param string   $prefix Meta key prefix, e.g. "breeder".
	 * @param string[] $keys   Contact keys.
	 * @return array<string, array>
	 */
	private static function person_fields( $prefix, array $keys ) {
		$fields = array();

		foreach ( $keys as $key ) {
			$fields[ $prefix . '_' . $key ] = self::contact_field( $key );
		}

		return $fields;
	}

	/**
	 * A single contact field definition.
	 *
	 * @param string $key Contact key.
	 * @return array
	 */
	private static function contact_field( $key ) {
		$definitions = array(
			'address' => array(
				'type'  => 'textarea',
				'label' => __( 'Address', 'rudelwerk' ),
			),
			'email'   => array(
				'type'  => 'email',
				'label' => __( 'Email', 'rudelwerk' ),
			),
			'phone'   => array(
				'type'  => 'text',
				'label' => __( 'Phone', 'rudelwerk' ),
			),
			'mobile'  => array(
				'type'  => 'text',
				'label' => __( 'Mobile', 'rudelwerk' ),
			),
			'fax'     => array(
				'type'  => 'text',
				'label' => __( 'Fax', 'rudelwerk' ),
			),
			'website' => array(
				'type'        => 'url',
				'label'       => __( 'Website', 'rudelwerk' ),
				'description' => __( 'Starting with https://', 'rudelwerk' ),
			),
		);

		return $definitions[ $key ];
	}

	/**
	 * Merges the fields of all groups.
	 *
	 * @param array $groups Groups.
	 * @return array<string, array>
	 */
	private static function flatten( array $groups ) {
		$fields = array();

		foreach ( $groups as $group ) {
			$fields += $group['fields'];
		}

		return $fields;
	}
}

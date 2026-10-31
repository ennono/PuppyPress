<?php
/**
 * What gets migrated and which fields contain IDs.
 *
 * @package PuppyPressMigrator
 */

namespace PuppyPressMigrator;

defined( 'ABSPATH' ) || exit;

/**
 * Central, filterable configuration.
 *
 * The keys match the "Extra Child" theme and PuppyPress, which use identical
 * post types, taxonomies and meta keys.
 */
final class Config {

	/**
	 * Club post types (always exported).
	 *
	 * @return string[]
	 */
	public static function club_post_types() {
		return apply_filters(
			'ppm_club_post_types',
			array( 'stud-dogs', 'puppies', 'dogs-to-place', 'approvals', 'breeding-license', 'results', 'shows', 'trimming-proofs', 'dog-sitters', 'literature', 'vacation-deals' )
		);
	}

	/**
	 * Club taxonomies. All of their terms are exported, including empty ones.
	 *
	 * @return string[]
	 */
	public static function club_taxonomies() {
		return apply_filters(
			'ppm_club_taxonomies',
			array( 'placement-category', 'kennel', 'breeder', 'owner', 'event', 'period', 'zip', 'city', 'type', 'dog-sitters-zip', 'dog-sitters-city', 'literature-category', 'literature-author', 'puppies-city', 'exam', 'hd', 'ed', 'size', 'title' )
		);
	}

	/**
	 * Post statuses that are exported (revisions and auto drafts are skipped).
	 *
	 * @return string[]
	 */
	public static function post_statuses() {
		return apply_filters( 'ppm_post_statuses', array( 'publish', 'future', 'draft', 'pending', 'private', 'inherit' ) );
	}

	/**
	 * Post meta keys holding a single post ID.
	 *
	 * @return string[]
	 */
	public static function post_id_meta_keys() {
		$keys = array( '_thumbnail_id', 'license', 'approval', 'father_stud_dog_id', '_menu_item_menu_item_parent' );

		foreach ( array( 'father', 'mother', 'grandfather_1', 'grandmother_1', 'grandfather_2', 'grandmother_2', 'greatgrandfather_1', 'greatgrandmother_1', 'greatgrandfather_2', 'greatgrandmother_2', 'greatgrandfather_3', 'greatgrandmother_3', 'greatgrandfather_4', 'greatgrandmother_4' ) as $ancestor ) {
			$keys[] = $ancestor . '_approval';
		}

		return apply_filters( 'ppm_post_id_meta_keys', $keys );
	}

	/**
	 * Post meta keys holding comma separated post IDs.
	 *
	 * @return string[]
	 */
	public static function post_id_list_meta_keys() {
		return apply_filters( 'ppm_post_id_list_meta_keys', array( 'father_photo', 'mother_photo' ) );
	}

	/**
	 * Term meta keys holding a term ID.
	 *
	 * @return string[]
	 */
	public static function term_id_term_meta_keys() {
		return apply_filters( 'ppm_term_id_term_meta_keys', array( 'breeder_kennel' ) );
	}

	/**
	 * Post meta keys that are not migrated.
	 *
	 * @return string[]
	 */
	public static function excluded_post_meta_keys() {
		return apply_filters( 'ppm_excluded_post_meta_keys', array( '_edit_lock', '_edit_last', '_encloseme', '_pingme', Importer::SOURCE_META ) );
	}
}

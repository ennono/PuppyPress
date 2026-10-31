<?php
/**
 * Ordering terms by a value of the "Category Custom Fields" plugin.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Compat;

defined( 'ABSPATH' ) || exit;

/**
 * Allows get_terms( array( 'orderby' => 'category_custom_field', 'category_custom_field' => 'field' ) ).
 *
 * The values live in the table `{prefix}ccf_Value` of the "Category Custom
 * Fields" plugin. The hooks are only added when that table exists.
 */
final class TermCustomFieldOrder {

	const ORDERBY = 'category_custom_field';

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'maybe_hook' ) );
	}

	/**
	 * Adds the term query filters if the plugin table exists.
	 */
	public function maybe_hook() {
		global $wpdb;

		$table = $wpdb->prefix . 'ccf_Value';
		$found = wp_cache_get( 'rudelwerk_ccf_table', 'rudelwerk' );

		if ( false === $found ) {
			$found = $table === $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) ? 'yes' : 'no'; // phpcs:ignore WordPress.DB.DirectDatabaseQuery
			wp_cache_set( 'rudelwerk_ccf_table', $found, 'rudelwerk', HOUR_IN_SECONDS );
		}

		if ( 'yes' !== $found ) {
			return;
		}

		add_filter( 'get_terms_orderby', array( $this, 'orderby' ), 10, 2 );
		add_filter( 'get_terms_fields', array( $this, 'fields' ), 10, 2 );
		add_filter( 'terms_clauses', array( $this, 'clauses' ), 10, 3 );
	}

	/**
	 * Whether the query orders by a custom field.
	 *
	 * @param array $args Term query arguments.
	 * @return bool
	 */
	private function applies( $args ) {
		return isset( $args['orderby'], $args['category_custom_field'] ) && self::ORDERBY === $args['orderby'] && '' !== (string) $args['category_custom_field'];
	}

	/**
	 * ORDER BY clause.
	 *
	 * @param string $orderby ORDER BY clause.
	 * @param array  $args    Term query arguments.
	 * @return string
	 */
	public function orderby( $orderby, $args ) {
		return $this->applies( $args ) ? 'cv.field_value' : $orderby;
	}

	/**
	 * Selected fields.
	 *
	 * @param string[] $selects Fields.
	 * @param array    $args    Term query arguments.
	 * @return string[]
	 */
	public function fields( $selects, $args ) {
		if ( $this->applies( $args ) ) {
			$selects[] = 'cv.field_value';
		}

		return $selects;
	}

	/**
	 * JOIN clause.
	 *
	 * @param array    $pieces     Query clauses.
	 * @param string[] $taxonomies Taxonomies.
	 * @param array    $args       Term query arguments.
	 * @return array
	 */
	public function clauses( $pieces, $taxonomies, $args ) {
		global $wpdb;

		if ( $this->applies( $args ) ) {
			$pieces['join'] .= $wpdb->prepare(
				" LEFT JOIN {$wpdb->prefix}ccf_Value cv ON cv.term_id = tt.term_id AND cv.field_name = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				(string) $args['category_custom_field']
			);
		}

		return $pieces;
	}
}

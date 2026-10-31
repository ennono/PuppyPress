<?php
/**
 * Stud dog search filter.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Frontend;

use Rudelwerk\Content\Taxonomies;

defined( 'ABSPATH' ) || exit;

/**
 * Parses the filter form (GET parameters) and turns it into query arguments.
 *
 * Filters: ZIP code prefix of the kennel, size, HD, ED, exams (all must
 * match), titles (all must match) and "approval available".
 */
final class StudDogFilter {

	const PARAM_ZIP      = 'rw_zip';
	const PARAM_SIZE     = 'rw_size';
	const PARAM_HD       = 'rw_hd';
	const PARAM_ED       = 'rw_ed';
	const PARAM_EXAMS    = 'rw_exams';
	const PARAM_TITLES   = 'rw_titles';
	const PARAM_APPROVAL = 'rw_approval';

	/**
	 * ZIP code prefix (digits only).
	 *
	 * @var string
	 */
	public $zip = '';

	/**
	 * Size term slug.
	 *
	 * @var string
	 */
	public $size = '';

	/**
	 * HD term slug.
	 *
	 * @var string
	 */
	public $hd = '';

	/**
	 * ED term slug.
	 *
	 * @var string
	 */
	public $ed = '';

	/**
	 * Exam term slugs.
	 *
	 * @var string[]
	 */
	public $exams = array();

	/**
	 * Title term slugs.
	 *
	 * @var string[]
	 */
	public $titles = array();

	/**
	 * Only dogs with an approval.
	 *
	 * @var bool
	 */
	public $approval = false;

	/**
	 * Builds the filter from the current request.
	 *
	 * @return self
	 */
	public static function from_request() {
		// Read-only search form; no state is changed, so no nonce is needed.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$filter = new self();

		$filter->zip      = isset( $_GET[ self::PARAM_ZIP ] ) ? substr( preg_replace( '/\D/', '', sanitize_text_field( wp_unslash( $_GET[ self::PARAM_ZIP ] ) ) ), 0, 5 ) : '';
		$filter->size     = isset( $_GET[ self::PARAM_SIZE ] ) ? sanitize_title( wp_unslash( $_GET[ self::PARAM_SIZE ] ) ) : '';
		$filter->hd       = isset( $_GET[ self::PARAM_HD ] ) ? sanitize_title( wp_unslash( $_GET[ self::PARAM_HD ] ) ) : '';
		$filter->ed       = isset( $_GET[ self::PARAM_ED ] ) ? sanitize_title( wp_unslash( $_GET[ self::PARAM_ED ] ) ) : '';
		$filter->exams    = isset( $_GET[ self::PARAM_EXAMS ] ) ? self::slugs( wp_unslash( $_GET[ self::PARAM_EXAMS ] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in slugs().
		$filter->titles   = isset( $_GET[ self::PARAM_TITLES ] ) ? self::slugs( wp_unslash( $_GET[ self::PARAM_TITLES ] ) ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized in slugs().
		$filter->approval = ! empty( $_GET[ self::PARAM_APPROVAL ] );
		// phpcs:enable

		return $filter;
	}

	/**
	 * Sanitizes a list of term slugs.
	 *
	 * @param mixed $value Raw value.
	 * @return string[]
	 */
	private static function slugs( $value ) {
		return array_values( array_filter( array_map( 'sanitize_title', (array) $value ) ) );
	}

	/**
	 * Whether any filter is set.
	 *
	 * @return bool
	 */
	public function is_active() {
		return '' !== $this->zip || '' !== $this->size || '' !== $this->hd || '' !== $this->ed
			|| $this->exams || $this->titles || $this->approval;
	}

	/**
	 * Top level kennels, optionally limited to the ZIP code prefix, ordered by slug.
	 *
	 * @return \WP_Term[]
	 */
	public function kennels() {
		$args = array(
			'taxonomy'   => Taxonomies::KENNEL,
			'parent'     => 0,
			'hide_empty' => false,
			'orderby'    => 'slug',
		);

		if ( '' !== $this->zip ) {
			$args['meta_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => 'kennel_zip',
					'value'   => '^' . $this->zip,
					'compare' => 'REGEXP',
				),
			);
		}

		$terms = get_terms( $args );

		return is_wp_error( $terms ) ? array() : $terms;
	}

	/**
	 * Adds the filter to WP_Query arguments.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public function apply( array $args ) {
		$tax_query = isset( $args['tax_query'] ) ? (array) $args['tax_query'] : array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query

		if ( '' !== $this->zip ) {
			$kennel_ids  = wp_list_pluck( $this->kennels(), 'term_id' );
			$tax_query[] = array(
				'taxonomy'         => Taxonomies::KENNEL,
				'field'            => 'term_id',
				'terms'            => $kennel_ids ? $kennel_ids : array( 0 ),
				'include_children' => true,
			);
		}

		foreach ( array(
			Taxonomies::SIZE => $this->size,
			Taxonomies::HD   => $this->hd,
			Taxonomies::ED   => $this->ed,
		) as $taxonomy => $slug ) {
			if ( '' !== $slug ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $slug,
				);
			}
		}

		foreach ( array(
			Taxonomies::EXAM  => $this->exams,
			Taxonomies::TITLE => $this->titles,
		) as $taxonomy => $slugs ) {
			if ( $slugs ) {
				$tax_query[] = array(
					'taxonomy' => $taxonomy,
					'field'    => 'slug',
					'terms'    => $slugs,
					'operator' => 'AND',
				);
			}
		}

		if ( $tax_query ) {
			$tax_query['relation'] = 'AND';
			$args['tax_query']     = $tax_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
		}

		if ( $this->approval ) {
			$meta_query          = isset( $args['meta_query'] ) ? (array) $args['meta_query'] : array(); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			$meta_query[]        = array(
				'key'     => 'approval',
				'value'   => array( '', '0' ),
				'compare' => 'NOT IN',
			);
			$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		return $args;
	}

	/**
	 * Values for the filter form template.
	 *
	 * @return array
	 */
	public function form_data() {
		$terms = static function ( $taxonomy ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
				)
			);

			return is_wp_error( $terms ) ? array() : wp_list_pluck( $terms, 'name', 'slug' );
		};

		return array(
			'filter' => $this,
			'sizes'  => $terms( Taxonomies::SIZE ),
			'hds'    => $terms( Taxonomies::HD ),
			'eds'    => $terms( Taxonomies::ED ),
			'exams'  => $terms( Taxonomies::EXAM ),
			'titles' => $terms( Taxonomies::TITLE ),
		);
	}
}

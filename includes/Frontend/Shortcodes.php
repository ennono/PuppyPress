<?php
/**
 * Shortcodes for overview pages.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Frontend;

use Rudelwerk\Content\PostTypes;
use Rudelwerk\Content\Taxonomies;
use Rudelwerk\Data\Contact;
use WP_Term;

defined( 'ABSPATH' ) || exit;

/**
 * Replaces the "All …" page templates of the former theme.
 *
 * Every shortcode accepts the optional attributes `title` and `intro`.
 */
final class Shortcodes {

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'register' ) );
	}

	/**
	 * Registers the shortcodes.
	 */
	public function register() {
		$shortcodes = array(
			'rudelwerk_stud_dogs'          => 'stud_dogs',
			'rudelwerk_stud_dog_filter'    => 'stud_dog_filter',
			'rudelwerk_terms'              => 'terms',
			'rudelwerk_placement_overview' => 'placement_overview',
			'rudelwerk_posts'              => 'posts',
			'rudelwerk_litters'            => 'litters',
			'rudelwerk_breeders_board'     => 'breeders_board',
			'rudelwerk_directory'          => 'directory',
			'rudelwerk_literature'         => 'literature',
		);

		foreach ( $shortcodes as $tag => $method ) {
			add_shortcode( $tag, array( $this, $method ) );
		}
	}

	/**
	 * Stud dogs, grouped by kennel, optionally with filter form.
	 *
	 * [rudelwerk_stud_dogs filter="yes" group="kennel|none"]
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function stud_dogs( $atts ) {
		$atts = $this->atts(
			array(
				'filter' => 'no',
				'group'  => 'kennel',
			),
			$atts,
			'rudelwerk_stud_dogs'
		);

		$with_filter = wp_validate_boolean( $atts['filter'] );
		$filter      = $with_filter ? StudDogFilter::from_request() : new StudDogFilter();
		$base_args   = array(
			'post_type'      => PostTypes::STUD_DOG,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'title',
			'order'          => 'ASC',
		);

		$groups = array();

		if ( 'none' === $atts['group'] ) {
			$groups[] = array(
				'kennel' => null,
				'posts'  => get_posts( $filter->apply( $base_args ) ),
			);
		} else {
			// The ZIP code is applied by selecting the kennels.
			$per_kennel      = clone $filter;
			$per_kennel->zip = '';

			foreach ( $filter->kennels() as $kennel ) {
				$args              = $base_args;
				$args['tax_query'] = array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => Taxonomies::KENNEL,
						'field'    => 'term_id',
						'terms'    => $kennel->term_id,
					),
				);

				$posts = get_posts( $per_kennel->apply( $args ) );

				if ( $posts ) {
					$groups[] = array(
						'kennel' => $kennel,
						'posts'  => $posts,
					);
				}
			}
		}

		$html = '';

		if ( $with_filter ) {
			$html .= Templates::html( 'parts/stud-dog-filter.php', $filter->form_data() + array( 'action' => '' ) );
		}

		$html .= Templates::html( 'lists/stud-dogs.php', array( 'groups' => $groups ) );

		return $this->wrap( 'stud-dogs', $html, $atts );
	}

	/**
	 * Filter form only. Submits to the stud dog archive by default.
	 *
	 * [rudelwerk_stud_dog_filter action="current"]
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function stud_dog_filter( $atts ) {
		$atts = $this->atts( array( 'action' => 'archive' ), $atts, 'rudelwerk_stud_dog_filter' );

		$action = 'current' === $atts['action'] ? '' : get_post_type_archive_link( PostTypes::STUD_DOG );
		$html   = Templates::html( 'parts/stud-dog-filter.php', StudDogFilter::from_request()->form_data() + array( 'action' => $action ) );

		return $this->wrap( 'stud-dog-filter', $html, $atts );
	}

	/**
	 * Hierarchical list of terms (kennels, events, periods, cities, …).
	 *
	 * [rudelwerk_terms taxonomy="kennel" count="no" hide_empty="yes" depth="2"]
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function terms( $atts ) {
		$atts = $this->atts(
			array(
				'taxonomy'   => Taxonomies::KENNEL,
				'count'      => 'no',
				'hide_empty' => 'yes',
				'link_empty' => 'yes',
				'orderby'    => 'name',
				'order'      => 'ASC',
				'depth'      => 2,
			),
			$atts,
			'rudelwerk_terms'
		);

		if ( ! taxonomy_exists( $atts['taxonomy'] ) ) {
			return '';
		}

		$html = Templates::html(
			'lists/terms.php',
			array(
				'items'      => $this->term_tree( $atts['taxonomy'], 0, max( 1, (int) $atts['depth'] ), $atts ),
				'show_count' => wp_validate_boolean( $atts['count'] ),
				'link_empty' => wp_validate_boolean( $atts['link_empty'] ),
			)
		);

		return $this->wrap( 'terms rudelwerk-terms--' . sanitize_html_class( $atts['taxonomy'] ), $html, $atts );
	}

	/**
	 * Overview of dogs to place: puppies plus placement categories with counts.
	 *
	 * [rudelwerk_placement_overview]
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function placement_overview( $atts ) {
		$atts = $this->atts( array(), $atts, 'rudelwerk_placement_overview' );

		$counts  = wp_count_posts( PostTypes::PUPPIES );
		$puppies = array(
			'name'     => __( 'Puppy placement', 'rudelwerk' ),
			'url'      => (string) get_post_type_archive_link( PostTypes::PUPPIES ),
			'count'    => isset( $counts->publish ) ? (int) $counts->publish : 0,
			'children' => array(),
		);

		$items = $this->term_tree(
			Taxonomies::PLACEMENT_CATEGORY,
			0,
			2,
			array(
				'hide_empty' => 'no',
				'orderby'    => 'name',
				'order'      => 'ASC',
			)
		);

		$html = Templates::html(
			'lists/terms.php',
			array(
				'items'      => array_merge( array( $puppies ), $items ),
				'show_count' => true,
				'link_empty' => false,
			)
		);

		return $this->wrap( 'placement-overview', $html, $atts );
	}

	/**
	 * Alphabetical list of posts with kennel, e.g. approvals or breeding licenses.
	 *
	 * [rudelwerk_posts post_type="approvals"]
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function posts( $atts ) {
		$atts = $this->atts( array( 'post_type' => PostTypes::APPROVAL ), $atts, 'rudelwerk_posts' );

		if ( ! post_type_exists( $atts['post_type'] ) || ! is_post_type_viewable( $atts['post_type'] ) ) {
			return '';
		}

		$posts = get_posts(
			array(
				'post_type'      => $atts['post_type'],
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			)
		);

		return $this->wrap( 'posts', Templates::html( 'lists/posts.php', array( 'posts' => $posts ) ), $atts );
	}

	/**
	 * Litters: born and/or expected, ordered by ZIP code.
	 *
	 * [rudelwerk_litters status="all|born|expected"]
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function litters( $atts ) {
		$atts = $this->atts( array( 'status' => 'all' ), $atts, 'rudelwerk_litters' );

		$sections = array();

		if ( in_array( $atts['status'], array( 'all', 'born' ), true ) ) {
			$sections['born'] = array(
				'title' => __( 'Born litters', 'rudelwerk' ),
				'posts' => $this->litter_posts( false ),
			);
		}

		if ( in_array( $atts['status'], array( 'all', 'expected' ), true ) ) {
			$sections['expected'] = array(
				'title' => __( 'Expected litters', 'rudelwerk' ),
				'posts' => $this->litter_posts( true ),
			);
		}

		return $this->wrap(
			'litters',
			Templates::html(
				'lists/litters.php',
				array(
					'sections'      => $sections,
					'show_headings' => count( $sections ) > 1,
				)
			),
			$atts
		);
	}

	/**
	 * Visible kennels ordered by ZIP code with their breeders' contact details.
	 *
	 * [rudelwerk_breeders_board]
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function breeders_board( $atts ) {
		$atts = $this->atts( array(), $atts, 'rudelwerk_breeders_board' );

		$kennels = get_terms(
			array(
				'taxonomy'   => Taxonomies::KENNEL,
				'hide_empty' => false,
				'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
					'relation' => 'OR',
					array(
						'key'     => 'kennel_hidden',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'kennel_hidden',
						'value'   => '1',
						'compare' => '!=',
					),
				),
			)
		);

		$breeders = get_terms(
			array(
				'taxonomy'   => Taxonomies::BREEDER,
				'hide_empty' => false,
			)
		);

		$by_kennel = array();
		foreach ( is_wp_error( $breeders ) ? array() : $breeders as $breeder ) {
			$kennel_id = absint( get_term_meta( $breeder->term_id, 'breeder_kennel', true ) );

			if ( $kennel_id ) {
				$by_kennel[ $kennel_id ][] = Contact::for_term( $breeder );
			}
		}

		$rows = array();
		foreach ( is_wp_error( $kennels ) ? array() : $kennels as $kennel ) {
			$rows[] = array(
				'kennel'   => $kennel,
				'zip'      => trim( (string) get_term_meta( $kennel->term_id, 'kennel_zip', true ) ),
				'breeders' => isset( $by_kennel[ $kennel->term_id ] ) ? $by_kennel[ $kennel->term_id ] : array(),
			);
		}

		// Sort by ZIP code, kennels without ZIP code last, then by name.
		usort(
			$rows,
			static function ( $a, $b ) {
				$key_a = ( '' === $a['zip'] ? '1' : '0' ) . $a['zip'];
				$key_b = ( '' === $b['zip'] ? '1' : '0' ) . $b['zip'];

				return strcmp( $key_a, $key_b ) ?: strnatcasecmp( $a['kennel']->name, $b['kennel']->name );
			}
		);

		return $this->wrap( 'breeders-board', Templates::html( 'lists/breeders-board.php', array( 'rows' => $rows ) ), $atts );
	}

	/**
	 * Contact directory grouped by ZIP regions (trimming proofs or dog sitters).
	 *
	 * [rudelwerk_directory post_type="trimming-proofs|dog-sitters"]
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function directory( $atts ) {
		$atts = $this->atts( array( 'post_type' => PostTypes::TRIMMING_PROOF ), $atts, 'rudelwerk_directory' );

		$config = array(
			PostTypes::TRIMMING_PROOF => array(
				'zip'   => Taxonomies::TRIMMING_ZIP,
				'city'  => Taxonomies::TRIMMING_CITY,
				'empty' => __( 'No groomers found.', 'rudelwerk' ),
			),
			PostTypes::DOG_SITTER     => array(
				'zip'   => Taxonomies::DOG_SITTER_ZIP,
				'city'  => Taxonomies::DOG_SITTER_CITY,
				'empty' => __( 'No dog sitters found.', 'rudelwerk' ),
			),
		);

		if ( ! isset( $config[ $atts['post_type'] ] ) ) {
			return '';
		}

		$config  = $config[ $atts['post_type'] ];
		$regions = array();

		foreach ( $this->get_terms_list( $config['zip'], 0 ) as $region ) {
			$rows = array();

			foreach ( $this->get_terms_list( $config['zip'], $region->term_id ) as $zip ) {
				$posts = get_posts(
					array(
						'post_type'      => $atts['post_type'],
						'post_status'    => 'publish',
						'posts_per_page' => -1,
						'orderby'        => 'title',
						'order'          => 'ASC',
						'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
							array(
								'taxonomy'         => $config['zip'],
								'field'            => 'term_id',
								'terms'            => $zip->term_id,
								'include_children' => false,
							),
						),
					)
				);

				foreach ( $posts as $post ) {
					$cities = get_the_terms( $post, $config['city'] );

					$rows[] = array(
						'zip'     => $zip->name,
						'city'    => ( $cities && ! is_wp_error( $cities ) ) ? implode( ' ', wp_list_pluck( $cities, 'name' ) ) : '',
						'post'    => $post,
						'contact' => Contact::for_post( $post->ID ),
					);
				}
			}

			if ( $rows ) {
				$regions[] = array(
					'region' => $region,
					'rows'   => $rows,
				);
			}
		}

		$html = Templates::html(
			'lists/directory.php',
			array(
				'regions' => $regions,
				'empty'   => $config['empty'],
			)
		);

		return $this->wrap( 'directory rudelwerk-directory--' . sanitize_html_class( $atts['post_type'] ), $html, $atts );
	}

	/**
	 * Literature grouped by category.
	 *
	 * [rudelwerk_literature]
	 *
	 * @param array|string $atts Attributes.
	 * @return string
	 */
	public function literature( $atts ) {
		$atts = $this->atts( array(), $atts, 'rudelwerk_literature' );

		$categories = array();

		foreach ( $this->get_terms_list( Taxonomies::LITERATURE_CATEGORY, null ) as $category ) {
			$books = get_posts(
				array(
					'post_type'      => PostTypes::LITERATURE,
					'post_status'    => 'publish',
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
					'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						array(
							'taxonomy' => Taxonomies::LITERATURE_CATEGORY,
							'field'    => 'term_id',
							'terms'    => $category->term_id,
						),
					),
				)
			);

			if ( $books ) {
				$categories[] = array(
					'category' => $category,
					'books'    => $books,
				);
			}
		}

		return $this->wrap( 'literature', Templates::html( 'lists/literature.php', array( 'categories' => $categories ) ), $atts );
	}

	/**
	 * Meta query that allows ordering litters by ZIP code without losing litters without ZIP code.
	 *
	 * @param array $extra Additional clauses (combined with AND).
	 * @return array
	 */
	public static function zip_order_meta_query( array $extra = array() ) {
		$zip = array(
			'relation'   => 'OR',
			'zip_clause' => array(
				'key'     => 'zip',
				'compare' => 'EXISTS',
			),
			array(
				'key'     => 'zip',
				'compare' => 'NOT EXISTS',
			),
		);

		if ( ! $extra ) {
			return $zip;
		}

		return array_merge( array( 'relation' => 'AND', $zip ), $extra );
	}

	/**
	 * Litters, born or expected.
	 *
	 * @param bool $expected Expected litters.
	 * @return \WP_Post[]
	 */
	private function litter_posts( $expected ) {
		$status = $expected
			? array(
				array(
					'key'     => 'expected_puppies',
					'value'   => array( '', '0' ),
					'compare' => 'NOT IN',
				),
			)
			: array(
				array(
					'relation' => 'OR',
					array(
						'key'     => 'expected_puppies',
						'compare' => 'NOT EXISTS',
					),
					array(
						'key'     => 'expected_puppies',
						'value'   => array( '', '0' ),
						'compare' => 'IN',
					),
				),
			);

		return get_posts(
			array(
				'post_type'      => PostTypes::PUPPIES,
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_query'     => self::zip_order_meta_query( $status ), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'orderby'        => array(
					'zip_clause' => 'ASC',
					'date'       => 'DESC',
				),
			)
		);
	}

	/**
	 * Builds a nested term list.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @param int    $parent   Parent term ID.
	 * @param int    $depth    Remaining depth.
	 * @param array  $atts     Shortcode attributes (hide_empty, orderby, order).
	 * @return array<int, array{name: string, url: string, count: int, children: array}>
	 */
	private function term_tree( $taxonomy, $parent, $depth, array $atts ) {
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'parent'     => $parent,
				'hide_empty' => wp_validate_boolean( $atts['hide_empty'] ),
				'orderby'    => sanitize_key( $atts['orderby'] ),
				'order'      => 'DESC' === strtoupper( $atts['order'] ) ? 'DESC' : 'ASC',
			)
		);

		if ( is_wp_error( $terms ) ) {
			return array();
		}

		$items = array();

		foreach ( $terms as $term ) {
			$link    = is_taxonomy_viewable( $taxonomy ) ? get_term_link( $term ) : '';
			$items[] = array(
				'name'     => $term->name,
				'url'      => is_wp_error( $link ) ? '' : $link,
				'count'    => (int) $term->count,
				'children' => $depth > 1 ? $this->term_tree( $taxonomy, $term->term_id, $depth - 1, $atts ) : array(),
			);
		}

		return $items;
	}

	/**
	 * Non-empty terms (including parents of non-empty terms) by name.
	 *
	 * @param string   $taxonomy Taxonomy.
	 * @param int|null $parent   Parent ID, or null for all levels.
	 * @return WP_Term[]
	 */
	private function get_terms_list( $taxonomy, $parent ) {
		$args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => 'ASC',
		);

		if ( null !== $parent ) {
			$args['parent'] = $parent;
		}

		$terms = get_terms( $args );

		return is_wp_error( $terms ) ? array() : $terms;
	}

	/**
	 * Normalizes shortcode attributes (including title and intro).
	 *
	 * @param array        $defaults Defaults.
	 * @param array|string $atts     Raw attributes.
	 * @param string       $tag      Shortcode tag.
	 * @return array
	 */
	private function atts( array $defaults, $atts, $tag ) {
		return shortcode_atts(
			$defaults + array(
				'title' => '',
				'intro' => '',
			),
			$atts,
			$tag
		);
	}

	/**
	 * Wraps shortcode output and enqueues the styles.
	 *
	 * @param string $name Modifier class.
	 * @param string $html Inner HTML.
	 * @param array  $atts Attributes.
	 * @return string
	 */
	private function wrap( $name, $html, array $atts ) {
		Assets::enqueue_frontend();

		$header = '';

		if ( '' !== $atts['title'] ) {
			$header .= '<h2 class="rudelwerk__title">' . esc_html( $atts['title'] ) . '</h2>';
		}

		if ( '' !== $atts['intro'] ) {
			$header .= '<p class="rudelwerk__intro">' . esc_html( $atts['intro'] ) . '</p>';
		}

		$classes = implode( ' ', array_map( 'sanitize_html_class', explode( ' ', 'rudelwerk-' . $name ) ) );

		return '<div class="rudelwerk ' . esc_attr( $classes ) . '">' . $header . $html . '</div>';
	}
}

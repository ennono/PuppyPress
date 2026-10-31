<?php
/**
 * Compatibility with the page templates of the former theme.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Keeps pages that were assigned to an "All …" template of the old theme working.
 *
 * Once the template files are removed from the theme, WordPress falls back to
 * page.php. Rudelwerk recognizes the stored template name and appends the
 * matching shortcode output to the page content, so no page has to be edited.
 * The templates also stay selectable in the page attributes box.
 */
final class LegacyPageTemplates {

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_filter( 'theme_page_templates', array( $this, 'add_templates' ) );
		add_filter( 'the_content', array( $this, 'append_listing' ), 30 );
	}

	/**
	 * Template file => label and the shortcodes rendering the listing.
	 *
	 * @return array<string, array{label: string, shortcodes: array<int, array{0: string, 1: array}>}>
	 */
	public static function map() {
		$map = array(
			'template-all-stud-dogs.php'               => array(
				'label'      => __( 'Rudelwerk: Kennels with stud dogs', 'rudelwerk' ),
				'shortcodes' => array(
					array(
						'rudelwerk_terms',
						array(
							'taxonomy' => 'kennel',
							'title'    => __( 'Registered kennels with stud dogs', 'rudelwerk' ),
							'intro'    => __( 'An overview of all registered kennels. Click on a kennel to see its dogs.', 'rudelwerk' ),
						),
					),
				),
			),
			'template-all-stud-dogs-complete-list.php' => array(
				'label'      => __( 'Rudelwerk: All stud dogs (complete list)', 'rudelwerk' ),
				'shortcodes' => array(
					array(
						'rudelwerk_stud_dogs',
						array(
							'group' => 'none',
							'title' => __( 'All stud dogs', 'rudelwerk' ),
							'intro' => __( 'A complete list of all stud dogs we know. Click on a dog to have a closer look.', 'rudelwerk' ),
						),
					),
				),
			),
			'template-all-approvals.php'               => array(
				'label'      => __( 'Rudelwerk: Approvals', 'rudelwerk' ),
				'shortcodes' => array(
					array(
						'rudelwerk_posts',
						array(
							'post_type' => 'approvals',
							'title'     => __( 'Registered approved dogs', 'rudelwerk' ),
							'intro'     => __( 'An overview of all registered approvals. Click on an entry to read the approval.', 'rudelwerk' ),
						),
					),
				),
			),
			'template-all-breeding-licenses.php'       => array(
				'label'      => __( 'Rudelwerk: Breeding licenses', 'rudelwerk' ),
				'shortcodes' => array(
					array(
						'rudelwerk_posts',
						array(
							'post_type' => 'breeding-license',
							'title'     => __( 'Registered breeding licenses', 'rudelwerk' ),
							'intro'     => __( 'An overview of all registered breeding licenses. Click on an entry to read the license.', 'rudelwerk' ),
						),
					),
				),
			),
			'template-all-puppies.php'                 => array(
				'label'      => __( 'Rudelwerk: Puppies by city', 'rudelwerk' ),
				'shortcodes' => array(
					array(
						'rudelwerk_terms',
						array(
							'taxonomy'   => 'puppies-city',
							'count'      => 'yes',
							'hide_empty' => 'no',
							'title'      => __( 'Registered puppies', 'rudelwerk' ),
							'intro'      => __( 'An overview of all registered puppies by city. Click on a city to learn more.', 'rudelwerk' ),
						),
					),
				),
			),
			'template-all-dogs-to-place.php'           => array(
				'label'      => __( 'Rudelwerk: Dogs to place', 'rudelwerk' ),
				'shortcodes' => array(
					array(
						'rudelwerk_placement_overview',
						array(
							'title' => __( 'Registered dogs to place', 'rudelwerk' ),
							'intro' => __( 'An overview of all dogs looking for a new home. Click on a category to learn more.', 'rudelwerk' ),
						),
					),
				),
			),
			'template-all-shows.php'                   => array(
				'label'      => __( 'Rudelwerk: Shows', 'rudelwerk' ),
				'shortcodes' => array(
					array(
						'rudelwerk_terms',
						array(
							'taxonomy' => 'period',
							'title'    => __( 'Periods', 'rudelwerk' ),
							'intro'    => __( 'Click on a period to see its shows.', 'rudelwerk' ),
						),
					),
				),
			),
			'template-all-results.php'                 => array(
				'label'      => __( 'Rudelwerk: Results and shows', 'rudelwerk' ),
				'shortcodes' => array(
					array(
						'rudelwerk_terms',
						array(
							'taxonomy' => 'event',
							'title'    => __( 'Events', 'rudelwerk' ),
							'intro'    => __( 'An overview of all registered events. Click on an event to see the results.', 'rudelwerk' ),
						),
					),
					array(
						'rudelwerk_terms',
						array(
							'taxonomy' => 'period',
							'title'    => __( 'Shows', 'rudelwerk' ),
							'intro'    => __( 'Click on a period to see its shows.', 'rudelwerk' ),
						),
					),
				),
			),
			'template-all-trimming-proofs.php'         => array(
				'label'      => __( 'Rudelwerk: Trimming proofs', 'rudelwerk' ),
				'shortcodes' => array( array( 'rudelwerk_directory', array( 'post_type' => 'trimming-proofs' ) ) ),
			),
			'template-all-dog-sitters.php'             => array(
				'label'      => __( 'Rudelwerk: Dog sitters', 'rudelwerk' ),
				'shortcodes' => array( array( 'rudelwerk_directory', array( 'post_type' => 'dog-sitters' ) ) ),
			),
			'template-all-literature.php'              => array(
				'label'      => __( 'Rudelwerk: Literature', 'rudelwerk' ),
				'shortcodes' => array( array( 'rudelwerk_literature', array() ) ),
			),
			'template-breeders-board.php'              => array(
				'label'      => __( 'Rudelwerk: Breeders\' board', 'rudelwerk' ),
				'shortcodes' => array( array( 'rudelwerk_breeders_board', array() ) ),
			),
		);

		/**
		 * Filters the legacy page template map.
		 *
		 * @param array $map Template file => label and shortcodes.
		 */
		return apply_filters( 'rudelwerk_legacy_page_templates', $map );
	}

	/**
	 * Keeps the templates selectable, unless the theme still ships them.
	 *
	 * @param array $templates Template file => name.
	 * @return array
	 */
	public function add_templates( $templates ) {
		foreach ( self::map() as $file => $template ) {
			if ( ! isset( $templates[ $file ] ) ) {
				$templates[ $file ] = $template['label'];
			}
		}

		return $templates;
	}

	/**
	 * Appends the listing to pages using a legacy template.
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public function append_listing( $content ) {
		if ( ! is_page() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$template = get_page_template_slug();
		$map      = self::map();

		// If the theme still provides the template file, it renders the listing itself.
		if ( ! isset( $map[ $template ] ) || locate_template( $template ) ) {
			return $content;
		}

		global $shortcode_tags;

		foreach ( $map[ $template ]['shortcodes'] as $shortcode ) {
			list( $tag, $atts ) = $shortcode;

			if ( isset( $shortcode_tags[ $tag ] ) && is_callable( $shortcode_tags[ $tag ] ) ) {
				$content .= call_user_func( $shortcode_tags[ $tag ], $atts, '', $tag );
			}
		}

		return $content;
	}
}

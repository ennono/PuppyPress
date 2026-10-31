<?php
/**
 * Widget areas.
 *
 * @package PuppyPress
 */

namespace PuppyPress\Frontend;

use PuppyPress\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the result sidebar and shows it on single results.
 */
final class Sidebars {

	const RESULT_SIDEBAR = 'single-result-sidebar';

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'widgets_init', array( $this, 'register' ) );
		add_filter( 'sidebars_widgets', array( $this, 'swap_on_results' ) );
	}

	/**
	 * Registers the widget area (same ID as in the former theme).
	 */
	public function register() {
		register_sidebar(
			array(
				'name'          => __( 'Single result sidebar', 'puppypress' ),
				'id'            => self::RESULT_SIDEBAR,
				'description'   => __( 'Replaces the main sidebar on single result pages.', 'puppypress' ),
				'before_widget' => '<div id="%1$s" class="et_pb_widget widget %2$s">',
				'after_widget'  => '</div>',
				'before_title'  => '<h4 class="widgettitle">',
				'after_title'   => '</h4>',
			)
		);
	}

	/**
	 * Shows the result sidebar's widgets in place of the theme's main sidebar.
	 *
	 * @param array $sidebars_widgets Sidebar ID => widget IDs.
	 * @return array
	 */
	public function swap_on_results( $sidebars_widgets ) {
		if ( is_admin() || ! did_action( 'wp' ) || ! is_singular( PostTypes::RESULT ) || empty( $sidebars_widgets[ self::RESULT_SIDEBAR ] ) ) {
			return $sidebars_widgets;
		}

		/**
		 * Filters the sidebar IDs that are replaced on single results.
		 *
		 * Defaults cover Extra ("sidebar-main") and Divi/most themes ("sidebar-1").
		 *
		 * @param string[] $sidebar_ids Sidebar IDs.
		 */
		$replaced = (array) apply_filters( 'puppypress_result_replaced_sidebars', array( 'sidebar-main', 'sidebar-1' ) );

		foreach ( $replaced as $sidebar_id ) {
			if ( isset( $sidebars_widgets[ $sidebar_id ] ) ) {
				$sidebars_widgets[ $sidebar_id ] = $sidebars_widgets[ self::RESULT_SIDEBAR ];
			}
		}

		return $sidebars_widgets;
	}
}

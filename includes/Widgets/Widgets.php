<?php
/**
 * Widget registration.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Widgets;

use Rudelwerk\Content\PostTypes;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the "recent entries" widgets.
 */
final class Widgets {

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'widgets_init', array( $this, 'register' ) );
	}

	/**
	 * Registers one widget instance per configuration.
	 */
	public function register() {
		foreach ( $this->configs() as $config ) {
			register_widget( new RecentEntries( $config ) );
		}
	}

	/**
	 * Widget configurations. ID bases and option keys match the former theme.
	 *
	 * @return array[]
	 */
	public function configs() {
		$configs = array(
			array(
				'id_base'     => 'widget_all_recent_posts',
				'name'        => __( 'Rudelwerk: Recent entries', 'rudelwerk' ),
				'description' => __( 'Most recent posts, litters, stud dogs, dogs to place and results.', 'rudelwerk' ),
				'post_types'  => array( PostTypes::PUPPIES, PostTypes::STUD_DOG, PostTypes::DOG_TO_PLACE, PostTypes::RESULT, 'post' ),
				'show_type'   => true,
			),
			array(
				'id_base'     => 'widget_recent_puppies',
				'name'        => __( 'Rudelwerk: Recent litters', 'rudelwerk' ),
				'description' => __( 'Most recent litters.', 'rudelwerk' ),
				'post_types'  => array( PostTypes::PUPPIES ),
			),
			array(
				'id_base'     => 'widget_recent_stud_dogs',
				'name'        => __( 'Rudelwerk: Recent stud dogs', 'rudelwerk' ),
				'description' => __( 'Most recent stud dogs.', 'rudelwerk' ),
				'post_types'  => array( PostTypes::STUD_DOG ),
			),
			array(
				'id_base'     => 'widget_recent_results',
				'name'        => __( 'Rudelwerk: Recent results', 'rudelwerk' ),
				'description' => __( 'Most recent results, breeding licenses and shows.', 'rudelwerk' ),
				'post_types'  => array( PostTypes::RESULT, PostTypes::BREEDING_LICENSE, PostTypes::SHOW ),
				'show_type'   => true,
				'link_option' => 'all_results_url',
				'link_label'  => __( 'All results', 'rudelwerk' ),
			),
			array(
				'id_base'     => 'widget_recent_dogs_to_place',
				'name'        => __( 'Rudelwerk: Recent dogs to place', 'rudelwerk' ),
				'description' => __( 'Most recent dogs to place.', 'rudelwerk' ),
				'post_types'  => array( PostTypes::DOG_TO_PLACE ),
				'link_option' => 'all_dogs_to_place_url',
				'link_label'  => __( 'All dogs to place', 'rudelwerk' ),
			),
			array(
				'id_base'     => 'widget_recent_dogs_to_place_and_puppies',
				'name'        => __( 'Rudelwerk: Recent dogs to place and litters', 'rudelwerk' ),
				'description' => __( 'Most recent dogs to place including litters.', 'rudelwerk' ),
				'post_types'  => array( PostTypes::DOG_TO_PLACE, PostTypes::PUPPIES ),
				'show_type'   => true,
				'link_option' => 'all_dogs_to_place_url',
				'link_label'  => __( 'All dogs to place', 'rudelwerk' ),
			),
			array(
				'id_base'       => 'widget_recent_news',
				'name'          => __( 'Rudelwerk: News', 'rudelwerk' ),
				'description'   => __( 'Most recent posts, stud dogs, breeding licenses and approvals.', 'rudelwerk' ),
				'post_types'    => array( 'post', PostTypes::STUD_DOG, PostTypes::BREEDING_LICENSE, PostTypes::APPROVAL ),
				'show_type'     => true,
				/* translators: %s: type of entry, e.g. "Stud dog" */
				'type_format'   => __( 'New: %s', 'rudelwerk' ),
				'link_option'   => 'all_news_url',
				'link_label'    => __( 'All news', 'rudelwerk' ),
				'ignore_sticky' => false,
			),
		);

		/**
		 * Filters the widget configurations.
		 *
		 * @param array[] $configs Configurations.
		 */
		return apply_filters( 'rudelwerk_widgets', $configs );
	}
}

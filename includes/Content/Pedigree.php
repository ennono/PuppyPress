<?php
/**
 * Pedigree (three generations of ancestors) of a stud dog.
 *
 * @package PuppyPress
 */

namespace PuppyPress\Content;

defined( 'ABSPATH' ) || exit;

/**
 * Describes the 14 ancestor positions and how they are laid out.
 *
 * Every position stores a free text description in the meta key `{position}`
 * and an optional approval post ID in `{position}_approval`.
 */
final class Pedigree {

	/**
	 * Ancestors per generation, ordered from top (paternal) to bottom (maternal).
	 *
	 * @return array<int, string[]>
	 */
	public static function generations() {
		return array(
			array( 'father', 'mother' ),
			array( 'grandfather_1', 'grandmother_1', 'grandfather_2', 'grandmother_2' ),
			array(
				'greatgrandfather_1',
				'greatgrandmother_1',
				'greatgrandfather_2',
				'greatgrandmother_2',
				'greatgrandfather_3',
				'greatgrandmother_3',
				'greatgrandfather_4',
				'greatgrandmother_4',
			),
		);
	}

	/**
	 * All position keys.
	 *
	 * @return string[]
	 */
	public static function positions() {
		return array_merge( ...self::generations() );
	}

	/**
	 * Human readable label of a position.
	 *
	 * @param string $position Position key.
	 * @return string
	 */
	public static function label( $position ) {
		$labels = array(
			'father'             => __( 'Father', 'puppypress' ),
			'mother'             => __( 'Mother', 'puppypress' ),
			'grandfather_1'      => __( 'Paternal grandfather', 'puppypress' ),
			'grandmother_1'      => __( 'Paternal grandmother', 'puppypress' ),
			'grandfather_2'      => __( 'Maternal grandfather', 'puppypress' ),
			'grandmother_2'      => __( 'Maternal grandmother', 'puppypress' ),
			'greatgrandfather_1' => __( 'Great-grandfather 1', 'puppypress' ),
			'greatgrandmother_1' => __( 'Great-grandmother 1', 'puppypress' ),
			'greatgrandfather_2' => __( 'Great-grandfather 2', 'puppypress' ),
			'greatgrandmother_2' => __( 'Great-grandmother 2', 'puppypress' ),
			'greatgrandfather_3' => __( 'Great-grandfather 3', 'puppypress' ),
			'greatgrandmother_3' => __( 'Great-grandmother 3', 'puppypress' ),
			'greatgrandfather_4' => __( 'Great-grandfather 4', 'puppypress' ),
			'greatgrandmother_4' => __( 'Great-grandmother 4', 'puppypress' ),
		);

		return isset( $labels[ $position ] ) ? $labels[ $position ] : $position;
	}

	/**
	 * Table layout: 8 rows, each row lists the cells starting in it.
	 *
	 * A parent spans 4 rows, a grandparent 2 rows, a great-grandparent 1 row.
	 *
	 * @return array<int, array<int, array{position: string, rowspan: int, generation: int}>>
	 */
	public static function rows() {
		list( $parents, $grandparents, $greatgrandparents ) = self::generations();

		$rows = array();

		foreach ( $greatgrandparents as $index => $position ) {
			$row = array();

			if ( 0 === $index % 4 ) {
				$row[] = array(
					'position'   => $parents[ $index / 4 ],
					'rowspan'    => 4,
					'generation' => 1,
				);
			}

			if ( 0 === $index % 2 ) {
				$row[] = array(
					'position'   => $grandparents[ $index / 2 ],
					'rowspan'    => 2,
					'generation' => 2,
				);
			}

			$row[] = array(
				'position'   => $position,
				'rowspan'    => 1,
				'generation' => 3,
			);

			$rows[] = $row;
		}

		return $rows;
	}

	/**
	 * Reads the complete pedigree of a stud dog.
	 *
	 * @param int $post_id Post ID.
	 * @return array<string, array{description: string, approval: int}>
	 */
	public static function get( $post_id ) {
		$pedigree = array();

		foreach ( self::positions() as $position ) {
			$pedigree[ $position ] = array(
				'description' => (string) get_post_meta( $post_id, $position, true ),
				'approval'    => absint( get_post_meta( $post_id, $position . '_approval', true ) ),
			);
		}

		return $pedigree;
	}

	/**
	 * Whether any ancestor has been entered.
	 *
	 * @param array $pedigree Result of self::get().
	 * @return bool
	 */
	public static function is_empty( array $pedigree ) {
		foreach ( $pedigree as $ancestor ) {
			if ( '' !== trim( $ancestor['description'] ) || $ancestor['approval'] ) {
				return false;
			}
		}

		return true;
	}
}

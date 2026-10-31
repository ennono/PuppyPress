<?php
/**
 * Read helpers for stud dogs, litters and dogs to place.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Data;

use Rudelwerk\Content\PostTypes;
use Rudelwerk\Content\Taxonomies;
use WP_Term;

defined( 'ABSPATH' ) || exit;

/**
 * Domain logic that used to be spread over the theme templates.
 */
final class Dogs {

	/**
	 * Whether a kennel is marked as hidden.
	 *
	 * @param WP_Term $kennel Kennel term.
	 * @return bool
	 */
	public static function is_kennel_hidden( WP_Term $kennel ) {
		return '1' === (string) get_term_meta( $kennel->term_id, 'kennel_hidden', true );
	}

	/**
	 * The kennel shown next to a dog's name: the first kennel that is not hidden.
	 *
	 * @param int $post_id Post ID.
	 * @return WP_Term|null
	 */
	public static function display_kennel( $post_id ) {
		$kennels = get_the_terms( $post_id, Taxonomies::KENNEL );

		if ( empty( $kennels ) || is_wp_error( $kennels ) ) {
			return null;
		}

		foreach ( $kennels as $kennel ) {
			if ( ! self::is_kennel_hidden( $kennel ) ) {
				return $kennel;
			}
		}

		return null;
	}

	/**
	 * Name of a stud dog including its kennel, e.g. "Arko vom Waldrand".
	 *
	 * @param int         $post_id Post ID.
	 * @param string|null $title   Raw title; defaults to the post title.
	 * @param WP_Term     $kennel  Kennel to use instead of the display kennel.
	 * @return string
	 */
	public static function stud_dog_name( $post_id, $title = null, $kennel = null ) {
		$title  = null === $title ? get_post_field( 'post_title', $post_id, 'raw' ) : $title;
		$kennel = $kennel instanceof WP_Term ? $kennel : self::display_kennel( $post_id );

		if ( ! $kennel ) {
			return $title;
		}

		$name = get_post_meta( $post_id, 'kennel_before_name', true )
			? $kennel->name . ' ' . $title
			: $title . ' ' . $kennel->name;

		/**
		 * Filters the display name of a stud dog.
		 *
		 * @param string  $name    Name including kennel.
		 * @param string  $title   Post title.
		 * @param WP_Term $kennel  Kennel.
		 * @param int     $post_id Post ID.
		 */
		return apply_filters( 'rudelwerk_stud_dog_name', $name, $title, $kennel, $post_id );
	}

	/**
	 * Whether a litter is still expected.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_expected_litter( $post_id ) {
		// The theme stored "on" for checked boxes, Rudelwerk stores "1".
		return '' !== (string) get_post_meta( $post_id, 'expected_puppies', true );
	}

	/**
	 * Title of a litter, e.g. "Puppies vom Waldrand" or "Expected puppies vom Waldrand".
	 *
	 * @param int         $post_id Post ID.
	 * @param string|null $title   Raw title; defaults to the post title.
	 * @return string
	 */
	public static function litter_title( $post_id, $title = null ) {
		$title = null === $title ? get_post_field( 'post_title', $post_id, 'raw' ) : $title;

		$name = self::is_expected_litter( $post_id )
			/* translators: %s: litter title */
			? sprintf( __( 'Expected puppies %s', 'rudelwerk' ), $title )
			/* translators: %s: litter title */
			: sprintf( __( 'Puppies %s', 'rudelwerk' ), $title );

		/**
		 * Filters the display title of a litter.
		 *
		 * @param string $name    Title including prefix.
		 * @param string $title   Post title.
		 * @param int    $post_id Post ID.
		 */
		return apply_filters( 'rudelwerk_litter_title', $name, $title, $post_id );
	}

	/**
	 * Numbers of a litter. Values are integers or null if unknown.
	 *
	 * @param int $post_id Post ID.
	 * @return array{male: int|null, female: int|null, available_male: int|null, available_female: int|null, last_update: string}
	 */
	public static function litter_counts( $post_id ) {
		$number = static function ( $key ) use ( $post_id ) {
			$value = trim( (string) get_post_meta( $post_id, $key, true ) );
			return is_numeric( $value ) ? (int) $value : null;
		};

		return array(
			'male'             => $number( 'male_count' ),
			'female'           => $number( 'female_count' ),
			'available_male'   => $number( 'still_available_male' ),
			'available_female' => $number( 'still_available_female' ),
			'last_update'      => (string) get_post_meta( $post_id, 'still_available_last_update', true ),
		);
	}

	/**
	 * "2 males, 3 females" – skips unknown numbers.
	 *
	 * @param int|null $male   Number of males.
	 * @param int|null $female Number of females.
	 * @return string
	 */
	public static function format_sexes( $male, $female ) {
		$parts = array();

		if ( null !== $male ) {
			/* translators: %d: number of male puppies */
			$parts[] = sprintf( _n( '%d male', '%d males', $male, 'rudelwerk' ), $male );
		}

		if ( null !== $female ) {
			/* translators: %d: number of female puppies */
			$parts[] = sprintf( _n( '%d female', '%d females', $female, 'rudelwerk' ), $female );
		}

		return implode( ', ', $parts );
	}

	/**
	 * Attachment IDs of a media field.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @return int[]
	 */
	public static function attachment_ids( $post_id, $key ) {
		return array_values( array_filter( array_map( 'absint', explode( ',', (string) get_post_meta( $post_id, $key, true ) ) ) ) );
	}

	/**
	 * Formats a stored Y-m-d date for display.
	 *
	 * @param string $date Date (Y-m-d).
	 * @return string Empty string for empty or invalid dates.
	 */
	public static function format_date( $date ) {
		$timestamp = $date ? strtotime( $date . ' 12:00:00' ) : false;

		if ( ! $timestamp ) {
			return '';
		}

		/**
		 * Filters the date format used for dates of birth.
		 *
		 * @param string $format PHP date format.
		 */
		$format = apply_filters( 'rudelwerk_date_format', get_option( 'date_format' ) );

		return wp_date( $format, $timestamp, wp_timezone() );
	}

	/**
	 * Formats the withers height, e.g. "62" or "62,5".
	 *
	 * @param string $withers Stored value.
	 * @return string
	 */
	public static function format_withers( $withers ) {
		if ( ! is_numeric( $withers ) ) {
			return (string) $withers;
		}

		$value = (float) $withers;

		return number_format_i18n( $value, round( $value ) === $value ? 0 : 1 );
	}

	/**
	 * Display label of a dog's gender.
	 *
	 * @param string $gender Stored value.
	 * @return string
	 */
	public static function gender_label( $gender ) {
		$options = \Rudelwerk\Content\Fields::gender_options();

		return ( '' !== $gender && isset( $options[ $gender ] ) ) ? $options[ $gender ] : $gender;
	}

	/**
	 * Display label of a post type, e.g. for "Recent posts" widgets.
	 *
	 * @param string $post_type Post type.
	 * @return string
	 */
	public static function post_type_label( $post_type ) {
		if ( PostTypes::PUPPIES === $post_type ) {
			return __( 'Puppies', 'rudelwerk' );
		}

		$object = get_post_type_object( $post_type );

		return $object ? $object->labels->singular_name : $post_type;
	}
}

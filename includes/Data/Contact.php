<?php
/**
 * Contact details of breeders, owners, groomers and dog sitters.
 *
 * @package PuppyPress
 */

namespace PuppyPress\Data;

use WP_Term;

defined( 'ABSPATH' ) || exit;

/**
 * Normalizes contact details into one shape.
 */
final class Contact {

	const KEYS = array( 'address', 'phone', 'mobile', 'fax', 'email', 'website' );

	/**
	 * Contact details of a breeder or owner term (meta keys "{taxonomy}_{key}").
	 *
	 * @param WP_Term $term Breeder or owner.
	 * @return array{name: string, address: string, phone: string, mobile: string, fax: string, email: string, website: string}
	 */
	public static function for_term( WP_Term $term ) {
		$contact = array( 'name' => $term->name );

		foreach ( self::KEYS as $key ) {
			$contact[ $key ] = trim( (string) get_term_meta( $term->term_id, $term->taxonomy . '_' . $key, true ) );
		}

		return $contact;
	}

	/**
	 * Contact details of a trimming proof or dog sitter post.
	 *
	 * @param int $post_id Post ID.
	 * @return array{name: string, address: string, phone: string, mobile: string, fax: string, email: string, website: string}
	 */
	public static function for_post( $post_id ) {
		$contact = array( 'name' => get_the_title( $post_id ) );

		foreach ( self::KEYS as $key ) {
			$contact[ $key ] = trim( (string) get_post_meta( $post_id, $key, true ) );
		}

		return $contact;
	}

	/**
	 * Best link for a contact: email first, then website.
	 *
	 * @param array $contact Contact details.
	 * @param bool  $website_first Prefer the website.
	 * @return string URL or empty string.
	 */
	public static function link( array $contact, $website_first = false ) {
		$email   = is_email( $contact['email'] ) ? 'mailto:' . $contact['email'] : '';
		$website = $contact['website'];

		return $website_first ? ( $website ? $website : $email ) : ( $email ? $email : $website );
	}
}

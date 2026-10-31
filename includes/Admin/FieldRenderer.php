<?php
/**
 * Renders form controls for schema fields.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Outputs the input element for a field definition of Content\Fields.
 */
final class FieldRenderer {

	const INPUT_NAME = 'rudelwerk_fields';

	/**
	 * Cached choice lists for post and term selects.
	 *
	 * @var array<string, array<int, string>>
	 */
	private static $choices = array();

	/**
	 * HTML id of a field.
	 *
	 * @param string $key Meta key.
	 * @return string
	 */
	public static function id( $key ) {
		return 'rudelwerk-field-' . str_replace( '_', '-', $key );
	}

	/**
	 * Echoes the control of a field.
	 *
	 * @param string $key   Meta key.
	 * @param array  $field Field definition.
	 * @param string $value Current value.
	 */
	public static function control( $key, array $field, $value ) {
		$id   = self::id( $key );
		$name = self::INPUT_NAME . '[' . $key . ']';

		switch ( $field['type'] ) {
			case 'textarea':
				printf(
					'<textarea id="%1$s" name="%2$s" rows="4" class="large-text">%3$s</textarea>',
					esc_attr( $id ),
					esc_attr( $name ),
					esc_textarea( $value )
				);
				break;

			case 'checkbox':
				printf(
					'<input type="checkbox" id="%1$s" name="%2$s" value="1" %3$s />',
					esc_attr( $id ),
					esc_attr( $name ),
					checked( '' !== $value && '0' !== $value, true, false )
				);
				break;

			case 'select':
				$options = isset( $field['options'] ) ? $field['options'] : array();

				// Keep legacy values that are not part of the option list.
				if ( '' !== $value && ! array_key_exists( $value, $options ) ) {
					$options[ $value ] = $value;
				}

				self::select( $id, $name, $options, $value );
				break;

			case 'post':
				self::select( $id, $name, array( '' => __( '— None —', 'rudelwerk' ) ) + self::post_choices( $field['post_type'] ), $value );

				if ( ! empty( $field['add_new'] ) ) {
					$object = get_post_type_object( $field['post_type'] );

					if ( $object && current_user_can( $object->cap->create_posts ) ) {
						printf(
							'<a class="rudelwerk-add-new" href="%1$s" target="_blank" rel="noopener">%2$s</a>',
							esc_url( admin_url( 'post-new.php?post_type=' . $field['post_type'] ) ),
							esc_html( $object->labels->add_new_item )
						);
					}
				}
				break;

			case 'term':
				self::select( $id, $name, array( '' => __( '— None —', 'rudelwerk' ) ) + self::term_choices( $field['taxonomy'] ), $value );
				break;

			case 'media':
				self::media( $id, $name, $value );
				break;

			default:
				$types = array(
					'date'   => 'date',
					'number' => 'number',
					'email'  => 'email',
					'url'    => 'url',
				);

				printf(
					'<input type="%1$s" id="%2$s" name="%3$s" value="%4$s" class="widefat"%5$s />',
					esc_attr( isset( $types[ $field['type'] ] ) ? $types[ $field['type'] ] : 'text' ),
					esc_attr( $id ),
					esc_attr( $name ),
					esc_attr( $value ),
					isset( $field['step'] ) ? ' step="' . esc_attr( $field['step'] ) . '"' : ''
				);
		}

		if ( ! empty( $field['description'] ) && 'checkbox' !== $field['type'] ) {
			printf( '<p class="description">%s</p>', esc_html( $field['description'] ) );
		}
	}

	/**
	 * Echoes a select element.
	 *
	 * @param string $id      HTML id.
	 * @param string $name    Input name.
	 * @param array  $options Value => label.
	 * @param string $value   Selected value.
	 */
	private static function select( $id, $name, array $options, $value ) {
		printf( '<select id="%1$s" name="%2$s" class="widefat">', esc_attr( $id ), esc_attr( $name ) );

		foreach ( $options as $option_value => $label ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $option_value ),
				selected( (string) $option_value, (string) $value, false ),
				esc_html( $label )
			);
		}

		echo '</select>';
	}

	/**
	 * Echoes the media picker (comma separated attachment IDs).
	 *
	 * @param string $id    HTML id.
	 * @param string $name  Input name.
	 * @param string $value Attachment IDs.
	 */
	private static function media( $id, $name, $value ) {
		$ids = array_filter( array_map( 'absint', explode( ',', (string) $value ) ) );

		echo '<div class="rudelwerk-media" data-rudelwerk-media>';
		printf(
			'<input type="hidden" id="%1$s" name="%2$s" value="%3$s" data-rudelwerk-media-input />',
			esc_attr( $id ),
			esc_attr( $name ),
			esc_attr( implode( ',', $ids ) )
		);

		echo '<ul class="rudelwerk-media__preview" data-rudelwerk-media-preview>';
		foreach ( $ids as $attachment_id ) {
			printf(
				'<li data-id="%1$d">%2$s<button type="button" class="rudelwerk-media__remove" aria-label="%3$s">&times;</button></li>',
				(int) $attachment_id,
				wp_get_attachment_image( $attachment_id, 'thumbnail' ),
				esc_attr__( 'Remove', 'rudelwerk' )
			);
		}
		echo '</ul>';

		printf(
			'<button type="button" class="button" data-rudelwerk-media-add>%s</button>',
			esc_html__( 'Add photos', 'rudelwerk' )
		);
		echo '</div>';
	}

	/**
	 * Published posts of a type, ordered by title.
	 *
	 * @param string $post_type Post type.
	 * @return array<int, string>
	 */
	private static function post_choices( $post_type ) {
		$cache_key = 'post:' . $post_type;

		if ( ! isset( self::$choices[ $cache_key ] ) ) {
			$posts = get_posts(
				array(
					'post_type'              => $post_type,
					'post_status'            => 'publish',
					'numberposts'            => -1,
					'orderby'                => 'title',
					'order'                  => 'ASC',
					'no_found_rows'          => true,
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);

			self::$choices[ $cache_key ] = array();

			foreach ( $posts as $post ) {
				self::$choices[ $cache_key ][ $post->ID ] = $post->post_title;
			}
		}

		return self::$choices[ $cache_key ];
	}

	/**
	 * Terms of a taxonomy, ordered by name.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @return array<int, string>
	 */
	private static function term_choices( $taxonomy ) {
		$cache_key = 'term:' . $taxonomy;

		if ( ! isset( self::$choices[ $cache_key ] ) ) {
			$terms = get_terms(
				array(
					'taxonomy'   => $taxonomy,
					'hide_empty' => false,
					'orderby'    => 'name',
				)
			);

			self::$choices[ $cache_key ] = is_wp_error( $terms ) ? array() : wp_list_pluck( $terms, 'name', 'term_id' );
		}

		return self::$choices[ $cache_key ];
	}
}

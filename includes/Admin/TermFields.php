<?php
/**
 * Custom fields for kennels, breeders and owners.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Admin;

use Rudelwerk\Content\Fields;
use WP_Term;

defined( 'ABSPATH' ) || exit;

/**
 * Adds the term meta fields of Content\Fields to the term screens.
 */
final class TermFields {

	const NONCE_ACTION = 'rudelwerk_save_term_meta';
	const NONCE_NAME   = 'rudelwerk_term_nonce';

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'init', array( $this, 'hook_taxonomies' ), 30 );
	}

	/**
	 * Attaches the form and save callbacks to every taxonomy with fields.
	 */
	public function hook_taxonomies() {
		foreach ( array_keys( Fields::term_fields() ) as $taxonomy ) {
			add_action( "{$taxonomy}_add_form_fields", array( $this, 'render_add' ) );
			add_action( "{$taxonomy}_edit_form_fields", array( $this, 'render_edit' ), 10, 2 );
			add_action( "created_{$taxonomy}", array( $this, 'save' ) );
			add_action( "edited_{$taxonomy}", array( $this, 'save' ) );
		}
	}

	/**
	 * Fields on the "Add new term" form.
	 *
	 * @param string $taxonomy Taxonomy.
	 */
	public function render_add( $taxonomy ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		foreach ( $this->fields( $taxonomy ) as $key => $field ) {
			$default = isset( $field['unchecked'] ) ? $field['unchecked'] : '';

			echo '<div class="form-field rudelwerk-term-field">';
			printf( '<label for="%1$s">%2$s</label>', esc_attr( FieldRenderer::id( $key ) ), esc_html( $field['label'] ) );
			FieldRenderer::control( $key, $field, $default );
			$this->checkbox_description( $field );
			echo '</div>';
		}
	}

	/**
	 * Fields on the "Edit term" form.
	 *
	 * @param WP_Term $term     Term.
	 * @param string  $taxonomy Taxonomy.
	 */
	public function render_edit( $term, $taxonomy ) {
		wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );

		foreach ( $this->fields( $taxonomy ) as $key => $field ) {
			echo '<tr class="form-field rudelwerk-term-field">';
			printf( '<th scope="row"><label for="%1$s">%2$s</label></th><td>', esc_attr( FieldRenderer::id( $key ) ), esc_html( $field['label'] ) );
			FieldRenderer::control( $key, $field, (string) get_term_meta( $term->term_id, $key, true ) );
			$this->checkbox_description( $field );
			echo '</td></tr>';
		}
	}

	/**
	 * Saves the term fields.
	 *
	 * @param int $term_id Term ID.
	 */
	public function save( $term_id ) {
		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE_NAME ] ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_term', $term_id ) ) {
			return;
		}

		$term = get_term( $term_id );

		if ( ! $term instanceof WP_Term ) {
			return;
		}

		// Values are sanitized per field type in Fields::sanitize().
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$input = isset( $_POST[ FieldRenderer::INPUT_NAME ] ) ? wp_unslash( (array) $_POST[ FieldRenderer::INPUT_NAME ] ) : array();

		foreach ( $this->fields( $term->taxonomy ) as $key => $field ) {
			if ( 'checkbox' === $field['type'] ) {
				$raw = isset( $input[ $key ] ) ? '1' : '';
			} elseif ( array_key_exists( $key, $input ) ) {
				$raw = $input[ $key ];
			} else {
				continue;
			}

			update_term_meta( $term_id, $key, wp_slash( Fields::sanitize( $raw, $field ) ) );
		}
	}

	/**
	 * Fields of a taxonomy.
	 *
	 * @param string $taxonomy Taxonomy.
	 * @return array<string, array>
	 */
	private function fields( $taxonomy ) {
		$fields = Fields::term_fields();

		return isset( $fields[ $taxonomy ] ) ? $fields[ $taxonomy ] : array();
	}

	/**
	 * Checkbox descriptions are not printed by the renderer itself.
	 *
	 * @param array $field Field definition.
	 */
	private function checkbox_description( array $field ) {
		if ( 'checkbox' === $field['type'] && ! empty( $field['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $field['description'] ) );
		}
	}
}

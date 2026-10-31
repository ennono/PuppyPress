<?php
/**
 * Meta boxes for all post types.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Admin;

use Rudelwerk\Content\Fields;
use Rudelwerk\Content\Pedigree;
use WP_Post;

defined( 'ABSPATH' ) || exit;

/**
 * Renders and saves the meta boxes defined in Content\Fields.
 */
final class MetaBoxes {

	const NONCE_ACTION = 'rudelwerk_save_meta';
	const NONCE_NAME   = 'rudelwerk_meta_nonce';

	/**
	 * Whether the nonce field has been printed on this screen.
	 *
	 * @var bool
	 */
	private $nonce_printed = false;

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ), 10, 2 );
		add_action( 'save_post', array( $this, 'save' ), 10, 2 );
	}

	/**
	 * Registers the meta boxes of the current post type.
	 *
	 * @param string $post_type Post type.
	 */
	public function add_meta_boxes( $post_type ) {
		$groups = Fields::post_groups();

		if ( empty( $groups[ $post_type ] ) ) {
			return;
		}

		foreach ( $groups[ $post_type ] as $group_id => $group ) {
			add_meta_box(
				'rudelwerk-' . str_replace( '_', '-', $group_id ),
				$group['title'],
				array( $this, 'render' ),
				$post_type,
				isset( $group['context'] ) ? $group['context'] : 'normal',
				isset( $group['priority'] ) ? $group['priority'] : 'default',
				array( 'group' => $group )
			);
		}
	}

	/**
	 * Renders a meta box.
	 *
	 * @param WP_Post $post Post.
	 * @param array   $box  Meta box arguments.
	 */
	public function render( $post, $box ) {
		$group = $box['args']['group'];

		if ( ! $this->nonce_printed ) {
			wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME );
			$this->nonce_printed = true;
		}

		if ( isset( $group['renderer'] ) && 'pedigree' === $group['renderer'] ) {
			$this->render_pedigree( $post, $group['fields'] );
			return;
		}

		echo '<div class="rudelwerk-fields">';

		foreach ( $group['fields'] as $key => $field ) {
			$value = (string) get_post_meta( $post->ID, $key, true );

			echo '<div class="rudelwerk-field rudelwerk-field--' . esc_attr( $field['type'] ) . '">';

			if ( 'checkbox' === $field['type'] ) {
				echo '<label>';
				FieldRenderer::control( $key, $field, $value );
				echo ' ' . esc_html( $field['label'] ) . '</label>';
			} else {
				printf( '<label for="%1$s">%2$s</label>', esc_attr( FieldRenderer::id( $key ) ), esc_html( $field['label'] ) );
				FieldRenderer::control( $key, $field, $value );
			}

			echo '</div>';
		}

		echo '</div>';
	}

	/**
	 * Renders the pedigree as a three-generation grid.
	 *
	 * @param WP_Post $post   Post.
	 * @param array   $fields Pedigree fields.
	 */
	private function render_pedigree( WP_Post $post, array $fields ) {
		echo '<div class="rudelwerk-pedigree-scroll"><table class="rudelwerk-pedigree-admin"><tbody>';

		foreach ( Pedigree::rows() as $row ) {
			echo '<tr>';

			foreach ( $row as $cell ) {
				$position     = $cell['position'];
				$approval_key = $position . '_approval';

				printf( '<td rowspan="%1$d" class="generation-%2$d">', (int) $cell['rowspan'], (int) $cell['generation'] );
				printf(
					'<label class="rudelwerk-pedigree-admin__name" for="%1$s">%2$s</label>',
					esc_attr( FieldRenderer::id( $position ) ),
					esc_html( Pedigree::label( $position ) )
				);
				FieldRenderer::control( $position, $fields[ $position ], (string) get_post_meta( $post->ID, $position, true ) );
				printf(
					'<label class="rudelwerk-pedigree-admin__approval" for="%1$s">%2$s</label>',
					esc_attr( FieldRenderer::id( $approval_key ) ),
					esc_html__( 'Approval', 'rudelwerk' )
				);
				FieldRenderer::control( $approval_key, $fields[ $approval_key ], (string) get_post_meta( $post->ID, $approval_key, true ) );
				echo '</td>';
			}

			echo '</tr>';
		}

		echo '</tbody></table></div>';
	}

	/**
	 * Saves all fields of the post type.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post.
	 */
	public function save( $post_id, $post ) {
		$fields = Fields::for_post_type( $post->post_type );

		if ( empty( $fields ) ) {
			return;
		}

		if ( ! isset( $_POST[ self::NONCE_NAME ] ) || ! wp_verify_nonce( sanitize_key( $_POST[ self::NONCE_NAME ] ), self::NONCE_ACTION ) ) {
			return;
		}

		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Values are sanitized per field type in Fields::sanitize().
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$input = isset( $_POST[ FieldRenderer::INPUT_NAME ] ) ? wp_unslash( (array) $_POST[ FieldRenderer::INPUT_NAME ] ) : array();

		foreach ( $fields as $key => $field ) {
			if ( 'checkbox' === $field['type'] ) {
				$raw = isset( $input[ $key ] ) ? '1' : '';
			} elseif ( array_key_exists( $key, $input ) ) {
				$raw = $input[ $key ];
			} else {
				continue;
			}

			update_post_meta( $post_id, $key, wp_slash( Fields::sanitize( $raw, $field ) ) );
		}
	}
}

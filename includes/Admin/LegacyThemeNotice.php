<?php
/**
 * Warns if the former theme still registers the content types.
 *
 * @package PuppyPress
 */

namespace PuppyPress\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Shows an admin notice while the old "Extra Child" includes are still loaded.
 */
final class LegacyThemeNotice {

	/**
	 * Functions of the former theme that PuppyPress replaces.
	 */
	const LEGACY_FUNCTIONS = array(
		'extrachild_custom_post_types',
		'extrachild_custom_taxonomies',
		'extrachild_stud_dogs_add_details_meta_box',
		'extrachild_load_all_recent_posts_widgets',
		'extrachild_register_additional_sidebars',
	);

	/**
	 * Hooks.
	 */
	public function register_hooks() {
		add_action( 'admin_notices', array( $this, 'render' ) );
	}

	/**
	 * Prints the notice for administrators.
	 */
	public function render() {
		if ( ! current_user_can( 'switch_themes' ) ) {
			return;
		}

		$found = array_filter( self::LEGACY_FUNCTIONS, 'function_exists' );

		if ( ! $found ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s</p><p><code>%3$s</code></p></div>',
			esc_html__( 'PuppyPress:', 'puppypress' ),
			esc_html__( 'The active theme still registers post types, fields or widgets that are now provided by PuppyPress. Please remove the files in the theme\'s "includes" folder and their require_once lines from functions.php (see docs/MIGRATION.md).', 'puppypress' ),
			esc_html( implode( '(), ', $found ) . '()' )
		);
	}
}

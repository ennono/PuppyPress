<?php
/**
 * Template loader.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Loads templates from the plugin, overridable by themes.
 *
 * A theme overrides `templates/single/stud-dog.php` by placing a copy in
 * `{theme}/rudelwerk/single/stud-dog.php`.
 */
final class Templates {

	/**
	 * Resolves the path of a template.
	 *
	 * @param string $name Template name relative to the templates folder, e.g. "parts/pedigree.php".
	 * @return string
	 */
	public static function locate( $name ) {
		$name  = ltrim( $name, '/' );
		$theme = locate_template( array( 'rudelwerk/' . $name ) );
		$path  = $theme ? $theme : RUDELWERK_DIR . 'templates/' . $name;

		/**
		 * Filters the resolved template path.
		 *
		 * @param string $path Absolute path.
		 * @param string $name Template name.
		 */
		return apply_filters( 'rudelwerk_template', $path, $name );
	}

	/**
	 * Echoes a template. Variables are available as `$args` inside the template.
	 *
	 * @param string $name Template name.
	 * @param array  $args Template variables.
	 */
	public static function render( $name, array $args = array() ) {
		$path = self::locate( $name );

		if ( ! is_readable( $path ) ) {
			return;
		}

		( static function ( $rudelwerk_template_path, $args ) {
			include $rudelwerk_template_path;
		} )( $path, $args );
	}

	/**
	 * Returns a rendered template as string.
	 *
	 * @param string $name Template name.
	 * @param array  $args Template variables.
	 * @return string
	 */
	public static function html( $name, array $args = array() ) {
		ob_start();
		self::render( $name, $args );
		return (string) ob_get_clean();
	}
}

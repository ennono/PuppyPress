<?php
/**
 * Inline SVG icons (based on Feather icons, MIT license).
 *
 * @package PuppyPress
 */

namespace PuppyPress\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Self-contained icons, so the plugin does not depend on an icon font of the theme.
 */
final class Icons {

	/**
	 * SVG paths.
	 *
	 * @var array<string, string>
	 */
	private static $paths = array(
		'phone'   => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
		'mobile'  => '<rect x="5" y="2" width="14" height="20" rx="2" ry="2"/><line x1="12" y1="18" x2="12.01" y2="18"/>',
		'fax'     => '<polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/>',
		'email'   => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
		'website' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
		'arrow'   => '<circle cx="12" cy="12" r="10"/><polyline points="12 16 16 12 12 8"/><line x1="8" y1="12" x2="16" y2="12"/>',
	);

	/**
	 * Returns the SVG markup of an icon.
	 *
	 * @param string $name Icon name.
	 * @return string
	 */
	public static function svg( $name ) {
		if ( ! isset( self::$paths[ $name ] ) ) {
			return '';
		}

		$svg = '<svg class="puppypress-icon puppypress-icon--' . esc_attr( $name ) . '" xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . self::$paths[ $name ] . '</svg>';

		/**
		 * Filters an icon, e.g. to replace it with an icon font.
		 *
		 * @param string $svg  SVG markup.
		 * @param string $name Icon name.
		 */
		return apply_filters( 'puppypress_icon', $svg, $name );
	}
}

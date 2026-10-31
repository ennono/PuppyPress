<?php
/**
 * Plugin Name:       PuppyPress
 * Plugin URI:        https://www.fricke-media.de
 * Description:       Club management for dog breeding clubs: stud dogs with pedigree, litters, dogs to place, approvals, breeding licenses, results, shows, trimming proofs, dog sitters and literature.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Timo Fricke
 * Author URI:        https://www.fricke-media.de
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       puppypress
 * Domain Path:       /languages
 *
 * @package PuppyPress
 */

defined( 'ABSPATH' ) || exit;

define( 'PUPPYPRESS_VERSION', '1.0.0' );
define( 'PUPPYPRESS_FILE', __FILE__ );
define( 'PUPPYPRESS_DIR', plugin_dir_path( __FILE__ ) );
define( 'PUPPYPRESS_URL', plugin_dir_url( __FILE__ ) );

/*
 * PSR-4 style autoloader: PuppyPress\Foo\Bar => includes/Foo/Bar.php
 */
spl_autoload_register(
	static function ( $class ) {
		$prefix = 'PuppyPress\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$file = PUPPYPRESS_DIR . 'includes/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';

		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

require_once PUPPYPRESS_DIR . 'includes/template-functions.php';

register_activation_hook( __FILE__, array( 'PuppyPress\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PuppyPress\\Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'PuppyPress\\Plugin', 'instance' ) );

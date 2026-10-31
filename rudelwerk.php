<?php
/**
 * Plugin Name:       Rudelwerk
 * Plugin URI:        https://www.fricke-media.de
 * Description:       Club management for dog breeding clubs: stud dogs with pedigree, litters, dogs to place, approvals, breeding licenses, results, shows, trimming proofs, dog sitters and literature.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Timo Fricke
 * Author URI:        https://www.fricke-media.de
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       rudelwerk
 * Domain Path:       /languages
 *
 * @package Rudelwerk
 */

defined( 'ABSPATH' ) || exit;

define( 'RUDELWERK_VERSION', '1.0.0' );
define( 'RUDELWERK_FILE', __FILE__ );
define( 'RUDELWERK_DIR', plugin_dir_path( __FILE__ ) );
define( 'RUDELWERK_URL', plugin_dir_url( __FILE__ ) );

/*
 * PSR-4 style autoloader: Rudelwerk\Foo\Bar => includes/Foo/Bar.php
 */
spl_autoload_register(
	static function ( $class ) {
		$prefix = 'Rudelwerk\\';

		if ( 0 !== strpos( $class, $prefix ) ) {
			return;
		}

		$file = RUDELWERK_DIR . 'includes/' . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';

		if ( is_readable( $file ) ) {
			require $file;
		}
	}
);

require_once RUDELWERK_DIR . 'includes/template-functions.php';

register_activation_hook( __FILE__, array( 'Rudelwerk\\Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Rudelwerk\\Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'Rudelwerk\\Plugin', 'instance' ) );

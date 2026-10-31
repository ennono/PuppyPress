<?php
/**
 * Bootstrap.
 *
 * @package PuppyPressMigrator
 */

namespace PuppyPressMigrator;

defined( 'ABSPATH' ) || exit;

/**
 * Wires the admin screen, WP-CLI and housekeeping.
 */
final class Plugin {

	const CLEANUP_HOOK = 'ppm_cleanup_jobs';

	/**
	 * Registers hooks.
	 */
	public static function boot() {
		add_action( 'init', array( __CLASS__, 'load_textdomain' ) );
		add_action( self::CLEANUP_HOOK, array( Storage::class, 'cleanup' ) );

		if ( ! wp_next_scheduled( self::CLEANUP_HOOK ) ) {
			wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', self::CLEANUP_HOOK );
		}

		if ( is_admin() ) {
			( new Admin\Page() )->register_hooks();
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'puppypress-migrator', Cli\Command::class );
		}
	}

	/**
	 * Loads translations.
	 */
	public static function load_textdomain() {
		load_plugin_textdomain( 'puppypress-migrator', false, dirname( plugin_basename( PPM_FILE ) ) . '/languages' );
	}

	/**
	 * Removes the cron event (job files are kept until cleanup or uninstall).
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( self::CLEANUP_HOOK );
	}
}

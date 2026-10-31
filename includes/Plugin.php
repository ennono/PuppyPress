<?php
/**
 * Plugin bootstrap.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk;

defined( 'ABSPATH' ) || exit;

/**
 * Wires all modules into WordPress.
 */
final class Plugin {

	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Returns (and on first call boots) the plugin.
	 *
	 * @return Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
			self::$instance->boot();
		}

		return self::$instance;
	}

	/**
	 * Registers all hooks.
	 */
	private function boot() {
		add_action( 'init', array( $this, 'load_textdomain' ), 0 );
		add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ), 99 );

		( new Content\PostTypes() )->register_hooks();
		( new Content\Taxonomies() )->register_hooks();
		( new Content\Fields() )->register_hooks();
		( new Widgets\Widgets() )->register_hooks();
		( new Frontend\Sidebars() )->register_hooks();
		( new Compat\TermCustomFieldOrder() )->register_hooks();

		if ( is_admin() ) {
			( new Admin\MetaBoxes() )->register_hooks();
			( new Admin\TermFields() )->register_hooks();
			( new Admin\Assets() )->register_hooks();
			( new Admin\LegacyThemeNotice() )->register_hooks();
		}

		( new Frontend\Assets() )->register_hooks();
		( new Frontend\Titles() )->register_hooks();
		( new Frontend\SingleContent() )->register_hooks();
		( new Frontend\Queries() )->register_hooks();
		( new Frontend\Shortcodes() )->register_hooks();
		( new Frontend\LegacyPageTemplates() )->register_hooks();

		/**
		 * Fires after Rudelwerk has registered all of its hooks.
		 *
		 * @param Plugin $plugin Plugin instance.
		 */
		do_action( 'rudelwerk_loaded', $this );
	}

	/**
	 * Loads translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'rudelwerk', false, dirname( plugin_basename( RUDELWERK_FILE ) ) . '/languages' );
	}

	/**
	 * Flushes rewrite rules once after activation or an update.
	 *
	 * Runs late on `init` so that all post types and taxonomies are registered
	 * with their translated slugs.
	 */
	public function maybe_flush_rewrite_rules() {
		if ( get_option( 'rudelwerk_version' ) === RUDELWERK_VERSION ) {
			return;
		}

		flush_rewrite_rules();
		update_option( 'rudelwerk_version', RUDELWERK_VERSION );
	}

	/**
	 * Activation: schedule a rewrite flush for the next request.
	 */
	public static function activate() {
		delete_option( 'rudelwerk_version' );
	}

	/**
	 * Deactivation: let WordPress rebuild its rewrite rules without ours.
	 */
	public static function deactivate() {
		delete_option( 'rewrite_rules' );
		delete_option( 'rudelwerk_version' );
	}
}

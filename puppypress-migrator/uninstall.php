<?php
/**
 * Removes job files and markers.
 *
 * @package PuppyPressMigrator
 */

defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

$ppm_uploads = wp_upload_dir( null, false );
$ppm_dir     = trailingslashit( $ppm_uploads['basedir'] ) . 'puppypress-migrator';

if ( is_dir( $ppm_dir ) ) {
	$ppm_items = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator( $ppm_dir, FilesystemIterator::SKIP_DOTS ),
		RecursiveIteratorIterator::CHILD_FIRST
	);

	foreach ( $ppm_items as $ppm_item ) {
		$ppm_item->isDir() ? rmdir( $ppm_item->getPathname() ) : unlink( $ppm_item->getPathname() ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	}

	rmdir( $ppm_dir ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_rmdir
}

global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'ppm\\_widgets\\_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

wp_clear_scheduled_hook( 'ppm_cleanup_jobs' );

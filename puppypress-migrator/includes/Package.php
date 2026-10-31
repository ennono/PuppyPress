<?php
/**
 * The migration package (ZIP).
 *
 * @package PuppyPressMigrator
 */

namespace PuppyPressMigrator;

defined( 'ABSPATH' ) || exit;

/**
 * Layout:
 *   data.json        All records (see Exporter::collect()).
 *   uploads/{path}   Media files, relative to the uploads folder.
 */
final class Package {

	const FORMAT  = 'puppypress-migration';
	const VERSION = 1;

	const DATA_ENTRY    = 'data.json';
	const UPLOADS_ENTRY = 'uploads/';

	/**
	 * File extensions that are never extracted, even if WordPress would allow them.
	 */
	const BLOCKED_EXTENSIONS = array( 'php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'pht', 'phps', 'cgi', 'pl', 'py', 'sh', 'asp', 'aspx', 'jsp', 'htaccess', 'htpasswd', 'exe', 'js', 'html', 'htm', 'svg', 'svgz' );

	/**
	 * Whether ZIP support is available.
	 *
	 * @return bool
	 */
	public static function supported() {
		return class_exists( 'ZipArchive' );
	}

	/**
	 * Validates the relative path of a media file inside the package.
	 *
	 * @param string $path Path relative to uploads/ (e.g. "2020/04/dog.jpg").
	 * @return bool
	 */
	public static function is_allowed_upload_path( $path ) {
		$path = (string) $path;

		if ( '' === $path || false !== strpos( $path, "\0" ) || false !== strpos( $path, '\\' ) || '/' === $path[0] || preg_match( '#(^|/)\.\.?(/|$)#', $path ) || preg_match( '#^[a-z]:#i', $path ) ) {
			return false;
		}

		if ( 0 !== validate_file( $path ) ) {
			return false;
		}

		$basename = basename( $path );

		if ( '.' === $basename[0] ) {
			return false;
		}

		$parts = explode( '.', strtolower( $basename ) );
		array_shift( $parts );

		// Reject double extensions like "dog.php.jpg" and blocked types anywhere in the name.
		foreach ( $parts as $part ) {
			if ( in_array( $part, self::BLOCKED_EXTENSIONS, true ) ) {
				return false;
			}
		}

		$type = wp_check_filetype( $basename, wp_get_mime_types() );

		return ! empty( $type['ext'] );
	}
}

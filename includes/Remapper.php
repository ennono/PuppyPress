<?php
/**
 * Rewrites IDs and URLs of imported data.
 *
 * @package PuppyPressMigrator
 */

namespace PuppyPressMigrator;

defined( 'ABSPATH' ) || exit;

/**
 * Pure helpers: no database access, so they are easy to test.
 */
final class Remapper {

	/**
	 * Old post ID => new post ID.
	 *
	 * @var array<int, int>
	 */
	private $posts;

	/**
	 * Old term ID => new term ID.
	 *
	 * @var array<int, int>
	 */
	private $terms;

	/**
	 * URL search => replace pairs, most specific first.
	 *
	 * @var array<string, string>
	 */
	private $urls;

	/**
	 * Constructor.
	 *
	 * @param array $posts Post ID map.
	 * @param array $terms Term ID map.
	 * @param array $urls  URL replacements (search => replace).
	 */
	public function __construct( array $posts = array(), array $terms = array(), array $urls = array() ) {
		$this->posts = $posts;
		$this->terms = $terms;
		$this->urls  = self::expand_url_variants( $urls );
	}

	/**
	 * New ID of a post, or 0 if it was not imported.
	 *
	 * @param mixed $old Old ID.
	 * @return int
	 */
	public function post( $old ) {
		$old = (int) $old;

		return $old && isset( $this->posts[ $old ] ) ? (int) $this->posts[ $old ] : 0;
	}

	/**
	 * New ID of a term, or 0 if it was not imported.
	 *
	 * @param mixed $old Old ID.
	 * @return int
	 */
	public function term( $old ) {
		$old = (int) $old;

		return $old && isset( $this->terms[ $old ] ) ? (int) $this->terms[ $old ] : 0;
	}

	/**
	 * Remaps a comma separated list of post IDs, dropping unknown ones.
	 *
	 * @param string $list IDs.
	 * @return string
	 */
	public function post_list( $list ) {
		$ids = array();

		foreach ( explode( ',', (string) $list ) as $old ) {
			$new = $this->post( trim( $old ) );
			if ( $new ) {
				$ids[] = $new;
			}
		}

		return implode( ',', $ids );
	}

	/**
	 * Replaces URLs in strings, arrays and objects (recursively).
	 *
	 * @param mixed $value Value.
	 * @return mixed
	 */
	public function urls( $value ) {
		if ( ! $this->urls ) {
			return $value;
		}

		if ( is_string( $value ) ) {
			return strtr( $value, $this->urls );
		}

		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				$value[ $key ] = $this->urls( $item );
			}
			return $value;
		}

		if ( $value instanceof \stdClass ) {
			foreach ( get_object_vars( $value ) as $key => $item ) {
				$value->$key = $this->urls( $item );
			}
		}

		return $value;
	}

	/**
	 * Rewrites attachment IDs inside post content.
	 *
	 * Handles [gallery ids="…"], block attributes ("id":12, "ids":[1,2]) of
	 * image, gallery, cover, media-text and file blocks, and wp-image-12 classes.
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public function content_ids( $content ) {
		if ( '' === $content ) {
			return $content;
		}

		// [gallery ids="1,2,3"] and include="…".
		$content = preg_replace_callback(
			'/(\[gallery\b[^\]]*?\b(?:ids|include)=)(["\'])([\d,\s]+)\2/i',
			function ( $m ) {
				return $m[1] . $m[2] . $this->post_list( $m[3] ) . $m[2];
			},
			$content
		);

		// Block comments.
		$content = preg_replace_callback(
			'/<!--\s+wp:(image|gallery|cover|media-text|file|video|audio)\s+(\{.*?\})\s+(\/)?-->/s',
			function ( $m ) {
				$attrs = json_decode( $m[2], true );

				if ( ! is_array( $attrs ) ) {
					return $m[0];
				}

				$original = $attrs;

				foreach ( array( 'id', 'mediaId' ) as $key ) {
					if ( isset( $attrs[ $key ] ) && is_numeric( $attrs[ $key ] ) ) {
						$new           = $this->post( $attrs[ $key ] );
						$attrs[ $key ] = $new ? $new : $attrs[ $key ];
					}
				}

				if ( isset( $attrs['ids'] ) && is_array( $attrs['ids'] ) ) {
					$attrs['ids'] = array_values( array_filter( array_map( array( $this, 'post' ), $attrs['ids'] ) ) );
				}

				if ( $attrs === $original ) {
					return $m[0];
				}

				return '<!-- wp:' . $m[1] . ' ' . wp_json_encode( $attrs, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ' ' . ( isset( $m[3] ) ? $m[3] : '' ) . '-->';
			},
			$content
		);

		// wp-image-123 classes and data-id="123" (galleries).
		$content = preg_replace_callback(
			'/\b(wp-image-|data-id=")(\d+)/',
			function ( $m ) {
				$new = $this->post( $m[2] );
				return $m[1] . ( $new ? $new : $m[2] );
			},
			$content
		);

		return $content;
	}

	/**
	 * Unserializes a stored meta value without allowing objects to be created.
	 *
	 * @param string $value Raw value.
	 * @return mixed
	 */
	public static function unserialize( $value ) {
		if ( ! is_string( $value ) || ! is_serialized( $value ) ) {
			return $value;
		}

		$data = @unserialize( trim( $value ), array( 'allowed_classes' => array( 'stdClass' ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize, WordPress.PHP.NoSilencedErrors.Discouraged

		return ( false === $data && 'b:0;' !== trim( $value ) ) ? $value : $data;
	}

	/**
	 * Adds http/https and JSON-escaped variants of each URL pair.
	 *
	 * @param array $urls Search => replace.
	 * @return array
	 */
	public static function expand_url_variants( array $urls ) {
		$pairs = array();

		foreach ( $urls as $search => $replace ) {
			$search  = untrailingslashit( (string) $search );
			$replace = untrailingslashit( (string) $replace );

			if ( '' === $search || $search === $replace ) {
				continue;
			}

			$variants = array( $search => $replace );

			if ( preg_match( '#^https?://#', $search ) ) {
				$other              = 0 === strpos( $search, 'https://' ) ? 'http://' . substr( $search, 8 ) : 'https://' . substr( $search, 7 );
				$variants[ $other ] = $replace;
			}

			foreach ( $variants as $from => $to ) {
				$pairs[ $from ]                          = $to;
				$pairs[ str_replace( '/', '\\/', $from ) ] = str_replace( '/', '\\/', $to );
			}
		}

		// strtr() prefers the longest match, so specific file URLs win over base URLs.
		return $pairs;
	}

	/**
	 * Orders terms so that parents come before their children.
	 *
	 * @param array $terms List of terms with term_id and parent.
	 * @return array Ordered list. Terms with unknown parents are treated as top level.
	 */
	public static function order_terms( array $terms ) {
		$by_id    = array();
		$children = array();

		foreach ( $terms as $term ) {
			$by_id[ (int) $term['term_id'] ] = $term;
		}

		$roots = array();
		foreach ( $terms as $term ) {
			$parent = (int) $term['parent'];

			if ( $parent && isset( $by_id[ $parent ] ) && $parent !== (int) $term['term_id'] ) {
				$children[ $parent ][] = (int) $term['term_id'];
			} else {
				$roots[] = (int) $term['term_id'];
			}
		}

		$ordered = array();
		$seen    = array();
		$queue   = $roots;

		while ( $queue ) {
			$id = array_shift( $queue );

			if ( isset( $seen[ $id ] ) ) {
				continue;
			}

			$seen[ $id ] = true;
			$ordered[]   = $by_id[ $id ];

			if ( isset( $children[ $id ] ) ) {
				foreach ( $children[ $id ] as $child ) {
					$queue[] = $child;
				}
			}
		}

		// Cycles: append whatever is left.
		foreach ( $by_id as $id => $term ) {
			if ( ! isset( $seen[ $id ] ) ) {
				$ordered[] = $term;
			}
		}

		return $ordered;
	}
}

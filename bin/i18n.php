<?php
/**
 * Dependency free i18n tooling.
 *
 * Usage:
 *   php bin/i18n.php pot [plugin-dir]   Extract strings into {plugin-dir}/languages/{slug}.pot
 *   php bin/i18n.php update [plugin-dir]   Merge the POT into all languages/*.po files
 *   php bin/i18n.php compile [plugin-dir]  Compile languages/*.po into .mo and .l10n.php
 *
 * WP-CLI (`wp i18n make-pot`, `make-mo`, `make-php`) produces equivalent files.
 *
 * @package PuppyPress
 */

if ( 'cli' !== PHP_SAPI ) {
	exit( 1 );
}

/*
 * Plugin directory (default: repository root). The slug, text domain and main
 * file name are derived from the main file {slug}.php in that directory.
 */
$root = isset( $argv[2] ) ? realpath( $argv[2] ) : dirname( __DIR__ );

if ( ! $root || ! is_dir( $root ) ) {
	fwrite( STDERR, "Plugin directory not found.\n" );
	exit( 1 );
}

$pp_slug = is_file( $root . '/' . basename( $root ) . '.php' ) ? basename( $root ) : 'puppypress-migrator';

define( 'PP_DOMAIN', $pp_slug );

/**
 * Translation functions: name => argument roles.
 */
$functions = array(
	'__'           => array( 'text', 'domain' ),
	'_e'           => array( 'text', 'domain' ),
	'esc_html__'   => array( 'text', 'domain' ),
	'esc_html_e'   => array( 'text', 'domain' ),
	'esc_attr__'   => array( 'text', 'domain' ),
	'esc_attr_e'   => array( 'text', 'domain' ),
	'_x'           => array( 'text', 'context', 'domain' ),
	'_ex'          => array( 'text', 'context', 'domain' ),
	'esc_html_x'   => array( 'text', 'context', 'domain' ),
	'esc_attr_x'   => array( 'text', 'context', 'domain' ),
	'_n'           => array( 'text', 'plural', null, 'domain' ),
	'_nx'          => array( 'text', 'plural', null, 'context', 'domain' ),
	'_n_noop'      => array( 'text', 'plural', 'domain' ),
	'_nx_noop'     => array( 'text', 'plural', 'context', 'domain' ),
);

/**
 * Decodes a PHP string literal token.
 *
 * @param string $literal Token.
 * @return string
 */
function pp_decode_literal( $literal ) {
	$quote = $literal[0];
	$body  = substr( $literal, 1, -1 );

	if ( "'" === $quote ) {
		return str_replace( array( "\\'", '\\\\' ), array( "'", '\\' ), $body );
	}

	return stripcslashes( $body );
}

/**
 * Extracts translatable strings from a PHP file.
 *
 * @param string $file      Absolute path.
 * @param string $relative  Path for references.
 * @param array  $functions Function map.
 * @return array[]
 */
function pp_extract( $file, $relative, array $functions ) {
	$tokens  = token_get_all( file_get_contents( $file ) );
	$count   = count( $tokens );
	$found   = array();
	$comment = '';

	for ( $i = 0; $i < $count; $i++ ) {
		$token = $tokens[ $i ];

		if ( is_array( $token ) && T_COMMENT === $token[0] && false !== stripos( $token[1], 'translators:' ) ) {
			$comment = trim( preg_replace( '#^(/\*+|//)|\*+/$#', '', $token[1] ) );
			continue;
		}

		if ( ! is_array( $token ) || T_STRING !== $token[0] || ! isset( $functions[ $token[1] ] ) ) {
			continue;
		}

		// Next meaningful token must be "(".
		$j = $i + 1;
		while ( $j < $count && is_array( $tokens[ $j ] ) && T_WHITESPACE === $tokens[ $j ][0] ) {
			$j++;
		}
		if ( $j >= $count || '(' !== $tokens[ $j ] ) {
			continue;
		}

		// Collect top-level arguments.
		$args    = array();
		$current = array();
		$depth   = 0;

		for ( $k = $j + 1; $k < $count; $k++ ) {
			$t = $tokens[ $k ];

			if ( in_array( $t, array( '(', '[', '{' ), true ) || ( is_array( $t ) && in_array( $t[0], array( T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES ), true ) ) ) {
				$depth++;
			} elseif ( in_array( $t, array( ')', ']', '}' ), true ) ) {
				if ( 0 === $depth ) {
					$args[] = $current;
					break;
				}
				$depth--;
			} elseif ( ',' === $t && 0 === $depth ) {
				$args[]  = $current;
				$current = array();
				continue;
			}

			if ( is_array( $t ) && in_array( $t[0], array( T_WHITESPACE, T_COMMENT ), true ) ) {
				continue;
			}

			$current[] = $t;
		}

		$entry = array( 'line' => $token[2] );

		foreach ( $functions[ $token[1] ] as $index => $role ) {
			if ( null === $role ) {
				continue;
			}

			$arg = isset( $args[ $index ] ) ? $args[ $index ] : array();

			if ( 1 !== count( $arg ) || ! is_array( $arg[0] ) || T_CONSTANT_ENCAPSED_STRING !== $arg[0][0] ) {
				$entry[ $role ] = null;
				continue;
			}

			$entry[ $role ] = pp_decode_literal( $arg[0][1] );
		}

		if ( PP_DOMAIN !== $entry['domain'] || null === $entry['text'] || '' === $entry['text'] ) {
			continue;
		}

		$found[] = array(
			'context'   => isset( $entry['context'] ) ? $entry['context'] : null,
			'msgid'     => $entry['text'],
			'plural'    => isset( $entry['plural'] ) ? $entry['plural'] : null,
			'reference' => $relative . ':' . $entry['line'],
			'comment'   => $comment,
		);

		$comment = '';
	}

	return $found;
}

/**
 * Escapes a string for PO files.
 *
 * @param string $string String.
 * @return string
 */
function pp_po_escape( $string ) {
	return '"' . str_replace( array( '\\', '"', "\n", "\t" ), array( '\\\\', '\\"', '\\n', '\\t' ), $string ) . '"';
}

/**
 * Unique key of an entry.
 *
 * @param array $entry Entry.
 * @return string
 */
function pp_key( array $entry ) {
	return ( null !== $entry['context'] ? $entry['context'] . "\x04" : '' ) . $entry['msgid'];
}

/**
 * Parses a PO/POT file.
 *
 * @param string $file Path.
 * @return array{headers: string, entries: array<string, array>}
 */
function pp_parse_po( $file ) {
	$lines   = file( $file, FILE_IGNORE_NEW_LINES );
	$entries = array();
	$headers = '';
	$entry   = null;
	$last    = null;

	$flush = static function () use ( &$entry, &$entries, &$headers ) {
		if ( null === $entry || ! isset( $entry['msgid'] ) ) {
			$entry = null;
			return;
		}

		if ( '' === $entry['msgid'] && null === $entry['context'] ) {
			$headers = isset( $entry['msgstr'][0] ) ? $entry['msgstr'][0] : '';
		} else {
			$entries[ pp_key( $entry ) ] = $entry;
		}

		$entry = null;
	};

	foreach ( $lines as $line ) {
		$line = trim( $line );

		if ( '' === $line ) {
			$flush();
			continue;
		}

		if ( null === $entry ) {
			$entry = array(
				'context'    => null,
				'plural'     => null,
				'msgstr'     => array(),
				'fuzzy'      => false,
				'references' => array(),
				'comments'   => array(),
			);
		}

		if ( 0 === strpos( $line, '#,' ) ) {
			$entry['fuzzy'] = false !== strpos( $line, 'fuzzy' );
		} elseif ( 0 === strpos( $line, '#:' ) ) {
			$entry['references'][] = trim( substr( $line, 2 ) );
		} elseif ( 0 === strpos( $line, '#.' ) ) {
			$entry['comments'][] = trim( substr( $line, 2 ) );
		} elseif ( '#' === $line[0] ) {
			continue;
		} elseif ( preg_match( '/^(msgctxt|msgid_plural|msgid|msgstr(?:\[(\d+)\])?)\s+(".*")$/', $line, $m ) ) {
			$value = stripcslashes( substr( $m[3], 1, -1 ) );

			if ( 'msgctxt' === $m[1] ) {
				$last             = array( 'context' );
				$entry['context'] = $value;
			} elseif ( 'msgid' === $m[1] ) {
				$last           = array( 'msgid' );
				$entry['msgid'] = $value;
			} elseif ( 'msgid_plural' === $m[1] ) {
				$last            = array( 'plural' );
				$entry['plural'] = $value;
			} else {
				$index                     = isset( $m[2] ) && '' !== $m[2] ? (int) $m[2] : 0;
				$last                      = array( 'msgstr', $index );
				$entry['msgstr'][ $index ] = $value;
			}
		} elseif ( '"' === $line[0] && null !== $last ) {
			$value = stripcslashes( substr( $line, 1, -1 ) );

			if ( 'msgstr' === $last[0] ) {
				$entry['msgstr'][ $last[1] ] .= $value;
			} else {
				$entry[ $last[0] ] .= $value;
			}
		}
	}

	$flush();

	return array(
		'headers' => $headers,
		'entries' => $entries,
	);
}

/**
 * Writes a PO/POT file.
 *
 * @param string $file    Path.
 * @param string $headers Header block.
 * @param array  $entries Entries.
 * @param int    $nplurals Number of plural forms.
 */
function pp_write_po( $file, $headers, array $entries, $nplurals = 2 ) {
	$out = "msgid \"\"\nmsgstr \"\"\n";

	foreach ( explode( "\n", rtrim( $headers, "\n" ) ) as $header ) {
		$out .= pp_po_escape( $header . "\n" ) . "\n";
	}

	foreach ( $entries as $entry ) {
		$out .= "\n";

		foreach ( array_unique( $entry['comments'] ) as $comment ) {
			$out .= '#. ' . $comment . "\n";
		}

		if ( $entry['references'] ) {
			$out .= '#: ' . implode( ' ', array_unique( $entry['references'] ) ) . "\n";
		}

		if ( ! empty( $entry['fuzzy'] ) ) {
			$out .= "#, fuzzy\n";
		}

		if ( null !== $entry['context'] ) {
			$out .= 'msgctxt ' . pp_po_escape( $entry['context'] ) . "\n";
		}

		$out .= 'msgid ' . pp_po_escape( $entry['msgid'] ) . "\n";

		if ( null !== $entry['plural'] ) {
			$out .= 'msgid_plural ' . pp_po_escape( $entry['plural'] ) . "\n";

			for ( $i = 0; $i < $nplurals; $i++ ) {
				$out .= 'msgstr[' . $i . '] ' . pp_po_escape( isset( $entry['msgstr'][ $i ] ) ? $entry['msgstr'][ $i ] : '' ) . "\n";
			}
		} else {
			$out .= 'msgstr ' . pp_po_escape( isset( $entry['msgstr'][0] ) ? $entry['msgstr'][0] : '' ) . "\n";
		}
	}

	file_put_contents( $file, $out );
}

/**
 * Builds the POT.
 *
 * @param string $root      Plugin root.
 * @param array  $functions Function map.
 */
function pp_make_pot( $root, array $functions ) {
	$files = array( $root . '/' . PP_DOMAIN . '.php' );

	foreach ( array( 'includes', 'templates' ) as $dir ) {
		if ( ! is_dir( $root . '/' . $dir ) ) {
			continue;
		}
		$iterator = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root . '/' . $dir, FilesystemIterator::SKIP_DOTS ) );
		foreach ( $iterator as $file ) {
			if ( 'php' === $file->getExtension() ) {
				$files[] = $file->getPathname();
			}
		}
	}

	sort( $files );

	$entries = array();

	foreach ( $files as $file ) {
		$relative = ltrim( substr( $file, strlen( $root ) ), '/' );

		foreach ( pp_extract( $file, $relative, $functions ) as $found ) {
			$key = pp_key( $found );

			if ( ! isset( $entries[ $key ] ) ) {
				$entries[ $key ] = array(
					'context'    => $found['context'],
					'msgid'      => $found['msgid'],
					'plural'     => $found['plural'],
					'msgstr'     => array(),
					'references' => array(),
					'comments'   => array(),
				);
			}

			$entries[ $key ]['references'][] = $found['reference'];

			if ( '' !== $found['comment'] ) {
				$entries[ $key ]['comments'][] = $found['comment'];
			}

			if ( null === $entries[ $key ]['plural'] && null !== $found['plural'] ) {
				$entries[ $key ]['plural'] = $found['plural'];
			}
		}
	}

	// Plugin header strings.
	$header = file_get_contents( $root . '/' . PP_DOMAIN . '.php' );
	foreach ( array( 'Plugin Name', 'Description', 'Author' ) as $field ) {
		if ( preg_match( '/^\s*\*\s*' . preg_quote( $field, '/' ) . ':\s*(.+)$/m', $header, $m ) ) {
			$entry = array(
				'context'    => null,
				'msgid'      => trim( $m[1] ),
				'plural'     => null,
				'msgstr'     => array(),
				'references' => array( PP_DOMAIN . '.php' ),
				'comments'   => array( $field . ' of the plugin' ),
			);

			$entries[ pp_key( $entry ) ] = isset( $entries[ pp_key( $entry ) ] ) ? $entries[ pp_key( $entry ) ] : $entry;
		}
	}

	preg_match( '/^\s*\*\s*Version:\s*(.+)$/m', $header, $version );

	$headers = implode(
		"\n",
		array(
			'Project-Id-Version: ' . ( preg_match( '/^\s*\*\s*Plugin Name:\s*(.+)$/m', $header, $name ) ? trim( $name[1] ) : PP_DOMAIN ) . ' ' . trim( $version[1] ),
			'Report-Msgid-Bugs-To: ',
			'POT-Creation-Date: ' . gmdate( 'Y-m-d H:i+0000' ),
			'MIME-Version: 1.0',
			'Content-Type: text/plain; charset=UTF-8',
			'Content-Transfer-Encoding: 8bit',
			'X-Generator: puppypress/bin/i18n.php',
			'X-Domain: ' . PP_DOMAIN,
		)
	);

	pp_write_po( $root . '/languages/' . PP_DOMAIN . '.pot', $headers, $entries );

	fwrite( STDOUT, sprintf( "Wrote languages/%s.pot (%d strings)\n", PP_DOMAIN, count( $entries ) ) );
}

/**
 * Number of plural forms from a header block.
 *
 * @param string $headers Headers.
 * @return int
 */
function pp_nplurals( $headers ) {
	return preg_match( '/nplurals\s*=\s*(\d+)/', $headers, $m ) ? (int) $m[1] : 2;
}

/**
 * Merges the POT into the PO files.
 *
 * @param string $root Plugin root.
 */
function pp_update( $root ) {
	$pot = pp_parse_po( $root . '/languages/' . PP_DOMAIN . '.pot' );

	foreach ( glob( $root . '/languages/*.po' ) as $po_file ) {
		$po      = pp_parse_po( $po_file );
		$merged  = array();
		$missing = 0;

		foreach ( $pot['entries'] as $key => $entry ) {
			$entry['msgstr'] = isset( $po['entries'][ $key ] ) ? $po['entries'][ $key ]['msgstr'] : array();
			$entry['fuzzy']  = isset( $po['entries'][ $key ] ) && $po['entries'][ $key ]['fuzzy'];
			$merged[ $key ]  = $entry;

			if ( '' === implode( '', $entry['msgstr'] ) ) {
				$missing++;
			}
		}

		pp_write_po( $po_file, $po['headers'], $merged, pp_nplurals( $po['headers'] ) );

		fwrite( STDOUT, sprintf( "Updated %s (%d untranslated)\n", basename( $po_file ), $missing ) );
	}
}

/**
 * Compiles PO files to MO and PHP translation files.
 *
 * @param string $root Plugin root.
 */
function pp_compile( $root ) {
	foreach ( glob( $root . '/languages/*.po' ) as $po_file ) {
		$po       = pp_parse_po( $po_file );
		$messages = array();

		foreach ( $po['entries'] as $entry ) {
			$translation = implode( "\0", $entry['msgstr'] );

			if ( $entry['fuzzy'] || '' === str_replace( "\0", '', $translation ) ) {
				continue;
			}

			$original              = pp_key( $entry ) . ( null !== $entry['plural'] ? "\0" . $entry['plural'] : '' );
			$messages[ $original ] = $translation;
		}

		ksort( $messages, SORT_STRING );

		// MO file.
		$all       = array( '' => $po['headers'] ) + $messages;
		$count     = count( $all );
		$originals = '';
		$strings   = '';
		$o_table   = array();
		$t_table   = array();
		$offset    = 28 + 16 * $count;

		foreach ( array_keys( $all ) as $original ) {
			$o_table[] = array( strlen( $original ), $offset + strlen( $originals ) );
			$originals .= $original . "\0";
		}

		$offset += strlen( $originals );

		foreach ( $all as $translation ) {
			$t_table[] = array( strlen( $translation ), $offset + strlen( $strings ) );
			$strings  .= $translation . "\0";
		}

		$mo = pack( 'VVVVVVV', 0x950412de, 0, $count, 28, 28 + 8 * $count, 0, 0 );
		foreach ( $o_table as $row ) {
			$mo .= pack( 'VV', $row[0], $row[1] );
		}
		foreach ( $t_table as $row ) {
			$mo .= pack( 'VV', $row[0], $row[1] );
		}
		$mo .= $originals . $strings;

		$base = substr( $po_file, 0, -3 );
		file_put_contents( $base . '.mo', $mo );

		// PHP translation file (WordPress 6.5+).
		$php = array();
		foreach ( explode( "\n", $po['headers'] ) as $header ) {
			if ( false !== strpos( $header, ':' ) ) {
				list( $name, $value ) = array_map( 'trim', explode( ':', $header, 2 ) );
				$php[ strtolower( $name ) ] = $value;
			}
		}

		$php['messages'] = array();
		foreach ( $messages as $original => $translation ) {
			$parts                        = explode( "\0", $original );
			$php['messages'][ $parts[0] ] = $translation;
		}

		file_put_contents( $base . '.l10n.php', '<?php' . "\n" . 'return ' . var_export( $php, true ) . ';' . "\n" );

		fwrite( STDOUT, sprintf( "Compiled %s (%d messages)\n", basename( $po_file ), count( $messages ) ) );
	}
}

$command = isset( $argv[1] ) ? $argv[1] : '';

switch ( $command ) {
	case 'pot':
		pp_make_pot( $root, $functions );
		break;
	case 'update':
		pp_update( $root );
		break;
	case 'compile':
		pp_compile( $root );
		break;
	default:
		fwrite( STDERR, "Usage: php bin/i18n.php pot|update|compile [plugin-dir]\n" );
		exit( 1 );
}

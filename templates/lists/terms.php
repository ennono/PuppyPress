<?php
/**
 * Nested list of terms.
 *
 * Override: {theme}/puppypress/lists/terms.php
 *
 * @package PuppyPress
 * @version 1.0.0
 *
 * @var array $args {
 *     @type array $items      List of array{name: string, url: string, count: int, children: array}.
 *     @type bool  $show_count Show the number of entries.
 *     @type bool  $link_empty Link items without entries.
 * }
 */

defined( 'ABSPATH' ) || exit;

$pp_render = static function ( array $items ) use ( &$pp_render, $args ) {
	if ( ! $items ) {
		return;
	}

	echo '<ul class="puppypress-terms__list">';

	foreach ( $items as $item ) {
		$linked = '' !== $item['url'] && ( $args['link_empty'] || $item['count'] > 0 );

		echo '<li>';

		if ( $linked ) {
			printf( '<a href="%1$s">%2$s</a>', esc_url( $item['url'] ), esc_html( $item['name'] ) );
		} else {
			echo esc_html( $item['name'] );
		}

		if ( $args['show_count'] ) {
			printf( ' <span class="puppypress-terms__count">(%s)</span>', esc_html( number_format_i18n( $item['count'] ) ) );
		}

		$pp_render( $item['children'] );

		echo '</li>';
	}

	echo '</ul>';
};

$pp_render( $args['items'] );

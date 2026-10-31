<?php
/**
 * Configurable "recent entries" widget.
 *
 * @package Rudelwerk
 */

namespace Rudelwerk\Widgets;

use Rudelwerk\Data\Dogs;
use Rudelwerk\Frontend\Icons;
use WP_Query;
use WP_Widget;

defined( 'ABSPATH' ) || exit;

/**
 * One widget class for all seven "recent …" widgets of the former theme.
 *
 * Each configuration keeps the original widget ID base and option keys, so
 * widgets placed in sidebars keep their settings.
 */
class RecentEntries extends WP_Widget {

	/**
	 * Configuration.
	 *
	 * @var array{id_base: string, name: string, description: string, post_types: string[], show_type: bool, type_format: string, link_option: string, link_label: string, ignore_sticky: bool}
	 */
	private $config;

	/**
	 * Constructor.
	 *
	 * @param array $config See self::$config.
	 */
	public function __construct( array $config ) {
		$this->config = wp_parse_args(
			$config,
			array(
				'description'   => '',
				'show_type'     => false,
				'type_format'   => '%s',
				'link_option'   => '',
				'link_label'    => '',
				'ignore_sticky' => true,
			)
		);

		parent::__construct(
			$this->config['id_base'],
			$this->config['name'],
			array(
				'classname'                   => $this->config['id_base'] . ' rudelwerk-recent',
				'description'                 => $this->config['description'],
				'customize_selective_refresh' => true,
			)
		);

		$this->alt_option_name = $this->config['id_base'];
	}

	/**
	 * Front end output.
	 *
	 * @param array $args     Sidebar arguments.
	 * @param array $instance Settings.
	 */
	public function widget( $args, $instance ) {
		$title  = ! empty( $instance['title'] ) ? $instance['title'] : __( 'Recent entries', 'rudelwerk' );
		$title  = apply_filters( 'widget_title', $title, $instance, $this->id_base );
		$number = ! empty( $instance['number'] ) ? absint( $instance['number'] ) : 5;

		$query_args = array(
			'post_type'           => $this->config['post_types'],
			'posts_per_page'      => $number,
			'no_found_rows'       => true,
			'post_status'         => 'publish',
			'ignore_sticky_posts' => $this->config['ignore_sticky'],
		);

		/** This filter is documented in wp-includes/widgets/class-wp-widget-recent-posts.php */
		$query_args = apply_filters( 'widget_posts_args', $query_args, $instance );

		/**
		 * Filters the query of a Rudelwerk "recent entries" widget.
		 *
		 * @param array  $query_args WP_Query arguments.
		 * @param array  $instance   Widget settings.
		 * @param string $id_base    Widget ID base.
		 */
		$query = new WP_Query( apply_filters( 'rudelwerk_recent_entries_query_args', $query_args, $instance, $this->id_base ) );

		if ( ! $query->have_posts() ) {
			return;
		}

		$link = $this->config['link_option'] && ! empty( $instance[ $this->config['link_option'] ] ) ? $instance[ $this->config['link_option'] ] : '';

		echo $args['before_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		if ( $title ) {
			echo $args['before_title'] . esc_html( $title ) . $args['after_title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		echo '<ul>';

		foreach ( $query->posts as $post ) {
			$post_title = get_the_title( $post );

			echo '<li>';

			if ( $this->config['show_type'] ) {
				printf(
					'<span class="rudelwerk-recent__type">%s:</span><br>',
					esc_html( sprintf( $this->config['type_format'], Dogs::post_type_label( $post->post_type ) ) )
				);
			}

			printf(
				'<a href="%1$s">%2$s</a>',
				esc_url( get_permalink( $post ) ),
				esc_html( '' !== $post_title ? $post_title : __( '(no title)', 'rudelwerk' ) )
			);

			if ( ! empty( $instance['show_date'] ) ) {
				printf( ' <span class="post-date">%s</span>', esc_html( get_the_date( '', $post ) ) );
			}

			echo '</li>';
		}

		echo '</ul>';

		if ( $link ) {
			printf(
				'<a href="%1$s" class="button rudelwerk-recent__more">%2$s&nbsp;%3$s</a>',
				esc_url( $link ),
				Icons::svg( 'arrow' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				esc_html( $this->config['link_label'] )
			);
		}

		echo $args['after_widget']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Sanitizes the settings.
	 *
	 * @param array $new_instance New settings.
	 * @param array $old_instance Old settings.
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance              = $old_instance;
		$instance['title']     = sanitize_text_field( isset( $new_instance['title'] ) ? $new_instance['title'] : '' );
		$instance['number']    = max( 1, absint( isset( $new_instance['number'] ) ? $new_instance['number'] : 5 ) );
		$instance['show_date'] = ! empty( $new_instance['show_date'] );

		if ( $this->config['link_option'] ) {
			$key              = $this->config['link_option'];
			$instance[ $key ] = esc_url_raw( isset( $new_instance[ $key ] ) ? $new_instance[ $key ] : '' );
		}

		return $instance;
	}

	/**
	 * Settings form.
	 *
	 * @param array $instance Settings.
	 * @return string
	 */
	public function form( $instance ) {
		$title     = isset( $instance['title'] ) ? $instance['title'] : '';
		$number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
		$show_date = ! empty( $instance['show_date'] );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title:', 'rudelwerk' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<?php if ( $this->config['link_option'] ) : ?>
			<?php $key = $this->config['link_option']; ?>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>">
					<?php
					/* translators: %s: button label, e.g. "All results" */
					echo esc_html( sprintf( __( 'Link of the button "%s":', 'rudelwerk' ), $this->config['link_label'] ) );
					?>
				</label>
				<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( $key ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( $key ) ); ?>" type="url" value="<?php echo esc_attr( isset( $instance[ $key ] ) ? $instance[ $key ] : '' ); ?>" />
			</p>
		<?php endif; ?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of entries to show:', 'rudelwerk' ); ?></label>
			<input class="tiny-text" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" step="1" min="1" value="<?php echo esc_attr( $number ); ?>" size="3" />
		</p>
		<p>
			<input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'show_date' ) ); ?>" />
			<label for="<?php echo esc_attr( $this->get_field_id( 'show_date' ) ); ?>"><?php esc_html_e( 'Display date?', 'rudelwerk' ); ?></label>
		</p>
		<?php
		return '';
	}
}

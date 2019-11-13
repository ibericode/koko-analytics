<?php
namespace KokoAnalytics;

use WP_Widget;
use WP_Query;

class Widget_Most_Viewed_Posts extends WP_Widget {

	/**
	 * Sets up a new Recent Posts widget instance.
	 *
	 * @since 2.8.0
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'                   => 'widget_recent_entries',
			'description'                 => __( 'Your site&#8217;s most viewed posts, as counted by Koko Analytics.', 'koko-analytics' ),
			'customize_selective_refresh' => true,
		);
		parent::__construct( 'koko-analytics-most-viewed-posts', 'Koko Analytics: ' . __( 'Most viewed posts', 'koko-analytics' ), $widget_ops );
		$this->alt_option_name = 'widget_koko_analytics_most_viewed_posts';
	}

	private function get_default_settings() {
		return array(
			'title' => __( 'Most viewed posts', 'koko-analytics' ),
			'number' => 5,
			'post_type' => 'post',
			'show_date' => false,
			'period' => 30,
		);
    }

	/**
	 * Outputs the content for the current Recent Posts widget instance.
	 *
	 * @since 2.8.0
	 *
	 * @param array $args     Display arguments including 'before_title', 'after_title',
	 *                        'before_widget', and 'after_widget'.
	 * @param array $settings Settings for the current Recent Posts widget instance.
	 */
	public function widget( $args, $settings ) {
		if ( ! isset( $args['widget_id'] ) ) {
			$args['widget_id'] = $this->id;
		}


		$settings = array_merge( $this->get_default_settings(), $settings );
		/** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
		$title = apply_filters( 'widget_title', $settings['title'], $settings, $this->id_base );


		// query most viewed posts
		global $wpdb;
		$start_date = gmdate( 'Y-m-d', strtotime( "-{$settings['period']} days" ) );
		$end_date   = gmdate( 'Y-m-d', strtotime( 'tomorrow midnight' ) );
		$sql        = $wpdb->prepare( "SELECT p.id, SUM(visitors) As visitors, SUM(pageviews) AS pageviews FROM {$wpdb->prefix}koko_analytics_post_stats s JOIN {$wpdb->posts} p ON s.id = p.id WHERE s.date >= %s AND s.date <= %s AND p.post_type = %s AND p.post_status = 'publish' GROUP BY s.id ORDER BY pageviews DESC LIMIT 0, %d", array( $start_date, $end_date, $settings['post_type'], $settings['number'] ) );
		$results    = $wpdb->get_results( $sql );
		if ( empty( $results ) ) {
			return;
		}

		$ids = wp_list_pluck( $results, 'id' );
		$r   = new WP_Query(
			array(
				'posts_per_page'      => -1,
				'post__in'            => $ids,
				'orderby'             => 'post__in',
				'no_found_rows'       => true,
				'ignore_sticky_posts' => true,
                'post_type' => $settings['post_type'],
			)
		);

		echo $args['before_widget'];
		if ( $title ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		echo '<ul>';
		foreach ( $r->posts as $p ) {
				$post_title   = get_the_title( $p );
				$title        = $post_title !== '' ? $post_title : __( '(no title)', 'koko-analytics' );
				$aria_current = '';

			if ( get_queried_object_id() === $p->ID ) {
				$aria_current = ' aria-current="page"';
			}

				echo '<li>';
				echo sprintf( '<a href="%s" %s>%s</a>', get_the_permalink( $p ), $aria_current, $title );

			if ( $settings['show_date'] ) {
				echo sprintf( PHP_EOL . ' <span class="post-date">%s</span>', get_the_date( '', $p ) );
			}
				echo '</li>';
		}
		echo '</ul>';
		echo $args['after_widget'];
	}

	/**
	 * Handles updating the settings for the current Recent Posts widget instance.
	 *
	 * @since 2.8.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 *                            WP_Widget::form().
	 * @param array $old_instance Old settings for this instance.
	 * @return array Updated settings to save.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance              = $old_instance;
		$instance['title']     = sanitize_text_field( $new_instance['title'] );
		$instance['number']    = absint( $new_instance['number'] );
		$instance['show_date'] = isset( $new_instance['show_date'] ) ? (bool) $new_instance['show_date'] : false;
		$instance['period']    = absint( $new_instance['period'] );
		$instance['post_type'] = isset( $new_instance['post_type'] ) && in_array( $new_instance['post_type'], get_post_types() ) ? $new_instance['post_type'] : 'post';
		return $instance;
	}

	/**
	 * @param array $settings Current settings.
	 */
	public function form( $settings ) {
	    $settings = array_merge( $this->get_default_settings(), $settings );
	    $post_types = get_post_types( array( 'public' => true ), false );
		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'koko-analytics' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $settings['title'] ); ?>" /></p>

        <p><label for="<?php echo $this->get_field_id( 'post_type' ); ?>"><?php _e( 'Post type:', 'koko-analytics' ); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id( 'post_type' ); ?>" name="<?php echo $this->get_field_name( 'post_type' ); ?>">
                <?php
                foreach( $post_types as $post_type ) {
                   echo sprintf('<option value="%s" %s>%s</option>', $post_type->name, selected( $settings['post_type'], $post_type->name, false ), $post_type->label );
                } ?>
            </select>
        </p>

		<p><label for="<?php echo $this->get_field_id( 'period' ); ?>"><?php _e( 'Period:', 'koko-analytics' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'period' ); ?>" name="<?php echo $this->get_field_name( 'period' ); ?>">
				<option value="7" <?php selected( 7, $settings['period'] ); ?>>Last 7 days</option>
				<option value="30" <?php selected( 30, $settings['period'] ); ?>>Last 30 days</option>
				<option value="90" <?php selected( 90, $settings['period'] ); ?>>Last 90 days</option>
				<option value="365" <?php selected( 365, $settings['period'] ); ?>>Last 365 days</option>
			</select>
		</p>

		<p><label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:', 'koko-analytics' ); ?></label>
			<input class="tiny-text" id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="number" step="1" min="1" value="<?php echo esc_attr( $settings['number'] ); ?>" size="3" /></p>

		<p><input class="checkbox" type="checkbox"<?php checked( $settings['show_date'] ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
			<label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?', 'koko-analytics' ); ?></label></p>
		<?php
	}
}

<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use WP_Widget;

class Widget_Most_Viewed_Posts extends WP_Widget
{
    public function __construct()
    {
        $widget_ops = array(
            'classname'                   => 'widget_recent_entries',
            'description'                 => esc_html__('Your site&#8217;s most viewed posts, as counted by Koko Analytics.', 'koko-analytics'),
            'customize_selective_refresh' => true,
        );
        parent::__construct('koko-analytics-most-viewed-posts', 'Koko Analytics: ' . esc_html__('Most viewed posts', 'koko-analytics'), $widget_ops);
        $this->alt_option_name = 'widget_koko_analytics_most_viewed_posts';
    }

    private function get_default_settings()
    {
        return array(
            'title'     => esc_html__('Most viewed posts', 'koko-analytics'),
            'number'    => 5,
            'post_type' => 'post',
            'show_date' => false,
            'days'    => 30,
        );
    }

    /**
     * Outputs the content for the current widget instance.
     *
     * @param array $args     Display arguments including 'before_title', 'after_title', 'before_widget', and 'after_widget'.
     * @param array $settings Settings for the current Recent Posts widget instance.
     */
    public function widget($args, $settings)
    {
        if (! isset($args['widget_id'])) {
            $args['widget_id'] = $this->id;
        }

        $settings = array_merge($this->get_default_settings(), $settings);
        /** This filter is documented in wp-includes/widgets/class-wp-widget-pages.php */
        $title = apply_filters('widget_title', $settings['title'], $settings, $this->id_base);

        echo $args['before_widget'];
        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        $shortcode = new Shortcode_Most_Viewed_Posts();
        echo $shortcode->content($settings);
        echo $args['after_widget'];
    }

    /**
     * Handles updating the settings for the current widget instance.
     *
     * @param array $new_instance New settings for this instance as input by the user via
     *                            WP_Widget::form().
     * @param array $old_instance Old settings for this instance.
     * @return array Updated settings to save.
     */
    public function update($new_instance, $old_instance)
    {
        $instance              = $old_instance;
        $instance['title']     = sanitize_text_field($new_instance['title']);
        $instance['number']    = absint($new_instance['number']);
        $instance['show_date'] = isset($new_instance['show_date']) ? (bool) $new_instance['show_date'] : false;
        $instance['days']      = absint($new_instance['days']);
        $instance['post_type'] = isset($new_instance['post_type']) && in_array($new_instance['post_type'], get_post_types(), true) ? $new_instance['post_type'] : 'post';
        return $instance;
    }

    /**
     * Outputs the form for updating current widget instance settings.
     *
     * @param array $settings Current settings.
     */
    public function form($settings)
    {
        $settings   = array_merge($this->get_default_settings(), $settings);
        $post_types = get_post_types(array( 'public' => true ), 'objects');
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>" style="display: block;"><?php echo esc_html__('Title:', 'koko-analytics'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($settings['title']); ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('post_type'); ?>" style="display: block;"><?php echo esc_html__('Post type:', 'koko-analytics'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('post_type'); ?>" name="<?php echo $this->get_field_name('post_type'); ?>">
                <?php
                foreach ($post_types as $post_type) {
                    $selected = selected($settings['post_type'], $post_type->name, false);
                    echo "<option value=\"{$post_type->name}\" {$selected}>{$post_type->label}</option>";
                }
                ?>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('days'); ?>" style="display: block;"><?php echo esc_html__('Number of days to use statistics for:', 'koko-analytics'); ?></label>
            <input id="<?php echo $this->get_field_id('days'); ?>" name="<?php echo $this->get_field_name('days'); ?>" type="number" step="1" min="1" max="1975" value="<?php echo esc_attr($settings['days']); ?>" required class="tiny-text" size="3" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('number'); ?>" style="display: block;"><?php echo esc_html__('Number of posts to show:', 'koko-analytics'); ?></label>
            <input id="<?php echo $this->get_field_id('number'); ?>" class="tiny-text" name="<?php echo $this->get_field_name('number'); ?>" type="number" step="1" min="1" value="<?php echo esc_attr($settings['number']); ?>" size="3" />
        </p>

        <p>
            <input class="checkbox" type="checkbox" <?php checked($settings['show_date']); ?> id="<?php echo $this->get_field_id('show_date'); ?>" name="<?php echo $this->get_field_name('show_date'); ?>" />
            <label for="<?php echo $this->get_field_id('show_date'); ?>"><?php echo esc_html__('Display post date?', 'koko-analytics'); ?></label>
        </p>
        <?php
    }
}

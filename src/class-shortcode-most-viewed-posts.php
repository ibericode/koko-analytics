<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Shortcode_Most_Viewed_Posts
{
    private const SHORTCODE = 'koko_analytics_most_viewed_posts';

    public function __construct()
    {
        add_shortcode(self::SHORTCODE, array( $this, 'content' ));
    }

    public function content($args): string
    {
        $allowed_args = array(
            'number'    => 5,
            'show_date' => false,
            'days'    => 30,
            'post_type' => 'post',
        );
        $args         = shortcode_atts($allowed_args, $args, self::SHORTCODE);
        if ($args['show_date'] === "false") {
            $args['show_date'] = false;
        }
        $posts        = get_most_viewed_posts($args);

        // If shortcode arguments did not return any results
        // Show a helpful message to editors and up
        if (count($posts) === 0 && current_user_can('edit_posts')) {
            return '<p>' . esc_html__('Heads up! Your shortcode is working, but did not return any results. Please check your shortcode arguments.', 'koko-analytics') . '</p>';
        }

        $html = '<ul>';
        foreach ($posts as $p) {
            $post_title   = get_the_title($p);
            $title        = $post_title !== '' ? $post_title : esc_html__('(no title)', 'koko-analytics');
            $permalink = get_the_permalink($p);

            $aria_current = '';
            if (get_queried_object_id() === $p->ID) {
                $aria_current = ' aria-current="page"';
            }

            $html .= '<li>';
            $html .= "<a href=\"{$permalink}\" {$aria_current}>{$title}</a>";

            if ($args['show_date']) {
                $date = get_the_date('', $p);
                $html .= " <span class=\"post-date\">{$date}</span>";
            }
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }
}

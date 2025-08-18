<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

class Bar
{
    public static function register(\WP_Admin_Bar $wp_admin_bar): void
    {
        // only show on frontend
        // only show for users who can access statistics page
        if (is_admin() || !current_user_can('view_koko_analytics')) {
            return;
        }

        $wp_admin_bar->add_node(
            [
                'parent' => 'site-name',
                'id' => 'koko-analytics',
                'title' => esc_html__('Analytics', 'koko-analytics'),
                'href' => admin_url('/index.php?page=koko-analytics'),
            ]
        );
    }
}

<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

use Exception;

class Data_Reset
{
    public static function action_listener(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        check_admin_referer('koko_analytics_reset_statistics');

        /** @var \wpdb $wpdb */
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_site_stats;");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_post_stats;");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_paths;");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_referrer_stats;");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_referrer_urls;");
        delete_option('koko_analytics_realtime_pageview_count');

        // delete version option so that migrations re-create all database tables on next page load
        delete_option('koko_analytics_version');

        // redirect with success message
        wp_safe_redirect(add_query_arg(['notice' => ['type' => 'success', 'message' => __('Statistics successfully reset', 'koko-analytics') ]], admin_url('/index.php?page=koko-analytics&tab=settings')));
        exit;
    }
}

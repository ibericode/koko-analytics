<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

use KokoAnalytics\Table;

use function KokoAnalytics\get_migrations;

class Data_Reset
{
    public function action_listener(): void
    {
        if (!current_user_can('manage_koko_analytics') || ! check_admin_referer('koko_analytics_reset_statistics')) {
            return;
        }

        /** @var \wpdb $wpdb */
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_site_stats;");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_post_stats;");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_paths;");
        (new Table('referrer'))->destroy();

        delete_option('koko_analytics_realtime_pageview_count');

        // Delete the version option so that all database tables are re-created.
        delete_option('koko_analytics_migrations');
        $migrations_complete = get_migrations()->ensure_current();

        $settings_page = admin_url('options-general.php?page=koko-analytics-settings&tab=data');
        $query_args    = $migrations_complete
            ? ['message' => urlencode(__('Statistics successfully reset', 'koko-analytics'))]
            : ['error' => urlencode(__('Statistics were reset, but the database tables could not be re-created. Please try again.', 'koko-analytics'))];

        wp_safe_redirect(add_query_arg($query_args, $settings_page));
        exit;
    }
}

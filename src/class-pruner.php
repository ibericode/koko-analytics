<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Pruner
{
    public static function setup_scheduled_event(): void
    {
        if (! wp_next_scheduled('koko_analytics_prune_data')) {
            wp_schedule_event(time() + DAY_IN_SECONDS, 'daily', 'koko_analytics_prune_data');
        }
    }

    public static function clear_scheduled_event(): void
    {
        wp_clear_scheduled_hook('koko_analytics_prune_data');
    }

    public static function run()
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        $settings = get_settings();
        if ($settings['prune_data_after_months'] <= 0) {
            return;
        }

        $date = (new \DateTime("-{$settings['prune_data_after_months']} months", wp_timezone()))->format('Y-m-d');

        // delete stats older than date above
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_site_stats WHERE date < %s", $date));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_post_stats WHERE date < %s", $date));
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_stats WHERE date < %s", $date));

        // delete unused referrer URL's
        $wpdb->query("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE id NOT IN (SELECT DISTINCT(id) FROM {$wpdb->prefix}koko_analytics_referrer_stats )");

        // delete unused paths
        $wpdb->query("DELETE FROM {$wpdb->prefix}koko_analytics_paths WHERE id NOT IN (SELECT DISTINCT(path_id) FROM {$wpdb->prefix}koko_analytics_post_stats )");
    }
}

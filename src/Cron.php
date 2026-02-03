<?php

namespace KokoAnalytics;

class Cron
{
    public function setup()
    {
        if (! wp_next_scheduled('koko_analytics_aggregate_stats')) {
            wp_schedule_event(time() + 60, 'koko_analytics_stats_aggregate_interval', 'koko_analytics_aggregate_stats');
        }

        if (! wp_next_scheduled('koko_analytics_prune_data')) {
            wp_schedule_event(time() + DAY_IN_SECONDS, 'daily', 'koko_analytics_prune_data');
        }

        if (! wp_next_scheduled('koko_analytics_rotate_fingerprint_seed')) {
            $time_next_midnight = (new \DateTimeImmutable('tomorrow, midnight', wp_timezone()))->getTimestamp();
            wp_schedule_event($time_next_midnight, 'daily', 'koko_analytics_rotate_fingerprint_seed');
        }
    }
    public function clear()
    {
        wp_clear_scheduled_hook('koko_analytics_aggregate_stats');
        wp_clear_scheduled_hook('koko_analytics_prune_data');
        wp_clear_scheduled_hook('koko_analytics_rotate_fingerprint_seed');
    }
}

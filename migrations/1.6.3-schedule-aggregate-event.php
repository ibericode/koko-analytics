<?php

defined('ABSPATH') or exit;

if (! wp_next_scheduled('koko_analytics_aggregate_stats')) {
    // ensure schedule exists
    add_filter('cron_schedules', function ($schedules) {
        $schedules['koko_analytics_stats_aggregate_interval'] = [
            'interval' => 60, // 60 seconds
            'display'  => esc_html__('Every minute', 'koko-analytics'),
        ];
        return $schedules;
    }, 10, 1);

    // schedule event
    wp_schedule_event(time() + 60, 'koko_analytics_stats_aggregate_interval', 'koko_analytics_aggregate_stats');
}

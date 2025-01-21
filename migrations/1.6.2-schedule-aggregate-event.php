<?php

defined('ABSPATH') or exit;

if (! wp_next_scheduled('koko_analytics_aggregate_stats')) {
    wp_schedule_event(time() + 60, 'koko_analytics_stats_aggregate_interval', 'koko_analytics_aggregate_stats');
}

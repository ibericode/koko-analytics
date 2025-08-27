<?php

defined('ABSPATH') or exit;

@set_time_limit(0);

/** @var wpdb $wpdb */
global $wpdb;

// create back-up of post_stats table
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_post_stats_backup");
$wpdb->query("CREATE TABLE {$wpdb->prefix}koko_analytics_post_stats_backup LIKE {$wpdb->prefix}koko_analytics_post_stats");
$wpdb->query("INSERT INTO {$wpdb->prefix}koko_analytics_post_stats_backup SELECT * FROM {$wpdb->prefix}koko_analytics_post_stats");

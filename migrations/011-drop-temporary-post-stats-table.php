<?php

defined('ABSPATH') || exit;

/** @var \wpdb $wpdb */
global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_post_stats_old");

<?php

defined('ABSPATH') || exit;

/** @var \wpdb $wpdb */
global $wpdb;

$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats ADD INDEX (path_id)");

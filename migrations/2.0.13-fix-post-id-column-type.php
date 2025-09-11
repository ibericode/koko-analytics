<?php

defined('ABSPATH') or exit;

/** @var \wpdb $wpdb */
global $wpdb;

$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats MODIFY COLUMN post_id INT UNSIGNED NOT NULL DEFAULT 0");

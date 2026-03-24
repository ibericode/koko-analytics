<?php

defined('ABSPATH') or exit;

/** @var \wpdb $wpdb */
global $wpdb;

// Add secondary index on id so orphan-deletion queries (LEFT JOIN on id alone) can use it.
// Without this, every join probe on referrer_stats / post_stats requires a full table scan
// because the primary key is (date, id) and date is the leading column.
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_referrer_stats ADD INDEX (id)");

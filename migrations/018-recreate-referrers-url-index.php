<?php

defined('ABSPATH') or exit;

/** @var \wpdb $wpdb */
global $wpdb;

$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_referrer_labels DROP INDEX url, ADD UNIQUE INDEX (value)");

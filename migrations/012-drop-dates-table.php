<?php

defined('ABSPATH') or exit;

global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_dates;");

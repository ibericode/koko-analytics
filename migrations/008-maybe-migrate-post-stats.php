<?php

use KokoAnalytics\Post_Stats_Migrator;

defined('ABSPATH') or exit;

/** @var wpdb $wpdb */
global $wpdb;

$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}koko_analytics_post_stats");
if ($count && $count < 25000) {
    (new Post_Stats_Migrator())->migrate_to_v2();
}

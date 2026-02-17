<?php

use KokoAnalytics\Admin\Actions;

defined('ABSPATH') or exit;

/** @var wpdb $wpdb */
global $wpdb;

$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}koko_analytics_post_stats");
if ($count && $count < 25000 && method_exists(Actions::class, 'migrate_post_stats_to_v2')) {
    (new Actions())->migrate_post_stats_to_v2();
}

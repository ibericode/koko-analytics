<?php

use KokoAnalytics\Admin\Actions;

defined('ABSPATH') or exit;

/** @var \wpdb $wpdb */
global $wpdb;

$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}koko_analytics_post_stats");
if ($count && $count < 25000 && method_exists(Actions::class, 'fix_post_paths_after_v2')) {
    (new Actions())->fix_post_paths_after_v2();
}

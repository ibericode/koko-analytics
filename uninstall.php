<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 *
 * Perform the necessary steps to completely uninstall Koko Analytics
 */

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

// delete wp-options
delete_option("koko_analytics_settings");
delete_option("koko_analytics_use_custom_endpoint");
delete_option("koko_analytics_realtime_pageview_count");
delete_option('koko_analytics_jetpack_import_params');
delete_option('koko_analytics_last_aggregation_at');

// not removing koko_analytics_version because the database tables itself are not removed upon uninstall
// not removing koko_analytics_referrers_v2 because it also describes database state

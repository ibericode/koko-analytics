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
delete_option("koko_analytics_version");
delete_option("koko_analytics_use_custom_endpoint");
delete_option("koko_analytics_realtime_pageview_count");
delete_option('koko_analytics_jetpack_import_params');

// drop koko tables
global $wpdb;
$wpdb->query(
    "DROP TABLE IF EXISTS
    {$wpdb->prefix}koko_analytics_site_stats,
    {$wpdb->prefix}koko_analytics_post_stats,
    {$wpdb->prefix}koko_analytics_referrer_stats,
    {$wpdb->prefix}koko_analytics_dates,
    {$wpdb->prefix}koko_analytics_referrer_urls"
);

// delete custom endpoint file
if (file_exists(ABSPATH . '/koko-analytics-collect.php')) {
    unlink(ABSPATH . '/koko-analytics-collect.php');
}

<?php
/**
 * Perform the necessary steps to completely uninstall Koko Analytics
 */

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) die;

// delete wp-options
delete_option("koko_analytics_settings");
delete_option("koko_analytics_version");

// drop koko tables
global $wpdb;
$wpdb->query(
    "DROP TABLE IF EXISTS
    {$wpdb->prefix}koko_analytics_site_stats,
    {$wpdb->prefix}koko_analytics_post_stats,
    {$wpdb->prefix}koko_analytics_referrer_stats,
    {$wpdb->prefix}koko_analytics_referrer_urls"
);
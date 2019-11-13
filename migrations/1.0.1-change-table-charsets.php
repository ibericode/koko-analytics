<?php

defined('ABSPATH') or exit;

global $wpdb;

$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_site_stats CONVERT TO CHARACTER SET ascii;");
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats CONVERT TO CHARACTER SET ascii;");
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_referrer_stats CONVERT TO CHARACTER SET ascii;");
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_referrer_urls CONVERT TO CHARACTER SET ascii;");

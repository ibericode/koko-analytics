<?php

// This migration modifies the schema for the referrer_stats and referrer_urls table so they fit into the Table abstraction class

defined('ABSPATH') or exit;

global $wpdb;

$wpdb->query("RENAME TABLE {$wpdb->prefix}koko_analytics_referrer_urls TO {$wpdb->prefix}koko_analytics_referrer_labels");
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_referrer_labels CHANGE url value VARCHAR(255) NOT NULL");
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_referrer_stats CHANGE visitors unique_hits INT UNSIGNED NOT NULL DEFAULT 0");
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_referrer_stats CHANGE pageviews hits INT UNSIGNED NOT NULL DEFAULT 0");



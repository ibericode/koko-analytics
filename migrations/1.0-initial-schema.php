<?php

defined('ABSPATH') or exit;

global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aaa_todays_visitors");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aaa_todays_pageviews");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aaa_pageviews");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aaa_stats");

$wpdb->query("CREATE TABLE {$wpdb->prefix}aaa_todays_visitors(
    hash VARCHAR(32) NOT NULL PRIMARY KEY,
    seen DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB CHARACTER SET={$wpdb->charset} STATS_AUTO_RECALC=0");

$wpdb->query("CREATE TABLE {$wpdb->prefix}aaa_todays_pageviews(
    hash VARCHAR(32) NOT NULL PRIMARY KEY
) ENGINE=INNODB CHARACTER SET={$wpdb->charset} STATS_AUTO_RECALC=0");

$wpdb->query("CREATE TABLE {$wpdb->prefix}aaa_pageviews (
   post_id BIGINT(20) UNSIGNED NOT NULL,
   is_unique TINYINT(1) NOT NULL,
   is_new_visitor TINYINT(1) NOT NULL,
   timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=INNODB CHARACTER SET={$wpdb->charset} STATS_AUTO_RECALC=0");

$wpdb->query("CREATE TABLE {$wpdb->prefix}aaa_stats (
   type VARCHAR(20) NOT NULL DEFAULT 'post',
   post_id BIGINT(20) UNSIGNED NULL,
   unique_views INTEGER UNSIGNED NOT NULL,
   views INTEGER UNSIGNED NOT NULL,
   date DATE NOT NULL,
   INDEX (type, post_id, date)
) ENGINE=INNODB CHARACTER SET={$wpdb->charset}");

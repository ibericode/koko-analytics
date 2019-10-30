<?php

defined('ABSPATH') or exit;

global $wpdb;

if (WP_DEBUG) {
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ap_stats");
}

// TODO: Check optimal index order here
$wpdb->query("CREATE TABLE {$wpdb->prefix}ap_stats (
   type VARCHAR(10) NOT NULL DEFAULT 'post_type',
   id BIGINT(20) UNSIGNED NULL,
   date DATE NOT NULL,
   visitors INTEGER UNSIGNED NOT NULL,
   pageviews INTEGER UNSIGNED NOT NULL,
   UNIQUE INDEX (date, id)
) ENGINE=INNODB CHARACTER SET={$wpdb->charset} COLLATE={$wpdb->collate}");

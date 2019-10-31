<?php

defined('ABSPATH') or exit;

global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ap_stats");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}ap_referrers");

// TODO: Check optimal index order here
$wpdb->query("CREATE TABLE {$wpdb->prefix}ap_stats (
   type ENUM('post', 'referrer') NOT NULL DEFAULT 'post',
   id BIGINT(20) UNSIGNED NULL,
   date DATE NOT NULL,
   visitors INTEGER UNSIGNED NOT NULL,
   pageviews INTEGER UNSIGNED NOT NULL,
   UNIQUE INDEX (date, id)
) ENGINE=INNODB CHARACTER SET={$wpdb->charset} COLLATE={$wpdb->collate}");

$wpdb->query("CREATE TABLE {$wpdb->prefix}ap_referrers (
   id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   url VARCHAR(255) NOT NULL,
   UNIQUE INDEX (url)
) ENGINE=INNODB CHARACTER SET={$wpdb->charset} COLLATE={$wpdb->collate}");

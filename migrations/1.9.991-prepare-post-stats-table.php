<?php

defined('ABSPATH') or exit;

/** @var wpdb $wpdb */
global $wpdb;

$wpdb->query(
    "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}koko_analytics_paths (
       id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
       path VARCHAR(2000) NOT NULL,
       INDEX (path(191))
    ) ENGINE=INNODB CHARACTER SET=utf8mb4"
);

// prepare columns
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats CHANGE COLUMN id post_id MEDIUMINT UNSIGNED");
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats ADD COLUMN path_id MEDIUMINT UNSIGNED");

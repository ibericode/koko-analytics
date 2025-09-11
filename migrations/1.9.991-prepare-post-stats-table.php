<?php

defined('ABSPATH') or exit;

/** @var wpdb $wpdb */
global $wpdb;

$wpdb->hide_errors();

$wpdb->query(
    "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}koko_analytics_paths (
       id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
       path VARCHAR(2000) NOT NULL,
       INDEX (path(191))
    ) ENGINE=INNODB CHARACTER SET=utf8mb4"
);

// prepare columns
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats CHANGE COLUMN id post_id INT UNSIGNED");
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats ADD COLUMN path_id MEDIUMINT UNSIGNED");

$results = $wpdb->get_var("SELECT COUNT(DISTINCT(post_id)) FROM {$wpdb->prefix}koko_analytics_post_stats WHERE post_id IS NOT NULL AND path_id IS NULL");
if (!$results) {
   // make new path_id column not-nullable
    $wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats MODIFY COLUMN path_id MEDIUMINT UNSIGNED NOT NULL");

  // change primary key to be on date and path_id column
    $wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats DROP PRIMARY KEY, ADD PRIMARY KEY(date, path_id)");
}

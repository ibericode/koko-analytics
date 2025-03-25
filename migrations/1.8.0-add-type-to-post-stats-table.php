<?php

defined('ABSPATH') or exit;

global $wpdb;
$wpdb->query(
    "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}koko_analytics_paths (
       id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
       path VARCHAR(255) NOT NULL,
       UNIQUE INDEX (path)
    ) ENGINE=INNODB CHARACTER SET=utf8mb4"
);
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats ADD COLUMN type ENUM('post', 'path') NOT NULL DEFAULT 'post';");

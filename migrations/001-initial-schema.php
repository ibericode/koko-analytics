<?php

defined('ABSPATH') or exit;

/** @var \wpdb $wpdb */
global $wpdb;

$wpdb->query(
    "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}koko_analytics_site_stats (
           date DATE PRIMARY KEY NOT NULL,
           visitors INT UNSIGNED NOT NULL DEFAULT 0,
           pageviews INT UNSIGNED NOT NULL DEFAULT 0
    ) ENGINE=INNODB CHARACTER SET=ascii"
);

$wpdb->query(
    "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}koko_analytics_post_stats (
       date DATE NOT NULL,
       id BIGINT UNSIGNED NOT NULL,
       visitors INT UNSIGNED NOT NULL DEFAULT 0,
       pageviews INT UNSIGNED NOT NULL DEFAULT 0,
       PRIMARY KEY (date, id)
    ) ENGINE=INNODB CHARACTER SET=ascii"
);

$wpdb->query(
    "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}koko_analytics_referrer_stats (
       date DATE NOT NULL,
       id INT UNSIGNED NOT NULL,
       visitors INT UNSIGNED NOT NULL DEFAULT 0,
       pageviews INT UNSIGNED NOT NULL DEFAULT 0,
       PRIMARY KEY (date, id)
    ) ENGINE=INNODB CHARACTER SET=ascii"
);

$wpdb->query(
    "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}koko_analytics_referrer_urls (
       id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
       url VARCHAR(255) NOT NULL,
       UNIQUE INDEX (url)
    ) ENGINE=INNODB CHARACTER SET=ascii"
);

// Grant admin role the capabilities to view and manage Koko Analytics
$role = get_role('administrator');
if ($role) {
    $role->add_cap('view_koko_analytics');
    $role->add_cap('manage_koko_analytics');
}

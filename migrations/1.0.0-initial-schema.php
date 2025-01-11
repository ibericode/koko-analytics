<?php

defined('ABSPATH') or exit;

/** @var \wpdb $wpdb */
global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_site_stats");
$wpdb->query(
    "CREATE TABLE {$wpdb->prefix}koko_analytics_site_stats (
           date DATE PRIMARY KEY NOT NULL,
           visitors MEDIUMINT UNSIGNED NOT NULL,
           pageviews MEDIUMINT UNSIGNED NOT NULL
    ) ENGINE=INNODB CHARACTER SET=ascii"
);

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_post_stats");
$wpdb->query(
    "CREATE TABLE {$wpdb->prefix}koko_analytics_post_stats (
       date DATE NOT NULL,
       id BIGINT(20) UNSIGNED NOT NULL,
       visitors MEDIUMINT UNSIGNED NOT NULL,
       pageviews MEDIUMINT UNSIGNED NOT NULL,
       PRIMARY KEY (date, id)
    ) ENGINE=INNODB CHARACTER SET=ascii"
);

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_referrer_stats");
$wpdb->query(
    "CREATE TABLE {$wpdb->prefix}koko_analytics_referrer_stats (
       date DATE NOT NULL,
       id MEDIUMINT UNSIGNED NOT NULL,
       visitors MEDIUMINT UNSIGNED NOT NULL,
       pageviews MEDIUMINT UNSIGNED NOT NULL,
       PRIMARY KEY (date, id)
    ) ENGINE=INNODB CHARACTER SET=ascii"
);

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_referrer_urls");
$wpdb->query(
    "CREATE TABLE {$wpdb->prefix}koko_analytics_referrer_urls (
       id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
       url VARCHAR(255) NOT NULL,
       UNIQUE INDEX (url)
    ) ENGINE=INNODB CHARACTER SET=ascii"
);

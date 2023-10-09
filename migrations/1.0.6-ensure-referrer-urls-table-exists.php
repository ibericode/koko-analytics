<?php

defined('ABSPATH') or exit;

global $wpdb;
$wpdb->query(
    "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}koko_analytics_referrer_urls (
	   id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	   url VARCHAR(255) NOT NULL,
	   UNIQUE INDEX (url)
	) ENGINE=INNODB CHARACTER SET=ascii"
);

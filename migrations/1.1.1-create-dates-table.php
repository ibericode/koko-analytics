<?php

defined('ABSPATH') or exit;

/** @var \wpdb $wpdb */
global $wpdb;

$wpdb->query(
    "DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_dates"
);
$wpdb->query(
    "CREATE TABLE {$wpdb->prefix}koko_analytics_dates (
		date DATE PRIMARY KEY NOT NULL
	) ENGINE=INNODB CHARACTER SET=ascii"
);

$date   = new \DateTime('2000-01-01');
$end    = new \DateTime('2100-01-01');
$values = [];
while ($date < $end) {
    $values[] = $date->format('Y-m-d');
    $date->modify('+1 day');

    if (count($values) === 365) {
        $placeholders = rtrim(str_repeat('(%s),', count($values)), ',');
        $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_dates(date) VALUES {$placeholders}", $values));
        $values = [];
    }
}

$placeholders = rtrim(str_repeat('(%s),', count($values)), ',');
$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_dates(date) VALUES {$placeholders}", $values));

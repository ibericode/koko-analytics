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

$date   = new \DateTime('-10 years');
$end    = new \DateTime('+30 years');
while ($date < $end) {
    $dates[] = $date->format('Y-m-d');
    $date->modify('+1 day');
}

foreach (array_chunk($dates, 500) as $values) {
    $placeholders = rtrim(str_repeat('(%s),', count($values)), ',');
    $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_dates(date) VALUES {$placeholders}", $values));
}

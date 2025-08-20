<?php

use KokoAnalytics\Normalizers\Normalizer;

defined('ABSPATH') or exit;

@set_time_limit(0);

/** @var wpdb $wpdb */
global $wpdb;

$results = $wpdb->get_results("SELECT id, url FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE url LIKE 'http%'");
foreach ($results as $row) {
    $row->url = Normalizer::referrer($row->url);
    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_referrer_urls SET url = %s WHERE id = %s LIMIT 1", [ $row->url, $row->id ]));
}

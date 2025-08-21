<?php

use KokoAnalytics\Normalizers\Normalizer;

defined('ABSPATH') or exit;

@set_time_limit(0);

/** @var wpdb $wpdb */
global $wpdb;

$results = $wpdb->get_results("SELECT id, url FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE url LIKE 'http%'");
foreach ($results as $row) {
    $row->url = Normalizer::referrer($row->url);

    // skip seriously malformed url's
    if ($row->url === '') {
        continue;
    }

    // check if normalized url already has an entry
    $id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE url = %s", [$row->url]));
    if ($id) {
        // if so, update stats to point to existing entry
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_referrer_stats SET id = %d WHERE id = %d", [ $id, $row->id ]));
    } else {
        // otherwise change entry to normalized version
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_referrer_urls SET url = %s WHERE id = %s LIMIT 1", [ $row->url, $row->id ]));
    }
}

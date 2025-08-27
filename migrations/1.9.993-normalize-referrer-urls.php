<?php

use KokoAnalytics\Normalizers\Normalizer;

defined('ABSPATH') or exit;

@set_time_limit(0);

/** @var wpdb $wpdb */
global $wpdb;

// some of the UPDATE queries below can fail, we don't want to exit when that happens
$wpdb->hide_errors();

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
        // grab all rows in stats table pointing to old ID
        $stats = $wpdb->get_results($wpdb->prepare("SELECT date, id, pageviews, visitors FROM {$wpdb->prefix}koko_analytics_referrer_stats WHERE id = %d", [$row->id]));

        // update rows (if exist) with values from each date, id entry
        foreach ($stats as $s) {
            $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_referrer_stats SET visitors = visitors + %d, pageviews = pageviews + %d WHERE date = %s AND id = %d", [$s->visitors, $s->pageviews, $s->date, $id]));
        }

        // try to update all rows to new id (this will fail for some rows)
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_referrer_stats SET id = %d WHERE id = %d", [ $id, $row->id ]));

        // delete rows that still have old ID at this point
        $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_stats WHERE id = %d", [ $row->id ]));
    } else {
        // otherwise change entry to normalized version
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_referrer_urls SET url = %s WHERE id = %s LIMIT 1", [ $row->url, $row->id ]));
    }
}

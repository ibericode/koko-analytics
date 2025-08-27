<?php

use KokoAnalytics\Normalizers\Normalizer;

defined('ABSPATH') or exit;

@set_time_limit(0);

/** @var wpdb $wpdb */
global $wpdb;

// Select all rows with a post ID but no path ID
$results = $wpdb->get_results("SELECT DISTINCT(post_id) FROM {$wpdb->prefix}koko_analytics_post_stats WHERE post_id IS NOT NULL AND path_id IS NULL");

// process rows one by one
// this is slower, but the migration will continue and eventually finish over multiple requests
foreach ($results as $row) {
    $post_id = $row->post_id;
    $post_permalink = $post_id === "0" ? home_url('/') : get_permalink($post_id);
    if (!$post_permalink) {
        continue;
    }

    $url_parts = parse_url($post_permalink);
    $path = $url_parts['path'];
    if (!empty($url_parts['query'])) {
        $path .= '?' . $url_parts['query'];
    }

    // Entry points to nowhere, skip it... (ie deleted post)
    if (!$path) {
        continue;
    }

    // normalize path
    $path = Normalizer::path($path);

    // insert path
    // NOTE: We can't upsert here because we need a unique path_id for every date, post_id combination
    $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_paths (path) VALUES (%s)", [$path]));
    $path_id = $wpdb->insert_id;

    // update post_stats entry
    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_post_stats SET path_id = %d WHERE post_id = %d", [ $path_id, $post_id ]));
}

// now we can remove all rows without a path id
$wpdb->query("DELETE FROM {$wpdb->prefix}koko_analytics_post_stats WHERE path_id IS NULL");

// make new path_id column not-nullable
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats MODIFY COLUMN path_id MEDIUMINT UNSIGNED NOT NULL");

// change primary key to be on date and path_id column
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats DROP PRIMARY KEY, ADD PRIMARY KEY(date, path_id)");

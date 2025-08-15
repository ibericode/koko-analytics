<?php

defined('ABSPATH') or exit;

@set_time_limit(0);

/** @var wpdb $wpdb */
global $wpdb;

$wpdb->show_errors = WP_DEBUG;

$wpdb->query(
    "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}koko_analytics_paths (
       id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
       path VARCHAR(2000) NOT NULL,
       INDEX (path(191))
    ) ENGINE=INNODB CHARACTER SET=utf8mb4"
);
$wpdb->query("CREATE TABLE {$wpdb->prefix}koko_analytics_post_stats_backup AS SELECT * FROM {$wpdb->prefix}koko_analytics_post_stats");
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats CHANGE COLUMN id post_id MEDIUMINT UNSIGNED");
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats ADD COLUMN path_id MEDIUMINT UNSIGNED");

// We are updating posts one-by-one, because sometimes two posts have the same path
// Which is fine, but we want them to resolve to different path entries

// Select all rows with a post ID but no path ID
$results = $wpdb->get_results("SELECT DISTINCT(post_id) FROM {$wpdb->prefix}koko_analytics_post_stats WHERE post_id IS NOT NULL AND path_id IS NULL");

foreach ($results as $row) {
    $post_id = $row->post_id;
    $post_permalink = $post_id === "0" ? home_url('/') : get_permalink($post_id);
    if (!$post_permalink) {
        continue;
    }

    $url_parts = parse_url($post_permalink);
    $path = $url_parts['path'];
    if ($url_parts['query']) {
        $path .= '?' . $url_parts['query'];
    }

    // Entry points to nowhere, skip it... (ie deleted post)
    if (!$path) {
        continue;
    }

    // insert path
    $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_paths (path) VALUES (%s)", [$path]));
    $path_id = $wpdb->insert_id;

    // update post_stats entry
    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_post_stats SET path_id = %d WHERE post_id = %d", [ $path_id, $post_id ]));
}

// now we can remove all rows without a path id
$wpdb->query("DELETE FROM {$wpdb->prefix}koko_analytics_post_stats WHERE path_id IS NULL");

// make new column not-nullable
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats MODIFY COLUMN path_id MEDIUMINT UNSIGNED NOT NULL");

// change primary key to be on date and path_id column
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats DROP PRIMARY KEY, ADD PRIMARY KEY(date, path_id)");

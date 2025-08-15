<?php

defined('ABSPATH') or exit;

@set_time_limit(0);

/** @var wpdb $wpdb */
global $wpdb;

$wpdb->show_errors = true;

$wpdb->query(
    "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}koko_analytics_paths (
       id MEDIUMINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
       path VARCHAR(2000) NOT NULL,
       INDEX (path(191))
    ) ENGINE=INNODB CHARACTER SET=utf8mb4"
);

$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats CHANGE COLUMN id post_id MEDIUMINT UNSIGNED");
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats ADD COLUMN path_id MEDIUMINT UNSIGNED");

// We are updating posts one-by-one, because sometimes two posts have the same path
// Which is fine, but we want them to resolve to different path entries


// Select all rows with a post ID but no path ID
$results = $wpdb->get_results("SELECT DISTINCT(post_id) FROM {$wpdb->prefix}koko_analytics_post_stats WHERE post_id IS NOT NULL AND path_id IS NULL");

foreach ($results as $row) {
    $post_id = $row->post_id;
    $post_permalink = $post_id === "0" ? home_url('/') : get_permalink($post_id);
    $path = (string) parse_url($post_permalink, PHP_URL_PATH);
    $query_string = parse_url($post_permalink, PHP_URL_QUERY);
    if ($query_string) {
        $path .= '?' . $query_string;
    }

    // Entry points to nowhere
    // Maybe combine into a "deleted" path or something? Or
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
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats MODIFY COLUMN path_id MEDIUMINT UNSIGNED NOT NULL");
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats DROP PRIMARY KEY, ADD PRIMARY KEY(date, path_id)");

// TODO: COPY table as a form of back-up?
// TODO: Insert of modifying the existing table, let's just copy everything over to a new table? We can rename later.

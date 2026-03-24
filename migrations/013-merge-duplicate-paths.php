<?php

defined('ABSPATH') or exit;

/** @var wpdb $wpdb */
global $wpdb;

$table_paths = "{$wpdb->prefix}koko_analytics_paths";
$table_stats = "{$wpdb->prefix}koko_analytics_post_stats";

// Merge post_stats rows that reference duplicate paths (by first 180 chars)
// into the canonical (lowest id) path, summing visitors/pageviews on (date, path_id) collision
$wpdb->query(
    "INSERT INTO {$table_stats} (date, path_id, post_id, visitors, pageviews)
    SELECT ps.date, c.canonical_id, MAX(ps.post_id), SUM(ps.visitors), SUM(ps.pageviews)
    FROM {$table_stats} ps
    JOIN {$table_paths} p ON ps.path_id = p.id
    JOIN (
      SELECT LEFT(path, 255) AS prefix, MIN(id) AS canonical_id
      FROM {$table_paths}
      GROUP BY prefix
    ) c ON LEFT(p.path, 255) = c.prefix
    WHERE ps.path_id != c.canonical_id
    GROUP BY ps.date, c.canonical_id
    ON DUPLICATE KEY UPDATE
      visitors = {$table_stats}.visitors + VALUES(visitors),
      pageviews = {$table_stats}.pageviews + VALUES(pageviews)"
);

<?php

defined('ABSPATH') or exit;

/** @var wpdb $wpdb */
global $wpdb;

$table_paths = "{$wpdb->prefix}koko_analytics_paths";
$table_stats = "{$wpdb->prefix}koko_analytics_post_stats";

// Delete orphaned post_stats rows (those still pointing to non-canonical paths)
$wpdb->query(
    "DELETE ps FROM {$table_stats} ps
    JOIN {$table_paths} p ON ps.path_id = p.id
    JOIN (
      SELECT LEFT(path, 255) AS prefix, MIN(id) AS canonical_id
      FROM {$table_paths}
      GROUP BY prefix
    ) c ON LEFT(p.path, 255) = c.prefix
    WHERE ps.path_id != c.canonical_id"
);

<?php

defined('ABSPATH') or exit;

/** @var wpdb $wpdb */
global $wpdb;

$table_paths = "{$wpdb->prefix}koko_analytics_paths";
$table_stats = "{$wpdb->prefix}koko_analytics_post_stats";

// Step 3: Delete non-canonical path rows
$wpdb->query(
    "DELETE FROM {$table_paths}
    WHERE id NOT IN (
      SELECT canonical_id FROM (
        SELECT MIN(id) AS canonical_id
        FROM {$table_paths}
        GROUP BY LEFT(path, 255)
      ) t
    )"
);

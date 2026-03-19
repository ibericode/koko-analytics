<?php

defined('ABSPATH') or exit;

/** @var wpdb $wpdb */
global $wpdb;

$wpdb->hide_errors();

$table_paths = "{$wpdb->prefix}koko_analytics_paths";
$table_stats = "{$wpdb->prefix}koko_analytics_post_stats";

// Step 1: Merge post_stats rows that reference duplicate paths (by first 180 chars)
// into the canonical (lowest id) path, summing visitors/pageviews on (date, path_id) collision
$wpdb->query(
    "INSERT INTO {$table_stats} (date, path_id, post_id, visitors, pageviews)
    SELECT ps.date, c.canonical_id, MAX(ps.post_id), SUM(ps.visitors), SUM(ps.pageviews)
    FROM {$table_stats} ps
    JOIN {$table_paths} p ON ps.path_id = p.id
    JOIN (
      SELECT LEFT(path, 180) AS prefix, MIN(id) AS canonical_id
      FROM {$table_paths}
      GROUP BY prefix
    ) c ON LEFT(p.path, 180) = c.prefix
    WHERE ps.path_id != c.canonical_id
    GROUP BY ps.date, c.canonical_id
    ON DUPLICATE KEY UPDATE
      visitors = {$table_stats}.visitors + VALUES(visitors),
      pageviews = {$table_stats}.pageviews + VALUES(pageviews)"
);

// Step 2: Delete orphaned post_stats rows (those still pointing to non-canonical paths)
$wpdb->query(
    "DELETE ps FROM {$table_stats} ps
    JOIN {$table_paths} p ON ps.path_id = p.id
    JOIN (
      SELECT LEFT(path, 180) AS prefix, MIN(id) AS canonical_id
      FROM {$table_paths}
      GROUP BY prefix
    ) c ON LEFT(p.path, 180) = c.prefix
    WHERE ps.path_id != c.canonical_id"
);

// Step 3: Delete non-canonical path rows
$wpdb->query(
    "DELETE FROM {$table_paths}
    WHERE id NOT IN (
      SELECT canonical_id FROM (
        SELECT MIN(id) AS canonical_id
        FROM {$table_paths}
        GROUP BY LEFT(path, 180)
      ) t
    )"
);

// Step 4: Truncate long paths and change column to VARCHAR(180)
$wpdb->query("UPDATE {$table_paths} SET path = LEFT(path, 180) WHERE CHAR_LENGTH(path) > 180");
$wpdb->query("ALTER TABLE {$table_paths} MODIFY COLUMN path VARCHAR(180) NOT NULL");

// Step 5: Replace the prefix index with a UNIQUE index on the full column
$wpdb->query("ALTER TABLE {$table_paths} DROP INDEX path, ADD UNIQUE INDEX (path)");

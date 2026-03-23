<?php

defined('ABSPATH') or exit;

/** @var wpdb $wpdb */
global $wpdb;

$table_paths = "{$wpdb->prefix}koko_analytics_paths";

// Step 4: Convert the paths table to ASCII (since non-ASCII chars are truncated anyway)
$wpdb->query("ALTER TABLE {$table_paths} CONVERT TO CHARACTER SET ascii COLLATE ascii_general_ci");

// Step 5: Truncate long paths and change column to VARCHAR(255)
$wpdb->query("UPDATE {$table_paths} SET path = LEFT(path, 255) WHERE CHAR_LENGTH(path) > 255");
$wpdb->query("ALTER TABLE {$table_paths} MODIFY COLUMN path VARCHAR(255) NOT NULL");

// Step 6: Replace the prefix index with a UNIQUE index on the full column
$wpdb->query("ALTER TABLE {$table_paths} DROP INDEX path, ADD UNIQUE INDEX (path)");

<?php

defined('ABSPATH') or exit;

/** @var wpdb $wpdb */
global $wpdb;

$table_paths = "{$wpdb->prefix}koko_analytics_paths";

// Truncate any paths longer than 255 characters before changing column size
$wpdb->query("UPDATE {$table_paths} SET path = LEFT(path, 255) WHERE CHAR_LENGTH(path) > 255");

// Change column to VARCHAR(255) and replace prefix index with UNIQUE index in a single rebuild
$wpdb->query("ALTER TABLE {$table_paths} MODIFY COLUMN path VARCHAR(255) NOT NULL, DROP INDEX path, ADD UNIQUE INDEX (path)");

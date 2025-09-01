<?php

defined('ABSPATH') or exit;

/** @var \wpdb $wpdb */
global $wpdb;

// newer versions of MariaDB default to utf8mb4_uca1400_ai_ci, which is not widely supported
// specifically, some back-up tools would fail to back-up this specific table with this collation
// so here we convert to the older but more widely supported utf8mb4_unicode_520_ci collation instead
$wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_paths CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;");

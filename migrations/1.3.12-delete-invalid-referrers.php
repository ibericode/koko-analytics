<?php

defined('ABSPATH') or exit;

/** @var \wpdb $wpdb */
global $wpdb;

$site_url = get_site_url();

$wpdb->query($wpdb->prepare("DELETE s, u FROM {$wpdb->prefix}koko_analytics_referrer_stats s LEFT JOIN {$wpdb->prefix}koko_analytics_referrer_urls u ON s.id = u.id WHERE u.url LIKE %s;", [$site_url . '%']));

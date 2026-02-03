<?php

use KokoAnalytics\Endpoint_Installer;

defined('ABSPATH') or exit;

$settings = (array) get_option('koko_analytics_settings', []);

// migrate to new 'tracking_method' setting
if (isset($settings['use_cookie'])) {
    $settings['tracking_method'] = $settings['use_cookie'] ? 'cookie' : 'none';
    unset($settings['use_cookie']);
    update_option('koko_analytics_settings', $settings, true);
}

// re-install optimized endpoint file
(new Endpoint_Installer())->install();

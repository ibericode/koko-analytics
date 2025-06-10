<?php

defined('ABSPATH') or exit;

$settings = (array) get_option('koko_analytics_settings', []);

if (isset($settings['use_cookie'])) {
    $settings['tracking_method'] = $settings['use_cookie'] ? 'cookie' : 'none';
    unset($settings['use_cookie']);
    update_option('koko_analytics_settings', $settings, true);
}

// re-install optimized endpoint file
if (class_exists(KokoAnalytics\Endpoint_Installer::class)) {
    $endpoint_installer = new KokoAnalytics\Endpoint_Installer();
    if ($endpoint_installer->is_eligibile()) {
        $endpoint_installer->install();
    }
}

<?php

add_filter('koko_analytics_settings', function ($settings) {
    // for backwards compatibility with user scripts dynamically setting the "use_cookie" setting
    if (isset($settings['use_cookie']) && $settings['use_cookie']) {
        $settings['tracking_method'] = 'cookie';
        unset($settings['use_cookie']);
    }

    return $settings;
}, PHP_INT_MAX);

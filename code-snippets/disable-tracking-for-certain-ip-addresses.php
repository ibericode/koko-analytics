<?php

/**
 * Plugin Name: Koko Analytics: Disable tracking for certain IP addresses
 */

add_filter('koko_analytics_load_tracking_script', function () {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $excluded_ip_addresses = [
        '127.0.0.1',
        '254.123.182.120',
    ];
    if (in_array($ip_address, $excluded_ip_addresses, true)) {
        return false;
    }

    return true;
});

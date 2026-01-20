<?php

/**
 * Plugin Name: Koko Analytics: Disable certain Pro features
 *
 * This plugin completely disables the "Toolbar" and "Column" feature from Koko Analytics Pro.
 *
 * Available features to exclude:
 *  - Geolocation
 *  - Devices
 *  - Toolbar
 *  - Column
 *  - CSV
 *  - Events
 *  - Emails
 */

add_filter('koko_analytics_pro_features', function ($features) {
    return array_diff($features, ['Toolbar', 'Column']);
});

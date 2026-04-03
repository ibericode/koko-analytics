<?php

/**
 * Plugin Name: Koko Analytics: Disable tracking on certain URL prefixes
 */

add_filter('koko_analytics_load_tracking_script', function () {
    // do not load tracking script if URL starts with "/blog/"
    if (str_starts_with($_SERVER['REQUEST_URI'], '/blog/')) {
        return false;
    }

    return true;
});

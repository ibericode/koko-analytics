<?php
/**
 * Plugin Name: Koko Analytics: Ignore 404 Pages
 */

add_filter('koko_analytics_load_tracking_script', function ($load) {
    return $load && ! is_404();
});

<?php

/**
 * Plugin Name: Koko Analytics: Allow 'area' query var
 *
 * This code snippet adds the "area" query parameter to the list of allowed query variables.
 */
add_filter('koko_analytics_allowed_query_vars', function($allowed) {
    $allowed[] = 'area';
    return $allowed;
});

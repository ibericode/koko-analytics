<?php

// Filter to set the value for the numbers of days for the "pageviews column" added by Koko Analytics Pro

add_filter('get_user_metadata', function ($value, $object_id, $meta_key) {
    if ($meta_key !== '_screen_option_koko_analytics_days') {
        return $value;
    }

    return 1000;
}, 10, 3);

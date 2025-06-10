<?php

/**
 * Sets the default value of the "tracking_method" setting to "fingerprint"
 */

add_filter('default_option_koko_analytics_settings', function ($options) {
    $options['tracking_method'] = 'fingerprint';
    return $options;
});

<?php

/**
 * Plugin Name: Koko Analytics Pro: Change Email Report Item Limits
 *
 * Set a limit to 0 to hide that section from periodic email reports.
 */

add_filter('koko_analytics_email_report_item_limits', function ($limits) {
    $limits['posts']             = 15;
    $limits['referrers']         = 15;
    $limits['countries']         = 10;
    $limits['browsers']          = 0;
    $limits['operating_systems'] = 0;
    $limits['devices']           = 5;

    return $limits;
});

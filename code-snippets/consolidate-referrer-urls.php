<?php

/**
 * You can instruct Koko Analytics to normalize referrer URL's into a single entry.
 * The `koko_analytics_url_aggregations` accepts a regex rule and regex replacement, so this can be very powerful.
 *
 * Below is a simple example that replaces all traffic coming from https://syndicatedsearch.goog with https://google.com
 *
 * Tip: Use https://regex101.com to test your regexes.
 */

add_filter('koko_analytics_url_aggregations', function ($rules) {
    $rules['/^https?:\/\/syndicatedsearch\.goog$/'] = 'https://google.com';
    return $rules;
});

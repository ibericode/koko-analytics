<?php

/**
 * Plugin Name: Koko Analytics: Add [koko_analytics_counter] shortcode to every post
 */

add_filter('the_content', function ($content) {
    if (is_single()) {
        $content .= PHP_EOL;
        $content .= 'This post was viewed a total of ';
        $content .= '[koko_analytics_counter]';
        $content .= ' times.';
    }

    return $content;
});

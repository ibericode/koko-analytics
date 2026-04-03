<?php

/**
 * Plugin Name: Koko Analytics: Disable HTML Comments
 */

add_filter('koko_analytics_print_html_comments', '__return_false');

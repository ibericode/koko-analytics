<?php

/*
Plugin Name: Koko Analytics
Plugin URI: https://www.kokoanalytics.com/#utm_source=wp-plugin&utm_medium=koko-analytics&utm_campaign=plugins-page
Version: 1.3.0
Description: Privacy-friendly analytics for your WordPress site.
Author: ibericode
Author URI: https://ibericode.com/#utm_source=wp-plugin&utm_medium=koko-analytics&utm_campaign=plugins-page
Author Email: support@kokoanalytics.com
Text Domain: koko-analytics
License: GPL-3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Koko Analytics - website analytics plugin for WordPress

Copyright (C) 2019 - 2023, Danny van Kooten, hi@dannyvankooten.com

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

phpcs:disable PSR1.Files.SideEffects
*/

namespace KokoAnalytics;

\define('KOKO_ANALYTICS_VERSION', '1.3.0');
\define('KOKO_ANALYTICS_PLUGIN_FILE', __FILE__);
\define('KOKO_ANALYTICS_PLUGIN_DIR', __DIR__);

// Conditionally load our autoloader
// This allows people to install the plugin through wpackagist and use a site-wide autoloader
if (! class_exists('KokoAnalytics\Plugin')) {
    require __DIR__ . '/vendor/autoload.php';
}

if (\defined('DOING_AJAX') && DOING_AJAX) {
    maybe_collect_request();
} elseif (is_admin()) {
    $admin = new Admin();
    $admin->init();
} else {
    $loader = new Script_Loader();
    $loader->init();

    add_action('admin_bar_menu', 'KokoAnalytics\admin_bar_menu', 40);
}

$dashboard = new Dashboard();
$dashboard->add_hooks();

$aggregator = new Aggregator();
$aggregator->init();

$plugin = new Plugin($aggregator);
$plugin->init();

$rest = new Rest();
$rest->init();

$shortcode = new Shortcode_Most_Viewed_Posts();
$shortcode->init();

$pruner = new Pruner();
$pruner->init();

if (\class_exists('WP_CLI')) {
    \WP_CLI::add_command('koko-analytics', 'KokoAnalytics\Command');
}

add_action('widgets_init', 'KokoAnalytics\widgets_init');
add_action('koko_analytics_test_custom_endpoint', 'KokoAnalytics\test_custom_endpoint');

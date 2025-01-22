<?php

/*
Plugin Name: Koko Analytics
Plugin URI: https://www.kokoanalytics.com/#utm_source=wp-plugin&utm_medium=koko-analytics&utm_campaign=plugins-page
Version: 1.6.3
Description: Privacy-friendly analytics for your WordPress site.
Author: ibericode
Author URI: https://www.ibericode.com/
Author Email: support@kokoanalytics.com
Text Domain: koko-analytics
License: GPL-3.0-or-later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Koko Analytics - website analytics plugin for WordPress

Copyright (C) 2019 - 2025, Danny van Kooten, hi@dannyvankooten.com

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

\define('KOKO_ANALYTICS_VERSION', '1.6.3');
\define('KOKO_ANALYTICS_PLUGIN_FILE', __FILE__);
\define('KOKO_ANALYTICS_PLUGIN_DIR', __DIR__);

// Load the Koko Analytics autoloader
require __DIR__ . '/autoload.php';

// don't run if PHP version is lower than 7.4
// prevent direct file access
if (PHP_VERSION_ID < 70400 || ! \defined('ABSPATH')) {
    return;
}

// Maybe run any pending database migrations
require __DIR__ . '/src/class-migrations.php';
$migrations = new Migrations('koko_analytics_version', KOKO_ANALYTICS_VERSION, KOKO_ANALYTICS_PLUGIN_DIR . '/migrations/');
add_action('init', [$migrations, 'maybe_run'], 10, 0);

require __DIR__ . '/src/class-aggregator.php';
new Aggregator();

require __DIR__ . '/src/class-plugin.php';
new Plugin();

if (\defined('DOING_AJAX') && DOING_AJAX) {
    // ajax only
    add_action('init', 'KokoAnalytics\maybe_collect_request', 1, 0);
} elseif (is_admin()) {
    // wp-admin only
    new Admin();
    new Dashboard_Widget();
} else {
    // frontend only
    require __DIR__ . '/src/class-script-loader.php';
    new Script_Loader();
    add_action('admin_bar_menu', 'KokoAnalytics\admin_bar_menu', 40, 1);
}

require __DIR__ . '/src/class-query-loop-block.php';
new QueryLoopBlock();

require __DIR__ . '/src/class-dashboard.php';
new Dashboard();

require __DIR__ . '/src/class-rest.php';
new Rest();

require __DIR__ . '/src/class-shortcode-most-viewed-posts.php';
new Shortcode_Most_Viewed_Posts();

require __DIR__ . '/src/class-shortcode-site-counter.php';
new ShortCode_Site_Counter();

require __DIR__ . '/src/class-pruner.php';
new Pruner();

if (\class_exists('WP_CLI')) {
    \WP_CLI::add_command('koko-analytics', 'KokoAnalytics\Command');
}

add_action('widgets_init', function () {
    require KOKO_ANALYTICS_PLUGIN_DIR . '/src/class-widget-most-viewed-posts.php';
    register_widget(Widget_Most_Viewed_Posts::class);
});

add_action('koko_analytics_test_custom_endpoint', 'KokoAnalytics\test_custom_endpoint');

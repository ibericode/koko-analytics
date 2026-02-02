<?php

/*
Plugin Name: Koko Analytics
Plugin URI: https://www.kokoanalytics.com/#utm_source=wp-plugin&utm_medium=koko-analytics&utm_campaign=plugins-page
Version: 2.2.1
Description: Privacy-friendly and efficient statistics for your WordPress site.
Author: ibericode
Author URI: https://www.ibericode.com/
Author Email: support@kokoanalytics.com
Text Domain: koko-analytics
License: GPL-3.0-or-later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Koko Analytics - website analytics plugin for WordPress

Copyright (C) 2019 - 2026, Danny van Kooten, hi@dannyvankooten.com

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

use KokoAnalytics\Shortcodes\Shortcode_Most_Viewed_Posts;
use KokoAnalytics\Shortcodes\Shortcode_Site_Counter;
use KokoAnalytics\Widgets\Most_Viewed_Posts_Widget;

\define('KOKO_ANALYTICS_VERSION', '2.2.1');
\define('KOKO_ANALYTICS_PLUGIN_FILE', __FILE__);
\define('KOKO_ANALYTICS_PLUGIN_DIR', __DIR__);

// Load the Koko Analytics autoloader
require __DIR__ . '/autoload.php';

// don't run if PHP version is lower than 7.4
// prevent direct file access
if (PHP_VERSION_ID < 70400 || ! \defined('ABSPATH')) {
    return;
}

// Main hooks (global)
require __DIR__ . '/src/Controller.php';
(new Controller())->hook();

// block types
require __DIR__ . '/src/Blocks.php';
(new Blocks())->hook();

// Admin hooks (admin/ajax only)
if (is_admin() && (false == defined('DOING_AJAX') || false == DOING_AJAX)) {
    (new Admin\Controller())->hook();
}

// TODO: Optimize the below so we only need a single action hook
add_action('koko_analytics_aggregate_stats', [Aggregator::class, 'run'], 10, 0);
add_action('koko_analytics_prune_data', [Pruner::class, 'run'], 10, 0);
add_action('koko_analytics_rotate_fingerprint_seed', [Fingerprinter::class, 'run_daily_maintenance'], 10, 0);
add_action('koko_analytics_test_custom_endpoint', [Endpoint_Installer::class, 'test'], 10, 0);

// WP CLI command
if (class_exists('WP_CLI') && method_exists('WP_CLI', 'add_command')) {
    \WP_CLI::add_command('koko-analytics', Command::class);
}

// on plugin activation
register_activation_hook(__FILE__, function () {
    Aggregator::setup_scheduled_event();
    Pruner::setup_scheduled_event();
    Fingerprinter::setup_scheduled_event();
    Endpoint_Installer::install();
    Plugin::setup_capabilities();
    Plugin::create_and_protect_uploads_dir();
});

// on plugin deactivation
register_deactivation_hook(__FILE__, function () {
    Aggregator::clear_scheduled_event();
    Pruner::clear_scheduled_event();
    Fingerprinter::clear_scheduled_event();
    Endpoint_Installer::uninstall();
});

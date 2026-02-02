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

// Maybe run any pending database migrations
add_action('wp_loaded', function () {
    $migrations = new Migrations('koko_analytics', KOKO_ANALYTICS_VERSION, KOKO_ANALYTICS_PLUGIN_DIR . '/migrations/');
    $migrations->maybe_run();
}, 10, 0);

// aggregator
add_filter('cron_schedules', function ($schedules) {
    $schedules['koko_analytics_stats_aggregate_interval'] = [
        'interval' => 60, // 60 seconds
        'display'  => esc_html__('Every minute', 'koko-analytics'),
    ];
    return $schedules;
}, 10, 1);
add_action('koko_analytics_aggregate_stats', [Aggregator::class, 'run'], 10, 0);

// ajax collection endpoint (only used in case optimized endpoint is not installed)
add_action('init', 'KokoAnalytics\maybe_collect_request', 0, 0);

// script loader
add_action('wp_head', [Script_Loader::class, 'print_js_object'], 1, 0);
add_action('wp_footer', [Script_Loader::class, 'maybe_print_script'], 10, 0);
add_action('amp_print_analytics', [Script_Loader::class, 'print_amp_analytics_tag'], 10, 0);
add_action('admin_bar_menu', [Admin\Bar::class, 'register'], 40, 1);

// init REST API endpoint
add_action('rest_api_init', [Rest::class, 'register_routes'], 10, 0);

// pruner
add_action('koko_analytics_prune_data', [Pruner::class, 'run'], 10, 0);

// fingerprinting
add_action('koko_analytics_rotate_fingerprint_seed', [Fingerprinter::class, 'run_daily_maintenance'], 10, 0);

// optimized endpoint
add_action('koko_analytics_test_custom_endpoint', [Endpoint_Installer::class, 'test'], 10, 0);

// WP CLI command
if (class_exists('WP_CLI') && method_exists('WP_CLI', 'add_command')) {
    \WP_CLI::add_command('koko-analytics', Command::class);
}

// register shortcodes
add_action('init', function () {
    add_shortcode('koko_analytics_most_viewed_posts', [Shortcode_Most_Viewed_Posts::class, 'content']);
    add_shortcode('koko_analytics_counter', [Shortcode_Site_Counter::class, 'content']);
}, 10, 0);

// run koko_analytics_action=[a-z] hooks
add_action('wp_loaded', [Actions::class, 'run'], 20, 0);

// maybe show standalone dashboard
add_action('init', function () {
    if (!Router::is('dashboard-standalone')) {
        return;
    }

    $settings = get_settings();
    if (!$settings['is_dashboard_public'] && !current_user_can('view_koko_analytics')) {
        return;
    }

    // don't serve public dashboard to anything that looks like a bot or crawler
    if (empty($_SERVER['HTTP_USER_AGENT']) || \preg_match("/bot|crawl|spider/", strtolower($_SERVER['HTTP_USER_AGENT']))) {
        return;
    }

    header("Content-Type: text/html; charset=utf-8");
    header("X-Robots-Tag: noindex, nofollow");

    if (is_user_logged_in()) {
        header("Cache-Control: no-store, must-revalidate, no-cache, max-age=0, private");
    } elseif (isset($_GET['end_date'], $_GET['start_date']) && $_GET['end_date'] < date('Y-m-d')) {
        header("Cache-Control: public, max-age=68400");
    } else {
        header("Cache-Control: public, max-age=60");
    }

    (new Dashboard_Standalone())->show();
    exit;
}, 10, 0);

// register most viewed posts widget
add_action('widgets_init', [Most_Viewed_Posts_Widget::class, 'register'], 10, 0);

// block types
require __DIR__ . '/src/Blocks.php';
(new Blocks())->hook();

if (\is_admin()) {
    new Admin\Admin();
    add_action('wp_dashboard_setup', [Dashboard_Widget::class, 'register_dashboard_widget'], 10, 0);
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

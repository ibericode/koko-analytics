<?php
/*
Plugin Name: Koko Analytics
Version: 1.0.0-rc1
Plugin URI: https://dvk.co/#utm_source=wp-plugin&utm_medium=koko-analytics&utm_campaign=plugins-page
Description: Privacy-friendly analytics for your WordPress site.
Author: ibericode
Author URI: https://ibericode.com/#utm_source=wp-plugin&utm_medium=koko-analytics&utm_campaign=plugins-page
Text Domain: koko-analytics
Domain Path: /languages/
License: GPL v3

Copyright (C) 2019, Danny van Kooten, hi@dannyvankooten.com

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
*/

namespace KokoAnalytics;

define('KOKO_ANALYTICS_VERSION', '1.0.0-rc1');
define('KOKO_ANALYTICS_PLUGIN_FILE', __FILE__);
define('KOKO_ANALYTICS_PLUGIN_DIR', __DIR__);

require __DIR__ . '/src/functions.php';

maybe_collect_request();

if (defined('DOING_AJAX') && DOING_AJAX) {

} else if(defined('DOING_CRON') && DOING_CRON) {

} else if (is_admin()) {
	// load translation files
	load_plugin_textdomain('koko-analytics', false, __DIR__ . '/languages');

	require __DIR__ . '/src/class-migrations.php';
	require __DIR__ . '/src/class-admin.php';
    $admin = new Admin();
    $admin->init();
} else {
    add_action('wp_head', function() {
		// TODO: Handle "term" requests so we track both terms and post types.
		$post_id = is_singular() ? (int) get_queried_object_id() : 0;
		$use_custom_endpoint = file_exists(ABSPATH . '/koko-analytics-collect.php');
        wp_enqueue_script('koko-analytics-tracker', plugins_url('assets/dist/js/tracker.js', KOKO_ANALYTICS_PLUGIN_FILE), array(), KOKO_ANALYTICS_VERSION, true);
        wp_localize_script('koko-analytics-tracker', 'koko_analytics', array(
            'post_id' => $post_id,
			'tracker_url' => $use_custom_endpoint ? site_url('/koko-analytics-collect.php') : admin_url('admin-ajax.php'),
        ));
    });
}

require __DIR__ . '/src/class-plugin.php';
$plugin = new Plugin();
$plugin->init();

require __DIR__ . '/src/class-aggregator.php';
$aggregator = new Aggregator();
$aggregator->init();

require __DIR__ . '/src/class-rest.php';
$rest = new Rest();
$rest->init();

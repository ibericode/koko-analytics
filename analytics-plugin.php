<?php
/*
Plugin Name: Analytics Plugin
Version: 1.0
Plugin URI: https://dvk.co/#utm_source=wp-plugin&utm_medium=analytics-plugin&utm_campaign=plugins-page
Description: Privacy-friendly analytics for your WordPress site.
Author: ibericode
Author URI: https://ibericode.com/#utm_source=wp-plugin&utm_medium=analytics-plugin&utm_campaign=plugins-page
Text Domain: analytics-plugin
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

namespace AP;

define('AP_VERSION' ,'1.0');
define('AP_PLUGIN_FILE', __FILE__);
define('AP_PLUGIN_DIR', __DIR__);

// for compat with site-wide autoloader
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
}

maybe_collect_request();

if (defined('DOING_AJAX') && DOING_AJAX) {

} else if(defined('DOING_CRON') && DOING_CRON) {

} else if (is_admin()) {
    $admin = new Admin();
    $admin->init();
} else {
    add_action('wp_head', function() {
		// TODO: Handle "term" requests so we track both terms and post types.
		$post_id = is_singular() ? (int) get_queried_object_id() : 0;
		$use_custom_endpoint = file_exists(ABSPATH . '/ap-collect.php');
        wp_enqueue_script('ap-tracker', plugins_url('assets/dist/js/tracker.js', AP_PLUGIN_FILE), array(), AP_VERSION, true);
        wp_localize_script('ap-tracker', 'ap', array(
            'post_id' => $post_id,
			'tracker_url' => $use_custom_endpoint ? site_url('/ap-collect.php') : admin_url('admin-ajax.php'),
        ));


    });
}

$plugin = new Plugin();
$plugin->init();

$aggregator = new Aggregator();
$aggregator->init();

$rest = new Rest();
$rest->init();

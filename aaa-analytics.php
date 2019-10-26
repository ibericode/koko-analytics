<?php
/*
Plugin Name: Anonymous Analytics
Version: 1.0
Plugin URI: https://dvk.co/#utm_source=wp-plugin&utm_medium=anonymous-analytics&utm_campaign=plugins-page
Description: Privacy-friendly analytics for your WordPress site.
Author: ibericode
Author URI: https://ibericode.com/#utm_source=wp-plugin&utm_medium=boxzilla&utm_campaign=plugins-page
Text Domain: anonymous-analytics
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

namespace AAA;

define('AAA_VERSION', '1.0');
define('AAA_PLUGIN_FILE', __FILE__);
define('AAA_PLUGIN_DIR', __DIR__);

require __DIR__ . '/vendor/autoload.php';

maybe_collect_request();

if (defined('DOING_AJAX') && DOING_AJAX) {

} else if((defined('DOING_CRON') && DOING_CRON) || isset($_GET['aaa_aggregate'])) {

} else if (is_admin()) {
    $admin = new Admin();
    $admin->init();
} else {
	add_action('wp', function() {
		var_dump(get_queried_object_id());
	});
    add_action('wp_head', function() {
		$use_custom_endpoint = file_exists(ABSPATH . '/aaa-collect.php');
        wp_enqueue_script('aaa-tracker', plugins_url('assets/dist/js/tracker.js', AAA_PLUGIN_FILE), array(), AAA_VERSION, true);
        wp_localize_script('aaa-tracker', 'aaa', array(
            'post_id' =>get_queried_object_id(),
            'ip' => $_SERVER['REMOTE_ADDR'],
			'tracker_url' => $use_custom_endpoint ? site_url('/aaa-collect.php') : admin_url('admin-ajax.php'),
        ));
    });
}

$aggregator = new Aggregator();
$aggregator->init();


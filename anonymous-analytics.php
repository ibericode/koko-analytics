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

Boxzilla Plugin
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

define('AA_VERSION', '1.0');
define('AA_PLUGIN_FILE', __FILE__);

require __DIR__ . '/vendor/autoload.php';

add_action('wp_head', function() {
   wp_enqueue_script('anonymous-analytics-tracker', plugins_url('assets/js/tracker.js', AA_PLUGIN_FILE), array(), AA_VERSION, true);
    wp_localize_script('anonymous-analytics-tracker', 'aa', array(
       'tracker_url' => plugins_url('src/track.php', AA_PLUGIN_FILE),
    ));
});
<?php
/*
Plugin Name: Koko Analytics
Plugin URI: https://www.kokoanalytics.com/#utm_source=wp-plugin&utm_medium=koko-analytics&utm_campaign=plugins-page
Version: 1.0.13
Description: Privacy-friendly analytics for your WordPress site.
Author: ibericode
Author URI: https://ibericode.com/#utm_source=wp-plugin&utm_medium=koko-analytics&utm_campaign=plugins-page
Author Email: support@kokoanalytics.com
Text Domain: koko-analytics
Domain Path: /languages/
License: GPL-3.0
License URI: https://www.gnu.org/licenses/gpl-3.0.txt

Koko Analytics - website analytics plugin for WordPress

Copyright (C) 2019 - 2020, Danny van Kooten, hi@dannyvankooten.com

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

define( 'KOKO_ANALYTICS_VERSION', '1.0.13' );
define( 'KOKO_ANALYTICS_PLUGIN_FILE', __FILE__ );
define( 'KOKO_ANALYTICS_PLUGIN_DIR', __DIR__ );

require __DIR__ . '/src/functions.php';

if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
	maybe_collect_request();
} elseif ( is_admin() ) {
	require __DIR__ . '/src/class-migrations.php';
	require __DIR__ . '/src/class-admin.php';
	$admin = new Admin();
	$admin->init();
} else {
	require __DIR__ . '/src/class-script-loader.php';
	$loader = new Script_Loader();
	$loader->init();

	add_action( 'admin_bar_menu', 'KokoAnalytics\admin_bar_menu', 40 );
}

require __DIR__ . '/src/class-aggregator.php';
$aggregator = new Aggregator();
$aggregator->init();

require __DIR__ . '/src/class-rest.php';
$rest = new Rest();
$rest->init();

add_action( 'widgets_init', 'KokoAnalytics\widgets_init' );

require __DIR__ . '/src/class-shortcode-most-viewed-posts.php';
$shortcode = new Shortcode_Most_Viewed_Posts();
$shortcode->init();

require __DIR__ . '/src/class-pruner.php';
$pruner = new Pruner();
$pruner->init();

require __DIR__ . '/src/class-plugin.php';
$plugin = new Plugin( $aggregator );
$plugin->init();

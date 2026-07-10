<?php

/*
Plugin Name: Koko Analytics
Plugin URI: https://www.kokoanalytics.com/#utm_source=koko-analytics&utm_medium=link&utm_campaign=free-plugin-plugin-header
Version: 2.5.1-dev1
Description: Privacy-friendly and efficient statistics for your WordPress site.
Author: ibericode
Author URI: https://www.ibericode.com/
Author Email: support@kokoanalytics.com
Requires at least: 6.2
Requires PHP: 7.4
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
*/

namespace KokoAnalytics;

use WP_CLI;

// prevent direct file access
defined('ABSPATH') || exit;

// don't run on PHP < 7.4
PHP_VERSION_ID >= 70400 || exit;

define('KOKO_ANALYTICS_VERSION', '2.5.1-dev1');
define('KOKO_ANALYTICS_PLUGIN_FILE', __FILE__);
define('KOKO_ANALYTICS_PLUGIN_DIR', __DIR__);

// Load the Koko Analytics autoloader
require __DIR__ . '/autoload.php';

// Main hooks (global)
require __DIR__ . '/src/Controller.php';
(new Controller())->hook();

// Block related hooks
require __DIR__ . '/src/Blocks.php';
(new Blocks())->hook();

// Admin hooks (admin only)
if (is_admin()) {
    require __DIR__ . '/src/Admin/Controller.php';
    (new Admin\Controller())->hook();
}

// WP CLI command
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('koko-analytics', Command::class);
}

register_activation_hook(__FILE__, lazy(Plugin::class, 'action_activate_plugin'));
register_deactivation_hook(__FILE__, lazy(Plugin::class, 'action_deactivate_plugin'));

<?php

// phpcs:disable PSR1.Files.SideEffects

// if query parameter is set, disable opcache until end of request
if (isset($_GET['disable-opcache']) && (int) $_GET['disable-opcache'] === 1) {
    ini_set('opcache.enable', 0);
    ini_set('opcache.enable_cli', 0);
}

require dirname(__DIR__) . '/mocks.php';

// make sure we're not running through all migrations
update_option('koko_analytics_version', '999.1.1');

$memory = memory_get_usage();
$time_start = microtime(true);

// require main plugin file
require dirname(__DIR__, 2) . '/koko-analytics.php';

do_action('plugins_loaded');
do_action('setup_theme');
do_action('after_setup_theme');
do_action('init');
do_action('wp_loaded');
do_action('parse_request');

$time = (microtime(true) - $time_start) * 1000.0;
$memory_used = (memory_get_usage() - $memory) >> 10;

header("Content-Type: application/json");
echo json_encode(['memory' => $memory_used, 'time' => $time]);

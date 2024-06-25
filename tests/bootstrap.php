<?php

require __DIR__ . '/../autoload.php';

define('KOKO_ANALYTICS_PLUGIN_FILE', '../koko-analytics.php');

function apply_filters($a, $b, $prio = 10, $args = 2) {
	return $b;
}
function add_action($a, $b, $c = 10, $d = 1) {}
function add_filter($a, $b, $c = 10, $d = 1) {}
function add_shortcode($a, $b) {}
function register_activation_hook($a, $b) {}

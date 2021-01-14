<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 *
 * This file acts as an optimized endpoint file for the Koko Analytics plugin.
 */

// path to pageviews.php file in uploads directory
define('KOKO_ANALYTICS_BUFFER_FILE', __DIR__ . '/../../uploads/pageviews.php');

// path to src/functions.php in Koko Analytics plugin directory
require __DIR__ . '/src/functions.php';

// function call to collect request data
KokoAnalytics\collect_request();





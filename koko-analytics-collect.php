<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 * Assumes a default WordPress installation, needs to be placed in the root directory.
 */

define('KOKO_ANALYTICS_BUFFER_FILE', __DIR__ . '/wp-content/uploads/pageviews.php');

require __DIR__ . '/wp-content/plugins/koko-analytics/src/functions.php';

KokoAnalytics\collect_request();



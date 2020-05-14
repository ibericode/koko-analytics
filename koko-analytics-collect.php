<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 * Assumes a default WordPress installation, needs to be placed in the root directory.
 */

define('KOKO_ANALYTICS_BUFFER_FILE', __DIR__ . '/../../uploads/pageviews.php');

require __DIR__ . '/src/functions.php';

KokoAnalytics\collect_request();





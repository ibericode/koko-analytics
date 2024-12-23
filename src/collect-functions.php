<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 *
 * This file contains the code required for data ingestion.
 * It is meant to be included from the optimized endpoint file.
 */

namespace KokoAnalytics;

function maybe_collect_request()
{
    // since we call this function (early) on every AJAX request, detect our specific request here
    // this allows us to short-circuit a bunch of unrelated AJAX stuff and gain a lot of performance
    if (!isset($_GET['action']) || $_GET['action'] !== 'koko_analytics_collect' || !\defined('DOING_AJAX') || !DOING_AJAX) {
        return;
    }

    collect_request();
}

function extract_pageview_data(array $raw): array
{
    // do nothing if a required parameter is missing
    if (!isset($raw['p'], $raw['nv'], $raw['up'])) {
        return [];
    }

    // grab and validate parameters
    $post_id = \filter_var($raw['p'], FILTER_VALIDATE_INT);
    $new_visitor = \filter_var($raw['nv'], FILTER_VALIDATE_INT);
    $unique_pageview = \filter_var($raw['up'], FILTER_VALIDATE_INT);
    $referrer_url = !empty($raw['r']) ? \filter_var(\trim($raw['r']), FILTER_VALIDATE_URL) : '';

    if ($post_id === false || $new_visitor === false || $unique_pageview === false || $referrer_url === false) {
        return [];
    }

    // limit referrer URL to 255 chars
    $referrer_url = \substr($referrer_url, 0, 255);

    return [
        'p',                // type indicator
        $post_id,
        $new_visitor,
        $unique_pageview,
        $referrer_url,
    ];
}

function extract_event_data(array $raw): array
{
    if (!isset($raw['e'], $raw['p'], $raw['u'], $raw['v'])) {
        return [];
    }

    $unique_event = \filter_var($raw['u'], FILTER_VALIDATE_INT);
    $value = \filter_var($raw['v'], FILTER_VALIDATE_INT);
    if ($unique_event === false || $value === false) {
        return [];
    }

    $event_name = \trim($raw['e']);
    $event_param = \trim($raw['p']);

    if (\strlen($event_name) === 0) {
        return [];
    }

    // limit event name and parameter lengths
    $event_name = \substr($event_name, 0, 100);
    $event_param = \substr($event_param, 0, 185);

    return [
        'e',                   // type indicator
        $event_name,           // 0: event name
        $event_param,          // 1: event parameter
        $unique_event,         // 2: is unique?
        $value,                // 3: event value
    ];
}

function collect_request()
{
    // ignore requests from bots, crawlers and link previews
    if (empty($_SERVER['HTTP_USER_AGENT']) || \preg_match("/bot|crawl|spider|seo|lighthouse|facebookexternalhit|preview/i", $_SERVER['HTTP_USER_AGENT'])) {
        return;
    }

    // if WordPress environment is loaded, check if request is excluded
    // TODO: come up with a way to check for excluded request without WordPress
    if (\defined('ABSPATH') && function_exists('is_request_excluded') && is_request_excluded()) {
        return;
    }

    if (isset($_GET['e'])) {
        $data = extract_event_data($_GET);
    } else {
        $data = extract_pageview_data($_GET);
    }

    if (!empty($data)) {
        $success = isset($_GET['test']) ? test_collect_in_file() : collect_in_file($data);

        // set OK headers & prevent caching
        if (!$success) {
            \header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
        } else {
            \header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK');
        }
    } else {
        \header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
    }

    \header('Content-Type: text/plain');

    // Prevent this response from being cached
    \header('Cache-Control: no-cache, must-revalidate, max-age=0');

    // indicate that we are not tracking user specifically, see https://www.w3.org/TR/tracking-dnt/
    \header('Tk: N');

    // set cookie server-side if requested (eg for AMP requests)
    if (isset($_GET['p']) && isset($_GET['nv']) && isset($_GET['sc']) && (int) $_GET['sc'] === 1) {
        $posts_viewed = isset($_COOKIE['_koko_analytics_pages_viewed']) ? \explode(',', $_COOKIE['_koko_analytics_pages_viewed']) : array('');
        if ((int) $_GET['nv']) {
            $posts_viewed[] = (int) $_GET['p'];
        }
        $cookie = \join(',', $posts_viewed);
        \setcookie('_koko_analytics_pages_viewed', $cookie, \time() + 6 * 3600, '/');
    }

    exit;
}

function get_buffer_filename(): string
{
    if (\defined('KOKO_ANALYTICS_BUFFER_FILE')) {
        return KOKO_ANALYTICS_BUFFER_FILE;
    }

    $uploads = wp_upload_dir(null, false);
    return \rtrim($uploads['basedir'], '/') . '/pageviews.php';
}

function collect_in_file(array $data): bool
{
    $filename = get_buffer_filename();

    // if file does not yet exist, add PHP header to prevent direct file access
    if (!\is_file($filename)) {
        $content = '<?php exit; ?>' . PHP_EOL;
    } else {
        $content = '';
    }

    // append data to file
    $line     = \join(',', $data) . PHP_EOL;
    $content .= $line;
    return (bool) \file_put_contents($filename, $content, FILE_APPEND);
}

function test_collect_in_file(): bool
{
    $filename = get_buffer_filename();
    if (file_exists($filename)) {
        return is_writable($filename);
    }

    $dir = dirname($filename);
    return is_writable($dir);
}

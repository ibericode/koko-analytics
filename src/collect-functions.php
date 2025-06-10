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

function maybe_collect_request(): void
{
    if (($_GET['action'] ?? '') !== 'koko_analytics_collect') {
        return;
    }

    collect_request();
}

// TODO:
// - Clean-up session directory every midnight
// - Rotate daily seed at midnight
// - Update AMP code
// - Convert cookie to HTTP only

function extract_pageview_data(array $raw, $new_visitor, $unique_pageview): array
{
    // do nothing if a required parameter is missing
    if (!isset($raw['p'])) {
        return [];
    }

    // grab and validate parameters
    $post_id = \filter_var($raw['p'], FILTER_VALIDATE_INT);
    $referrer_url = !empty($raw['r']) ? \filter_var(\trim($raw['r']), FILTER_VALIDATE_URL) : '';

    if ($post_id === false || $referrer_url === false) {
        return [];
    }

    // limit referrer URL to 255 chars
    $referrer_url = \substr($referrer_url, 0, 255);

    return [
        'p',                 // type indicator
        \time(),             // unix timestamp
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
        $event_name,           // event name
        $event_param,          // event parameter
        $unique_event,         // is unique?
        $value,                // event value,
        \time(),               // unix timestamp
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
    if (\function_exists('is_request_excluded') && is_request_excluded()) {
        return;
    }

    // if WP environment is loaded and URL says to disable custom endpoint, do it
    if (isset($_GET['disable-custom-endpoint']) && \function_exists('update_option')) {
        update_option('koko_analytics_use_custom_endpoint', false, true);
    }

    switch ($_GET['m'] ?? 'none') {
        case 'cookie': {
            if (isset($_COOKIE['_koko_analytics_pages_viewed']) && \strlen($_COOKIE['_koko_analytics_pages_viewed']) === 32) {
                $visitor_id = $_COOKIE['_koko_analytics_pages_viewed'];
            } else {
                $visitor_id = bin2hex(random_bytes(16));
                \setcookie('_koko_analytics_pages_viewed', $visitor_id, \time() + 6 * 3600, '/');
            }
        }
        break;

        case 'fingerprint':
            $seed_value = file_get_contents(get_upload_dir() . '/sessions/.daily_seed');
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $ip_address = get_request_ip_address();
            $visitor_id = \hash("xxh64", "{$seed_value}-{$user_agent}-{$ip_address}", false);
            break;

        default:
            // in case of not using any tracking method, simply use a random ID
            $visitor_id = bin2hex(random_bytes(16));
            break;
    }

    $page_id = (int) $_GET['p'];
    [$new_visitor, $unique_pageview] = detect_uniqueness($visitor_id, $page_id);

    $data = isset($_GET['e']) ? extract_event_data($_GET) : extract_pageview_data($_GET, $new_visitor, $unique_pageview);
    if (!empty($data)) {
        // store data in buffer file
        $success = collect_in_file($data);

        // update session file
        $session_file = get_upload_dir() . "/sessions/{$visitor_id}";
        file_put_contents($session_file, "$page_id" . PHP_EOL, FILE_APPEND);

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
    if (isset($_GET['p'], $_GET['nv'], $_GET['sc']) && (int) $_GET['sc'] === 1) {
        $posts_viewed = isset($_COOKIE['_koko_analytics_pages_viewed']) ? \explode(',', $_COOKIE['_koko_analytics_pages_viewed']) : [''];
        if ((int) $_GET['nv']) {
            $posts_viewed[] = (int) $_GET['p'];
        }
        $cookie = \join(',', $posts_viewed);
        \setcookie('_koko_analytics_pages_viewed', $cookie, \time() + 6 * 3600, '/');
    }

    exit;
}

function get_upload_dir(): string
{
    if (\defined('KOKO_ANALYTICS_UPLOAD_DIR')) {
        return KOKO_ANALYTICS_UPLOAD_DIR;
    }

    // For backwards compatibility with optimize endpoints installed before the above constant was defined
    if (\defined('KOKO_ANALYTICS_BUFFER_FILE')) {
        return \dirname(KOKO_ANALYTICS_BUFFER_FILE) . '/koko-analytics';
    }

    $uploads = wp_upload_dir(null, false);
    return \rtrim($uploads['basedir'], '/') . '/koko-analytics';
}


function get_buffer_filename(): string
{
    $upload_dir = get_upload_dir();

    // return first file in directory that matches these conditions
    if (\is_dir($upload_dir)) {
        $filenames = \scandir($upload_dir);
        if (\is_array($filenames)) {
            foreach ($filenames as $filename) {
                if (\str_starts_with($filename, "buffer-") && ! \str_ends_with($filename, ".busy")) {
                    return "{$upload_dir}/{$filename}";
                }
            }
        }
    }

    // if no such file exists, generate a new random filename
    $filename = "buffer-" . \bin2hex(\random_bytes(16)) . ".csv";
    return "{$upload_dir}/{$filename}";
}

function collect_in_file(array $data): bool
{
    $filename = get_buffer_filename();
    $directory = \dirname($filename);
    if (! \is_dir($directory)) {
        \mkdir($directory, 0755, true);
    }

    // append serialized data to file
    // TODO: Write CSV data here, but ideally we want to run the aggregator just once using the old data format after each plugin update
    $content = \serialize($data);
    $content .= PHP_EOL;

    return (bool) \file_put_contents($filename, $content, FILE_APPEND);
}

function detect_uniqueness(string $visitor_id, int $page_id): array
{
    $session_file = get_upload_dir() . "/sessions/{$visitor_id}";
    if (! \is_file($session_file)) {
        return [true, true];
    }

    $pages_viewed = \file($session_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    return [false, ! \in_array($page_id, $pages_viewed)];
}

function get_request_ip_address()
{
    if (isset($_SERVER['X-Forwarded-For'])) {
        $ip_address = $_SERVER['X-Forwarded-For'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip_address = $_SERVER['REMOTE_ADDR'];
    }

    if (isset($ip_address)) {
        if (! is_array($ip_address)) {
            $ip_address = explode(',', $ip_address);
        }

        // use first IP in list
        $ip_address = trim($ip_address[0]);

        // if IP address is not valid, simply return null
        if (! filter_var($ip_address, FILTER_VALIDATE_IP)) {
            return null;
        }

        return $ip_address;
    }

    return null;
}

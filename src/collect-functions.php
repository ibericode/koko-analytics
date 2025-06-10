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

use DateTimeImmutable;

function maybe_collect_request(): void
{
    if (($_GET['action'] ?? '') !== 'koko_analytics_collect') {
        return;
    }

    collect_request();
}

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
        $new_visitor ? 1 : 0,
        $unique_pageview ? 1 : 0,
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

    $page_id = (int) $_GET['p'];

    switch ($_GET['m'] ?? 'n') {
        case 'c':
            [$new_visitor, $unique_pageview] = determine_uniqueness_cookie($page_id);
            break;

        case 'f':
            [$new_visitor, $unique_pageview] = determine_uniqueness_fingerprint($page_id);
            break;

        default:
            // not using any tracking method
            [$new_visitor, $unique_pageview] = [false, false];
            break;
    }

    $data = isset($_GET['e']) ? extract_event_data($_GET) : extract_pageview_data($_GET, $new_visitor, $unique_pageview);
    if (!empty($data)) {
        // store data in buffer file
        $success = collect_in_file($data);

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

function get_site_timezone(): \DateTimeZone
{
    if (defined('KOKO_ANALYTICS_TIMEZONE')) {
        return new \DateTimeZone(KOKO_ANALYTICS_TIMEZONE);
    }

    if (function_exists('wp_timezone')) {
        return wp_timezone();
    }

    return new \DateTimeZone('UTC');
}

/**
 * Return's client IP for current request, even if behind a reverse proxy
 */
function get_client_ip(): string
{
    $ips = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';

    // X-Forwarded-For sometimes contains a comma-separated list of IP addresses
    // @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
    $ips = \array_map('trim', \explode(',', $ips));

    // Always add REMOTE_ADDR to list of ips
    $ips[] = $_SERVER['REMOTE_ADDR'] ?? '';

    // return first valid IP address from list
    foreach ($ips as $ip) {
        if (\filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return '';
}


function determine_uniqueness_cookie(int $page_id): array
{
    $pages_viewed = isset($_COOKIE['_koko_analytics_pages_viewed']) ? explode(',', $_COOKIE['_koko_analytics_pages_viewed']) : [];
    $new_visitor = ! isset($_COOKIE['_koko_analytics_pages_viewed']);
    $unique_pageview = !in_array($page_id, $pages_viewed);

    if ($new_visitor || $unique_pageview) {
        $pages_viewed[] = $page_id;
        \setcookie('_koko_analytics_pages_viewed', \join(',', $pages_viewed), (new DateTimeImmutable('tomorrow, midnight', get_site_timezone()))->getTimestamp(), '/', "", false, true);
    }

    return [$new_visitor, $unique_pageview];
}

function determine_uniqueness_fingerprint(int $page_id): array
{
    $seed_value = file_get_contents(get_upload_dir() . '/sessions/.daily_seed');
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $ip_address = get_client_ip();
    $visitor_id = \hash("xxh64", "{$seed_value}-{$user_agent}-{$ip_address}", false);

    $session_file = get_upload_dir() . "/sessions/{$visitor_id}";
    if (! \is_file($session_file)) {
        file_put_contents($session_file, "{$page_id}" . PHP_EOL, FILE_APPEND);
        return [true, true];
    }

    $pages_viewed = \file($session_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $unique_pageview = ! \in_array($page_id, $pages_viewed);

    if ($unique_pageview) {
        file_put_contents($session_file, "{$page_id}" . PHP_EOL, FILE_APPEND);
    }

    return [false, $unique_pageview];
}

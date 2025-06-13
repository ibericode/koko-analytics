<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 *
 * This file contains the code required for data ingestion.
 * It is meant to be included from the optimized endpoint file
 * and should therefore not assume the WordPress environment is available.
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

function extract_pageview_data(array $raw): array
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

    [$new_visitor, $unique_pageview] = determine_uniqueness('pageview', $post_id);

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
    if (!isset($raw['e'], $raw['p'], $raw['v'])) {
        return [];
    }

    $event_name = \trim($raw['e']);
    $event_param = \trim($raw['p']);
    if (\strlen($event_name) === 0) {
        return [];
    }

    $value = \filter_var($raw['v'], FILTER_VALIDATE_INT);
    if ($value === false) {
        return [];
    }

    // limit event name and parameter lengths
    $event_name = \substr($event_name, 0, 100);
    $event_param = \substr($event_param, 0, 185);

    $event_hash = \hash("xxh64", "{$event_name}-{$event_param}");
    [$unused, $unique_event] = determine_uniqueness('', $event_hash);

    return [
        'e',                   // type indicator
        $event_name,           // event name
        $event_param,          // event parameter
        $unique_event ? 1 : 0, // is unique?
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

    $data = isset($_POST['e']) ? extract_event_data($_POST) : extract_pageview_data($_POST);
    if (!empty($data)) {
        // store data in buffer file
        $success = isset($_POST['test']) ? test_collect_in_file() : collect_in_file($data);

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

function test_collect_in_file(): bool
{
    $filename = get_buffer_filename();
    if (\is_file($filename)) {
        return \is_writable($filename);
    }

    $directory = \dirname($filename);
    if (! \is_dir($directory)) {
        \mkdir($directory, 0755, true);
    }

    return \is_writable($directory);
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

/**
 * Determines the uniqueness of $thing today
 *
 * @param int|string $thing
 * @return array [bool, bool]
 */
function determine_uniqueness(string $type, $thing): array
{
    // determine uniqueness based on specified tracking method
    switch ($_POST['m'] ?? 'n') {
        case 'c':
            return determine_uniqueness_cookie($type, $thing);
            break;

        case 'f':
            return determine_uniqueness_fingerprint($type, $thing);
            break;
    }

    // not using any tracking method
    return  [true, true];
}

function determine_uniqueness_cookie(string $type, $thing): array
{
    $things = isset($_COOKIE['_koko_analytics_pages_viewed']) ? \explode('-', $_COOKIE['_koko_analytics_pages_viewed']) : [];
    $unique_type = $type && !in_array($type[0], $things);
    $unique_thing =  $unique_type ? true : !in_array($thing, $things);

    if ($unique_type) {
        $things[] = $type[0];
    }

    if ($unique_type || $unique_thing) {
        $things[] = $thing;
        \setcookie('_koko_analytics_pages_viewed', \join('-', $things), (new DateTimeImmutable('tomorrow, midnight', get_site_timezone()))->getTimestamp(), '/', "", false, true);
    }

    return [$unique_type, $unique_thing];
}

function determine_uniqueness_fingerprint(string $type, $thing): array
{
    $seed_value = \file_get_contents(get_upload_dir() . '/sessions/.daily_seed');
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $ip_address = get_client_ip();
    $visitor_id = \hash("xxh64", "{$seed_value}-{$user_agent}-{$ip_address}", false);
    $session_file = get_upload_dir() . "/sessions/{$visitor_id}";
    $time_midnight = (new \DateTimeImmutable('today, midnight', get_site_timezone()))->getTimestamp();
    $things = [];

    // only read file if it exists and is not from before today
    // this is to protect against a cronjob that didn't run on time
    if (\is_file($session_file)) {
        if (filemtime($session_file) < $time_midnight) {
            unlink($session_file);
        } else {
            $things = \file($session_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        }
    }

    // check if type indicator is in session file
    $unique_type = $type && ! \in_array($type[0], $things);

    // check if page id or event hash is in session file
    $unique_thing = $unique_type ? true : ! \in_array($thing, $things);

    if ($unique_type || $unique_thing) {
        \file_put_contents($session_file, $unique_type ? "{$type[0]}\n{$thing}\n" : "{$thing}\n", FILE_APPEND);
    }

    return [$unique_type, $unique_thing];
}

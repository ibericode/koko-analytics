<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use WP_Admin_Bar;
use WP_Query;

function maybe_collect_request()
{
    // since we call this function (early) on every AJAX request, detect our specific request here
    // this allows us to short-circuit a bunch of unrelated AJAX stuff and gain a lot of performance
    if (!isset($_GET['action']) || $_GET['action'] !== 'koko_analytics_collect' || !defined('DOING_AJAX') || !DOING_AJAX) {
        return;
    }

    collect_request();
}

function extract_pageview_data(): array
{
    // do nothing if a required parameter is missing
    if (
        !isset($_GET['p'])
        || !isset($_GET['nv'])
        || !isset($_GET['up'])
    ) {
        return array();
    }

    // do nothing if parameters are not of the correct type
    if (
        false === filter_var($_GET['p'], FILTER_VALIDATE_INT)
        || false === filter_var($_GET['nv'], FILTER_VALIDATE_INT)
        || false === filter_var($_GET['up'], FILTER_VALIDATE_INT)
    ) {
        return array();
    }

    return array(
        'p',                // type indicator
        $_GET['p'],   // 0: post ID
        $_GET['nv'],  // 1: is new visitor?
        $_GET['up'],  // 2: is unique pageview?
        isset($_GET['r']) ? trim($_GET['r']) : '',   // 3: referrer URL
    );
}

function extract_event_data(): array
{
    if (!isset($_GET['e']) || !isset($_GET['p']) || !isset($_GET['u']) || !isset($_GET['v'])) {
        return array();
    }

    if (false === filter_var($_GET['u'], FILTER_VALIDATE_INT) || false === filter_var($_GET['v'], FILTER_VALIDATE_INT)) {
        return array();
    }

    return array(
        'e',                  // type indicator
        trim($_GET['e']),     // 0: event name
        trim($_GET['p']),     // 1: event parameter
        (int) $_GET['u'],     // 2: is unique?
        (int) $_GET['v'],     // 3: event value
    );
}

function collect_request()
{
    // ignore requests from bots, crawlers and link previews
    if (empty($_SERVER['HTTP_USER_AGENT']) || preg_match("/bot|crawl|spider|seo|lighthouse|facebookexternalhit|preview/i", $_SERVER['HTTP_USER_AGENT'])) {
        return;
    }

    if (isset($_GET['e'])) {
        $data = extract_event_data();
    } else {
        $data = extract_pageview_data();
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
        \setcookie('_koko_analytics_pages_viewed', $cookie, time() + 6 * 3600, '/');
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

function get_settings(): array
{
    $default_settings = array(
        'use_cookie' => 1,
        'exclude_user_roles' => array(),
        'exclude_ip_addresses' => array(),
        'prune_data_after_months' => 5 * 12, // 5 years
        'default_view' => 'last_28_days',
        'is_dashboard_public' => 0,
    );
    $settings         = (array) get_option('koko_analytics_settings', array());
    $settings         = array_merge($default_settings, $settings);
    return apply_filters('koko_analytics_settings', $settings);
}

function get_most_viewed_posts(array $args = array()): array
{
    global $wpdb;
    $cache_key = md5(serialize($args));
    $post_ids = wp_cache_get($cache_key, 'koko-analytics');

    if (!$post_ids) {
        $args = array_merge(array(
            'number'    => 5,
            'post_type' => 'post',
            'days'    => 30,
        ), $args);

        $args['days']      = abs((int) $args['days']);
        $args['post_type'] = is_array($args['post_type']) ? $args['post_type'] : explode(',', $args['post_type']);
        $args['post_type'] = array_map('trim', $args['post_type']);

        $start_date_str = $args['days'] === 0 ? 'today midnight' : "-{$args['days']} days";
        $start_date = create_local_datetime($start_date_str)->format('Y-m-d');
        $end_date = create_local_datetime('tomorrow midnight')->format('Y-m-d');

        // build query
        $sql_params             = array(
            get_option('page_on_front', 0),
            $start_date,
            $end_date,
        );
        $post_types_placeholder = join(', ', array_fill(0, count($args['post_type']), '%s'));
        $sql_params             = array_merge($sql_params, $args['post_type']);
        $sql_params[]           = $args['number'];
        $sql                    = $wpdb->prepare("SELECT p.id, SUM(pageviews) AS pageviews FROM {$wpdb->prefix}koko_analytics_post_stats s JOIN {$wpdb->posts} p ON s.id = p.id WHERE p.id NOT IN (0, %d) AND s.date >= %s AND s.date <= %s AND p.post_type IN ($post_types_placeholder) AND p.post_status = 'publish' GROUP BY p.id ORDER BY pageviews DESC LIMIT 0, %d", $sql_params);
        $results                = $wpdb->get_results($sql);
        if (empty($results)) {
            return array();
        }

        $post_ids = array_map(function ($r) {
            return $r->id;
        }, $results);
        wp_cache_set($cache_key, $post_ids, 'koko-analytics', 3600);
    }

    $r        = new WP_Query(
        array(
            'posts_per_page'      => -1,
            'post__in'            => $post_ids,
            // indicates that we want to use the order of our $post_ids array
            'orderby'             => 'post__in',

            // By default, WP_Query only returns "post" types
            // Without this argument, this function would not return any page types
            'post_type' => 'any',
            // Prevent sticky post from always being included
            'ignore_sticky_posts' => true,
            // Excludes SQL_CALC_FOUND_ROWS from the query (tiny performance gain)
            'no_found_rows'       => true,
        )
    );
    return $r->posts;
}

function admin_bar_menu(WP_Admin_Bar $wp_admin_bar)
{
    // only show on frontend
    if (is_admin()) {
        return;
    }

    // only show for users who can access statistics page
    if (!current_user_can('view_koko_analytics')) {
        return;
    }

    $wp_admin_bar->add_node(
        array(
            'parent' => 'site-name',
            'id' => 'koko-analytics',
            'title' => esc_html__('Analytics', 'koko-analytics'),
            'href' => admin_url('/index.php?page=koko-analytics'),
        )
    );
}

function widgets_init()
{
    require KOKO_ANALYTICS_PLUGIN_DIR . '/src/class-widget-most-viewed-posts.php';
    register_widget('KokoAnalytics\Widget_Most_Viewed_Posts');
}

/**
 * @param int|string $since Either an integer timestamp (in seconds since Unix epoch) or a relative time string that strtotime understands.
 * @return int
 */
function get_realtime_pageview_count($since = '-5 minutes'): int
{
    if (is_numeric($since)) {
        $since = (int) $since;
    } else {
        // $since is relative time string
        $since = strtotime($since);
    }
    $counts = (array) get_option('koko_analytics_realtime_pageview_count', array());
    $sum    = 0;
    foreach ($counts as $timestamp => $pageviews) {
        if ($timestamp > $since) {
            $sum += (int) $pageviews;
        }
    }
    return $sum;
}

function using_custom_endpoint(): bool
{
    if (defined('KOKO_ANALYTICS_CUSTOM_ENDPOINT')) {
        return (bool) KOKO_ANALYTICS_CUSTOM_ENDPOINT;
    }

    return (bool) get_option('koko_analytics_use_custom_endpoint', false);
}

function fmt_large_number($number): string
{
    if ($number < 10000.0) {
        return $number;
    }

    $number /= 1000.0;
    if ($number > 100.0) {
        $number = round($number);
    }
    return rtrim(rtrim(number_format($number, 1), '0'), '.')  . 'K';
}

function test_custom_endpoint(): void
{
    $endpoint_installer = new Endpoint_Installer();
    $endpoint_installer->verify();
}

function create_local_datetime($timestr): \DateTimeImmutable
{
    $offset = (float) get_option('gmt_offset', 0.0);
    if ($offset >= 0.00) {
        $offset = "+$offset";
    }

    $now_local = (new \DateTimeImmutable('now'));
    if ($offset > 0.00 || $offset < 0.00) {
        $now_local = $now_local->modify($offset . ' hours');
    }

    return $now_local->modify($timestr);
}

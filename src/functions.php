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

function test_custom_endpoint(): void
{
    $endpoint_installer = new Endpoint_Installer();
    $endpoint_installer->verify();
}

function create_local_datetime($timestr): ?\DateTimeImmutable
{
    $offset = (float) get_option('gmt_offset', 0.0);
    if ($offset >= 0.00) {
        $offset = "+$offset";
    }

    $now_local = (new \DateTimeImmutable('now'));
    if ($offset > 0.00 || $offset < 0.00) {
        $now_local = $now_local->modify($offset . ' hours');
    }

    $dt_local = $now_local->modify($timestr);
    if (! $dt_local) {
        return null;
    }

    return $dt_local;
}

/**
 *  @param int|WP_Post $post
 */
function get_page_title($post): string
{
    $post = get_post($post);
    if (!$post) {
        return '(deleted post)';
    }

    $title = $post->post_title;

    // if post has no title, use path + query part from permalink
    if ($title === '') {
        $permalink = get_permalink($post);
        $url_parts = parse_url($permalink);
        $title = $url_parts['path'];

        if ($url_parts['query'] !== '') {
            $title .= '?';
            $title .= $url_parts['query'];
        }
    }

    return $title;
}

function get_referrer_url_href(string $url): string
{
    if (strpos($url, '://t.co/') !== false) {
        return 'https://twitter.com/search?q=' . urlencode($url);
    } elseif (strpos($url, 'android-app://') === 0) {
        return str_replace('android-app://', 'https://play.google.com/store/apps/details?id=', $url);
    }

    return apply_filters('koko_analytics_referrer_url_href', $url);
}

function get_referrer_url_label(string $url): string
{
    // if link starts with android-app://, turn that prefix into something more human readable
    if (strpos($url, 'android-app://') === 0) {
        return str_replace('android-app://', 'Android app: ', $url);
    }

    // strip protocol and www. prefix
    $url = (string) preg_replace('/^https?:\/\/(?:www\.)?/', '', $url);

    // trim trailing slash
    $url = rtrim($url, '/');

    return apply_filters('koko_analytics_referrer_url_label', $url);
}

/**
 * Return's client IP for current request, even if behind a reverse proxy
 */
function get_client_ip(): string
{
    $ips = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';

    // X-Forwarded-For sometimes contains a comma-separated list of IP addresses
    // @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
    if (! is_array($ips)) {
        $ips = \array_map('trim', \explode(',', $ips));
    }

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

function percent_format_i18n($pct)
{
    if ($pct == 0) {
        return '';
    }

    $prefix = $pct > 0 ? '+' : '';
    $formatted = \number_format_i18n($pct * 100, 0);
    return $prefix . $formatted . '%';
}

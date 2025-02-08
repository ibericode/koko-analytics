<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use WP_Admin_Bar;
use WP_Query;
use WP_Post;

function get_settings(): array
{
    $default_settings = [
        'use_cookie' => 1,
        'exclude_user_roles' => [],
        'exclude_ip_addresses' => [],
        'prune_data_after_months' => 10 * 12, // 10 years
        'default_view' => 'last_28_days',
        'is_dashboard_public' => 0,
    ];
    $settings         = (array) get_option('koko_analytics_settings', []);
    $settings         = array_merge($default_settings, $settings);
    return apply_filters('koko_analytics_settings', $settings);
}

function get_most_viewed_post_ids(array $args)
{
    global $wpdb;
    $cache_key = md5(serialize($args));
    $post_ids = wp_cache_get($cache_key, 'koko-analytics');

    if (!$post_ids) {
        $args = array_merge([
            'number'    => 5,
            'post_type' => 'post',
            'days'    => 30,
            'paged' => 0,
        ], $args);

        $args['paged'] = abs((int) $args['paged']);
        $args['number'] = abs((int) $args['number']);
        $args['days']      = abs((int) $args['days']);
        $args['post_type'] = is_array($args['post_type']) ? $args['post_type'] : explode(',', $args['post_type']);
        $args['post_type'] = array_map('trim', $args['post_type']);

        $start_date_str = $args['days'] === 0 ? 'today midnight' : "-{$args['days']} days";
        $start_date = create_local_datetime($start_date_str)->format('Y-m-d');
        $end_date = create_local_datetime('tomorrow midnight')->format('Y-m-d');

        // build query
        $sql_params             = [
            get_option('page_on_front', 0),
            $start_date,
            $end_date,
        ];
        $post_types_placeholder = join(', ', array_fill(0, count($args['post_type']), '%s'));
        $sql_params             = array_merge($sql_params, $args['post_type']);
        $sql_params[] = $args['number'] * $args['paged'];
        $sql_params[] = $args['number'];
        $sql = $wpdb->prepare("SELECT p.id, SUM(pageviews) AS pageviews FROM {$wpdb->prefix}koko_analytics_post_stats s JOIN {$wpdb->posts} p ON s.id = p.id WHERE s.id NOT IN (0, %d) AND s.date >= %s AND s.date <= %s AND p.post_type IN ($post_types_placeholder) AND p.post_status = 'publish' GROUP BY p.id ORDER BY pageviews DESC LIMIT %d, %d", $sql_params);
        $results                = $wpdb->get_results($sql);
        if (empty($results)) {
            return [];
        }

        $post_ids = array_map(function ($r) {
            return $r->id;
        }, $results);
        wp_cache_set($cache_key, $post_ids, 'koko-analytics', 3600);
    }

    return $post_ids;
}

/**
 * $args['number'] int Number of posts
 * $args['day'] int Number of days
 * @args['post_type'] string|array List of post types to include
 * @args['paged'] int Number of current page *
 */
function get_most_viewed_posts($args = []): array
{
    $post_ids = get_most_viewed_post_ids($args);
    $query_args = [
        'posts_per_page' => -1,
        'post__in' => $post_ids,
        // indicates that we want to use the order of our $post_ids array
        'orderby' => 'post__in',

        // By default, WP_Query only returns "post" types
        // Without this argument, this function would not return any page types
        'post_type' => 'any',
        // Prevent sticky post from always being included
        'ignore_sticky_posts' => true,
        // Excludes SQL_CALC_FOUND_ROWS from the query (tiny performance gain)
        'no_found_rows'       => true,
    ];
    $r = new WP_Query($query_args);
    return $r->posts;
}

/**
 * @param int|string|null $since Either an integer timestamp (in seconds since Unix epoch) or a relative time string that strtotime understands.
 * @return int
 */
function get_realtime_pageview_count($since = null): int
{
    if (is_numeric($since) || is_int($since)) {
        $since = (int) $since;
    } elseif (is_string($since)) {
        // $since is relative time string
        $since = strtotime($since);
    } else {
        $since = strtotime('-5 minutes');
    }

    $counts = (array) get_option('koko_analytics_realtime_pageview_count', []);
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

function create_local_datetime(string $timestr): \DateTimeImmutable
{
    return new \DateTimeImmutable($timestr, wp_timezone());
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

        if (!empty($url_parts['query'])) {
            $title .= '?';
            $title .= $url_parts['query'];
        }
    }

    return $title;
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

function is_request_excluded(): bool
{
    $settings = get_settings();

    // check if exclude by logged-in user role
    if (count($settings['exclude_user_roles']) > 0) {
        $user = wp_get_current_user();

        if ($user instanceof \WP_User && $user->exists() && user_has_roles($user, $settings['exclude_user_roles'])) {
            return true;
        }
    }

    // check if excluded by IP address
    if (count($settings['exclude_ip_addresses']) > 0) {
        $ip_address = get_client_ip();
        if ($ip_address !== '' && in_array($ip_address, $settings['exclude_ip_addresses'], true)) {
            return true;
        }
    }

    return false;
}

function user_has_roles(\WP_User $user, array $roles): bool
{
    foreach ($user->roles as $user_role) {
        if (in_array($user_role, $roles, true)) {
            return true;
        }
    }

    return false;
}

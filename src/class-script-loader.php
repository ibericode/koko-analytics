<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use WP_User;

class Script_Loader
{
    /**
     * @param bool $echo Whether to use the default WP script enqueue method or print the script tag directly
     */
    public static function maybe_enqueue_script(bool $echo = false): void
    {
        $load_script = apply_filters('koko_analytics_load_tracking_script', true);
        if (! $load_script) {
            return;
        }

        if (is_request_excluded()) {
            return;
        }

        add_filter('script_loader_tag', [ Script_Loader::class , 'add_async_attribute' ], 20, 2);

        if (false === $echo) {
            // Print configuration object early on in the HTML so scripts can modify it
            if (did_action('wp_head')) {
                self::print_js_object();
            } else {
                add_action('wp_head', [ Script_Loader::class , 'print_js_object' ], 1, 0);
            }

            // Enqueue the actual tracking script (in footer, if possible)
            wp_enqueue_script('koko-analytics', plugins_url('assets/dist/js/script.js', KOKO_ANALYTICS_PLUGIN_FILE), [], KOKO_ANALYTICS_VERSION, true);
        } else {
            self::print_js_object();
            echo '<script defer src="', plugins_url('assets/dist/js/script.js?ver=' . KOKO_ANALYTICS_VERSION, KOKO_ANALYTICS_PLUGIN_FILE), '"></script>';
        }
    }

    /**
     * Returns the internal ID of the page or post that is being shown.
     * If page is not a singular object, the function returns 0 if it is the front page (from Settings) or -1 if something else (eg category archive).
     *
     * @return int
     */
    private static function get_post_id(): string
    {
        if (is_singular()) {
            return (string) get_queried_object_id();
        }

        if (is_front_page()) {
            return "0";
        }

        return "-1";
    }

    public static function get_canonical_path(): string
    {
        global $wp;

        $base_path = parse_url(home_url('/'), PHP_URL_PATH);
        $request_path = $_SERVER['REQUEST_URI'];

        if (str_starts_with($request_path, $base_path)) {
            $request_path = substr($request_path, strlen($base_path));
        }

        $question_mark_pos = strpos($request_path, '?');
        if ($question_mark_pos !== false) {
            $query_string = parse_url($request_path, PHP_URL_QUERY);
            $params = [];
            parse_str($query_string, $params);
            $new_params     = array_intersect_key($params, array_flip($wp->public_query_vars));
            $new_query_str  = http_build_query($new_params);
            $request_path        = rtrim(substr($request_path, 0, $question_mark_pos + 1) . $new_query_str, '?');
        }

        return '/' . ltrim($request_path, '/');
    }

    private static function get_tracker_url(): string
    {
        if (\defined('KOKO_ANALYTICS_CUSTOM_ENDPOINT') && KOKO_ANALYTICS_CUSTOM_ENDPOINT) {
            return site_url(KOKO_ANALYTICS_CUSTOM_ENDPOINT);
        }

        // We should use site_url() here because we place the file in ABSPATH and other plugins may be filtering home_url (eg multilingual plugin)
        // In any case: what we use here should match what we test when creating the optimized endpoint file.
        return using_custom_endpoint() ? site_url('/koko-analytics-collect.php') : admin_url('admin-ajax.php?action=koko_analytics_collect');
    }

    private static function get_cookie_path(): string
    {
        $home_url = home_url();
        return \parse_url($home_url, PHP_URL_PATH) ?? '/';
    }

    public static function print_js_object()
    {
        global $wp;
        $settings      = get_settings();
        $script_config = [
            // the URL of the tracking endpoint
            'url'   => self::get_tracker_url(),
            'site_url' => get_home_url(),

            'path' => self::get_canonical_path(),

            // ID of the current post (if singular post type), path name otherwise
            'post_id'       => (string) self::get_post_id(),

            // wether to set a cookie
            'use_cookie'    => (int) $settings['use_cookie'],

            // path to store the cookie in (will be subdirectory if website root is in subdirectory)
            'cookie_path' => self::get_cookie_path(),
        ];
        echo '<script>window.koko_analytics = ', json_encode($script_config), ';</script>';
    }

    public static function print_amp_analytics_tag()
    {
        $settings     = get_settings();
        $post_id      = self::get_post_id();
        $tracker_url  = self::get_tracker_url();
        $posts_viewed = isset($_COOKIE['_koko_analytics_pages_viewed']) ? explode(',', $_COOKIE['_koko_analytics_pages_viewed']) : [];
        $data         = [
            'sc' => $settings['use_cookie'], // inform tracker endpoint to set cookie server-side
            'nv' => $posts_viewed === [] ? 1 : 0,
            'up' => ! in_array($post_id, $posts_viewed) ? 1 : 0,
            'p' => $post_id,
        ];
        $url          = add_query_arg($data, $tracker_url);
        $config       = [
            'requests' => [
                'pageview' => $url,
            ],
            'triggers' => [
                'trackPageview' => [
                    'on' => 'visible',
                    'request' => 'pageview',
                ],
            ],
        ];

        echo '<amp-analytics><script type="application/json">', json_encode($config), '</script></amp-analytics>';
    }

    /**
     * @param string $tag
     * @param string $handle
     */
    public static function add_async_attribute($tag, $handle)
    {
        if ($handle !== 'koko-analytics' || strpos($tag, ' defer') !== false) {
            return $tag;
        }

        return str_replace(' src=', ' defer src=', $tag);
    }
}

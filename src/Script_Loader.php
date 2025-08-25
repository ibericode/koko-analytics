<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use KokoAnalytics\Normalizers\Normalizer;
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

        // TODO: Handle "term" requests so we track both terms and post types.
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
     *
     * If page is not a singular object, the function returns 0 if it is the front page (from Settings)
     */
    private static function get_post_id(): int
    {
        if (is_singular()) {
            return get_queried_object_id();
        }

        return 0;
    }

    private static function get_tracker_url(): string
    {
        global $wp;

        // People can create their own endpoint and define it through this constant
        if (\defined('KOKO_ANALYTICS_CUSTOM_ENDPOINT') && KOKO_ANALYTICS_CUSTOM_ENDPOINT) {
            // custom custom endpoint
            return site_url(KOKO_ANALYTICS_CUSTOM_ENDPOINT);
        } elseif (using_custom_endpoint()) {
            // default custom endpoint
            return site_url('/koko-analytics-collect.php');
        }

        // default URL (which includes WordPress)
        return admin_url('admin-ajax.php?action=koko_analytics_collect');
    }

    public static function get_request_path(): string
    {
        $path = trim($_SERVER["REQUEST_URI"] ?? '');
        return Normalizer::path($path);
    }

    public static function print_js_object()
    {
        $settings      = get_settings();
        $script_config = [
        // the URL of the tracking endpoint
        'url'   => self::get_tracker_url(),

        // root URL of site
        'site_url' => get_home_url(),

        'post_id' => self::get_post_id(),
        'path' => self::get_request_path(),

        // tracking method to use (passed to endpoint)
        'method' => $settings['tracking_method'],

        // for backwards compatibility with older versions
        // some users set this value from other client-side scripts, ie cookie consent banners
        // if true, takes priority of the method property defined above
        'use_cookie' => $settings['tracking_method'] === 'cookie',
        ];
        $data = 'window.koko_analytics = ' . \json_encode($script_config) . ';';
        wp_print_inline_script_tag($data);
    }

    public static function print_amp_analytics_tag()
    {
        $settings     = get_settings();
        $data         = [
            'm' => $settings['tracking_method'][0],
            'po' => self::get_post_id(),
            'pa' => self::get_request_path(),
        ];
        $url          = add_query_arg($data, self::get_tracker_url());
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

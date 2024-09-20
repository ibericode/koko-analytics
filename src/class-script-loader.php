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
    public function __construct()
    {
        add_action('wp_enqueue_scripts', array($this, 'maybe_enqueue_script'), 10, 0);
        add_action('amp_print_analytics', array($this, 'print_amp_analytics_tag'), 10, 0);
    }

    /**
     * Return's client IP for current request, even if behind a reverse proxy
     */
    private function get_client_ip(): string
    {
        $ips = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';

        // X-Forwarded-For sometimes contains a comma-separated list of IP addresses
        // @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/X-Forwarded-For
        if (! is_array($ips)) {
            $ips = explode(',', $ips);
            $ips = array_map('trim', $ips);
        }

        // Always add REMOTE_ADDR to list of ips
        $ips[] = $_SERVER['REMOTE_ADDR'] ?? '';

        // return first valid IP address from list
        foreach ($ips as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return '';
    }

    /**
     * @param bool $echo Whether to use the default WP script enqueue method or print the script tag directly
     */
    public function maybe_enqueue_script(bool $echo = false)
    {
        $load_script = apply_filters('koko_analytics_load_tracking_script', true);
        if (false === $load_script) {
            return;
        }

        // Do not load script if excluding current user by role
        $settings = get_settings();
        if (count($settings['exclude_user_roles']) > 0) {
            $user = wp_get_current_user();

            if ($user->exists() && $this->user_has_roles($user, $settings['exclude_user_roles'])) {
                return;
            }
        }

        // Do not load script if excluded by IP address
        if (count($settings['exclude_ip_addresses']) > 0) {
            $ip_address = $this->get_client_ip();
            if ($ip_address !== '' && in_array($ip_address, $settings['exclude_ip_addresses'], true)) {
                return;
            }
        }

        // TODO: Handle "term" requests so we track both terms and post types.
        add_filter('script_loader_tag', array( $this, 'add_async_attribute' ), 20, 2);

        if (false === $echo) {
            // Print configuration object early on in the HTML so scripts can modify it
            if (did_action('wp_head')) {
                $this->print_js_object();
            } else {
                add_action('wp_head', array( $this, 'print_js_object' ), 1);
            }

            // Enqueue the actual tracking script (in footer, if possible)
            wp_enqueue_script('koko-analytics', plugins_url('assets/dist/js/script.js', KOKO_ANALYTICS_PLUGIN_FILE), array(), KOKO_ANALYTICS_VERSION, true);
        } else {
            $this->print_js_object();
            echo '<script defer src="', plugins_url('assets/dist/js/script.js?ver=' . KOKO_ANALYTICS_VERSION, KOKO_ANALYTICS_PLUGIN_FILE), '"></script>';
        }
    }

    /**
     * Returns the internal ID of the page or post that is being shown.
     * If page is not a singular object, the function returns 1 if it is the front page (from Settings) or -1 if something else (eg category archive).
     *
     * @return int
     */
    private function get_post_id(): int
    {
        if (is_singular()) {
            return get_queried_object_id();
        }

        if (is_front_page()) {
            return 0;
        }

        return -1;
    }

    private function get_tracker_url(): string
    {
        if (defined('KOKO_ANALYTICS_CUSTOM_ENDPOINT') && KOKO_ANALYTICS_CUSTOM_ENDPOINT) {
            return site_url(KOKO_ANALYTICS_CUSTOM_ENDPOINT);
        }

        // We should use site_url() here because we place the file in ABSPATH and other plugins may be filtering home_url (eg multilingual plugin)
        // In any case: what we use here should match what we test when creating the optimized endpoint file.
        return using_custom_endpoint() ? site_url('/koko-analytics-collect.php') : admin_url('admin-ajax.php?action=koko_analytics_collect');
    }

    private function get_cookie_path(): string
    {
        $home_url = home_url();
        return parse_url($home_url, PHP_URL_PATH) ?? '/';
    }

    public function print_js_object()
    {
        $settings      = get_settings();
        $script_config = array(
            // the URL of the tracking endpoint
            'url'   => $this->get_tracker_url(),
            'site_url' => get_home_url(),

            // ID of the current post (or -1 in case of non-singular type)
            'post_id'       => (int) $this->get_post_id(),

            // wether to set a cookie
            'use_cookie'    => (int) $settings['use_cookie'],

            // path to store the cookie in (will be subdirectory if website root is in subdirectory)
            'cookie_path' => $this->get_cookie_path(),
        );
        echo '<script>window.koko_analytics = ', json_encode($script_config), ';</script>';
    }

    public function print_amp_analytics_tag()
    {
        $settings     = get_settings();
        $post_id      = $this->get_post_id();
        $tracker_url  = $this->get_tracker_url();
        $posts_viewed = isset($_COOKIE['_koko_analytics_pages_viewed']) ? explode(',', $_COOKIE['_koko_analytics_pages_viewed']) : array();
        $data         = array(
            'sc' => $settings['use_cookie'], // inform tracker endpoint to set cookie server-side
            'nv' => $posts_viewed === array() ? 1 : 0,
            'up' => ! in_array($post_id, $posts_viewed) ? 1 : 0,
            'p' => $post_id,
        );
        $url          = add_query_arg($data, $tracker_url);
        $config       = array(
            'requests' => array(
                'pageview' => $url,
            ),
            'triggers' => array(
                'trackPageview' => array(
                    'on' => 'visible',
                    'request' => 'pageview',
                ),
            ),
        );

        echo '<amp-analytics><script type="application/json">', json_encode($config), '</script></amp-analytics>';
    }

    /**
     * @param string $tag
     * @param string $handle
     */
    public function add_async_attribute($tag, $handle)
    {
        if ($handle !== 'koko-analytics' || stripos($tag, 'defer') !== false) {
            return $tag;
        }

        return str_replace(' src=', ' defer src=', $tag);
    }

    public function user_has_roles(WP_User $user, array $roles): bool
    {
        foreach ($user->roles as $user_role) {
            if (in_array($user_role, $roles, true)) {
                return true;
            }
        }

        return false;
    }
}

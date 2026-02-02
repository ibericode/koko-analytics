<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use KokoAnalytics\Normalizers\Normalizer;

class Script_Loader
{
    // WARNING: is used in Koko Analytics Pro (in its static form)
    public static function get_request_path(): string
    {
        return Normalizer::path(trim($_SERVER["REQUEST_URI"] ?? ''));
    }

    public function hook(): void
    {
        add_action('wp_head', [$this, 'print_js_object'], 1, 0);
        add_action('wp_footer', [$this, 'maybe_print_script'], 10, 0);
        add_action('amp_print_analytics', [$this, 'print_amp_analytics_tag'], 10, 0);
    }

    public function maybe_print_script()
    {
        $load_script = apply_filters('koko_analytics_load_tracking_script', true);
        if (! $load_script) {
            return;
        }

        if (is_request_excluded() || is_preview()) {
            return;
        }

        echo PHP_EOL . '<!-- Koko Analytics v' . KOKO_ANALYTICS_VERSION . ' - https://www.kokoanalytics.com/ -->' . PHP_EOL;
        wp_print_inline_script_tag(file_get_contents(KOKO_ANALYTICS_PLUGIN_DIR . '/assets/dist/js/script.js'));
        echo PHP_EOL;
    }

    /**
     * Returns the internal ID of the page or post that is being shown.
     *
     * If page is not a singular object, the function returns 0 if it is the front page (from Settings)
     */
    private function get_post_id(): int
    {
        if (is_singular()) {
            return get_queried_object_id();
        }

        return 0;
    }

    private function get_tracker_url(): string
    {
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

    public function print_js_object()
    {
        $settings      = get_settings();
        $script_config = [
            // the URL of the tracking endpoint
            'url'   => $this->get_tracker_url(),

            // root URL of site
            'site_url' => get_home_url(),

            'post_id' => $this->get_post_id(),
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

    public function print_amp_analytics_tag()
    {
        $settings     = get_settings();
        $data         = [
            'm' => $settings['tracking_method'][0],
            'po' => $this->get_post_id(),
            'pa' => self::get_request_path(),
        ];
        $url          = add_query_arg($data, $this->get_tracker_url());
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
}

<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Endpoint_Installer
{
    public function run(): bool
    {
        $successfully_installed = $this->create_and_test();
        update_option('koko_analytics_use_custom_endpoint', $successfully_installed, true);
        return $successfully_installed;
    }

    public function verify(): bool
    {
        $test = $this->test();
        update_option('koko_analytics_use_custom_endpoint', $test, true);
        return $test;
    }

    public function get_file_name(): string
    {
        return rtrim(ABSPATH, '/') . '/koko-analytics-collect.php';
    }

    public function get_file_contents(): string
    {
        $buffer_filename    = get_buffer_filename();
        $functions_filename = KOKO_ANALYTICS_PLUGIN_DIR . '/src/functions.php';
        return <<<EOT
<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 *
 * This file acts as an optimized endpoint file for the Koko Analytics plugin.
 */

// path to pageviews.php file in uploads directory
define('KOKO_ANALYTICS_BUFFER_FILE', '$buffer_filename');

// path to functions.php file in Koko Analytics plugin directory
require '$functions_filename';

// function call to collect the request data
KokoAnalytics\collect_request();
EOT;
    }

    private function test(): bool
    {
        $tracker_url = site_url('/koko-analytics-collect.php?nv=1&p=0&up=1&test=1');
        $response    = wp_remote_get($tracker_url);
        if (is_wp_error($response)) {
            return false;
        }

        $status  = wp_remote_retrieve_response_code($response);
        $headers = wp_remote_retrieve_headers($response);
        if ($status !== 200 || $headers['Content-Type'] !== 'text/plain') {
            return false;
        }

        return true;
    }

    private function create_and_test(): bool
    {
        /* Do nothing if running Multisite (because Multisite has separate uploads directory per site) */
        if (is_multisite()) {
            return false;
        }

        /* Do nothing if KOKO_ANALYTICS_CUSTOM_ENDPOINT is defined (means users disabled this feature or is using their own version of it) */
        if (defined('KOKO_ANALYTICS_CUSTOM_ENDPOINT')) {
            return false;
        }

        /* If we made it this far we ideally want to use the custom endpoint file */
        /* Therefore we schedule a recurring health check event to periodically re-attempt and re-test */
        if (! wp_next_scheduled('koko_analytics_test_custom_endpoint')) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', 'koko_analytics_test_custom_endpoint');
        }

        /* Check if path to buffer file changed */
        $file_name = $this->get_file_name();
        if (file_exists($file_name)) {
            $content = file_get_contents($file_name);
            if (strpos($content, get_buffer_filename()) === false) {
                @unlink(ABSPATH . '/koko-analytics-collect.php');
            }
        }

        /* Attempt to put the file into place if it does not exist already */
        if (! file_exists($file_name)) {
            $success = @file_put_contents($file_name, $this->get_file_contents());
            if (! $success) {
                return false;
            }
        }

        /* Send an HTTP request to the custom endpoint to see if it's working properly */
        $works = $this->test();
        if (! $works) {
            /* Remove the file */
            @unlink($file_name);
            return false;
        }

        /* All looks good! Custom endpoint file exists and returns the correct response */
        return true;
    }
}

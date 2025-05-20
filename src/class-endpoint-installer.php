<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Endpoint_Installer
{
    public function get_file_name(): string
    {
        return rtrim(ABSPATH, '/') . '/koko-analytics-collect.php';
    }

    public function get_file_contents(): string
    {
        $upload_dir = get_upload_dir();

        // make path relative to ABSPATH again
        if (str_starts_with($upload_dir, ABSPATH)) {
            $upload_dir = ltrim(substr($upload_dir, strlen(ABSPATH)), '/');
        }

        $functions_filename = KOKO_ANALYTICS_PLUGIN_DIR . '/src/collect-functions.php';

        // make path relative to ABSPATH again
        if (str_starts_with($functions_filename, ABSPATH)) {
            $functions_filename = ltrim(substr($functions_filename, strlen(ABSPATH)), '/');
        }

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
define('KOKO_ANALYTICS_UPLOAD_DIR', '$upload_dir');

// path to functions.php file in Koko Analytics plugin directory
require '$functions_filename';

// function call to collect the request data
KokoAnalytics\collect_request();

EOT;
    }

    /**
     * @return string|bool
     */
    public static function verify()
    {
        $verification_result = self::verify_internal();
        update_option('koko_analytics_use_custom_endpoint', $verification_result === true, true);
        return $verification_result;
    }

    /**
     * @return string|bool
     */
    private static function verify_internal()
    {
        $tracker_url = site_url('/koko-analytics-collect.php?nv=1&p=0&up=1&test=1');
        $response    = wp_remote_get($tracker_url, [
            'timeout' => 10,
        ]);
        if (is_wp_error($response)) {
            return __('Error requesting endpoint: ', 'koko-analytics') . join(', ', $response->get_error_messages());
        }

        $status  = wp_remote_retrieve_response_code($response);
        $headers = wp_remote_retrieve_headers($response);

        // verify whether we get an expected response
        if ($status == 200
            && isset($headers['Content-Type'])
            && str_contains($headers['Content-Type'], 'text/plain')
        ) {
            return true;
        }

        error_log(sprintf("Koko Analaytics: Error verifying optimized endpoint because it did not return the expected HTTP response.\nHTTP code: %s\nHTTP headers: %s\nHTTP body: %s", $status, var_export($headers, true), wp_remote_retrieve_body($response)));

        return __('Endpoint did not return the expected response.', 'koko-analytics');
    }

    /**
     * @return string|bool
     */
    public function install()
    {
        /* If we made it this far we ideally want to use the custom endpoint file */
        /* Therefore we schedule a recurring health check event to periodically re-attempt and re-test */
        if (! wp_next_scheduled('koko_analytics_test_custom_endpoint')) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'hourly', 'koko_analytics_test_custom_endpoint');
        }

        $file_name = $this->get_file_name();

        /* Attempt to put the file into place if it does not exist already */
        if (! is_file($file_name)) {
            $created = file_put_contents($file_name, $this->get_file_contents());
            if (! $created) {
                return __('Error creating file', 'koko-analytics');
            }
        }

        /* Send an HTTP request to the custom endpoint to see if it's working properly */
        $verification_result = self::verify();
        if ($verification_result !== true && isset($created) && $created) {
            unlink($file_name);
        }

        /* All looks good! Custom endpoint file exists and returns the correct response */
        return $verification_result;
    }

    public function is_eligibile(): bool
    {
        /* Do nothing if running Multisite (because Multisite has separate uploads directory per site) */
        if (is_multisite()) {
            return false;
        }

        /* Do nothing if KOKO_ANALYTICS_CUSTOM_ENDPOINT is defined (means users disabled this feature or is using their own version of it) */
        if (defined('KOKO_ANALYTICS_CUSTOM_ENDPOINT')) {
            return false;
        }

        return true;
    }
}

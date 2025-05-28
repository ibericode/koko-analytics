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
    public function install()
    {
        $file_name = $this->get_file_name();

        /* Attempt to put the file into place if it does not exist already */
        if (! is_file($file_name)) {
            $created = file_put_contents($file_name, $this->get_file_contents());
            if (! $created) {
                return __('Error creating file', 'koko-analytics');
            }
        }

        // File was successfully created, so let's start using it from now on
        update_option('koko_analytics_use_custom_endpoint', true, true);
        return true;
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

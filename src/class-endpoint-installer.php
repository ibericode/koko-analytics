<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Endpoint_Installer
{
    public static function get_file_name(): string
    {
        return rtrim(ABSPATH, '/') . '/koko-analytics-collect.php';
    }

    public static function get_file_contents(): string
    {
        $settings = get_settings();
        $upload_dir = get_upload_dir();

        // make path relative to ABSPATH again
        if (str_starts_with($upload_dir, ABSPATH)) {
            $upload_dir = ltrim(substr($upload_dir, strlen(ABSPATH)), '/');
        }
        $wp_timezone_string = wp_timezone_string();
        $functions_filename = KOKO_ANALYTICS_PLUGIN_DIR . '/src/collect-functions.php';
        $excluded_ip_addresses_string = var_export($settings['exclude_ip_addresses'], true);

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
define('KOKO_ANALYTICS_TIMEZONE', '$wp_timezone_string');

// path to functions.php file in Koko Analytics plugin directory
require '$functions_filename';

// check if IP address is on list of addresses to ignore
if (!isset(\$_GET['test']) && in_array(KokoAnalytics\get_client_ip(), $excluded_ip_addresses_string)) {
    exit;
}

// function call to collect the request data
KokoAnalytics\collect_request();

EOT;
    }

    /**
     * @return string|bool
     */
    public static function install()
    {
        // do nothing if site is not eligible for the use of a custom endpoint (ie multisite)
        if (!self::is_eligibile()) {
            return;
        }

        $file_name = self::get_file_name();

        // attempt to overwrite file with latest contents to ensure it's up-to-date
        file_put_contents($file_name, self::get_file_contents());

        // Check if file exists
        // Note that we're not checking whether we were able to write to the file
        // To allow for users manually creating the file with the correct contents
        $exists = is_file($file_name);
        update_option('koko_analytics_use_custom_endpoint', $exists, true);

        if (! $exists) {
            return __('Error creating file', 'koko-analytics');
        }

        return true;
    }

    public static function uninstall(): void
    {
        $file_name = self::get_file_name();
        if (is_file($file_name)) {
            unlink($file_name);
        }
    }

    public static function is_eligibile(): bool
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

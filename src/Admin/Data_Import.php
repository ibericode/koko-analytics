<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

use Exception;

class Data_Import
{
    public function action_listener(): void
    {
        if (!current_user_can('manage_koko_analytics') || ! check_admin_referer('koko_analytics_import_data')) {
            return;
        }

        $settings_page = admin_url('options-general.php?page=koko-analytics-settings&tab=data');

        if (empty($_FILES['import-file']) || $_FILES['import-file']['error'] !== UPLOAD_ERR_OK) {
            wp_safe_redirect(add_query_arg(['error' => urlencode(__('Something went wrong trying to process your import file.', 'koko-analytics'))], $settings_page));
            exit;
        }

        // don't accept MySQL blobs over 16 MB
        if ($_FILES['import-file']['size'] > 16000000) {
            wp_safe_redirect(add_query_arg(['error' => urlencode(__('Sorry, your import file is too large. Please import it into your database in some other way.', 'koko-analytics'))], $settings_page));
            exit;
        }

        // try to increase time limit
        @set_time_limit(300);

        // read SQL from upload file
        $sql = file_get_contents($_FILES['import-file']['tmp_name']);

        // verify file looks like a Koko Analytics export file
        if (!preg_match('/^(--|DELETE|SELECT|INSERT|TRUNCATE|CREATE|DROP)/', $sql)) {
            wp_safe_redirect(add_query_arg(['error' => urlencode(__('Sorry, the uploaded import file does not look like a Koko Analytics export file', 'koko-analytics'))], $settings_page));
            exit;
        }

        // good to go, let's run the SQL
        try {
            $this->run($sql);
        } catch (\Exception $e) {
            wp_safe_redirect(add_query_arg(['error' => urlencode(__('Something went wrong trying to process your import file.', 'koko-analytics') . "\n" . $e->getMessage())], $settings_page));
            exit;
        }

        // unlink tmp file
        unlink($_FILES['import-file']['tmp_name']);

        // redirect with success message
        wp_safe_redirect(add_query_arg(['message' => urlencode(__('Database was successfully imported from the given file', 'koko-analytics'))], $settings_page));
        exit;
    }

    protected function run(string $sql): void
    {
        if ($sql === '') {
            return;
        }

        /** @var \wpdb $wpdb */
        global $wpdb;
        $statements = explode(';', $sql);
        foreach ($statements as $statement) {
            // skip over empty statements
            $statement = trim($statement);
            if (!$statement) {
                continue;
            }

            // verify statement acts on the options table OR a koko analytics table
            if (! preg_match("/{$wpdb->options}|{$wpdb->prefix}koko_analytics/", $statement)) {
                continue;
            }

            $result = $wpdb->query($statement);
            if ($result === false) {
                throw new Exception($wpdb->last_error);
            }
        }
    }
}

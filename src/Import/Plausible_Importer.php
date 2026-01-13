<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Import;

use Exception;

class Plausible_Importer extends Importer
{
    protected static function get_admin_url(): string
    {
        return admin_url('options-general.php?page=koko-analytics-settings&tab=plausible_importer');
    }

    public static function start_import(): void
    {
        // authorize user & verify nonce
        if (!current_user_can('manage_koko_analytics') || !check_admin_referer('koko_analytics_start_plausible_import')) {
            return;
        }


        // verify file upload
        if (empty($_FILES['plausible-export-file']) || $_FILES['plausible-export-file']['error'] !== 0) {
            static::redirect_with_error(static::get_admin_url(), 'A file upload error occurred.');
        }

        $date_start = $_POST['date-start'] ?? '2010-01-01';
        $date_end = $_POST['date-end'] ?? date('Y-m-d');
        $fh = fopen($_FILES['plausible-export-file']['tmp_name'], "r");
        $header = fgetcsv($fh, 1024, ',', '"', '');
        @set_time_limit(300);

        try {
            if (count($header) >= 3 && $header[0] == 'date' && $header[1] == 'visitors' && $header[2] == 'pageviews') {
                self::import_site_stats($fh, $header, $date_start, $date_end);
            } elseif (count($header) >= 4 && $header[0] == 'date' && $header[1] == 'hostname' && $header[2] == 'page' && $header[4] == 'visitors' && $header[5] == 'pageviews') {
                self::import_page_stats($fh, $header, $date_start, $date_end);
            } elseif (count($header) >= 3 && $header[0] == 'date' && $header[1] == 'source' && $header[2] == 'referrer') {
                self::import_referrer_stats($fh, $header, $date_start, $date_end);
            } else {
                throw new Exception("Sorry, that file is not supported.");
            }
        } catch (Exception $e) {
            static::redirect_with_error(static::get_admin_url(), $e->getMessage());
        }

        fclose($fh);

        static::redirect(static::get_admin_url(), ['success' => 1]);
    }

    private static function import_site_stats($fh, array $headers, string $date_start, string $date_end): void
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        while ($row = fgetcsv($fh, 1024, ',', '"', '')) {
            $row = array_combine($headers, $row);

            // skip rows outside of date range
            if ($row['date'] < $date_start || $row['date'] > $date_end) {
                continue;
            }

            // update site stats
            $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_site_stats(date, visitors, pageviews) VALUES (%s, %d, %d) ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", [$row['date'], $row['visitors'], $row['pageviews']]);
            $wpdb->query($query);
            if ($wpdb->last_error !== '') {
                throw new Exception(__("A database error occurred: ", 'koko-analytics') . " {$wpdb->last_error}");
            }
        }
    }

    private static function import_page_stats($fh, array $headers, string $date_start, string $date_end): void
    {
        // "date","hostname","page","visits","visitors","pageviews","total_scroll_depth","total_scroll_depth_visits","total_time_on_page","total_time_on_page_visits"

        $rows = [];
        while ($row = fgetcsv($fh, 1024, ',', '"', '')) {
            $row = array_combine($headers, $row);

            // skip rows outside of date range
            if ($row['date'] < $date_start || $row['date'] > $date_end) {
                continue;
            }

            // add to rows
            $rows[] = [$row['date'], $row['page'], 0, $row['visitors'], $row['pageviews']];

            if (count($rows) >= 100) {
                static::bulk_insert_page_stats($rows);
                $rows = [];
            }
        }

        static::bulk_insert_page_stats($rows);
    }

    private static function import_referrer_stats($fh, array $headers, string $date_start, string $date_end): void
    {
        // "date","source","referrer","utm_source","utm_medium","utm_campaign","utm_content","utm_term","pageviews","visitors","visits","visit_duration","bounces"

        $rows = [];
        while ($row = fgetcsv($fh, 1024, ',', '"', '')) {
            $row = array_combine($headers, $row);

            // skip rows outside of date range
            if ($row['date'] < $date_start || $row['date'] > $date_end || empty($row['referrer'])) {
                continue;
            }

            // add to rows
            $rows[] = [$row['date'], $row['referrer'], $row['visitors'], $row['pageviews']];

            if (count($rows) >= 100) {
                static::bulk_insert_referrer_stats($rows);
                $rows = [];
            }
        }

        static::bulk_insert_referrer_stats($rows);
    }
}

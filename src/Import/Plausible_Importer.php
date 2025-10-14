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
    protected static function show_page_content()
    {
        ?>
        <h1><?php esc_html_e('Import from Plausible', 'koko-analytics'); ?></h1>
        <form method="post" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to import statistics between', 'koko-analytics'); ?> ' + this['date-start'].value + '<?php esc_attr_e(' and ', 'koko-analytics'); ?>' + this['date-end'].value + '<?php esc_attr_e('? This will add to any existing data in your Koko Analytics database tables.', 'koko-analytics'); ?>');" action="<?php echo esc_url(admin_url('index.php?page=koko-analytics&tab=plausible_importer')); ?>" enctype="multipart/form-data">

            <input type="hidden" name="koko_analytics_action" value="start_plausible_import">
            <?php wp_nonce_field('koko_analytics_start_plausible_import'); ?>

            <table class="form-table">
                <tr>
                    <th><label for="plausible-export-file"><?php esc_html_e('Plausible CSV export', 'koko-analytics'); ?></label></th>
                    <td>
                        <input id="plausible-export-file" type="file" class="form-control" name="plausible-export-file" accept=".csv" required>
                         <p class="description"><?php esc_html_e('Accepted files are "imported_visitors.csv", "imported_pages.csv" and "imported_sources.csv" from the Plausible export ZIP.', 'koko-analytics'); ?></p>
                    </td>
                </tr>

                <tr>
                    <th><label for="date-start"><?php esc_html_e('Start date', 'koko-analytics'); ?></label></th>
                    <td>
                        <input id="date-start" name="date-start" type="date" value="<?php echo esc_attr(date('Y-m-d', strtotime('-1 year'))); ?>" required>
                         <p class="description"><?php esc_html_e('The earliest date for which to import data.', 'koko-analytics'); ?></p>

                    </td>
                </tr>

                <tr>
                    <th><label for="date-end"><?php esc_html_e('End date', 'koko-analytics'); ?></label></th>
                    <td>
                        <input id="date-end" name="date-end" type="date" value="<?php echo esc_attr(date('Y-m-d')); ?>" required>
                        <p class="description"><?php esc_html_e('The last date for which to import data.', 'koko-analytics'); ?></p>

                    </td>
                </tr>
            </table>

            <p style="color: indianred;">
                <strong><?php esc_html_e('Warning: ', 'koko-analytics'); ?></strong>
                <?php esc_html_e('Importing data for a given date range will add to any existing data. The import process can not be reverted unless you reinstate a back-up of your database in its current state.', 'koko-analytics'); ?>
            </p>

            <p>
                <button type="submit" class="button"><?php esc_html_e('Import analytics data', 'koko-analytics'); ?></button>
            </p>
        </form>
        <?php
    }

    public static function start_import(): void
    {
        // authorize user
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        // verify nonce
        check_admin_referer('koko_analytics_start_plausible_import');

        // verify file upload
        if (empty($_FILES['plausible-export-file']) || $_FILES['plausible-export-file']['error'] !== 0) {
            static::redirect_with_error(admin_url('index.php?page=koko-analytics&tab=plausible_importer'), 'A file upload error occurred.');
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
            static::redirect_with_error(admin_url('index.php?page=koko-analytics&tab=plausible_importer'), $e->getMessage());
        }

        fclose($fh);

        // redirect with success parameter
        wp_safe_redirect(get_admin_url(null, '/index.php?page=koko-analytics&tab=plausible_importer&success=1'));
        exit;
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

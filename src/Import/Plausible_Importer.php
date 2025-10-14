<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Import;

use WP_Error;
use Exception;
use DateTimeImmutable;
use KokoAnalytics\Path_Repository;
use ZipArchive;

class Plausible_Importer
{
    public static function show_page(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        ?>
        <div class="wrap" style="max-width: 820px;">

        <?php if (isset($_GET['error'])) { ?>
                <div class="notice notice-error is-dismissible">
                    <p>
                        <?php esc_html_e('An error occurred trying to import your statistics.', 'koko-analytics'); ?>
                        <?php echo ' '; ?>
                        <?php echo wp_kses(stripslashes(trim($_GET['error'])), [ 'br' => []]); ?>
                    </p>
                </div>
        <?php } ?>

        <?php if (isset($_GET['success']) && $_GET['success'] == 1) { ?>
                <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Big success! Your stats are now imported into Koko Analytics.', 'koko-analytics'); ?></p></div>
        <?php } ?>

            <h1><?php esc_html_e('Import from Plausible', 'koko-analytics'); ?></h1>

            <form method="post" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to import statistics between', 'koko-analytics'); ?> ' + this['date-start'].value + '<?php esc_attr_e(' and ', 'koko-analytics'); ?>' + this['date-end'].value + '<?php esc_attr_e('? This will overwrite any existing data in your Koko Analytics database tables.', 'koko-analytics'); ?>');" action="<?php echo esc_url(admin_url('index.php?page=koko-analytics&tab=plausible_importer')); ?>" enctype="multipart/form-data">

                <input type="hidden" name="koko_analytics_action" value="start_plausible_import">
                <?php wp_nonce_field('koko_analytics_start_plausible_import'); ?>

                <table class="form-table">
                    <tr>
                        <th><label for="plausible-export-file"><?php esc_html_e('Plausible CSV export', 'koko-analytics'); ?></label></th>
                        <td>
                            <input id="plausible-export-file" type="file" class="form-control" name="plausible-export-file" accept=".csv" required>
                             <p class="description"><?php esc_html_e('Upload the "imported_visitors" and "imported_pages" CSV files (one by one). The other import files are not supported at this point.', 'koko-analytics'); ?></p>
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
                            <p class="description"><?php esc_html_e('The last date for which to import data. You should probably set this to just before the date that you installed and activated Koko Analytics.', 'koko-analytics'); ?></p>

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
        </div>
        <?php
    }

    private static function redirect_with_error(string $redirect_url, string $error_message): void
    {
        $redirect_url = add_query_arg([ 'error' => urlencode($error_message)], $redirect_url);
        wp_safe_redirect($redirect_url);
        exit;
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
            self::redirect_with_error(admin_url('index.php?page=koko-analytics&tab=plausible_importer'), 'A file upload error occurred.');
            return;
        }

        $date_start = $_POST['date-start'] ?? '2010-01-01';
        $date_end = $_POST['date-end'] ?? date('Y-m-d');
        $fh = fopen($_FILES['plausible-export-file']['tmp_name'], "r");
        $header = fgetcsv($fh);
        @set_time_limit(300);

        try {
            if (count($header) >= 3 && $header[0] == 'date' && $header[1] == 'visitors' && $header[2] == 'pageviews') {
                self::import_site_stats($fh, $date_start, $date_end);
            } elseif (count($header) >= 4 && $header[0] == 'date' && $header[1] == 'hostname' && $header[2] == 'page' && $header[4] == 'visitors' && $header[5] == 'pageviews') {
                self::import_page_stats($fh, $date_start, $date_end);
            }
        } catch (Exception $e) {
            self::redirect_with_error(admin_url('index.php?page=koko-analytics&tab=plausible_importer'), $e->getMessage());
            exit;
        }

        fclose($fh);

        // redirect with success parameter
        wp_safe_redirect(get_admin_url(null, '/index.php?page=koko-analytics&tab=plausible_importer&success=1'));
        exit;
    }

    private static function import_site_stats($fh, string $date_start, string $date_end): void
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        while ($row = fgetcsv($fh)) {
            [$date, $visitors, $pageviews] = $row;

            // skip rows outside of date range
            if ($date < $date_start || $date > $date_end) {
                continue;
            }

            // update site stats
            $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_site_stats(date, visitors, pageviews) VALUES (%s, %d, %d) ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", [$date, $visitors, $pageviews]);
            $wpdb->query($query);
            if ($wpdb->last_error !== '') {
                throw new Exception(__("A database error occurred: ", 'koko-analytics') . " {$wpdb->last_error}");
            }
        }
    }

    private static function import_page_stats($fh, string $date_start, string $date_end): void
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $rows = [];
        while ($row = fgetcsv($fh)) {
            [$date, $hostname, $page, $visits, $visitors, $pageviews] = $row;

            // skip rows outside of date range
            if ($date < $date_start || $date > $date_end) {
                continue;
            }

            $rows[] = [$date, $page, $visitors, $pageviews];

            if (count($rows) >= 100) {
                self::bulk_insert_rows($rows);
                $rows = [];
            }
        }

        self::bulk_insert_rows($rows);
    }

    private static function bulk_insert_rows(array $rows): void
    {
        /** @var wpdb $wpdb */
        global $wpdb;

        $paths = array_map(function ($r) {
            return $r[1];
        }, $rows);

        $path_ids = Path_Repository::upsert($paths);
        $values = [];
        foreach ($rows as $r) {
            array_push($values, $r[0], $path_ids[$r[1]], $r[2], $r[3]);
        }
        $placeholders = rtrim(str_repeat('(%s,%d,0,%d,%d),', count($rows)), ',');


        $query = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_post_stats(date, path_id, post_id, visitors, pageviews) VALUES {$placeholders} ON DUPLICATE KEY UPDATE visitors = visitors + VALUES(visitors), pageviews = pageviews + VALUES(pageviews)", $values);
        $wpdb->query($query);

        if ($wpdb->last_error !== '') {
            throw new Exception(__("A database error occurred: ", 'koko-analytics') . " {$wpdb->last_error}");
        }
    }
}

<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

use KokoAnalytics\Endpoint_Installer;
use KokoAnalytics\Data_Exporter;
use KokoAnalytics\Data_Importer;
use KokoAnalytics\Fingerprinter;
use KokoAnalytics\Normalizers\Normalizer;

use function KokoAnalytics\get_settings;

class Actions
{
    public static function install_optimized_endpoint(): void
    {
        wp_safe_redirect(add_query_arg([ 'endpoint-installed' => Endpoint_Installer::install() ], wp_get_referer()));
        exit;
    }

    public static function reset_statistics(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        check_admin_referer('koko_analytics_reset_statistics');

        /** @var \wpdb $wpdb */
        global $wpdb;
        $wpdb->query("TRUNCATE {$wpdb->prefix}koko_analytics_site_stats;");
        $wpdb->query("TRUNCATE {$wpdb->prefix}koko_analytics_post_stats;");
        $wpdb->query("TRUNCATE {$wpdb->prefix}koko_analytics_referrer_stats;");
        $wpdb->query("TRUNCATE {$wpdb->prefix}koko_analytics_referrer_urls;");
        delete_option('koko_analytics_realtime_pageview_count');
    }

    public static function export_data(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        check_admin_referer('koko_analytics_export_data');

        (new Data_Exporter())->run();
    }

    public static function import_data(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        check_admin_referer('koko_analytics_import_data');
        $settings_page = admin_url('/index.php?page=koko-analytics&tab=settings');

        if (empty($_FILES['import-file']) || $_FILES['import-file']['error'] !== UPLOAD_ERR_OK) {
            wp_safe_redirect(add_query_arg(['import-error' => $_FILES['import-file']['error']], $settings_page));
            exit;
        }

        // don't accept MySQL blobs over 16 MB
        if ($_FILES['import-file']['size'] > 16000000) {
            wp_safe_redirect(add_query_arg(['import-error' => UPLOAD_ERR_INI_SIZE], $settings_page));
            exit;
        }

        // read SQL from upload file
        $sql = file_get_contents($_FILES['import-file']['tmp_name']);
        if ($sql === '') {
            wp_safe_redirect(add_query_arg(['import-error' => UPLOAD_ERR_NO_FILE], $settings_page));
            exit;
        }

        // verify file looks like a Koko Analytics export file
        if (!str_starts_with($sql, 'INSERT INTO ') && !str_starts_with($sql, 'TRUNCATE ')) {
            wp_safe_redirect(add_query_arg(['import-error' => UPLOAD_ERR_EXTENSION], $settings_page));
            exit;
        }

        // good to go, let's run the SQL
        (new Data_Importer())->run($sql);

        // unlink tmp file
        unlink($_FILES['import-file']['tmp_name']);
        wp_safe_redirect(add_query_arg(['import-success' => 1], $settings_page));
        exit;
    }

    public static function save_settings(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        check_admin_referer('koko_analytics_save_settings');

        $posted                        = $_POST['koko_analytics_settings'];
        $settings                            = get_settings();

        // get rid of deprecated setting keys
        unset($settings['use_cookie']);

        $settings['exclude_ip_addresses']    = array_filter(array_map('trim', explode(PHP_EOL, str_replace(',', PHP_EOL, strip_tags($posted['exclude_ip_addresses'])))), function ($value) {
            return $value !== '';
        });
        $settings['exclude_user_roles']      = $posted['exclude_user_roles'] ?? [];
        $settings['prune_data_after_months'] = abs((int) $posted['prune_data_after_months']);
        $settings['is_dashboard_public']     = (int) $posted['is_dashboard_public'];
        $settings['default_view']            = trim($posted['default_view']);
        $settings['tracking_method'] = in_array($posted['tracking_method'], ['cookie', 'fingerprint', 'none']) ? $posted['tracking_method'] : 'cookie';

        $settings = apply_filters('koko_analytics_sanitize_settings', $settings, $posted);
        update_option('koko_analytics_settings', $settings, true);

        // maybe create sessions directory & initial seed file
        if ($settings['tracking_method'] === 'fingerprint') {
            Fingerprinter::create_storage_dir();
            Fingerprinter::setup_scheduled_event();
        }

        // Re-create optimized endpoint to ensure its contents are up-to-date
        Endpoint_Installer::install();

        wp_safe_redirect(add_query_arg(['settings-updated' => true], wp_get_referer()));
        exit;
    }

    public static function migrate_post_stats_to_v2(): void
    {
        @set_time_limit(0);

        /** @var wpdb $wpdb */
        global $wpdb;

        $offset = 0;
        $limit = 1000;

        do {
            // Select all rows with a post ID but no path ID
            $results = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT(post_id) FROM {$wpdb->prefix}koko_analytics_post_stats WHERE post_id IS NOT NULL AND path_id IS NULL LIMIT %d OFFSET %d", [$limit, $offset]));
            $offset += $limit;

            // process rows one by one
            // this is slower, but the migration will continue and eventually finish over multiple requests
            foreach ($results as $row) {
                $post_id = $row->post_id;
                $post_permalink = $post_id === "0" ? home_url('/') : get_permalink($post_id);
                if (!$post_permalink) {
                    continue;
                }

                $url_parts = parse_url($post_permalink);
                $path = $url_parts['path'];
                if (!empty($url_parts['query'])) {
                    $path .= '?' . $url_parts['query'];
                }

                // Entry points to nowhere, skip it... (ie deleted post)
                if (!$path) {
                    continue;
                }

                // normalize path
                $path = Normalizer::path($path);

                // insert path
                // NOTE: We can't upsert here because we need a unique path_id for every date, post_id combination
                $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_paths (path) VALUES (%s)", [$path]));
                $path_id = $wpdb->insert_id;

                // update post_stats entry
                $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_post_stats SET path_id = %d WHERE post_id = %d", [ $path_id, $post_id ]));
            }
        } while ($results);

        // now we can remove all rows without a path id
        $wpdb->query("DELETE FROM {$wpdb->prefix}koko_analytics_post_stats WHERE path_id IS NULL");

        // make new path_id column not-nullable
        $wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats MODIFY COLUMN path_id MEDIUMINT UNSIGNED NOT NULL");

        // change primary key to be on date and path_id column
        $wpdb->query("ALTER TABLE {$wpdb->prefix}koko_analytics_post_stats DROP PRIMARY KEY, ADD PRIMARY KEY(date, path_id)");
    }

    public static function migrate_referrer_stats_to_v2(): void
    {
        @set_time_limit(0);

        /** @var wpdb $wpdb */
        global $wpdb;

        // some of the UPDATE queries below can fail, we don't want to exit when that happens
        $wpdb->hide_errors();
        $offset = 0;
        $limit = 1000;
        do {
            $results = $wpdb->get_results($wpdb->prepare("SELECT id, url FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE url LIKE 'http://%' OR url LIKE 'https://%' LIMIT %d OFFSET %d"), [$limit, $offset]);
            foreach ($results as $row) {
                $row->url = Normalizer::referrer($row->url);

                // skip seriously malformed url's
                if ($row->url === '') {
                    continue;
                }

                // check if normalized url already has an entry
                $id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE url = %s", [$row->url]));
                if ($id) {
                    // grab all rows in stats table pointing to old ID
                    $stats = $wpdb->get_results($wpdb->prepare("SELECT date, id, pageviews, visitors FROM {$wpdb->prefix}koko_analytics_referrer_stats WHERE id = %d", [$row->id]));

                    // update rows (if exist) with values from each date, id entry
                    foreach ($stats as $s) {
                        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_referrer_stats SET visitors = visitors + %d, pageviews = pageviews + %d WHERE date = %s AND id = %d", [$s->visitors, $s->pageviews, $s->date, $id]));
                    }

                    // try to update all rows to new id (this will fail for some rows)
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_referrer_stats SET id = %d WHERE id = %d", [ $id, $row->id ]));

                    // delete rows that still have old ID at this point
                    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}koko_analytics_referrer_stats WHERE id = %d", [ $row->id ]));
                } else {
                    // otherwise change entry to normalized version
                    $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}koko_analytics_referrer_urls SET url = %s WHERE id = %s LIMIT 1", [ $row->url, $row->id ]));
                }
            }

            $offset += $limit;
        } while ($results);

        update_option('koko_analytics_referrers_v2', true, true);
    }
}

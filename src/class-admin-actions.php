<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Admin_Actions
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
}

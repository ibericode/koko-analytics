<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

use KokoAnalytics\Cron;
use KokoAnalytics\Endpoint_Installer;
use KokoAnalytics\Fingerprinter;
use KokoAnalytics\Import\Jetpack_Importer;
use KokoAnalytics\Import\Plausible_Importer;
use KokoAnalytics\Post_Stats_Migrator;

use function KokoAnalytics\get_settings;
use function KokoAnalytics\lazy;

class Actions
{
    public function run()
    {
        if (isset($_GET['koko_analytics_action'])) {
            $action = trim($_GET['koko_analytics_action']);
        } elseif (isset($_POST['koko_analytics_action'])) {
            $action = trim($_POST['koko_analytics_action']);
        } else {
            return;
        }

        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        // TODO: Allow plugins to hook into this to register their own actions
        $map = [
            'install_optimized_endpoint' => [$this, 'install_optimized_endpoint'],
            'save_settings' => [$this, 'save_settings'],
            'save_component_order' => [$this, 'save_component_order'],
            'migrate_post_stats_to_v2' => [$this, 'migrate_post_stats_to_v2'],
            'fix_post_paths_after_v2' => [$this, 'fix_post_paths_after_v2'],
            'reset_statistics' => lazy(Data_Reset::class, 'action_listener'),
            'import_data' => lazy(Data_Import::class, 'action_listener'),
            'export_data' => lazy(Data_Export::class, 'action_listener'),
            'start_jetpack_import' => lazy(Jetpack_Importer::class, 'start_import'),
            'jetpack_import_chunk' => lazy(Jetpack_Importer::class, 'import_chunk'),
            'start_plausible_import' => lazy(Plausible_Importer::class, 'start_import'),
        ];

        // for BC reasons, still fire the action hook
        // it is important we fire it before running the registered callback
        // because that way we can initiate a redirect from our own callback
        do_action("koko_analytics_{$action}");

        if (isset($map[$action])) {
            call_user_func($map[$action]);
        }

        wp_safe_redirect(remove_query_arg('koko_analytics_action'));
        exit;
    }

    public function install_optimized_endpoint()
    {
        check_admin_referer('koko_analytics_install_optimized_endpoint');

        $result = (new Endpoint_Installer())->install();
        if ($result !== true) {
            wp_safe_redirect(add_query_arg(['error' => urlencode($result)], wp_get_referer()));
        } else {
            wp_safe_redirect(add_query_arg(['message' => urlencode(__('Successfully installed optimized endpoint.', 'koko-analytics'))], wp_get_referer()));
        }
        exit;
    }

    public function save_settings()
    {
        if (!current_user_can('manage_koko_analytics') || ! check_admin_referer('koko_analytics_save_settings') || ! isset($_POST['koko_analytics_settings'])) {
            return;
        }

        // merge posted data with saved data to allow for partial updates
        $settings = array_merge(get_settings(), $_POST['koko_analytics_settings']);
        $settings['exclude_ip_addresses'] = is_array($settings['exclude_ip_addresses']) ? $settings['exclude_ip_addresses'] : explode(PHP_EOL, str_replace(',', PHP_EOL, strip_tags($settings['exclude_ip_addresses'])));
        $settings['exclude_ip_addresses']    = array_filter(array_map('trim', $settings['exclude_ip_addresses']));

        $settings['prune_data_after_months'] = abs((int) $settings['prune_data_after_months']);
        $settings['is_dashboard_public']     = (int) $settings['is_dashboard_public'];
        $settings['default_view']            = trim($settings['default_view']);
        $settings['tracking_method'] = in_array($settings['tracking_method'], ['cookie', 'fingerprint', 'none']) ? $settings['tracking_method'] : 'cookie';

        $settings = apply_filters('koko_analytics_sanitize_settings', $settings, $settings);
        update_option('koko_analytics_settings', $settings, true);

        do_action('koko_analytics_settings_updated', $settings);

        // ensure cron events are scheduled correctly
        (new Cron())->setup();

        // maybe create sessions directory & initial seed file
        if ($settings['tracking_method'] === 'fingerprint') {
            (new Fingerprinter())->create_storage_dir();
        }

        // Re-create optimized endpoint to ensure its contents are up-to-date
        (new Endpoint_Installer())->install();

        wp_safe_redirect(add_query_arg(['settings-updated' => 1], wp_get_referer()));
        exit;
    }

    public function save_component_order()
    {
        if (!check_admin_referer('koko_analytics_save_component_order', '_nonce')) {
            wp_send_json_error(null, 403);
        }

        $order = isset($_POST['component_order']) ? $_POST['component_order'] : [];
        $order = array_map('sanitize_key', (array) $order);

        $settings = (array) get_option('koko_analytics_settings', []);
        $settings['component_order'] = array_values($order);
        update_option('koko_analytics_settings', $settings, true);

        wp_send_json_success();
    }

    public function migrate_post_stats_to_v2()
    {
        check_admin_referer('koko_analytics_migrate_post_stats_to_v2');
        (new Post_Stats_Migrator())->migrate_to_v2();
    }

    public function fix_post_paths_after_v2()
    {
        check_admin_referer('koko_analytics_fix_post_paths_after_v2');
        (new Post_Stats_Migrator())->fix_paths();
    }
}

<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Admin
{
    public function __construct()
    {
        global $pagenow;

        add_action('admin_menu', [$this, 'register_menu'], 10, 0);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

        add_action('koko_analytics_install_optimized_endpoint', [Admin_Actions::class, 'install_optimized_endpoint'], 10, 0);
        add_action('koko_analytics_save_settings', [Admin_Actions::class, 'save_settings'], 10, 0);
        add_action('koko_analytics_reset_statistics', [Admin_Actions::class, 'reset_statistics'], 10, 0);
        add_action('koko_analytics_export_data', [Admin_Actions::class, 'export_data'], 10, 0);
        add_action('koko_analytics_import_data', [Admin_Actions::class, 'import_data'], 10, 0);

        // Hooks for plugins overview page
        if ($pagenow === 'plugins.php') {
            $plugin_basename = plugin_basename(KOKO_ANALYTICS_PLUGIN_FILE);
            add_filter('plugin_action_links_' . $plugin_basename, [$this, 'add_plugin_settings_link'], 10, 1);
            add_filter('plugin_row_meta', [$this, 'add_plugin_meta_links'], 10, 2);
        }

        // actions for jetpack importer
        add_action('koko_analytics_show_jetpack_importer_page', [Jetpack_Importer::class, 'show_page'], 10, 0);
        add_action('koko_analytics_start_jetpack_import', [Jetpack_Importer::class, 'start_import'], 10, 0);
        add_action('koko_analytics_jetpack_import_chunk', [Jetpack_Importer::class, 'import_chunk'], 10, 0);

        // actions for burst importer
        add_action('koko_analytics_show_burst_importer_page', [Burst_Importer::class, 'show_page'], 10, 0);
        add_action('koko_analytics_start_burst_import', [Burst_Importer::class, 'start_import'], 10, 0);
        add_action('koko_analytics_burst_import_chunk', [Burst_Importer::class, 'import_chunk'], 10, 0);
    }

    public function register_menu(): void
    {
        add_submenu_page('index.php', esc_html__('Koko Analytics', 'koko-analytics'), esc_html__('Analytics', 'koko-analytics'), 'view_koko_analytics', 'koko-analytics', [Admin_Page::class, 'show_page']);
    }


    /**
     * Add the settings link to the Plugins overview
     *
     * @param array $links
     *
     * @return array
     */
    public function add_plugin_settings_link($links): array
    {
        $href = admin_url('index.php?page=koko-analytics&tab=settings');
        $label = esc_html__('Settings', 'koko-analytics');
        $settings_link = "<a href=\"{$href}\">{$label}</a>";
        array_unshift($links, $settings_link);
        return $links;
    }

    /**
     * Adds meta links to the plugin in the WP Admin > Plugins screen
     *
     * @param array $links
     * @param string $file
     *
     * @return array
     */
    public function add_plugin_meta_links($links, $file): array
    {
        if ($file !== plugin_basename(KOKO_ANALYTICS_PLUGIN_FILE)) {
            return $links;
        }

        // add links to documentation
        $links[] = '<a href="https://www.kokoanalytics.com/kb/">' . esc_html__('Documentation', 'koko-analytics') . '</a>';

        // add link to Pro version, unless already running it
        if (! \defined('KOKO_ANALYTICS_PRO_VERSION')) {
            $links[] = '<a href="https://www.kokoanalytics.com/pricing/">' . esc_html__('Upgrade to Koko Analytics Pro', 'koko-analytics') . '</a>';
        }

        return $links;
    }

    public function enqueue_scripts($hook_suffix): void
    {
        if ($hook_suffix !== 'dashboard_page_koko-analytics') {
            return;
        }

        wp_enqueue_style('koko-analytics-dashboard', plugins_url('assets/dist/css/dashboard.css', KOKO_ANALYTICS_PLUGIN_FILE), [], KOKO_ANALYTICS_VERSION);
        wp_enqueue_script('koko-analytics-dashboard', plugins_url('assets/dist/js/dashboard.js', KOKO_ANALYTICS_PLUGIN_FILE), [], KOKO_ANALYTICS_VERSION, [ 'strategy' => 'defer' ]);
    }
}

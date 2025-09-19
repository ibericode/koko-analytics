<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

use KokoAnalytics\Jetpack_Importer;

class Admin
{
    public function __construct()
    {
        global $pagenow;

        add_action('admin_notices', [$this, 'show_migrate_to_v2_notice'], 10, 0);
        add_action('admin_menu', [$this, 'register_menu'], 10, 0);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts'], 10, 1);

        add_action('koko_analytics_install_optimized_endpoint', [Actions::class, 'install_optimized_endpoint'], 10, 0);
        add_action('koko_analytics_save_settings', [Actions::class, 'save_settings'], 10, 0);
        add_action('koko_analytics_reset_statistics', [Actions::class, 'reset_statistics'], 20, 0);
        add_action('koko_analytics_export_data', [Actions::class, 'export_data'], 10, 0);
        add_action('koko_analytics_import_data', [Actions::class, 'import_data'], 10, 0);
        add_action('koko_analytics_migrate_post_stats_to_v2', [Actions::class, 'migrate_post_stats_to_v2'], 10, 0);
        add_action('koko_analytics_migrate_referrer_stats_to_v2', [Actions::class, 'migrate_referrer_stats_to_v2'], 10, 0);
        add_action('koko_analytics_fix_post_paths_after_v2', [Actions::class, 'fix_post_paths_after_v2'], 10, 0);

        // Hooks for plugins overview page
        if ($pagenow === 'plugins.php') {
            $plugin_basename = basename(dirname(KOKO_ANALYTICS_PLUGIN_FILE)) . DIRECTORY_SEPARATOR . basename(KOKO_ANALYTICS_PLUGIN_FILE);
            add_filter('plugin_action_links_' . $plugin_basename, [$this, 'add_plugin_settings_link'], 10, 1);
            add_filter('plugin_row_meta', [$this, 'add_plugin_meta_links'], 10, 2);
        }

        // actions for jetpack importer
        add_action('koko_analytics_show_jetpack_importer_page', [Jetpack_Importer::class, 'show_page'], 10, 0);
        add_action('koko_analytics_start_jetpack_import', [Jetpack_Importer::class, 'start_import'], 10, 0);
        add_action('koko_analytics_jetpack_import_chunk', [Jetpack_Importer::class, 'import_chunk'], 10, 0);
    }

    public function register_menu(): void
    {
        add_submenu_page('index.php', esc_html__('Koko Analytics', 'koko-analytics'), esc_html__('Analytics', 'koko-analytics'), 'view_koko_analytics', 'koko-analytics', [Pages::class, 'show_page']);
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

        wp_enqueue_style('koko-analytics-dashboard', plugins_url('assets/dist/css/dashboard-2.css', KOKO_ANALYTICS_PLUGIN_FILE), [], KOKO_ANALYTICS_VERSION);
        wp_enqueue_script('koko-analytics-dashboard', plugins_url('assets/dist/js/dashboard.js', KOKO_ANALYTICS_PLUGIN_FILE), [], KOKO_ANALYTICS_VERSION, [ 'strategy' => 'defer' ]);
    }

    public function show_migrate_to_v2_notice(): void
    {
        // only show to users with required capability
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        // test if we have post_stats to migrate
        /** @var wpdb $wpdb */
        global $wpdb;

        // Test for unmigrated post id records
        $results = $wpdb->get_var("SELECT COUNT(DISTINCT(post_id)) FROM {$wpdb->prefix}koko_analytics_post_stats WHERE post_id IS NOT NULL AND path_id IS NULL");
        if ($results) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php esc_html_e('Koko Analytics needs to migrate your page stats to a new storage format.', 'koko-analytics'); ?>
                    <?php esc_html_e('Click the button below to proceed with the database migration, this can take some time if you have a large site.', 'koko-analytics'); ?>
                </p>
                <form action="" method="post">
                    <input type="hidden" name="koko_analytics_action" value="migrate_post_stats_to_v2">
                    <p><button type="submit" class="button button-primary"><?php esc_html_e('Migrate', 'koko-analytics'); ?></button></p>
                </form>
                <p class="help description text-muted"><?php esc_html_e('We recommend making a back-up of your Koko Analytics database tables before running the migration.', 'koko-analytics'); ?></p>
                <p class="help description text-muted"><?php esc_html_e('You can also run the migration using WP CLI: ', 'koko-analytics'); ?> <code>wp koko-analytics migrate_post_stats_to_v2</code></p>
            </div>
            <?php
        }

        // Test for unmigrated referrer records
        $results = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}koko_analytics_referrer_urls WHERE url LIKE 'http://%' OR url LIKE 'https://%'");
        if ($results && !get_option('koko_analytics_referrers_v2')) {
            ?>
            <div class="notice notice-warning">
                <p>
                    <?php esc_html_e('Koko Analytics needs to migrate your referrer stats to a new storage format.', 'koko-analytics'); ?>
                    <?php esc_html_e('Click the button below to proceed with the database migration, this can take some time if you have a large site.', 'koko-analytics'); ?>
                </p>
                <form action="" method="post">
                    <input type="hidden" name="koko_analytics_action" value="migrate_referrer_stats_to_v2">
                    <p><button type="submit" class="button button-primary"><?php esc_html_e('Migrate', 'koko-analytics'); ?></button></p>
                </form>
                <p class="help description text-muted"><?php esc_html_e('We recommend making a back-up of your Koko Analytics database tables before running the migration.', 'koko-analytics'); ?></p>
            </div>
            <?php
        }
    }
}

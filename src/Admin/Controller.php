<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

use KokoAnalytics\Dashboard_Widget;

use function KokoAnalytics\lazy;

class Controller
{
    public function hook()
    {
        add_action('wp_loaded', [$this, 'action_wp_loaded'], 10, 0);
        add_action('wp_dashboard_setup', [$this, 'action_wp_dashboard_setup'], 10, 0);
        add_action('admin_notices', [$this, 'action_admin_notices'], 10, 0);
        add_action('admin_menu', [$this, 'action_admin_menu'], 10, 0);
        add_action('admin_enqueue_scripts', [$this, 'action_admin_enqueue_scripts'], 10, 1);

        // Hooks for plugins overview page
        global $pagenow;
        if ($pagenow === 'plugins.php') {
            add_filter('plugin_action_links', [$this, 'filter_plugin_action_links'], 10, 2);
            add_filter('plugin_row_meta', [$this, 'filter_plugin_row_meta'], 10, 2);
        }
    }

    public function action_wp_loaded()
    {
        (new Actions())->run();
    }

    public function action_admin_menu()
    {
        add_submenu_page('index.php', 'Koko Analytics', 'Analytics', 'view_koko_analytics', 'koko-analytics', lazy(Pages::class, 'show_dashboard_page'));
        add_submenu_page('options-general.php', 'Koko Analytics', 'Koko Analytics', 'manage_koko_analytics', 'koko-analytics-settings', lazy(Pages::class, 'show_settings_page'));
    }

    public function action_wp_dashboard_setup()
    {
        (new Dashboard_Widget())->register();
    }

    /**
     * Add the settings link to the Plugins overview
     *
     * @param array $links
     * @param string $file
     * @return array
     */
    public function filter_plugin_action_links($links, $file)
    {
        if ($file !== plugin_basename(KOKO_ANALYTICS_PLUGIN_FILE)) {
            return $links;
        }

        $href = admin_url('options-general.php?page=koko-analytics-settings');
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
     * @return array
     */
    public function filter_plugin_row_meta($links, $file)
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

    /**
     * @param string $hook_suffix
     */
    public function action_admin_enqueue_scripts($hook_suffix)
    {
        if ($hook_suffix !== 'dashboard_page_koko-analytics' && $hook_suffix !== 'settings_page_koko-analytics-settings') {
            return;
        }

        wp_enqueue_style('koko-analytics-dashboard', plugins_url('assets/dist/css/dashboard-2.css', KOKO_ANALYTICS_PLUGIN_FILE), [], KOKO_ANALYTICS_VERSION);
        wp_enqueue_script('koko-analytics-dashboard', plugins_url('assets/dist/js/dashboard.js', KOKO_ANALYTICS_PLUGIN_FILE), [], KOKO_ANALYTICS_VERSION, ['strategy' => 'defer']);
    }

    public function action_admin_notices()
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
        if ($results > 0 && !get_option('koko_analytics_referrers_v2')) {
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

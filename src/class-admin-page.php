<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Admin_Page
{
    public static function show_page(): void
    {
        add_action('koko_analytics_show_settings_page', [self::class, 'show_settings_page'], 10, 0);
        add_action('koko_analytics_show_dashboard_page', [self::class, 'show_dashboard_page'], 10, 0);

        $tab = $_GET['tab'] ?? 'dashboard';
        do_action("koko_analytics_show_{$tab}_page");

        add_filter('admin_footer_text', [self::class, 'footer_text'], 10, 1);
    }

    public static function show_dashboard_page(): void
    {
        // aggregate stats whenever this page is requested
        do_action('koko_analytics_aggregate_stats');

        // check if cron event is scheduled properly
        if (false === self::is_cron_event_working()) {
            echo '<div class="notice notice-warning inline koko-analytics-cron-warning is-dismissible"><p>';
            echo esc_html__('There seems to be an issue with your site\'s WP Cron configuration that prevents Koko Analytics from automatically processing your statistics.', 'koko-analytics');
            echo ' ';
            echo esc_html__('If you\'re not sure what this is about, please ask your webhost to look into this.', 'koko-analytics');
            echo '</p></div>';
        }

        // determine whether buffer file is writable
        $buffer_filename        = get_buffer_filename();
        $buffer_dirname         = dirname($buffer_filename);
        $is_buffer_dir_writable = wp_mkdir_p($buffer_dirname) && is_writable($buffer_dirname);

        if (false === $is_buffer_dir_writable) {
            echo '<div class="notice notice-warning inline is-dismissible"><p>';
            echo wp_kses(\sprintf(__('Koko Analytics is unable to write to the <code>%s</code> directory. Please update the file permissions so that your web server can write to it.', 'koko-analytics'), $buffer_dirname), ['code' => []]);
            echo '</p></div>';
        }

        $dashboard = new Dashboard();
        $dashboard->show();
    }

    public static function show_settings_page(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        $settings           = get_settings();
        $endpoint_installer = new Endpoint_Installer();
        $using_custom_endpoint = using_custom_endpoint() && \is_file($endpoint_installer->get_file_name());
        $database_size      = self::get_database_size();
        $user_roles   = self::get_available_roles();
        $date_presets = (new Dashboard())->get_date_presets();

        require __DIR__ . '/views/settings-page.php';
    }

    public static function footer_text($text): string
    {
        // ensure upgrade text isn't showing
        add_filter('update_footer', '__return_empty_string');

        /* translators: %1$s links to the WordPress.org plugin review page, %2$s links to the admin page for creating a new post */
        return \sprintf(wp_kses(__('If you enjoy using Koko Analytics, please consider <a href="%1$s">purchasing Koko Analytics Pro</a>, <a href="%2$s">reviewing the plugin on WordPress.org</a> or <a href="%3$s">writing about it on your blog</a> to help out.', 'koko-analytics'), ['a' => ['href' => []]]), 'https://www.kokoanalytics.com/pricing/', 'https://wordpress.org/support/view/plugin-reviews/koko-analytics?rate=5#postform', admin_url('post-new.php'));
    }

    private static function get_available_roles(): array
    {
        $roles = [];
        foreach (wp_roles()->roles as $key => $role) {
            $roles[$key] = $role['name'];
        }
        return $roles;
    }

    /**
     * Checks to see if the cron event is correctly scheduled and running periodically
     * If the cron event is somehow not scheduled, this will schedule it again.
     */
    private static function is_cron_event_working(): bool
    {
        // Always return true on localhost / dev-ish environments
        $site_url = get_site_url();
        $parts = parse_url($site_url);
        if (!is_array($parts) || !empty($parts['port']) || str_contains($parts['host'], 'localhost') || str_contains($parts['host'], 'local')) {
            return true;
        }

        // detect issues with WP Cron event not running
        // it should run every minute, so if it didn't run in 40 minutes there is most likely something wrong
        // some host run WP Cron only once per 15 minutes, so that is probably the lower bound of this check
        $next_scheduled = wp_next_scheduled('koko_analytics_aggregate_stats');
        if ($next_scheduled === false) {
            // if the event does not appear in scheduled event list at all
            // schedule it now
            wp_schedule_event(time() + 60, 'koko_analytics_stats_aggregate_interval', 'koko_analytics_aggregate_stats');
            return true;
        }

        return $next_scheduled !== false && $next_scheduled > (time() - 40 * 60);
    }

    /**
     * @return int Total size of all Koko Analytics database tables in bytes
     */
    public static function get_database_size(): int
    {
        /** @var \wpdb $wpdb */
        global $wpdb;
        $sql = $wpdb->prepare(
            '
            SELECT SUM(DATA_LENGTH + INDEX_LENGTH)
            FROM information_schema.TABLES
            WHERE TABLE_SCHEMA = %s AND TABLE_NAME LIKE %s',
            [DB_NAME, $wpdb->prefix . 'koko_analytics_%']
        );
        return (int) $wpdb->get_var($sql);
    }
}

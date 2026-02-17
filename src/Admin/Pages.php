<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

use KokoAnalytics\Dashboard;
use KokoAnalytics\Dashboard_Public;
use KokoAnalytics\Router;

use function KokoAnalytics\get_buffer_filename;
use function KokoAnalytics\using_custom_endpoint;
use function KokoAnalytics\get_settings;

class Pages
{
    public function show_dashboard_page(): void
    {
        // aggregate stats whenever this page is requested
        do_action('koko_analytics_aggregate_stats');

        // check if cron event is scheduled properly
        if (false === $this->is_cron_event_working()) {
            echo '<div class="ka-alert ka-alert-warning ka-alert-dismissible" role="alert"  style="margin-top: 1rem; margin-right: 20px;">';
            echo esc_html__('There seems to be an issue with your site\'s WP Cron configuration that prevents Koko Analytics from automatically processing your statistics.', 'koko-analytics');
            echo ' ';
            echo esc_html__('If you\'re not sure what this is about, please ask your webhost to look into this.', 'koko-analytics');
            echo '<button type="button" class="btn-close" aria-label="', esc_attr('Close', 'koko-analytics'), '" onclick="this.parentElement.remove()"></button>';
            echo '</div>';
        }

        // determine whether buffer file is writable
        $buffer_filename        = get_buffer_filename();
        $buffer_dirname         = dirname($buffer_filename);
        $is_buffer_dir_writable = wp_mkdir_p($buffer_dirname) && is_writable($buffer_dirname);

        if (false === $is_buffer_dir_writable) {
            echo '<div class="ka-alert ka-alert-warning ka-alert-dismissible" role="alert" style="margin-top: 1rem; margin-right: 20px;">';
            echo wp_kses(\sprintf(__('Koko Analytics is unable to write to the <code>%s</code> directory. Please update the file permissions so that your web server can write to it.', 'koko-analytics'), $buffer_dirname), ['code' => []]);
            echo '<button type="button" class="btn-close" aria-label="', esc_attr('Close', 'koko-analytics'), '" onclick="this.parentElement.remove()"></button>';
            echo '</div>';
        }

        $dashboard = new Dashboard();
        $dashboard->show();
    }

    public function show_settings_page(): void
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        $tabs = apply_filters('koko_analytics_settings_tabs', [
            'tracking' => __('Tracking', 'koko-analytics'),
            'dashboard' => __('Dashboard', 'koko-analytics'),
            'events' => __('Events', 'koko-analytics'),
            'emails' => __('Email Reports', 'koko-analytics'),
            'data' => __('Data', 'koko-analytics'),
            'performance' => __('Performance', 'koko-analytics'),
            'help' => __('Help', 'koko-analytics'),
        ]);
        $active_tab = $_GET['tab'] ?? 'tracking';

        $settings           = get_settings();
        $using_custom_endpoint = using_custom_endpoint();
        $user_roles   = $this->get_available_roles();
        $date_presets = (new Dashboard())->get_date_presets();
        $public_dashboard_url = (new Dashboard_Public())->get_base_url();

        require KOKO_ANALYTICS_PLUGIN_DIR . '/src/Resources/views/settings-page.php';
    }


    private function get_available_roles(): array
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
    private function is_cron_event_working(): bool
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
}

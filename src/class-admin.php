<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Admin
{
    public function init(): void
    {
        global $pagenow;

        add_action('init', array( $this, 'maybe_run_actions' ), 10, 0);
        add_action('wp_loaded', array( $this, 'maybe_load_standalone' ), 10, 0);
        add_action('admin_menu', array( $this, 'register_menu' ), 10, 0);
        add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10, 1);
        add_action('wp_dashboard_setup', array( $this, 'register_dashboard_widget' ), 10, 0);
        add_action('koko_analytics_install_optimized_endpoint', 'KokoAnalytics\install_and_test_custom_endpoint', 10, 0);
        add_action('koko_analytics_save_settings', array( $this, 'save_settings' ), 10, 0);
        add_action('koko_analytics_reset_statistics', array( $this, 'reset_statistics' ), 10, 0);

        // Hooks for plugins overview page
        if ($pagenow === 'plugins.php') {
            $plugin_basename = plugin_basename(KOKO_ANALYTICS_PLUGIN_FILE);
            add_filter('plugin_action_links_' . $plugin_basename, array( $this, 'add_plugin_settings_link' ), 10, 1);
            add_filter('plugin_row_meta', array( $this, 'add_plugin_meta_links' ), 10, 2);
        }
    }

    public function register_menu(): void
    {
        add_submenu_page('index.php', esc_html__('Koko Analytics', 'koko-analytics'), esc_html__('Analytics', 'koko-analytics'), 'view_koko_analytics', 'koko-analytics', array( $this, 'show_page' ));
    }

    public function maybe_load_standalone(): void
    {
        global $pagenow;

        if ($pagenow !== 'index.php' || ($_GET['page'] ?? '') !== 'koko-analytics' || ! isset($_GET['standalone'])) {
            return;
        }

        if (!current_user_can('view_koko_analytics')) {
            return;
        }

        $this->register_scripts();
        require KOKO_ANALYTICS_PLUGIN_DIR . '/views/standalone.php';
        exit;
    }

    public function maybe_run_actions(): void
    {
        if (isset($_GET['koko_analytics_action'])) {
            $action = $_GET['koko_analytics_action'];
        } elseif (isset($_POST['koko_analytics_action'])) {
            $action = $_POST['koko_analytics_action'];
        } else {
            return;
        }

        if (! current_user_can('manage_koko_analytics')) {
            return;
        }

        do_action('koko_analytics_' . $action);
        wp_safe_redirect(remove_query_arg('koko_analytics_action'));
        exit;
    }

    public function register_scripts(): void
    {
        wp_register_script('koko-analytics-admin', plugins_url('assets/dist/js/admin.js', KOKO_ANALYTICS_PLUGIN_FILE), array(
            'wp-i18n',
            'react',
            'react-dom',
        ), KOKO_ANALYTICS_VERSION, true);
        wp_set_script_translations('koko-analytics-admin', 'koko-analytics');
        $settings = get_settings();
        $script_data = array(
            'root'             => rest_url(),
            'nonce'            => wp_create_nonce('wp_rest'),
            'startOfWeek'      => (int) get_option('start_of_week'),
            'defaultDateRange' => $settings['default_view'],
            'items_per_page'   => (int) apply_filters('koko_analytics_items_per_page', 20),
        );
        wp_add_inline_script('koko-analytics-admin', 'var koko_analytics = ' . json_encode($script_data), 'before');
    }

    public function enqueue_scripts($page): void
    {
        // do not load any scripts if user is missing required capability for viewing
        if (! current_user_can('view_koko_analytics')) {
            return;
        }

        switch ($page) {
            case 'index.php':
                $script_data = array(
                    'root' => rest_url(),
                    'nonce' => wp_create_nonce('wp_rest'),
                );
                // load scripts for dashboard widget
                wp_enqueue_script('koko-analytics-dashboard-widget', plugins_url('/assets/dist/js/dashboard-widget.js', KOKO_ANALYTICS_PLUGIN_FILE), array( 'wp-i18n', 'react', 'react-dom' ), KOKO_ANALYTICS_VERSION, true);
                wp_set_script_translations('koko-analytics-dashboard-widget', 'koko-analytics');
                wp_add_inline_script('koko-analytics-dashboard-widget', 'var koko_analytics = ' . json_encode($script_data), 'before');
                break;

            case 'dashboard_page_koko-analytics':
                wp_enqueue_style('koko-analytics-admin', plugins_url('assets/dist/css/admin.css', KOKO_ANALYTICS_PLUGIN_FILE));

                if (!isset($_GET['tab'])) {
                    $this->register_scripts();
                    wp_enqueue_script('koko-analytics-admin');
                }
                break;
        }
    }

    private function get_available_roles(): array
    {
        $roles = array();
        foreach (wp_roles()->roles as $key => $role) {
            $roles[ $key ] = $role['name'];
        }
        return $roles;
    }

    private function is_cron_event_working(): bool
    {
        // Always return true on localhost / dev-ish environments
        $site_url = get_site_url();
        if (strpos($site_url, ':') !== false || strpos($site_url, 'localhost') !== false || strpos($site_url, '.local') !== false) {
            return true;
        }

        // detect issues with WP Cron event not running
        // it should run every minute, so if it didn't run in 10 minutes there is most likely something wrong
        $next_scheduled = wp_next_scheduled('koko_analytics_aggregate_stats');
        return $next_scheduled !== false && $next_scheduled > (time() - HOUR_IN_SECONDS);
    }

    public function show_page(): void
    {
        add_action('koko_analytics_show_settings_page', array( $this, 'show_settings_page' ));
        add_action('koko_analytics_show_dashboard_page', array( $this, 'show_dashboard_page' ));

        $tab = $_GET['tab'] ?? 'dashboard';
        do_action("koko_analytics_show_{$tab}_page");

        add_action('admin_footer_text', array( $this, 'footer_text' ));
    }

    public function show_dashboard_page(): void
    {
        // aggregate stats whenever this page is requested
        do_action('koko_analytics_aggregate_stats');

        // determine whether buffer file is writable
        $buffer_filename        = get_buffer_filename();
        $buffer_dirname         = dirname($buffer_filename);
        $is_buffer_dir_writable = wp_mkdir_p($buffer_dirname) && is_writable($buffer_dirname);

        // determine whether cron event is set up properly and running in-time
        $is_cron_event_working = $this->is_cron_event_working();

        require KOKO_ANALYTICS_PLUGIN_DIR . '/views/dashboard-page.php';
    }

    public function show_settings_page(): void
    {
        if (! current_user_can('manage_koko_analytics')) {
            return;
        }
        add_action('koko_analytics_show_settings_sections', array($this, 'show_settings_section_for_custom_events'));

        $settings           = get_settings();
        $endpoint_installer = new Endpoint_Installer();
        $using_custom_endpoint = using_custom_endpoint() && \is_file($endpoint_installer->get_file_name());
        $database_size      = $this->get_database_size();
        require KOKO_ANALYTICS_PLUGIN_DIR . '/views/settings-page.php';
    }

    public function show_settings_section_for_custom_events(): void
    {
        // Do not show if Koko Analytics Pro is active
        if (\defined('KOKO_ANALYTICS_PRO_VERSION')) {
            return;
        }

        require KOKO_ANALYTICS_PLUGIN_DIR . '/views/settings-section-events.php';
    }

    public function footer_text(): string
    {
        // ensure upgrade text isn't showing
        add_filter('update_footer', '__return_empty_string');

        /* translators: %1$s links to the WordPress.org plugin review page, %2$s links to the admin page for creating a new post */
        return sprintf(wp_kses(__('If you enjoy using Koko Analytics, please <a href="%1$s">review the plugin on WordPress.org</a> or <a href="%2$s">write about it on your blog</a> to help out.', 'koko-analytics'), array( 'a' => array( 'href' => array() ) )), 'https://wordpress.org/support/view/plugin-reviews/koko-analytics?rate=5#postform', admin_url('post-new.php'));
    }

    public function register_dashboard_widget(): void
    {
        // only show if user can view stats
        if (! current_user_can('view_koko_analytics')) {
            return;
        }

        add_meta_box('koko-analytics-dashboard-widget', 'Koko Analytics', array( $this, 'dashboard_widget' ), 'dashboard', 'side', 'high');
    }

    public function dashboard_widget(): void
    {
        echo '<div id="koko-analytics-dashboard-widget-mount"></div>';
        echo sprintf('<p class="help" style="text-align: center;">%s &mdash; <a href="%s">%s</a></p>', esc_html__('Showing site visits over last 14 days', 'koko-analytics'), esc_attr(admin_url('index.php?page=koko-analytics')), esc_html__('View all statistics', 'koko-analytics'));
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
        $settings_link = sprintf('<a href="%s">%s</a>', admin_url('index.php?page=koko-analytics&tab=settings'), esc_html__('Settings', 'koko-analytics'));
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

        $links[] = '<a href="https://www.kokoanalytics.com/kb/">' . esc_html__('Documentation', 'koko-analytics') . '</a>';

        if (! \defined('KOKO_ANALYTICS_PRO_VERSION')) {
            $links[] = '<a href="https://www.kokoanalytics.com/pricing/">' . esc_html__('Koko Analytics Pro', 'koko-analytics') . '</a>';
        }
        return $links;
    }

    public function get_database_size(): string
    {
        /** @var \WPDB $wpdb */
        global $wpdb;
        $sql = $wpdb->prepare(
            '
			SELECT ROUND(SUM((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024), 2)
			FROM information_schema.TABLES
			WHERE TABLE_SCHEMA = %s AND TABLE_NAME LIKE %s',
            DB_NAME,
            $wpdb->prefix . 'koko_analytics_%'
        );

        return $wpdb->get_var($sql);
    }

    public function reset_statistics(): void
    {
        check_admin_referer('koko_analytics_reset_statistics');
        /** @var \WPDB $wpdb */
        global $wpdb;
        $wpdb->query("TRUNCATE {$wpdb->prefix}koko_analytics_site_stats;");
        $wpdb->query("TRUNCATE {$wpdb->prefix}koko_analytics_post_stats;");
        $wpdb->query("TRUNCATE {$wpdb->prefix}koko_analytics_referrer_stats;");
        $wpdb->query("TRUNCATE {$wpdb->prefix}koko_analytics_referrer_urls;");
        delete_option('koko_analytics_realtime_pageview_count');
    }

    public function save_settings(): void
    {
        check_admin_referer('koko_analytics_save_settings');
        $new_settings                        = $_POST['koko_analytics_settings'];
        $settings                            = get_settings();
        $settings['exclude_user_roles']      = $new_settings['exclude_user_roles'] ?? array();
        $settings['prune_data_after_months'] = abs((int) $new_settings['prune_data_after_months']);
        $settings['use_cookie']              = (int) $new_settings['use_cookie'];
        $settings['default_view']            = trim($new_settings['default_view']);
        update_option('koko_analytics_settings', $settings, true);
        wp_safe_redirect(add_query_arg(array( 'settings-updated' => true ), wp_get_referer()));
        exit;
    }
}

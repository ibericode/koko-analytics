<?php

namespace KokoAnalytics;

class Admin {

    public function init()
    {
        add_action('init', array($this, 'maybe_run_migrations'));
        add_action('init', array($this, 'maybe_seed'));
        add_action('admin_menu', array($this, 'register_menu'));
    }

    public function register_menu()
    {
        add_submenu_page('index.php', __('Analytics', 'koko-analytics'), __('Analytics', 'koko-analytics'), 'manage_options', 'koko-analytics', array($this, 'show_page'));
    }

    public function show_page()
    {
        wp_enqueue_script('koko-analytics-admin', plugins_url('assets/dist/js/admin.js', KOKO_ANALYTICS_PLUGIN_FILE), array(), KOKO_ANALYTICS_VERSION, true);
        wp_localize_script( 'koko-analytics-admin', 'koko_analytics', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' )
        ) );

        require KOKO_ANALYTICS_PLUGIN_DIR . '/views/admin-page.php';
    }

    public function maybe_run_migrations()
    {
        if (! current_user_can('install_plugins')) {
            return;
        }

        $from = isset($_GET['koko_analytics_migrate_from_version']) ? $_GET['koko_analytics_migrate_from_version'] : get_option('koko_analytics_version', '0.0.1');
        if (version_compare($from, KOKO_ANALYTICS_VERSION, '>=')) {
            return;
        }

        $migrations = new Migrations($from, KOKO_ANALYTICS_VERSION, KOKO_ANALYTICS_PLUGIN_DIR . '/migrations/');
        $migrations->run();
        update_option('koko_analytics_version', KOKO_ANALYTICS_VERSION);
    }

    public function maybe_seed()
    {
        global $wpdb;

        if (!isset($_GET['koko_analytics_seed']) || !current_user_can('manage_options')) {
            return;
        }

        $wpdb->suppress_errors(true);

        $n = 3*365;
        for ($i = 0; $i < $n; $i++) {
            $date = date("Y-m-d", strtotime(sprintf('-%d days', $i)));
            $pageviews = rand(200, 1000) / $n * ($n-$i) ;
            $visitors = rand(2, 6) / 10 * $pageviews;

            $wpdb->insert($wpdb->prefix . 'koko_analytics_stats', array(
               'id' => 0,
               'date' => $date,
               'pageviews' => $pageviews,
               'visitors' => $visitors,
			));
        }
    }
}

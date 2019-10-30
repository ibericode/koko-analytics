<?php

namespace AP;

class Admin {

    public function init()
    {
        add_action('init', array($this, 'maybe_run_migrations'));
        add_action('init', array($this, 'maybe_seed'));
        add_action('admin_menu', array($this, 'register_menu'));
    }

    public function register_menu()
    {
        add_submenu_page('index.php', __('Analytics', 'analytics-plugin'), __('Analytics', 'analytics-plugin'), 'manage_options', 'analytics-plugin', array($this, 'show_page'));
    }

    public function show_page()
    {
        wp_enqueue_script('ap-admin', plugins_url('assets/dist/js/admin.js', AP_PLUGIN_FILE), array(), AP_VERSION, true);
        #wp_enqueue_style('ap-admin', plugins_url('assets/dist/css/admin.css', AP_PLUGIN_FILE));
        wp_localize_script( 'ap-admin', 'ap', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' )
        ) );

        require AP_PLUGIN_DIR . '/views/admin-page.php';
    }

    public function maybe_run_migrations()
    {
        //delete_option('ap_version');
        $from = get_option('ap_version', '0.0.1');
        if (version_compare($from, AP_VERSION, '>=')) {
            return;
        }

        $migrations = new Migrations($from, AP_VERSION, AP_PLUGIN_DIR . '/migrations/');
        $migrations->run();
        update_option('ap_version', AP_VERSION);
    }

    public function maybe_seed()
    {
        global $wpdb;

        if (!isset($_GET['ap_seed']) || !current_user_can('manage_options')) {
            return;
        }

        $wpdb->suppress_errors(true);

        $n = 3*365;
        for ($i = 0; $i < $n; $i++) {
            $date = date("Y-m-d", strtotime(sprintf('-%d days', $i)));
            $pageviews = rand(200, 1000) / $n * ($n-$i) ;
            $visitors = rand(2, 6) / 10 * $pageviews;

            $wpdb->insert($wpdb->prefix . 'ap_stats', [
               'id' => 0,
               'date' => $date,
               'pageviews' => $pageviews,
               'visitors' => $visitors,
            ]);
        }
    }
}

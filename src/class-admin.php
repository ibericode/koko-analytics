<?php

namespace AAA;

class Admin {

    public function init()
    {
        $this->maybe_run_migrations();

        add_action('admin_menu', array($this, 'register_menu'));
        add_action('init', array($this, 'maybe_seed'));
    }

    public function register_menu()
    {
        add_submenu_page('index.php', __('Statistics', 'aaa-stats'), __('Statistics', 'aaa-stats'), 'manage_options', 'aaa-stats', array($this, 'show_page'));
    }

    public function show_page()
    {
        wp_enqueue_script('aaa-admin', plugins_url('assets/dist/js/admin.js', AAA_PLUGIN_FILE), array(), AAA_VERSION, true);
        #wp_enqueue_style('aaa-admin', plugins_url('assets/dist/css/admin.css', AAA_PLUGIN_FILE));
        wp_localize_script( 'aaa-admin', 'aaa', array(
            'root' => esc_url_raw( rest_url() ),
            'nonce' => wp_create_nonce( 'wp_rest' )
        ) );

        require AAA_PLUGIN_DIR . '/views/admin-page.php';
    }

    public function maybe_run_migrations()
    {
        //delete_option('aaa_version');
        $from = get_option('aaa_version', '0.0.1');
        if (version_compare($from, AAA_VERSION, '>=')) {
            return;
        }

        $migrations = new Migrations($from, AAA_VERSION, AAA_PLUGIN_DIR . '/migrations/');
        $migrations->run();
        update_option('aaa_version', AAA_VERSION);
    }

    public function maybe_seed()
    {
        global $wpdb;

        if (!isset($_GET['aaa_seed']) || !current_user_can('manage_options')) {
            return;
        }

        $wpdb->suppress_errors(true);

        $n = 3*365;
        for ($i = 0; $i < $n; $i++) {
            $date = date("Y-m-d", strtotime(sprintf('-%d days', $i)));
            $pageviews = rand(200, 1000) / $n * ($n-$i) ;
            $visitors = rand(2, 6) / 10 * $pageviews;

            $wpdb->insert($wpdb->prefix . 'aaa_stats', [
               'id' => 0,
               'date' => $date,
               'pageviews' => $pageviews,
               'visitors' => $visitors,
            ]);
        }
    }
}

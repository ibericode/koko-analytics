<?php

namespace AAA;

class Admin {

    public function init()
    {
        $this->maybe_run_migrations();

        add_action('admin_menu', array($this, 'register_menu'));
    }

    public function register_menu()
    {
        add_submenu_page('index.php', __('Analytics', 'aaa-analytics'), __('Analytics', 'aaa-analytics'), 'manage_options', 'aaa-analytics', array($this, 'show_page'));
    }

    public function show_page()
    {
        wp_enqueue_script('aaa-admin', plugins_url('assets/dist/js/admin.js', AAA_PLUGIN_FILE), array(), AAA_VERSION, true);

        // TODO: UI for viewing statistics in admin area.

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
}
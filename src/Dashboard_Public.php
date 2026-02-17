<?php

namespace KokoAnalytics;

class Dashboard_Public extends Dashboard
{
    public function get_base_url()
    {
        if (get_option('permalink_structure', false)) {
            return home_url('/koko-analytics-dashboard/');
        }

        return add_query_arg(['koko-analytics-dashboard' => ''], home_url());
    }

    public function show()
    {
        $settings = get_settings();
        if (!$settings['is_dashboard_public'] && !current_user_can('view_koko_analytics')) {
            return;
        }

        // don't serve public dashboard to anything that looks like a bot or crawler
        if (empty($_SERVER['HTTP_USER_AGENT']) || \preg_match("/bot|crawl|spider/", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return;
        }

        do_action('koko_analytics_public_dashboard_headers');

        header("Content-Type: text/html; charset=utf-8");
        header("X-Robots-Tag: noindex, nofollow");
        if (is_user_logged_in()) {
            header("Cache-Control: no-store, must-revalidate, no-cache, max-age=0, private");
        } elseif (isset($_GET['end_date'], $_GET['start_date']) && $_GET['end_date'] < date('Y-m-d')) {
            header("Cache-Control: public, max-age=68400");
        } else {
            header("Cache-Control: public, max-age=60");
        }

        require KOKO_ANALYTICS_PLUGIN_DIR . '/src/Resources/views/dashboard-public.php';
        exit;
    }
}

<?php

namespace KokoAnalytics;

use DateTimeImmutable;

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
        $today    = (new DateTimeImmutable('now', wp_timezone()))->format('Y-m-d');
        if (!$settings['is_dashboard_public'] && !current_user_can('view_koko_analytics')) {
            return;
        }

        // don't serve public dashboard to anything that looks like a bot or crawler
        $user_agent = wp_unslash($_SERVER['HTTP_USER_AGENT'] ?? '');
        if ($user_agent === '' || preg_match('/bot|crawl|crawler|spider|slurp|ahrefs|semrush|mj12bot|dotbot|bingpreview/i', $user_agent)) {
            status_header(403);
            header("X-Robots-Tag: noindex, nofollow", true);
            exit;
        }

        // redirect to the canonical URL for this view, so that visitors arriving through a link that
        // was shared or crawled hit the same cache entry as everyone else
        // GET only: a 301 would turn the POST that saves the component order into a GET
        $request_uri = wp_unslash($_SERVER['REQUEST_URI'] ?? '');
        $canonical   = sort_query_string($request_uri);
        if ($canonical !== $request_uri && wp_unslash($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
            wp_safe_redirect($canonical, 301);
            exit;
        }

        // the public dashboard is crawlable, so don't hand out paginated URLs for bots to follow
        self::hide_pagination_links();

        do_action('koko_analytics_public_dashboard_headers');

        header("Content-Type: text/html; charset=utf-8");
        header("X-Robots-Tag: noindex, nofollow");
        if (is_user_logged_in()) {
            header("Cache-Control: no-store, must-revalidate, no-cache, max-age=0, private");
        } elseif (isset($_GET['end_date'], $_GET['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', wp_unslash($_GET['start_date'])) && preg_match('/^\d{4}-\d{2}-\d{2}$/', wp_unslash($_GET['end_date'])) && wp_unslash($_GET['end_date']) < $today) {
            header("Cache-Control: public, max-age=68400");
        } else {
            header("Cache-Control: public, max-age=60");
        }

        require KOKO_ANALYTICS_PLUGIN_DIR . '/src/Resources/views/dashboard-public.php';
        exit;
    }
}

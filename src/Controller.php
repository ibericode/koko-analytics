<?php

namespace KokoAnalytics;

use KokoAnalytics\Shortcodes\Shortcode_Most_Viewed_Posts;
use KokoAnalytics\Shortcodes\Shortcode_Site_Counter;
use KokoAnalytics\Widgets\Most_Viewed_Posts_Widget;

class Controller
{
    public function hook(): void
    {
        add_action('init', [$this, 'action_init'], 0, 0);
        add_action('wp_loaded', [$this, 'action_wp_loaded'], 10, 0);
        add_filter('cron_schedules', [$this, 'filter_cron_schedules'], 10, 1);
        add_action('widgets_init', [$this, 'action_widgets_init'], 10, 0);
        add_action('rest_api_init', [$this, 'action_rest_api_init'], 10, 0);
        add_action('wp', [$this, 'action_wp'], 10, 0);
    }

    public function action_wp_loaded(): void
    {
        // Maybe run any pending database migrations
        $migrations = new Migrations('koko_analytics', KOKO_ANALYTICS_VERSION, KOKO_ANALYTICS_PLUGIN_DIR . '/migrations/');
        $migrations->maybe_run();

        // Run actions
        // TODO: Use instance with instance methods here
        Actions::run();
    }

    public function action_init(): void
    {
        // ajax collection endpoint (only used in case optimized endpoint is not installed)
        maybe_collect_request();

        add_shortcode('koko_analytics_most_viewed_posts', [Shortcode_Most_Viewed_Posts::class, 'content']);
        add_shortcode('koko_analytics_counter', [Shortcode_Site_Counter::class, 'content']);

        $this->maybe_show_dashboard();
    }

    public function filter_cron_schedules($schedules)
    {
        $schedules['koko_analytics_stats_aggregate_interval'] = [
            'interval' => 60, // 60 seconds
            'display'  => esc_html__('Every minute', 'koko-analytics'),
        ];
        return $schedules;
    }

    public function action_widgets_init(): void
    {
        register_widget(Most_Viewed_Posts_Widget::class);
    }

    public function rest_api_init(): void
    {
        (new Rest())->register_routes();
    }

    public function action_wp(): void
    {
        // TODO: Create instance here and use instance methods
        add_action('wp_head', [Script_Loader::class, 'print_js_object'], 1, 0);
        add_action('wp_footer', [Script_Loader::class, 'maybe_print_script'], 10, 0);
        add_action('amp_print_analytics', [Script_Loader::class, 'print_amp_analytics_tag'], 10, 0);
        add_action('admin_bar_menu', [Admin\Bar::class, 'register'], 40, 1);
    }

    protected function maybe_show_dashboard(): void
    {
        // TODO: Move most of this code to standalone dashboard class?
        if (!Router::is('dashboard-standalone')) {
            return;
        }

        $settings = get_settings();
        if (!$settings['is_dashboard_public'] && !current_user_can('view_koko_analytics')) {
            return;
        }

        // don't serve public dashboard to anything that looks like a bot or crawler
        if (empty($_SERVER['HTTP_USER_AGENT']) || \preg_match("/bot|crawl|spider/", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return;
        }

        header("Content-Type: text/html; charset=utf-8");
        header("X-Robots-Tag: noindex, nofollow");

        if (is_user_logged_in()) {
            header("Cache-Control: no-store, must-revalidate, no-cache, max-age=0, private");
        } elseif (isset($_GET['end_date'], $_GET['start_date']) && $_GET['end_date'] < date('Y-m-d')) {
            header("Cache-Control: public, max-age=68400");
        } else {
            header("Cache-Control: public, max-age=60");
        }

        (new Dashboard_Standalone())->show();
        exit;
    }
}

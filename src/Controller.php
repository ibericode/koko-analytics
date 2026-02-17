<?php

namespace KokoAnalytics;

use KokoAnalytics\Shortcodes\Shortcode_Most_Viewed_Posts;
use KokoAnalytics\Shortcodes\Shortcode_Site_Counter;
use KokoAnalytics\Widgets\Most_Viewed_Posts_Widget;
use WP_Admin_Bar;

class Controller
{
    public function hook(): void
    {
        add_action('init', [$this, 'action_init'], 0, 0);
        add_action('wp_loaded', [$this, 'action_wp_loaded'], 10, 0);
        add_action('wp', [$this, 'action_wp'], 10, 0);

        add_filter('cron_schedules', [$this, 'filter_cron_schedules'], 10, 1);
        add_action('rest_api_init', lazy(Rest::class, 'action_rest_api_init'), 10, 0);

        add_action('koko_analytics_aggregate_stats', lazy(Aggregator::class, 'run'), 10, 0);
        add_action('koko_analytics_prune_data', lazy(Pruner::class, 'run'), 10, 0);
        add_action('koko_analytics_rotate_fingerprint_seed', lazy(Fingerprinter::class, 'run_daily_maintenance'), 10, 0);
        add_action('koko_analytics_test_custom_endpoint', lazy(Endpoint_Installer::class, 'test'), 10, 0);
        add_action('koko_analytics_update_custom_endpoint', lazy(Endpoint_Installer::class, 'install'), 10, 0);
    }

    public function action_wp_loaded()
    {
        // Maybe run any pending database migrations
        $migrations = new Migrations('koko_analytics', KOKO_ANALYTICS_VERSION, KOKO_ANALYTICS_PLUGIN_DIR . '/migrations/');
        $migrations->maybe_run();
    }

    public function action_init()
    {
        // listener for ajax collection endpoint (only used in case optimized endpoint is not installed)
        $this->maybe_collect_request();

        // listener for public dashboard
        $this->maybe_show_dashboard();

        add_shortcode('koko_analytics_most_viewed_posts', lazy(Shortcode_Most_Viewed_Posts::class, 'content'));
        add_shortcode('koko_analytics_counter', lazy(Shortcode_Site_Counter::class, 'content'));
        register_widget(Most_Viewed_Posts_Widget::class);
    }

    public function filter_cron_schedules($schedules)
    {
        $schedules['koko_analytics_stats_aggregate_interval'] = [
            'interval' => 60, // 60 seconds
            'display'  => esc_html__('Every minute', 'koko-analytics'),
        ];
        return $schedules;
    }

    public function action_wp()
    {
        (new Script_Loader())->hook();
        add_action('admin_bar_menu', [$this, 'action_admin_bar_menu'], 40, 1);
    }

    public function action_admin_bar_menu(WP_Admin_Bar $wp_admin_bar): void
    {
        // only show on frontend
        // only show for users who can access statistics page
        if (is_admin() || false == current_user_can('view_koko_analytics')) {
            return;
        }

        $wp_admin_bar->add_node(
            [
                'parent' => 'site-name',
                'id' => 'koko-analytics',
                'title' => esc_html__('Analytics', 'koko-analytics'),
                'href' => admin_url('/index.php?page=koko-analytics'),
            ]
        );
    }

    protected function maybe_collect_request()
    {
        if (($_GET['action'] ?? '') !== 'koko_analytics_collect') {
            return;
        }

        collect_request();
    }

    protected function maybe_show_dashboard()
    {
        if (! isset($_GET['koko-analytics-dashboard']) && ! str_contains($_SERVER['REQUEST_URI'] ?? '', '/koko-analytics-dashboard/')) {
            return;
        }

        (new Dashboard_Public())->show();
    }
}

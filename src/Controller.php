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
        add_action('init', [$this, 'maybe_collect_request'], PHP_INT_MIN, 0);
        add_action('init', [$this, 'action_init'], 10, 0);
        add_action('wp_loaded', [$this, 'action_wp_loaded'], 10, 0);
        add_action('wp', [$this, 'action_wp'], 10, 0);
        add_action('widgets_init', [$this, 'action_widgets_init'], 10, 0);

        add_filter('cron_schedules', [$this, 'filter_cron_schedules'], 10, 1);
        add_action('rest_api_init', lazy(Rest::class, 'action_rest_api_init'), 10, 0);

        add_action('koko_analytics_aggregate_stats', lazy(Aggregator::class, 'run'), 10, 0);
        add_action('koko_analytics_prune_data', lazy(Pruner::class, 'run'), 10, 0);
        add_action('koko_analytics_rotate_fingerprint_seed', lazy(Fingerprinter::class, 'run_daily_maintenance'), 10, 0);
        add_action('koko_analytics_test_custom_endpoint', lazy(Endpoint_Installer::class, 'test'), 10, 0);
        add_action('koko_analytics_update_custom_endpoint', lazy(Endpoint_Installer::class, 'install'), 10, 0);
    }

    public function action_wp_loaded(): void
    {
        $this->run_pending_database_migrations();
    }

    public function action_init(): void
    {
        // listener for public dashboard
        $this->maybe_show_dashboard();

        add_shortcode('koko_analytics_most_viewed_posts', lazy(Shortcode_Most_Viewed_Posts::class, 'content'));
        add_shortcode('koko_analytics_counter', lazy(Shortcode_Site_Counter::class, 'content'));
    }

    public function action_widgets_init(): void
    {
        register_widget(Most_Viewed_Posts_Widget::class);
    }

    /**
     * @param array $schedules
     * @return array
     */
    public function filter_cron_schedules($schedules)
    {
        $schedules['koko_analytics_stats_aggregate_interval'] = [
            'interval' => 60, // 60 seconds
            'display'  => did_action('after_setup_theme') ? esc_html__('Every 60 seconds', 'koko-analytics') : 'Every 60 seconds',
        ];
        return $schedules;
    }

    public function action_wp(): void
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

    public function maybe_collect_request(): void
    {
        // TODO: Remove the $_GET check after 2026-04-16
        if (($_GET['action'] ?? '') !== 'koko_analytics_collect' && ($_POST['action'] ?? '') !== 'koko_analytics_collect') {
            return;
        }

        collect_request();
    }

    protected function maybe_show_dashboard(): void
    {
        if (! isset($_GET['koko-analytics-dashboard']) && ! str_contains($_SERVER['REQUEST_URI'] ?? '', '/koko-analytics-dashboard/')) {
            return;
        }

        (new Dashboard_Public())->show();
    }

    public function run_pending_database_migrations(): void
    {
        // Bring users on older versions up to the last semver-based migration (2.2.6.3)
        $old_db_version = (string) get_option('koko_analytics_version', '');
        if ($old_db_version) {
            $this->update_migration_version($old_db_version);
        }

        // Run integer-based migrations going forward
        $m = new Migrations_v2(KOKO_ANALYTICS_PLUGIN_DIR . '/migrations/', 'koko_analytics_migrations');
        $m->run();
    }

    protected function update_migration_version(string $old_db_version): void
    {
        // set numeric version based on old semver version, so we can run new integer-based migrations
        $map = [
            '1.0.0' => 1,
            '1.3.12' => 2,
            '1.6.3' => 3,
            '1.7.0' => 4,
            '1.8.0' => 5,
            '1.8.5' => 6,
            '1.9.991' => 7,
            '1.9.992' => 8,
            '2.0.12' => 9,
            '2.0.13' => 10,
            '2.0.20' => 11,
            '2.2.5' => 12,
        ];

        $new_db_version = 0;
        foreach ($map as $semver => $numeric) {
            if (version_compare($old_db_version, $semver, '>=')) {
                $new_db_version = $numeric;
            }
        }

        update_option('koko_analytics_migrations', $new_db_version, true);
        delete_option('koko_analytics_version');
    }
}

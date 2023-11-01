<?php

namespace KokoAnalytics;

class Dashboard_Widget
{
    public function init(): void
    {
        add_action('wp_dashboard_setup', array( $this, 'register_dashboard_widget' ), 10, 0);
    }

    public function register_dashboard_widget(): void
    {
        // only show if user can view stats
        if (! current_user_can('view_koko_analytics')) {
            return;
        }

        add_meta_box('koko-analytics-dashboard-widget', 'Koko Analytics', array( $this, 'dashboard_widget' ), 'dashboard', 'side', 'high');
        add_action('admin_print_scripts-index.php', array( $this, 'enqueue_scripts' ), 10, 0);
    }

    public function enqueue_scripts(): void
    {
        $script_data = array(
            'root' => rest_url(),
            'nonce' => wp_create_nonce('wp_rest'),
            'i18n' => array(
                'Visitors' => __('Visitors', 'koko-analytics'),
                'Pageviews' => __('Pageviews', 'koko-analytics'),
            )
        );
        // load scripts for dashboard widget
        wp_enqueue_style('koko-analytics-dashboard', plugins_url('assets/dist/css/dashboard.css', KOKO_ANALYTICS_PLUGIN_FILE));
        wp_enqueue_script('koko-analytics-dashboard-widget', plugins_url('/assets/dist/js/dashboard-widget.js', KOKO_ANALYTICS_PLUGIN_FILE), array(), KOKO_ANALYTICS_VERSION, true);
        wp_add_inline_script('koko-analytics-dashboard-widget', 'var koko_analytics = ' . json_encode($script_data), 'before');
    }

    public function dashboard_widget(): void
    {
        $number_of_top_items = (int) apply_filters('koko_analytics_dashboard_widget_number_of_top_items', 5);
        $stats = new Stats();
        $dateStart = create_local_datetime('today, midnight')->format('Y-m-d');
        $dateEnd = create_local_datetime('tomorrow, midnight')->format('Y-m-d');
        $realtime = get_realtime_pageview_count('-1 hour');
        $totals = $stats->get_totals($dateStart, $dateEnd);

        if ($number_of_top_items > 0) {
            $posts = $stats->get_posts($dateStart, $dateEnd, 0, $number_of_top_items);
            $referrers = $stats->get_referrers($dateStart, $dateEnd, 0, $number_of_top_items);
        }
        require __DIR__ . '/views/dashboard-widget.php';
    }
}

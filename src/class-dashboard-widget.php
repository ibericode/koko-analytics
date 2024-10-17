<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Dashboard_Widget
{
    public function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'register_dashboard_widget'), 10, 0);
    }

    public function register_dashboard_widget(): void
    {
        // only show if user can view stats
        if (!current_user_can('view_koko_analytics')) {
            return;
        }

        add_meta_box('koko-analytics-dashboard-widget', 'Koko Analytics', array($this, 'dashboard_widget'), 'dashboard', 'side', 'high');
    }

    public function dashboard_widget(): void
    {
        $number_of_top_items = (int) apply_filters('koko_analytics_dashboard_widget_number_of_top_items', 5);
        $stats = new Stats();
        $dateToday = create_local_datetime('today, midnight')->format('Y-m-d');
        $realtime = get_realtime_pageview_count('-1 hour');
        $totals = $stats->get_totals($dateToday, $dateToday);

        $dateStart = create_local_datetime('-14 days');
        $dateEnd = create_local_datetime('now');
        $chart_data = $stats->get_stats($dateStart->format('Y-m-d'), $dateEnd->format('Y-m-d'), 'day');

        if ($number_of_top_items > 0) {
            $posts = $stats->get_posts($dateToday, $dateToday, 0, $number_of_top_items);
            $referrers = $stats->get_referrers($dateToday, $dateToday, 0, $number_of_top_items);
        }

        require __DIR__ . '/views/dashboard-widget.php';
    }
}

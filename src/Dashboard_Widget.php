<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use DateTimeImmutable;

class Dashboard_Widget
{
    public static function register_dashboard_widget(): void
    {
        // only show if user can view stats
        if (!current_user_can('view_koko_analytics')) {
            return;
        }

        add_meta_box('koko-analytics-dashboard-widget', 'Koko Analytics', [self::class, 'dashboard_widget'], 'dashboard', 'side', 'high');
    }

    public static function dashboard_widget(): void
    {
        // aggregate stats whenever this page is requested
        do_action('koko_analytics_aggregate_stats');

        $number_of_top_items = (int) apply_filters('koko_analytics_dashboard_widget_number_of_top_items', 5);
        $timezone = wp_timezone();
        $stats = new Stats();
        $today = (new DateTimeImmutable('today, midnight', $timezone))->format('Y-m-d');
        $totals = $stats->get_totals($today, $today);

        // get realtime pageviews, but limit it to number of total pageviews today in case viewing shortly after midnight
        $realtime = min($totals->pageviews, get_realtime_pageview_count('-1 hour'));

        // get chart data
        $date_start = new DateTimeImmutable('-14 days', $timezone);
        $date_end = new DateTimeImmutable('now', $timezone);
        $chart_data = $stats->get_stats($date_start->format('Y-m-d'), $date_end->format('Y-m-d'), 'day');

        if ($number_of_top_items > 0) {
            $posts = $stats->get_posts($today, $today, 0, $number_of_top_items);
            $referrers = $stats->get_referrers($today, $today, 0, $number_of_top_items);
        }

        require KOKO_ANALYTICS_PLUGIN_DIR . '/src/Resources/views/dashboard-widget.php';
    }
}

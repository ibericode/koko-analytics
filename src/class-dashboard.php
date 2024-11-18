<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Dashboard
{
    public function __construct()
    {
        add_action('wp', array($this, 'maybe_show_dashboard'), 10, 0);
    }

    public function maybe_show_dashboard(): void
    {
        if (!isset($_GET['koko-analytics-dashboard'])) {
            return;
        }

        $settings = get_settings();
        if (!$settings['is_dashboard_public'] && !current_user_can('view_koko_analytics')) {
            return;
        }

        $this->show_standalone_dashboard_page();
    }

    public function show_standalone_dashboard_page(): void
    {
        require __DIR__ . '/views/standalone.php';
        exit;
    }

    public function show(): void
    {
        $settings   = get_settings();
        $dates = new Dates();
        $stats = new Stats();
        $items_per_page = (int) apply_filters('koko_analytics_items_per_page', 20);
        $dateFormat = get_option('date_format');
        $dashboard_url = remove_query_arg(['start_date', 'end_date', 'view', 'posts', 'referrers']);

        // parse query params
        $range = isset($_GET['view']) ? $_GET['view'] : $settings['default_view'];
        $dateRange = $dates->get_range($range);

        $page = isset($_GET['p']) ? absint($_GET['p']) : 0;
        $dateStart  = isset($_GET['start_date']) ? create_local_datetime($_GET['start_date']) : $dateRange[0];
        $dateStart = $dateStart ?? $dateRange[0];
        $dateEnd    = isset($_GET['end_date']) ? create_local_datetime($_GET['end_date']) : $dateRange[1];
        $dateEnd = $dateEnd ?? $dateRange[1];
        $nextDates = $this->get_next_period($dateStart, $dateEnd, 1);
        $prevDates = $this->get_next_period($dateStart, $dateEnd, -1);

        $posts_offset = isset($_GET['posts']['offset']) ? absint($_GET['posts']['offset']) : 0;
        $referrers_offset = isset($_GET['referrers']['offset']) ? absint($_GET['referrers']['offset']) : 0;
        $posts_limit = isset($_GET['posts']['limit']) ? absint($_GET['posts']['limit']) : $items_per_page;
        $referrers_limit = isset($_GET['referrers']['limit']) ? absint($_GET['referrers']['limit']) : $items_per_page;

        $totals = $stats->get_totals($dateStart->format('Y-m-d'), $dateEnd->format('Y-m-d'), $page);
        $totals_previous = $stats->get_totals($prevDates[0]->format('Y-m-d'), $prevDates[1]->format('Y-m-d'), $page);

        $posts = $stats->get_posts($dateStart->format("Y-m-d"), $dateEnd->format('Y-m-d'), $posts_offset, $posts_limit);
        $posts_count = $stats->count_posts($dateStart->format('Y-m-d'), $dateEnd->format('Y-m-d'));
        $referrers = $stats->get_referrers($dateStart->format("Y-m-d"), $dateEnd->format('Y-m-d'), $referrers_offset, $referrers_limit);
        $referrers_count = $stats->count_referrers($dateStart->format("Y-m-d"), $dateEnd->format('Y-m-d'));
        $realtime = get_realtime_pageview_count('-1 hour');

        $groupChartBy = $dateEnd->getTimestamp() - $dateStart->getTimestamp() >= 86400 * 36 ? 'month' : 'day';
        $chart_data =  $stats->get_stats($dateStart->format("Y-m-d"), $dateEnd->format('Y-m-d'), $groupChartBy, $page);

        $nextDates = $this->get_next_period($dateStart, $dateEnd, 1);
        $prevDates = $this->get_next_period($dateStart, $dateEnd, -1);

        require __DIR__ . '/views/dashboard-page.php';
    }

    private function get_next_period(\DateTimeImmutable $dateStart, \DateTimeImmutable $dateEnd, $dir = 1): array
    {
        $modifier = $dir > 0 ? "+" : "-";

        if ($dateStart->format('d') === "01" && $dateEnd->format('d') === $dateEnd->format('t')) {
            // cycling full months
            $diffInMonths = 1 + ($dateEnd->format('Y') - $dateStart->format('Y')) * 12 + $dateEnd->format('m') - $dateStart->format('m');
            $periodStart = $dateStart->setDate($dateStart->format('Y'), $dateStart->format('m') + ($dir * $diffInMonths), 1);
            $periodEnd = $dateEnd->setDate($dateStart->format('Y'), $dateEnd->format('m') + ($dir * $diffInMonths), 5);
            $periodEnd = $periodEnd->setDate((int) $periodEnd->format('Y'), (int) $periodEnd->format('m'), (int) $periodEnd->format('t'));
        } else {
            $diffInDays = 1 + ($dateEnd->format('Y') - $dateStart->format('Y')) * 365 + ($dateEnd->format('z') - $dateStart->format('z')) ;
            $periodStart = $dateStart->modify("{$modifier}{$diffInDays} days");
            $periodEnd = $dateEnd->modify("{$modifier}{$diffInDays} days");
        }

        return [ $periodStart, $periodEnd ];
    }

    public function get_date_presets(): array
    {
        return [
            'today' => __('Today', 'koko-analytics'),
            'yesterday' => __('Yesterday', 'koko-analytics'),
            'this_week' => __('This week', 'koko-analytics'),
            'last_week' => __('Last week', 'koko-analytics'),
            'last_14_days' => __('Last 14 days', 'koko-analytics'),
            'last_28_days' => __('Last 28 days', 'koko-analytics'),
            'this_month' => __('This month', 'koko-analytics'),
            'last_month' => __('Last month', 'koko-analytics'),
            'this_year' => __('This year', 'koko-analytics'),
            'last_year' => __('Last year', 'koko-analytics'),
        ];
    }

    private function maybe_show_adblocker_notice(): void
    {
        ?>
        <div class="notice notice-warning is-dismissible" id="koko-analytics-adblock-notice" style="display: none;">
            <p>
                <?php echo esc_html__('You appear to be using an ad-blocker that has Koko Analytics on its blocklist. Please whitelist this domain in your ad-blocker setting if your dashboard does not seem to be working correctly.', 'koko-analytics'); ?>
            </p>
        </div>
        <script src="<?php echo plugins_url('/assets/dist/js/koko-analytics-script-test.js', KOKO_ANALYTICS_PLUGIN_FILE); ?>?v=<?php echo KOKO_ANALYTICS_VERSION; ?>" defer onerror="document.getElementById('koko-analytics-adblock-notice').style.display = '';"></script>
        <?php
    }
}

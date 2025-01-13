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
        add_action('wp', [$this, 'maybe_show_dashboard'], 10, 0);
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
        $stats = new Stats();
        $items_per_page = (int) apply_filters('koko_analytics_items_per_page', 20);
        $dateFormat = get_option('date_format');
        $dashboard_url = remove_query_arg(['start_date', 'end_date', 'view', 'posts', 'referrers']);

        // parse query params
        $range = isset($_GET['view']) ? $_GET['view'] : $settings['default_view'];
        $now = create_local_datetime('now');
        $week_starts_on = (int) get_option('start_of_week', 0);
        $dateRange = $this->get_dates_for_range($now, $range, $week_starts_on);

        $page = isset($_GET['p']) ? absint($_GET['p']) : 0;
        try {
            $dateStart  = isset($_GET['start_date']) ? create_local_datetime($_GET['start_date']) : $dateRange[0];
        } catch (\Exception $e) {
            $dateStart = $dateRange[0];
        }
        try {
            $dateEnd    = isset($_GET['end_date']) ? create_local_datetime($_GET['end_date']) : $dateRange[1];
        } catch (\Exception $e) {
            $dateEnd = $dateRange[1];
        }
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
            $diffInMonths = 1 + ((int) $dateEnd->format('Y') - (int) $dateStart->format('Y')) * 12 + (int) $dateEnd->format('m') - (int) $dateStart->format('m');
            $periodStart = $dateStart->setDate((int) $dateStart->format('Y'), (int) $dateStart->format('m') + ($dir * $diffInMonths), 1);
            $periodEnd = $dateEnd->setDate((int) $dateStart->format('Y'), (int) $dateEnd->format('m') + ($dir * $diffInMonths), 5);
            $periodEnd = $periodEnd->setDate((int) $periodEnd->format('Y'), (int) $periodEnd->format('m'), (int) $periodEnd->format('t'));
        } else {
            $diffInDays = 1 + ((int) $dateEnd->format('Y') - (int) $dateStart->format('Y')) * 365 + ((int) $dateEnd->format('z') - (int) $dateStart->format('z')) ;
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

    private function maybe_show_pro_notice(): void
    {
        if (! current_user_can('manage_koko_analytics')) {
            return;
        }

        new Notice_Pro();
    }

    public function get_dates_for_range(\DateTimeImmutable $now, string $key, int $week_starts_on = 0): array
    {
        switch ($key) {
            case 'today':
                return [
                    $now->modify('today midnight'),
                    $now->modify('tomorrow midnight, -1 second')
                ];
            case 'yesterday':
                return [
                    $now->modify('yesterday midnight'),
                    $now->modify('today midnight, -1 second')
                ];
            case 'this_week':
                $start = $this->get_first_day_of_current_week($now, $week_starts_on);
                return [
                    $start,
                    $start->modify('+7 days, midnight, -1 second')
                ];
            case 'last_week':
                $start = $this->get_first_day_of_current_week($now, $week_starts_on)->modify('-7 days');
                return [
                    $start,
                    $start->modify('+7 days, midnight, -1 second')
                ];
            case 'last_14_days':
                return [
                    $now->modify('-14 days'),
                    $now->modify('tomorrow midnight, -1 second')
                ];
            default:
            case 'last_28_days':
                return [
                    $now->modify('-28 days'),
                    $now->modify('tomorrow midnight, -1 second')
                ];
            case 'this_month':
                return [
                    $now->modify('first day of this month'),
                    $now->modify('last day of this month')
                ];
            case 'last_month':
                return [
                    $now->modify('first day of last month, midnight'),
                    $now->modify('last day of last month')
                ];
            case 'this_year':
                return [
                    $now->setDate((int) $now->format('Y'), 1, 1),
                    $now->setDate((int) $now->format('Y'), 12, 31),
                ];
            case 'last_year':
                return [
                    $now->setDate((int) $now->format('Y') - 1, 1, 1),
                    $now->setDate((int) $now->format('Y') - 1, 12, 31),
                ];
        }
    }

    public function get_first_day_of_current_week(\DateTimeImmutable $now, int $week_starts_on = 0): \DateTimeImmutable
    {
        if ((int) $now->format('w') === $week_starts_on) {
            return $now;
        }

        return $now->modify("last sunday, +{$week_starts_on} days");
    }
}

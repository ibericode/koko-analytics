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
        add_action('init', array($this, 'maybe_show_dashboard'), 10, 0);
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
        $dateRange = $dates->get_range($settings['default_view']);
        $items_per_page = (int) apply_filters('koko_analytics_items_per_page', 20);
        $dateFormat = get_option('date_format');

        // parse query params
        // TODO: Handle parse failures for dates
        $page = isset($_GET['p']) ? absint($_GET['p']) : 0;
        $dateStart  = isset($_GET['start_date']) ? create_local_datetime($_GET['start_date']) : $dateRange[0];
        $dateEnd    = isset($_GET['end_date']) ? create_local_datetime($_GET['end_date']) : $dateRange[1];
        $preset     = !isset($_GET['start_date']) && !isset($_GET['end_date']) ? $settings['default_view'] : 'custom';
        $posts_offset = isset($_GET['posts']['offset']) ? absint($_GET['posts']['offset']) : 0;
        $referrers_offset = isset($_GET['referrers']['offset']) ? absint($_GET['referrers']['offset']) : 0;
        $posts_limit = isset($_GET['posts']['limit']) ? absint($_GET['posts']['limit']) : $items_per_page;
        $referrers_limit = isset($_GET['referrers']['limit']) ? absint($_GET['referrers']['limit']) : $items_per_page;

        $totals = $stats->get_totals($dateStart->format('Y-m-d'), $dateEnd->format('Y-m-d'), $page);
        $posts = $stats->get_posts($dateStart->format("Y-m-d"), $dateEnd->format('Y-m-d'), $posts_offset, $posts_limit);
        $posts_count = $stats->count_posts($dateStart->format('Y-m-d'), $dateEnd->format('Y-m-d'));
        $referrers = $stats->get_referrers($dateStart->format("Y-m-d"), $dateEnd->format('Y-m-d'), $referrers_offset, $referrers_limit);
        $referrers_count = $stats->count_referrers($dateStart->format("Y-m-d"), $dateEnd->format('Y-m-d'));
        $realtime = get_realtime_pageview_count('-1 hour');

        $nextDates = $this->get_next_period($dateStart, $dateEnd, 1);
        $prevDates = $this->get_next_period($dateStart, $dateEnd, -1);

        require __DIR__ . '/views/dashboard-page.php';
    }

    private function get_next_period(\DateTimeImmutable $dateStart, \DateTimeImmutable $dateEnd, $dir = 1): array {
        if ($dateStart->format('d') === "01" && $dateEnd->format('d') === $dateEnd->format('t')) {
            // cycling full months
            $periodStart = $dir > 0 ? $dateStart->modify('first day of next month') : $dateStart->modify('first day of previous month');
            $periodEnd = $dir > 0 ? $dateEnd->modify('last day of next month') : $dateEnd->modify('last day of previous month');
        } else {
            $dateStart->setTime(0, 0, 0);
            $dateEnd->setTime(23, 59, 59);
            $diffInDays = ($dateEnd->getTimestamp() - $dateStart->getTimestamp()) / 86400;
            $modifier = $dir > 0 ? "+" : "-";
            $periodStart = $dateStart->modify("{$modifier}{$diffInDays} days");
            $periodEnd = $dateEnd->modify("{$modifier}{$diffInDays} days");
        }

        return [ $periodStart, $periodEnd ];
    }

    private function get_script_data(\DateTimeInterface $dateStart, \DateTimeInterface $dateEnd): array
    {
        $stats = new Stats();
        $items_per_page = (int) apply_filters('koko_analytics_items_per_page', 20);
        $groupChartBy = 'day';

        // TODO: Do not read from $_GET here but take a function argument
        $page = isset($_GET['p']) ? absint($_GET['p']) : 0;


        if ($dateEnd->getTimestamp() - $dateStart->getTimestamp() >= 86400 * 364) {
            $groupChartBy = 'month';
        }

        return apply_filters('koko_analytics_dashboard_script_data', array(
            'root'             => rest_url(),
            'nonce'            => wp_create_nonce('wp_rest'),
            'items_per_page'   => $items_per_page,
            'startDate' => $_GET['start_date'] ?? $dateStart->format('Y-m-d'),
            'endDate' => $_GET['end_date'] ?? $dateEnd->format('Y-m-d'),
            'i18n' => array(
                'Visitors' => __('Visitors', 'koko-analytics'),
                'Pageviews' => __('Pageviews', 'koko-analytics'),
            ),
            'data' => array(
                'chart' => $stats->get_stats($dateStart->format("Y-m-d"), $dateEnd->format('Y-m-d'), $groupChartBy, $page),
            )
        ), $dateStart, $dateEnd);
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

    private function get_usage_tip(): string
    {
        $allowed_html = [ 'a' => [ 'href' => [] ] ];
        $tips = [
            esc_html__('Use the arrow keys on your keyboard to cycle through date ranges.', 'koko-analytics'),
            esc_html__('You can set a default date range in the plugin settings.', 'koko-analytics'),
            wp_kses(\sprintf(__('Did you know there is a widget, shortcode and template function to <a href="%1s">show a list of the most viewed posts</a> on your site?', 'koko-analytics'), 'https://www.kokoanalytics.com/kb/showing-most-viewed-posts-on-your-wordpress-site/'), $allowed_html),
            wp_kses(\sprintf(__('Use <a href="%s">Koko Analytics Pro</a> to set up custom event tracking.', 'koko-analytics'), 'https://www.kokoanalytics.com/pricing/'), $allowed_html),
            wp_kses(\sprintf(__('Use <a href="%s">Koko Analytics Pro</a> to receive periodic email reports of your statistics.', 'koko-analytics'), 'https://www.kokoanalytics.com/pricing/'), $allowed_html),
        ];
        return $tips[array_rand($tips)];
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

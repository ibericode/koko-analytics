<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use DateTimeImmutable;
use DateTimeInterface;

class Dashboard
{
    public function get_base_url()
    {
        return admin_url('index.php?page=koko-analytics');
    }

    public function show()
    {
        $settings   = get_settings();
        $stats = new Stats();
        $items_per_page = (int) apply_filters('koko_analytics_items_per_page', 20);
        $date_format = get_option('date_format', 'Y-m-d');
        $dashboard_url = $this->get_base_url();

        // parse query params
        if (isset($_GET['start_date']) || isset($_GET['end_date'])) {
            $range = 'custom';
        } elseif (isset($_GET['view'])) {
            $range = trim($_GET['view']);
        } else {
            $range = $settings['default_view'];
        }
        $timezone = wp_timezone();
        $now = new DateTimeImmutable('now', $timezone);
        $week_starts_on = (int) get_option('start_of_week', 0);
        $date_range = $this->get_dates_for_range($now, $range, $week_starts_on);
        $page = isset($_GET['p']) ? trim($_GET['p']) : 0;

        try {
            $date_start  = isset($_GET['start_date']) ? new DateTimeImmutable($_GET['start_date'], $timezone) : $date_range[0];
        } catch (\Exception $e) {
            $date_start = $date_range[0];
        }
        try {
            $date_end    = isset($_GET['end_date']) ? new DateTimeImmutable($_GET['end_date'], $timezone) : $date_range[1];
        } catch (\Exception $e) {
            $date_end = $date_range[1];
        }


        [$total_start_date, $total_end_date] = $stats->get_total_date_range();

        // calculate next and previous dates for datepicker component and comparison
        $next_dates = $this->get_next_period($date_start, $date_end, 1);
        $prev_dates = $this->get_next_period($date_start, $date_end, -1);

        $date_start_str = $date_start->format('Y-m-d');
        $date_end_str = $date_end->format('Y-m-d');

        $totals = $stats->get_totals($date_start_str, $date_end_str, $page);
        $totals_previous = $stats->get_totals($prev_dates[0]->format('Y-m-d'), $prev_dates[2]->format('Y-m-d'), $page);
        $realtime = get_realtime_pageview_count('-1 hour');

        if (isset($_GET['group']) && in_array($_GET['group'], ['day', 'week', 'month', 'year'])) {
            $group_chart_by = $_GET['group'];
        } else {
            $group_chart_by = $date_end->getTimestamp() - $date_start->getTimestamp() >= 86400 * 90 ? 'month' : 'day';
        }
        $chart_data =  $stats->get_stats($date_start_str, $date_end_str, $group_chart_by, $page);

        require KOKO_ANALYTICS_PLUGIN_DIR . '/src/Resources/views/dashboard-page.php';
    }

    public function get_next_period(\DateTimeImmutable $date_start, \DateTimeImmutable $date_end, int $dir = 1): array
    {
        $now = new \DateTimeImmutable('now', wp_timezone());
        $modifier = $dir > 0 ? "+" : "-";

        if ($date_start->format('d') === "01" && $date_end->format('d') === $date_end->format('t')) {
            // cycling full months
            $diffInMonths = 1 + ((int) $date_end->format('Y') - (int) $date_start->format('Y')) * 12 + (int) $date_end->format('m') - (int) $date_start->format('m');
            $periodStart = $date_start->setDate((int) $date_start->format('Y'), (int) $date_start->format('m') + ($dir * $diffInMonths), 1);
            $periodEnd = $date_end->setDate((int) $date_start->format('Y'), (int) $date_end->format('m') + ($dir * $diffInMonths), 5);
            $periodEnd = $periodEnd->setDate((int) $periodEnd->format('Y'), (int) $periodEnd->format('m'), (int) $periodEnd->format('t'));
        } else {
            $diffInDays = $date_end->diff($date_start)->days + 1;
            $periodStart = $date_start->modify("{$modifier}{$diffInDays} days");
            $periodEnd = $date_end->modify("{$modifier}{$diffInDays} days");
        }

        if ($date_end > $now) {
            // limit end date to difference between now and start date, counting from start date
            $days_diff = $now->diff($date_start)->days;
            $compareEnd = $periodStart->modify("+{$days_diff} days");
        } else {
            $compareEnd = $periodEnd;
        }

        return [$periodStart, $periodEnd, $compareEnd];
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
            'all_time' => __('All time', 'koko-analytics'),
        ];
    }

    public function notices(): void
    {
        $this->maybe_show_adblocker_notice();
        $this->maybe_show_pro_notice();
    }

    protected function maybe_show_adblocker_notice(): void
    {
        ?>
        <div class="ka-alert ka-alert-warning ka-alert-dismissible" role="alert" id="koko-analytics-adblock-notice" style="display: none;">
            <?php echo esc_html__('You appear to be using an ad-blocker that has Koko Analytics on its blocklist. Please whitelist this domain in your ad-blocker setting if your dashboard does not seem to be working correctly.', 'koko-analytics'); ?>
            <button type="button" class="btn-close" aria-label="<?= esc_attr__('Close', 'koko-analytics') ?>" onclick="this.parentElement.remove()"></button>
        </div>
        <script src="<?php echo plugins_url('/assets/js/koko-analytics-script-test.js', KOKO_ANALYTICS_PLUGIN_FILE); ?>?v=<?php echo KOKO_ANALYTICS_VERSION; ?>" defer onerror="document.getElementById('koko-analytics-adblock-notice').style.display = '';"></script>
        <?php
    }

    protected function maybe_show_pro_notice(): void
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
            case 'all_time':
                return (new Stats())->get_total_date_range();
        }
    }

    public function get_first_day_of_current_week(\DateTimeImmutable $now, int $week_starts_on = 0): \DateTimeImmutable
    {
        if ((int) $now->format('w') === $week_starts_on) {
            return $now;
        }

        return $now->modify("last sunday, +{$week_starts_on} days");
    }

    public function get_components(): array
    {
        $components = apply_filters('koko_analytics_dashboard_components', [
            'top-pages' => [$this, 'component_pages'],
            'top-referrers' => [$this, 'component_referrers'],
        ]);

        // sort components by stored order
        $settings = get_settings();
        if (!empty($settings['component_order'])) {
            $order = $settings['component_order'];
            uksort($components, function ($a, $b) use ($order) {
                $pa = array_search($a, $order);
                $pb = array_search($b, $order);
                if ($pa === false) {
                    $pa = PHP_INT_MAX;
                }
                if ($pb === false) {
                    $pb = PHP_INT_MAX;
                }
                return $pa - $pb;
            });
        }

        return $components;
    }

    public function pagination(string $key, int $offset, int $limit, int $count): void
    {
        if ($offset >= $limit || $offset + $limit < $count) { ?>
            <div class='ka-pagination'>
                <?php if ($offset >= $limit) { ?>
                    <a class='ka-pagination--prev' href="<?php echo esc_attr(add_query_arg(['p' => null, $key => $offset >= $limit * 2 ? ['offset' => $offset - $limit, 'limit' => $limit] : null ])); ?>" rel="nofollow"><?php esc_html_e('Previous', 'koko-analytics'); ?></a>
                <?php } ?>
                <?php if ($offset + $limit < $count) { ?>
                    <a class='ka-pagination--next' href="<?php echo esc_attr(add_query_arg(['p' => null, $key => ['offset' => $offset + $limit, 'limit' => $limit]])); ?>" rel="nofollow"><?php esc_html_e('Next', 'koko-analytics'); ?></a>
                <?php } ?>
            </div>
        <?php }
    }

    public function component_pages(DateTimeInterface $date_start, DateTimeInterface $date_end): void
    {
        $items_per_page = (int) apply_filters('koko_analytics_items_per_page', 20);
        $offset = isset($_GET['posts']['offset']) ? absint($_GET['posts']['offset']) : 0;
        $limit = isset($_GET['posts']['limit']) ? absint($_GET['posts']['limit']) : $items_per_page;
        $page = isset($_GET['p']) ? trim($_GET['p']) : 0;

        $stats = new Stats();
        $posts = $stats->get_posts($date_start, $date_end, $offset, $limit);
        if (count($posts) < $limit && $offset === 0) {
            $count = count($posts);
            $sum = array_sum(array_column($posts, 'pageviews'));
        } else {
            $count = $stats->count_posts($date_start, $date_end);
            $sum = $stats->sum_posts($date_start, $date_end);
        }
        ?>
        <table class="ka-table">
            <thead>
                <tr>
                    <th style="width: 3ch;" scope="col">#</th>
                    <th class="w-expand" scope="col"><?php esc_html_e('Pages', 'koko-analytics'); ?></th>
                    <th title="<?= esc_attr__('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics'); ?>" class="text-end d-none d-lg-table-cell w-fit text-truncate" scope="row"><?php esc_html_e('Visitors', 'koko-analytics'); ?></th>
                    <th title="<?= esc_attr__('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics'); ?>" class="text-end ka-pageviews w-fit text-truncate" scope="col"><?php esc_html_e('Pageviews', 'koko-analytics'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $i => $p) { ?>
                    <?php $pct = $sum > 0 && $page === 0  ? round(($p->pageviews / $sum) * 100, 0) : 0; ?>
                    <tr <?= $page == $p->path ? 'class="page-filter-active"' : ''; ?> style="background: linear-gradient(to right, var(--koko-analytics-row-gradient-color) <?= $pct ?>%, transparent <?= $pct ?>%);">
                        <td class="text-muted"><?=  $offset + $i + 1; ?></td>
                        <td class="text-truncate">
                            <a href="<?= esc_attr(add_query_arg(['p' => $p->path])); ?>"><?= esc_html($p->label); ?></a>
                            <a class="ka-visit-link" href="<?= esc_attr(esc_url($p->post_permalink)); ?>" target="_blank" rel="noopener" title="<?php esc_attr_e('View page', 'koko-analytics'); ?>"><i class="icon icon-sm icon-external-link" aria-hidden="true"></i></a>
                        </td>
                        <td class="text-end d-none d-lg-table-cell"><?= number_format_i18n(max(1, $p->visitors)); ?></td>
                        <td class="text-end"><?= number_format_i18n($p->pageviews); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if (empty($posts)) { ?>
            <p class="ka-empty-state"><?php esc_html_e('There is nothing here. Yet!', 'koko-analytics'); ?></p>
        <?php } ?>

        <?php $this->pagination('posts', $offset, $limit, $count); ?>
            
        <?php
    }

    public function component_referrers(DateTimeInterface $date_start, DateTimeInterface $date_end): void
    {
        $items_per_page = (int) apply_filters('koko_analytics_items_per_page', 20);
        $referrers_offset = isset($_GET['referrers']['offset']) ? absint($_GET['referrers']['offset']) : 0;
        $referrers_limit = isset($_GET['referrers']['limit']) ? absint($_GET['referrers']['limit']) : $items_per_page;
        $stats = new Stats();
        $referrers = $stats->get_referrers($date_start, $date_end, $referrers_offset, $referrers_limit);
        if (count($referrers) < $referrers_limit && $referrers_offset === 0) {
            $referrers_count = count($referrers);
            $referrers_sum = array_sum(array_column($referrers, 'pageviews'));
        } else {
            $referrers_count = $stats->count_referrers($date_start, $date_end);
            $referrers_sum = $stats->sum_referrers($date_start, $date_end);
        }
        ?>
        <table class="ka-table">
            <thead>
                <tr>
                    <th scope="col" style="width: 3ch;">#</th>
                    <th scope="col"><?php esc_html_e('Referrers', 'koko-analytics'); ?></th>
                    <th scope="col" title="<?= esc_attr__('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics'); ?>" class="text-end d-none d-lg-table-cell w-fit text-truncate" style=""><?php esc_html_e('Visitors', 'koko-analytics'); ?></th>
                    <th scope="col" title="<?= esc_attr__('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics'); ?>" class="text-end text-truncate w-fit ka-pageviews"><?php esc_html_e('Pageviews', 'koko-analytics'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($referrers as $i => $r) { ?>
                    <?php $pct = $referrers_sum > 0 ? round(($r->pageviews / $referrers_sum) * 100, 0) : 0; ?>
                    <tr style="background: linear-gradient(to right, var(--koko-analytics-row-gradient-color) <?= $pct ?>%, transparent <?= $pct ?>%);">
                        <td class="text-muted"><?= $referrers_offset + $i + 1; ?></td>
                        <td class="text-truncate"><?= Fmt::referrer_url_label(esc_html($r->url)); ?></td>
                        <td class="text-end d-none d-lg-table-cell"><?= number_format_i18n(max(1, $r->visitors)); ?></td>
                        <td class="text-end"><?= number_format_i18n($r->pageviews); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if (empty($referrers)) { ?>
            <p class="ka-empty-state"><?php esc_html_e('There is nothing here. Yet!', 'koko-analytics'); ?></p>
        <?php } ?>

        <?php $this->pagination('referrers', $referrers_offset, $referrers_limit, $referrers_count); ?>
        
        <?php
    }
}

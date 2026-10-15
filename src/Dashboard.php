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
    public const MAX_LIMIT  = 100;
    public const MAX_OFFSET = 10000;

    public static function clamp_limit($value, int $default = 10, int $minimum = 1): int
    {
        $limit = isset($value) ? absint($value) : $default;
        return min(self::MAX_LIMIT, max($minimum, $limit));
    }

    public static function clamp_offset($value): int
    {
        return min(self::MAX_OFFSET, isset($value) ? absint($value) : 0);
    }

    public function get_base_url()
    {
        return admin_url('index.php?page=koko-analytics');
    }

    public function show()
    {
        $settings       = get_settings();
        $stats          = new Stats();
        $items_per_page = (int) apply_filters('koko_analytics_items_per_page', 20);
        $date_format    = get_option('date_format', 'Y-m-d');
        $date_presets   = $this->get_date_presets();
        $default_range  = isset($date_presets[$settings['default_view']]) ? $settings['default_view'] : 'last_28_days';

        // parse query params
        if (isset($_GET['start_date']) || isset($_GET['end_date'])) {
            $range = 'custom';
        } elseif (isset($_GET['view'])) {
            $requested_range = sanitize_key(wp_unslash($_GET['view']));
            $range           = isset($date_presets[$requested_range]) ? $requested_range : $default_range;
        } else {
            $range = $default_range;
        }
        $timezone       = wp_timezone();
        $now            = new DateTimeImmutable('now', $timezone);
        $week_starts_on = (int) get_option('start_of_week', 0);
        $preset_range   = $range === 'custom' ? $default_range : $range;
        [$total_start_date, $total_end_date] = $stats->get_total_date_range();
        $date_range = $preset_range === 'all_time'
            ? [$total_start_date, $total_end_date]
            : $this->get_dates_for_range($now, $preset_range, $week_starts_on);
        $page           = isset($_GET['p']) ? trim(wp_unslash($_GET['p'])) : 0;

        try {
            $date_start = isset($_GET['start_date']) ? new DateTimeImmutable(wp_unslash($_GET['start_date']), $timezone) : $date_range[0];
        } catch (\Exception $e) {
            $date_start = $date_range[0];
        }
        try {
            $date_end = isset($_GET['end_date']) ? new DateTimeImmutable(wp_unslash($_GET['end_date']), $timezone) : $date_range[1];
        } catch (\Exception $e) {
            $date_end = $date_range[1];
        }

        // calculate next and previous dates for datepicker component and comparison
        $period_dates = in_array($range, ['custom', 'all_time'], true) ? [$date_start, $date_end] : $this->get_dates_for_range($now, $range, $week_starts_on, true);
        $cycle_by_month = $range === 'custom' ? null : in_array($range, ['this_month', 'last_month', 'this_year', 'last_year', 'last_12_months'], true);
        $next_dates     = $this->get_next_period($period_dates[0], $period_dates[1], 1, $cycle_by_month, $now);
        $prev_dates     = $this->get_next_period($period_dates[0], $period_dates[1], -1, $cycle_by_month, $now);

        $date_start_str = $date_start->format('Y-m-d');
        $date_end_str   = $date_end->format('Y-m-d');

        $totals          = $stats->get_totals($date_start_str, $date_end_str, $page);
        $totals_previous = $stats->get_totals($prev_dates[0]->format('Y-m-d'), $prev_dates[2]->format('Y-m-d'), $page);
        $realtime        = get_realtime_pageview_count('-1 hour');

        if (isset($_GET['group']) && in_array(wp_unslash($_GET['group']), ['day', 'week', 'month', 'year'])) {
            $group_chart_by = wp_unslash($_GET['group']);
        } else {
            $group_chart_by = $date_start->diff($date_end)->days > 90 ? 'month' : 'day';
        }
        $chart_data =  $stats->get_stats($date_start_str, $date_end_str, $group_chart_by, $page);

        require KOKO_ANALYTICS_PLUGIN_DIR . '/src/Resources/views/dashboard-page.php';
    }

    public function get_next_period(\DateTimeImmutable $date_start, \DateTimeImmutable $date_end, int $dir = 1, ?bool $cycle_by_month = null, ?\DateTimeImmutable $now = null): array
    {
        $now      = $now ?? new \DateTimeImmutable('now', wp_timezone());
        $modifier = $dir > 0 ? "+" : "-";

        if ($cycle_by_month ?? ($date_start->format('d') === "01" && $date_end->format('d') === $date_end->format('t'))) {
            // cycling full months
            $diff_in_months = 1 + ((int) $date_end->format('Y') - (int) $date_start->format('Y')) * 12 + (int) $date_end->format('m') - (int) $date_start->format('m');
            $period_start   = $date_start->modify("{$modifier}{$diff_in_months} months");
            $period_end     = $date_end->setDate((int) $date_end->format('Y'), (int) $date_end->format('m'), 1)->modify("{$modifier}{$diff_in_months} months");
            $period_end     = $period_end->setDate((int) $period_end->format('Y'), (int) $period_end->format('m'), (int) $period_end->format('t'));
        } else {
            $diff_in_days = $date_end->diff($date_start)->days + 1;
            $period_start = $date_start->modify("{$modifier}{$diff_in_days} days");
            $period_end   = $date_end->modify("{$modifier}{$diff_in_days} days");
        }

        if ($date_end > $now) {
            // limit end date to difference between now and start date, counting from start date
            $days_diff  = $now->diff($date_start)->days;
            $compare_end = $period_start->modify("+{$days_diff} days");
            if ($compare_end > $period_end) {
                $compare_end = $period_end;
            }
        } else {
            $compare_end = $period_end;
        }

        return [$period_start, $period_end, $compare_end];
    }

    public function get_date_presets(): array
    {
        return array_merge(...array_column($this->get_date_preset_groups(), 'presets'));
    }

    public function get_date_preset_groups(): array
    {
        return [
            [
                'label' => __('Days and rolling windows', 'koko-analytics'),
                'presets' => [
                    'today' => __('Today', 'koko-analytics'),
                    'yesterday' => __('Yesterday', 'koko-analytics'),
                    'last_7_days' => __('Last 7 days', 'koko-analytics'),
                    'last_14_days' => __('Last 14 days', 'koko-analytics'),
                    'last_28_days' => __('Last 28 days', 'koko-analytics'),
                    'last_91_days' => __('Last 91 days', 'koko-analytics'),
                ],
            ],
            [
                'label' => __('Current calendar periods', 'koko-analytics'),
                'presets' => [
                    'this_week' => __('Week to date', 'koko-analytics'),
                    'this_month' => __('Month to date', 'koko-analytics'),
                    'this_year' => __('Year to date', 'koko-analytics'),
                ],
            ],
            [
                'label' => __('Completed calendar periods', 'koko-analytics'),
                'presets' => [
                    'last_week' => __('Last week', 'koko-analytics'),
                    'last_month' => __('Last month', 'koko-analytics'),
                    'last_year' => __('Last year', 'koko-analytics'),
                ],
            ],
            [
                'label' => __('Longer history', 'koko-analytics'),
                'presets' => [
                    'last_12_months' => __('Last 12 months', 'koko-analytics'),
                    'all_time' => __('All time', 'koko-analytics'),
                ],
            ],
        ];
    }

    public function notices(): void
    {
        $this->maybe_show_adblocker_notice();
        $this->maybe_show_review_notice();
    }

    protected function maybe_show_adblocker_notice(): void
    {
        ?>
        <div class="ka-alert ka-alert-warning ka-alert-dismissible" role="alert" id="koko-analytics-adblock-notice" style="display: none;">
            <?php esc_html_e('You appear to be using an ad-blocker that has Koko Analytics on its blocklist. Please whitelist this domain in your ad-blocker setting if your dashboard does not seem to be working correctly.', 'koko-analytics'); ?>
            <button type="button" class="btn-close" aria-label="<?php esc_attr_e('Close', 'koko-analytics'); ?>" onclick="this.parentElement.remove()"></button>
        </div>
        <script src="<?php echo esc_url(plugins_url('/assets/js/koko-analytics-script-test.js', KOKO_ANALYTICS_PLUGIN_FILE)); ?>?v=<?php echo esc_attr(KOKO_ANALYTICS_VERSION); ?>" defer onerror="document.getElementById('koko-analytics-adblock-notice').style.display = '';"></script>
        <?php
    }

    protected function maybe_show_review_notice(): void
    {
        // Don't ask for a review when the Pro add-on is already installed.
        if (defined('KOKO_ANALYTICS_PRO_VERSION')) {
            return;
        }

        $notice = new Review_Notice(
            'koko-analytics',
            'koko_analytics_settings',
            fn (): array => [
                /* translators: %s is the plugin name. */
                'heading'      => sprintf(__('Enjoying %s?', 'koko-analytics'), 'Koko Analytics'),
                'body'         => __('A quick review on WordPress.org helps more people find the plugin and helps us keep maintaining it for the long term.', 'koko-analytics'),
                'review_link'  => __('Review the plugin on WordPress.org', 'koko-analytics'),
                'dismiss_link' => __('Don\'t show this again', 'koko-analytics'),
            ],
            'notice_pro',
            'manage_koko_analytics'
        );
        $notice->maybe_show();
    }

    public function get_dates_for_range(\DateTimeImmutable $now, string $key, int $week_starts_on = 0, bool $complete_current_period = false): array
    {
        $end_of_today = $now->modify('tomorrow midnight, -1 second');
        $rolling_days = [
            'last_7_days' => 7,
            'last_14_days' => 14,
            'last_28_days' => 28,
            'last_91_days' => 91,
        ];
        if (isset($rolling_days[$key])) {
            $days = $rolling_days[$key] - 1;
            return [
                $now->modify("-{$days} days, midnight"),
                $end_of_today,
            ];
        }

        switch ($key) {
            case 'today':
                return [
                    $now->modify('today midnight'),
                    $end_of_today,
                ];
            case 'yesterday':
                return [
                    $now->modify('yesterday midnight'),
                    $now->modify('today midnight, -1 second'),
                ];
            case 'this_week':
                $start = $this->get_first_day_of_current_week($now, $week_starts_on)->modify('today midnight');
                return [
                    $start,
                    $complete_current_period ? $start->modify('+7 days, midnight, -1 second') : $end_of_today,
                ];
            case 'last_week':
                $start = $this->get_first_day_of_current_week($now, $week_starts_on)->modify('-7 days, midnight');
                return [
                    $start,
                    $start->modify('+7 days, midnight, -1 second'),
                ];
            case 'this_month':
                return [
                    $now->modify('first day of this month, midnight'),
                    $complete_current_period ? $now->modify('first day of next month, midnight, -1 second') : $end_of_today,
                ];
            case 'last_month':
                return [
                    $now->modify('first day of last month, midnight'),
                    $now->modify('first day of this month, midnight, -1 second'),
                ];
            case 'this_year':
                return [
                    $now->setDate((int) $now->format('Y'), 1, 1)->setTime(0, 0),
                    $complete_current_period ? $now->setDate((int) $now->format('Y'), 12, 31)->setTime(23, 59, 59) : $end_of_today,
                ];
            case 'last_year':
                return [
                    $now->setDate((int) $now->format('Y') - 1, 1, 1)->setTime(0, 0),
                    $now->setDate((int) $now->format('Y') - 1, 12, 31)->setTime(23, 59, 59),
                ];
            case 'last_12_months':
                return [
                    $now->modify('first day of this month, midnight')->modify('-11 months'),
                    $complete_current_period ? $now->modify('first day of next month, midnight, -1 second') : $end_of_today,
                ];
            case 'all_time':
                return (new Stats())->get_total_date_range();
            default:
                return [
                    $now->modify('-27 days, midnight'),
                    $end_of_today,
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

    /**
     * Renders the pagination component for a dashboard component.
     *
     * @param string $key        The query string key holding the offset & limit for this component.
     * @param array  $extra_args Additional query args to preserve in the previous/next links.
     * @param bool|null $crawlable Whether to render crawlable links. Defaults to true in the admin area.
     */
    public static function pagination(string $key, int $offset, int $limit, int $count, array $extra_args = [], ?bool $crawlable = null): void
    {
        $crawlable = $crawlable ?? is_admin();
        if ($offset >= $limit || $offset + $limit < $count) {
            ?>
        <div class="ka-pagination2">
            <span class="ka-pagination2-muted">
                <?php
                /* translators: 1: first result number, 2: last result number, 3: total number of results. */
                printf(esc_html__('%1$d – %2$d of %3$d', 'koko-analytics'), (int) $offset + 1, (int) min($count, $offset + $limit), (int) $count);
                ?>
            </span>
            <?php if ($crawlable) : ?>
            <span>
            <?php else : ?>
            <span data-pagination-controls hidden>
            <?php endif; ?>
                <?php if ($offset >= $limit) : ?>
                    <?php $args = $offset >= $limit * 2 ? array_merge($extra_args, ['offset' => $offset - $limit, 'limit' => $limit]) : $extra_args; ?>
                    <?php if ($crawlable) : ?>
                    <a  href="<?php echo esc_attr(dashboard_url(['p' => null, $key => $args ?: null ])); ?>" rel="nofollow">← <?php esc_html_e('Previous', 'koko-analytics'); ?></a>
                    <?php else : ?>
                    <button type="button" class="ka-link-button" data-pagination-key="<?= esc_attr($key); ?>" data-pagination-query="<?= esc_attr(http_build_query([$key => $args], '', '&')); ?>">← <?php esc_html_e('Previous', 'koko-analytics'); ?></button>
                    <?php endif; ?>
                <?php else : ?>
                    <span class="ka-pagination2-muted">← <?php esc_html_e('Previous', 'koko-analytics'); ?></span>
                <?php endif; ?>
                <span> · </span>
                <?php if ($offset + $limit < $count) : ?>
                    <?php $args = array_merge($extra_args, ['offset' => $offset + $limit, 'limit' => $limit]); ?>
                    <?php if ($crawlable) : ?>
                <a  href="<?php echo esc_attr(dashboard_url(['p' => null, $key => $args])); ?>" rel="nofollow"><?php esc_html_e('Next', 'koko-analytics'); ?> →</a>
                    <?php else : ?>
                    <button type="button" class="ka-link-button" data-pagination-key="<?= esc_attr($key); ?>" data-pagination-query="<?= esc_attr(http_build_query([$key => $args], '', '&')); ?>"><?php esc_html_e('Next', 'koko-analytics'); ?> →</button>
                    <?php endif; ?>
                <?php else : ?>
                    <span class="ka-pagination2-muted"><?php esc_html_e('Next', 'koko-analytics'); ?> →</span>
                <?php endif; ?>
            </span>
        </div>
            <?php
        }
    }

    public function component_pages(DateTimeInterface $date_start, DateTimeInterface $date_end): void
    {
        $items_per_page = (int) apply_filters('koko_analytics_items_per_page', 20);
        $offset         = self::clamp_offset(wp_unslash($_GET['posts']['offset'] ?? null));
        $limit          = self::clamp_limit(wp_unslash($_GET['posts']['limit'] ?? null), $items_per_page);
        $page           = isset($_GET['p']) ? trim(wp_unslash($_GET['p'])) : 0;

        $stats = new Stats();
        $posts = $stats->get_posts($date_start, $date_end, $offset, $limit);
        if (count($posts) < $limit && $offset === 0) {
            $count = count($posts);
            $sum   = array_sum(array_column($posts, 'pageviews'));
        } else {
            $count = $stats->count_posts($date_start, $date_end);
            $sum   = $stats->sum_posts($date_start, $date_end);
        }
        ?>
        <div class="ka-card-head">
            <div>
                <div class="ka-card-head-title"><?php esc_html_e('Top pages', 'koko-analytics'); ?></div>
                <div class="ka-card-head-desc"><?php esc_html_e('Most viewed content this period', 'koko-analytics'); ?></div>
            </div>
        </div>
        <table class="ka-table">
            <thead>
                <tr>
                    <th style="width: 3ch;" scope="col">#</th>
                    <th class="w-expand" scope="col"><?php esc_html_e('Pages', 'koko-analytics'); ?></th>
                    <th title="<?php esc_attr_e('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics'); ?>" class="text-end d-none d-lg-table-cell w-fit text-truncate" scope="row"><?php esc_html_e('Visitors', 'koko-analytics'); ?></th>
                    <th title="<?php esc_attr_e('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics'); ?>" class="text-end ka-pageviews w-fit text-truncate" scope="col"><?php esc_html_e('Pageviews', 'koko-analytics'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $i => $p) { ?>
                    <?php $page_filter = $p->post_id > 0 ? (string) $p->post_id : $p->path; ?>
                    <?php $pct = $sum > 0 && $page === 0  ? round(($p->pageviews / $sum) * 100, 0) : 0; ?>
                    <tr <?= (string) $page === $page_filter ? 'class="page-filter-active"' : ''; ?> style="background: linear-gradient(to right, var(--koko-analytics-row-gradient-color) <?= esc_attr((string) $pct); ?>%, transparent <?= esc_attr((string) $pct); ?>%);">
                        <td class="text-muted"><?= esc_html((string) ($offset + $i + 1)); ?></td>
                        <td class="text-truncate">
                            <a href="<?= esc_attr(dashboard_url(['p' => $page_filter])); ?>"><?= esc_html($p->label); ?></a>
                            <a class="ka-visit-link" href="<?= esc_url($p->post_permalink); ?>" target="_blank" rel="noopener" title="<?php esc_attr_e('View page', 'koko-analytics'); ?>"><i class="icon icon-sm icon-external-link" aria-hidden="true"></i></a>
                        </td>
                        <td class="text-end d-none d-lg-table-cell"><?= esc_html(number_format_i18n(max(1, $p->visitors))); ?></td>
                        <td class="text-end"><?= esc_html(number_format_i18n($p->pageviews)); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if (empty($posts)) { ?>
            <p class="ka-empty-state"><?php esc_html_e('There is nothing here. Yet!', 'koko-analytics'); ?></p>
        <?php } ?>

        <?php self::pagination('posts', $offset, $limit, $count); ?>
            
        <?php
    }

    public function component_referrers(DateTimeInterface $date_start, DateTimeInterface $date_end): void
    {
        $items_per_page = (int) apply_filters('koko_analytics_items_per_page', 20);
        $offset         = self::clamp_offset(wp_unslash($_GET['referrers']['offset'] ?? null));
        $limit          = self::clamp_limit(wp_unslash($_GET['referrers']['limit'] ?? null), $items_per_page);
        $stats          = new Stats();
        $referrers      = $stats->get_referrers($date_start, $date_end, $offset, $limit);
        if (count($referrers) < $limit && $offset === 0) {
            $count = count($referrers);
            $sum   = array_sum(array_column($referrers, 'pageviews'));
        } else {
            $count = $stats->count_referrers($date_start, $date_end);
            $sum   = $stats->sum_referrers($date_start, $date_end);
        }
        ?>
        <div class="ka-card-head">
            <div>
                <div class="ka-card-head-title"><?php esc_html_e('Top referrers', 'koko-analytics'); ?></div>
                <div class="ka-card-head-desc"><?php esc_html_e('Where your visitors came from', 'koko-analytics'); ?></div>
            </div>
        </div>
        <table class="ka-table">
            <thead>
                <tr>
                    <th scope="col" style="width: 3ch;">#</th>
                    <th scope="col"><?php esc_html_e('Referrers', 'koko-analytics'); ?></th>
                    <th scope="col" title="<?php esc_attr_e('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics'); ?>" class="text-end d-none d-lg-table-cell w-fit text-truncate" style=""><?php esc_html_e('Visitors', 'koko-analytics'); ?></th>
                    <th scope="col" title="<?php esc_attr_e('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics'); ?>" class="text-end text-truncate w-fit ka-pageviews"><?php esc_html_e('Pageviews', 'koko-analytics'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($referrers as $i => $r) { ?>
                    <?php $pct = $sum > 0 ? round(($r->pageviews / $sum) * 100, 0) : 0; ?>
                    <tr style="background: linear-gradient(to right, var(--koko-analytics-row-gradient-color) <?= esc_attr((string) $pct); ?>%, transparent <?= esc_attr((string) $pct); ?>%);">
                        <td class="text-muted"><?= esc_html((string) ($offset + $i + 1)); ?></td>
                        <td class="text-truncate"><?= esc_html(Fmt::referrer_url_label($r->url)); ?></td>
                        <td class="text-end d-none d-lg-table-cell"><?= esc_html(number_format_i18n(max(1, $r->visitors))); ?></td>
                        <td class="text-end"><?= esc_html(number_format_i18n($r->pageviews)); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <?php if (empty($referrers)) { ?>
            <p class="ka-empty-state"><?php esc_html_e('There is nothing here. Yet!', 'koko-analytics'); ?></p>
        <?php } ?>

        <?php self::pagination('referrers', $offset, $limit, $count); ?>
        
        <?php
    }
}

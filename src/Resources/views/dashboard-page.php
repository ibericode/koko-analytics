<?php

use KokoAnalytics\Chart_View;
use KokoAnalytics\Fmt;

defined('ABSPATH') || exit;


/**
 * @var KokoAnalytics\Dashboard $this
 * @var \DateTimeInterface $date_start
 * @var \DateTimeInterface $date_end
 * @var object $totals
 * @var int $realtime
 * @var string $date_format
 * @var string $dashboard_url
 * @var array $next_dates
 * @var array $prev_dates
 */

$tab = 'dashboard';

?>
<div class="koko-analytics ka-wrap">
    <?php $this->notices(); ?>

    <div class="d-lg-flex">
        <div class="d-flex gap-3 mb-3">
            <div class="position-relative">
                <div class="ka-filter" tabindex="0" role="button" aria-expanded="false" aria-controls="ka-datepicker-dropdown" onclick="var el = document.getElementById('ka-datepicker-dropdown'); el.style.display = el.offsetParent === null ? 'block' : 'none'; this.ariaExpanded =  el.offsetParent === null ? 'false' : 'true';">
                    <i class="icon icon-calendar me-2"></i>
                    <?php
                    echo esc_html(wp_date($date_format, $date_start->getTimestamp()));
                    ?>
                    — <?php echo esc_html(wp_date($date_format, $date_end->getTimestamp())); ?>
                </div>

                <div id="ka-datepicker-dropdown" class="rounded bg-white shadow" style="display: none; position: absolute; width:360px; z-index: 9992;">
                    <div class="mb-3 bg-dark text-white p-3 rounded-top fw-bold d-flex justify-content-between">
                        <?php
                        // only output pagination for date ranges between reasonable dates... to prevent ever-crawling bots from going wild
                        ?>
                        <?php if ($date_start > $total_start_date) { ?>
                            <a class="js-quicknav-prev text-decoration-none text-white me-2" href="" data-href="<?= esc_attr(add_query_arg(['start_date' => $prev_dates[0]->format('Y-m-d'), 'end_date' => $prev_dates[1]->format('Y-m-d')], $dashboard_url)); ?>" rel="nofollow">◂</a>
                        <?php } else { ?>
                            <a class="text-decoration-none text-white me-2">◂</a>
                        <?php } ?>
                        <span><?php echo esc_html(wp_date($date_format, $date_start->getTimestamp())); ?> — <?= esc_html(wp_date($date_format, $date_end->getTimestamp())); ?></span>
                        <?php if ($date_end < $total_end_date) { ?>
                            <a class="js-quicknav-next text-decoration-none text-white ms-2" href="" data-href="<?= esc_attr(add_query_arg(['start_date' => $next_dates[0]->format('Y-m-d'), 'end_date' => $next_dates[1]->format('Y-m-d')], $dashboard_url)); ?>" rel="nofollow">▸</a>
                        <?php } else { ?>
                            <a class="text-decoration-none text-white ms-2">▸</a>
                        <?php } ?>
                    </div>
                    <form method="get" class="p-3 pt-0">
                        <?php
                        foreach (['page', 'p', 'koko-analytics-dashboard'] as $key) {
                            if (isset($_GET[$key])) {
                                echo '<input type="hidden" name="', esc_attr($key), '" value="', esc_attr(wp_unslash($_GET[$key])), '">';
                            }
                        }
                        ?>

                        <div class="mb-3">
                            <label for="ka-date-presets" class="ka-label"><?php esc_html_e('Date range', 'koko-analytics'); ?></label>
                            <select id="ka-date-presets" name="view" class="ka-select">
                                <option value="custom" <?= $range === 'custom' ? 'selected' : ''; ?> disabled><?php esc_html_e('Custom', 'koko-analytics'); ?></option>
                                <?php
                                foreach ($this->get_date_presets() as $key => $label) :
                                    ?>
                                    <option value="<?= esc_attr($key); ?>" <?= ($key === $range) ? ' selected' : ''; ?>><?= esc_html($label); ?></option>
                                    <?php
                                endforeach;
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for='ka-date-start' class="ka-label"><?php esc_html_e('Start date', 'koko-analytics'); ?></label>
                            <input name="start_date" id='ka-date-start' type="date" size="10" min="2000-01-01" max="2100-01-01"
                                value="<?= esc_attr($date_start->format('Y-m-d')); ?>" class="ka-input">
                        </div>
                        <div class="mb-3">
                            <label for='ka-date-end' class="ka-label"><?php esc_html_e('End date', 'koko-analytics'); ?></label>
                            <input name="end_date" id='ka-date-end' type="date" size="10" min="2000-01-01" max="2100-01-01"
                                value="<?= esc_attr($date_end->format('Y-m-d')); ?>" class="ka-input">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary"><?php esc_html_e('Submit', 'koko-analytics'); ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="ka-filter" <?= $page === 0 ? 'style="display: none;"' : ''; ?>>
                <?php esc_html_e('Page', 'koko-analytics'); ?> =
                <?php $filtered_post_id = is_string($page) && ctype_digit($page) ? (int) $page : 0; ?>
                <?php $filtered_permalink = $filtered_post_id > 0 ? get_permalink($filtered_post_id) : home_url($page); ?>
                <?php $filtered_label = $filtered_post_id > 0 ? get_the_title($filtered_post_id) : $page; ?>
                <a class="" href="<?= esc_url($filtered_permalink); ?>"><?= esc_html($filtered_label); ?></a>
                <a class="text-decoration-none text-reset ms-2" aria-label="<?php esc_attr_e('Clear page filter', 'koko-analytics'); ?>" title="<?php esc_attr_e('Clear page filter', 'koko-analytics'); ?>" href="<?= esc_attr(remove_query_arg('p')); ?>">✕</a>
            </div>

            <?php do_action('koko_analytics_after_datepicker', $date_start, $date_end); ?>
        </div>

        <?php require __DIR__ . '/nav.php'; ?>
    </div>

    <?php /* totals component */ ?>
    <div class="ka-row ka-row-cols-1 ka-row-cols-lg-3 g-3 mb-3">
        <?php
        /* Total visitors */
        $diff   = $totals->visitors - $totals_previous->visitors;
        $change = $totals_previous->visitors == 0 ? 0 : ($totals->visitors / $totals_previous->visitors) - 1;
        ?>
        <div class="ka-col">
            <div class="ka-card ka-kpi">
                <div class="ka-kpi-top">
                    <div class="ka-kpi-label"><?php esc_html_e('Visitors', 'koko-analytics'); ?></div>
                    <div class="ka-kpi-delta <?= ($diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'neutral')); ?>"><?= esc_html(Fmt::percent($change)); ?></div>
                </div>
                <div class="ka-kpi-value" title="<?= esc_attr($totals->visitors); ?>">
                    <?= esc_html(number_format_i18n($totals->visitors)); ?>
                </div>
                <div class="ka-kpi-cap">
                    <?php
                    if ($diff != 0) {
                        echo esc_html(number_format_i18n(abs($diff)));
                        echo ' ';
                    }
                    if ($diff > 0) {
                        esc_html_e('more than previous period', 'koko-analytics');
                    }
                    if ($diff < 0) {
                        esc_html_e('less than previous period', 'koko-analytics');
                    }
                    ?>
                </div>
            </div>
        </div>
        <?php
        /* Total pageviews */
        $diff   = $totals->pageviews - $totals_previous->pageviews;
        $change = $totals_previous->pageviews == 0 ? 0 : ($totals->pageviews / $totals_previous->pageviews) - 1;
        ?>
        <div class="ka-col">
            <div class="ka-card ka-kpi">
                <div class="ka-kpi-top">
                    <div class="ka-kpi-label"><?php esc_html_e('Pageviews', 'koko-analytics'); ?></div>
                    <div class="ka-kpi-delta <?= ($diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'neutral')); ?>"><?= esc_html(Fmt::percent($change)); ?></div>
                </div>
                <div class="ka-kpi-value" title="<?= esc_attr($totals->pageviews); ?>">
                    <?= esc_html(number_format_i18n($totals->pageviews)); ?>
                </div>
                <div class="ka-kpi-cap">
                    <?php
                    if ($diff != 0) {
                        echo esc_html(number_format_i18n(abs($diff)));
                        echo ' ';
                    }
                    if ($diff > 0) {
                        esc_html_e('more than previous period', 'koko-analytics');
                    }
                    if ($diff < 0) {
                        esc_html_e('less than previous period', 'koko-analytics');
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="ka-col">
            <div class="ka-card ka-kpi <?= $page !== 0 ? 'page-filter-active' : ''; ?>" id="ka-realtime">
                <div class="ka-kpi-top">
                    <div class="ka-kpi-label"><?php esc_html_e('Realtime', 'koko-analytics'); ?></div>
                    <div class="ka-kpi-live"><span class="ka-realtime-dot"></span> Live</div>
                </div>
                <div class="ka-kpi-value"><?= esc_html(number_format_i18n($realtime)); ?></div>
                <div class="ka-kpi-cap">
                    <?php esc_html_e('pageviews in the last hour', 'koko-analytics'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php /* end totals component */ ?>

    <?php /* CHART COMPONENT */ ?>
    <?php if (count($chart_data) > 1) { ?>
        <div class="ka-card mb-3">
            <?php new Chart_View($chart_data, $date_start, $date_end, 280, true, $group_chart_by); ?>
        </div>
    <?php } ?>

    <?php $can_sort = current_user_can('manage_koko_analytics'); ?>
    <div id="ka-components" class="ka-row ka-row-cols-1 ka-row-cols-xl-2 g-3 mb-3 <?= $page !== 0 ? 'page-filter-active' : ''; ?>" <?= $can_sort ? 'data-nonce="' . esc_attr(wp_create_nonce('koko_analytics_save_component_order')) . '"' : ''; ?>>
        <?php foreach ($this->get_components() as $id => $callback) : ?>
            <div id="<?= esc_attr($id); ?>" class="ka-col" <?= $can_sort ? 'data-sortable' : ''; ?>>
                <div class="ka-card">
                    <?php $callback($date_start, $date_end); ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php do_action_deprecated('koko_analytics_show_dashboard_components', [], '1.4', 'koko_analytics_after_dashboard_components'); ?>
        <?php do_action_deprecated('koko_analytics_after_dashboard_components', [$date_start, $date_end], '2.3', 'koko_analytics_dashboard_components'); ?>
    </div>
    <?php
    // end div.ka-row
    ?>

    <?php // show section about koko analytics pro unless on pro version already ?>
    <?php if (!defined('KOKO_ANALYTICS_PRO_VERSION')) : ?>
        <?php if (current_user_can('manage_koko_analytics')) : ?>
            <?php require __DIR__ . '/upsell.php'; ?>
        <?php else : ?>
            <div class="text-muted text-center mt-3">
                <?php /* translators: %1s: opening anchor tag for Koko Analytics website. */ ?>
                <?php echo wp_kses(sprintf(__('Powered by %1s - privacy-friendly analytics for WordPress sites', 'koko-analytics'), '<a href="https://www.kokoanalytics.com/#utm_source=koko-analytics&amp;utm_medium=link&amp;utm_campaign=free-plugin-dashboard-powered-by">Koko Analytics</a>'), ['a' => ['href' => []]]); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

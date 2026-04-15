<?php

use KokoAnalytics\Chart_View;
use KokoAnalytics\Fmt;

defined('ABSPATH') or exit;


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
                    <?= wp_date($date_format, $date_start->getTimestamp()); ?> — <?= wp_date($date_format, $date_end->getTimestamp()); ?>
                </div>

                <div id="ka-datepicker-dropdown" class="rounded bg-white shadow" style="display: none; position: absolute; width:360px; z-index: 9992;">
                    <div class="mb-3 bg-dark text-white p-3 rounded-top fw-bold d-flex justify-content-between">
                        <?php // only output pagination for date ranges between reasonable dates... to prevent ever-crawling bots from going wild
                        ?>
                        <?php if ($date_start > $total_start_date) { ?>
                            <a class="js-quicknav-prev text-decoration-none text-white me-2" href="" data-href="<?= esc_attr(add_query_arg(['start_date' => $prev_dates[0]->format('Y-m-d'), 'end_date' => $prev_dates[1]->format('Y-m-d')], $dashboard_url)); ?>" rel="nofollow">◂</a>
                        <?php } else { ?>
                            <a class="text-decoration-none text-white me-2">◂</a>
                        <?php } ?>
                        <span><?= wp_date($date_format, $date_start->getTimestamp()); ?> — <?= wp_date($date_format, $date_end->getTimestamp()); ?></span>
                        <?php if ($date_end < $total_end_date) { ?>
                            <a class="js-quicknav-next text-decoration-none text-white ms-2" href="" data-href="<?= esc_attr(add_query_arg(['start_date' => $next_dates[0]->format('Y-m-d'), 'end_date' => $next_dates[1]->format('Y-m-d')], $dashboard_url)); ?>" rel="nofollow">▸</a>
                        <?php } else { ?>
                            <a class="text-decoration-none text-white ms-2">▸</a>
                        <?php } ?>
                    </div>
                    <form method="get" class="p-3 pt-0">
                        <?php foreach (['page', 'p', 'koko-analytics-dashboard'] as $key) {
                            if (isset($_GET[$key])) {
                                echo '<input type="hidden" name="', $key, '" value="', esc_attr($_GET[$key]), '">';
                            }
                        } ?>

                        <div class="mb-3">
                            <label for="ka-date-presets" class="ka-label"><?php esc_html_e('Date range', 'koko-analytics'); ?></label>
                            <select id="ka-date-presets" name="view" class="ka-select">
                                <option value="custom" <?= $range === 'custom' ? 'selected' : ''; ?> disabled><?php esc_html_e('Custom', 'koko-analytics'); ?></option>
                                <?php foreach ($this->get_date_presets() as $key => $label) :
                                    ?><option value="<?= $key; ?>" <?= ($key === $range) ? ' selected' : ''; ?>><?= esc_html($label); ?></option><?php
                                endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for='ka-date-start' class="ka-label"><?php esc_html_e('Start date', 'koko-analytics'); ?></label>
                            <input name="start_date" id='ka-date-start' type="date" size="10" min="2000-01-01" max="2100-01-01"
                                value="<?= $date_start->format('Y-m-d'); ?>" class="ka-input">
                        </div>
                        <div class="mb-3">
                            <label for='ka-date-end' class="ka-label"><?php esc_html_e('End date', 'koko-analytics'); ?></label>
                            <input name="end_date" id='ka-date-end' type="date" size="10" min="2000-01-01" max="2100-01-01"
                                value="<?= $date_end->format('Y-m-d'); ?>" class="ka-input">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary"><?php esc_html_e('Submit', 'koko-analytics'); ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="ka-filter" <?= $page === 0 ? 'style="display: none;"' : ''; ?>>
                <?php esc_html_e('Page', 'koko-analytics'); ?> =
                <a class="" href="<?= esc_attr(home_url($page)); ?>"><?= esc_html($page); ?></a>
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
        $diff = $totals->visitors - $totals_previous->visitors;
        $change = $totals_previous->visitors == 0 ? 0 : ($totals->visitors / $totals_previous->visitors) - 1;
        ?>
        <div class="ka-col">
            <div class="ka-box p-3">
                <div class="text-muted mb-1"><?php esc_html_e('Total visitors', 'koko-analytics'); ?></div>
                <div class="ka-totals-number mb-1">
                    <span title="<?= esc_attr($totals->visitors); ?>"><?= number_format_i18n($totals->visitors); ?></span>
                    <span class="ka-totals-change <?= ($diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted')) ?>">
                        <?= Fmt::percent($change); ?>
                    </span>
                </div>
                <div class="text-muted">
                    <?php
                    if ($diff != 0) {
                        echo number_format_i18n(abs($diff));
                        echo ' ';
                    }
                    if ($diff > 0) {
                        echo esc_html__('more than previous period', 'koko-analytics');
                    }
                    if ($diff < 0) {
                        echo esc_html__('less than previous period', 'koko-analytics');
                    } ?>
                </div>
            </div>
        </div>
        <?php
        /* Total pageviews */
        $diff = $totals->pageviews - $totals_previous->pageviews;
        $change = $totals_previous->pageviews == 0 ? 0 : ($totals->pageviews / $totals_previous->pageviews) - 1;
        ?>
        <div class="ka-col">
            <div class="ka-box p-3">
                <div class="text-muted mb-1"><?php esc_html_e('Total pageviews', 'koko-analytics'); ?></div>
                <div class="ka-totals-number mb-1">
                    <span title="<?= esc_attr($totals->pageviews); ?>"><?= number_format_i18n($totals->pageviews); ?></span>
                    <span class="ka-totals-change <?= ($diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-muted')) ?>">
                        <?= Fmt::percent($change); ?>
                    </span>
                </div>
                <div class="text-muted">
                    <?php
                    if ($diff != 0) {
                        echo number_format_i18n(abs($diff));
                        echo ' ';
                    }
                    if ($diff > 0) {
                        echo esc_html__('more than previous period', 'koko-analytics');
                    }
                    if ($diff < 0) {
                        echo esc_html__('less than previous period', 'koko-analytics');
                    } ?>
                </div>
            </div>
        </div>
        <div class="ka-col">
            <div class="ka-box p-3" id="ka-realtime">
                <div class="text-muted mb-1"><span class="ka-realtime-dot"></span><?php esc_html_e('Realtime pageviews', 'koko-analytics'); ?></div>
                <div class="ka-totals-number mb-1"><?= number_format_i18n($realtime); ?></div>
                <div class="text-muted">
                    <?php esc_html_e('pageviews in the last hour', 'koko-analytics'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php /* end totals component */ ?>

    <?php /* CHART COMPONENT */ ?>
    <?php if (count($chart_data) > 1) { ?>
        <div class="ka-box mb-3 p-3">
            <?php new Chart_View($chart_data, $date_start, $date_end, 280, true, $group_chart_by); ?>
        </div>
    <?php } ?>

    <?php $can_sort = current_user_can('manage_koko_analytics'); ?>
    <div id="ka-components" class="ka-row ka-row-cols-1 ka-row-cols-xl-2 g-3 mb-3 <?= $page !== 0 ? 'page-filter-active' : ''; ?>" <?= $can_sort ? 'data-nonce="' . esc_attr(wp_create_nonce('koko_analytics_save_component_order')) . '"' : ''; ?>>
        <?php foreach ($this->get_components() as $id => $callback) : ?>
            <div id="<?= esc_attr($id) ?>" class="ka-col" <?= $can_sort ? 'data-sortable' : ''; ?>>
                <div class="ka-box">
                    <?php $callback($date_start, $date_end); ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php do_action_deprecated('koko_analytics_show_dashboard_components', [], '1.4', 'koko_analytics_after_dashboard_components'); ?>
        <?php do_action_deprecated('koko_analytics_after_dashboard_components', [$date_start, $date_end], '2.3', 'koko_analytics_dashboard_components'); ?>
    </div><?php // end div.ka-row
    ?>

    <?php // show section about koko analytics pro unless on pro version already ?>
    <?php if (!defined('KOKO_ANALYTICS_PRO_VERSION')) : ?>
        <?php if (current_user_can('manage_koko_analytics')) : ?>
            <div class="p-3 rounded" style="background: #fff3cd;">
                <h2 class="mt-0 mb-2"><?php esc_html_e('Upgrade to Koko Analytics Pro', 'koko-analytics'); ?></h2>
                <p class="mt-0 mb-2">
                    <?= esc_html__('You are currently using the free version of Koko Analytics.', 'koko-analytics'); ?>
                    <?= esc_html__('With Koko Analytics Pro you can unlock powerful benefits like country stats, device stats, custom event tracking and periodic email reports.', 'koko-analytics'); ?>
                </p>
                <p class="mt-0 mb-0"><a class="btn btn-sm btn-primary" href="https://www.kokoanalytics.com/pricing/" target="_blank"><?php esc_html_e('Upgrade Now', 'koko-analytics'); ?> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-circle-fill align-middle ms-2" viewBox="0 0 16 16">
                            <path d="M16 8A8 8 0 1 0 0 8a8 8 0 0 0 16 0m-7.5 3.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707z" />
                        </svg></a></p>
            </div>
        <?php else : ?>
            <div class="text-muted text-center mt-5 mb-3">
                <?php printf(__('Powered by %1s - privacy-friendly analytics for WordPress sites', 'koko-analytics'), '<a href="https://www.kokoanalytics.com/">Koko Analytics</a>'); ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
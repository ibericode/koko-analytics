<?php

use KokoAnalytics\Chart_View;
use KokoAnalytics\Fmt;

defined('ABSPATH') or exit;


/**
 * @var \KokoAnalytics\Dashboard $this
 * @var \DateTimeInterface $dateStart
 * @var \DateTimeInterface $dateEnd
 * @var object $totals
 * @var int $realtime
 * @var string $dateFormat
 * @var string $dashboard_url
 * @var \KokoAnalytics\Dates $dates
 * @var \KokoAnalytics\Stats $stats
 */

$tab = 'dashboard';

?>
<div class="koko-analytics ka-wrap">
    <?php $this->maybe_show_pro_notice(); ?>
    <?php $this->maybe_show_adblocker_notice(); ?>

    <div class="d-lg-flex">
        <div class="d-flex gap-3 mb-3">
            <div class="position-relative">
                <div class="ka-filter" tabindex="0" role="button" aria-expanded="false" aria-controls="ka-datepicker-dropdown" onclick="var el = document.getElementById('ka-datepicker-dropdown'); el.style.display = el.offsetParent === null ? 'block' : 'none'; this.ariaExpanded =  el.offsetParent === null ? 'false' : 'true';">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar3 me-2" style="vertical-align: middle;" viewBox="0 0 16 16"><path d="M14 0H2a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2M1 3.857C1 3.384 1.448 3 2 3h12c.552 0 1 .384 1 .857v10.286c0 .473-.448.857-1 .857H2c-.552 0-1-.384-1-.857z"/>
  <path d="M6.5 7a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m-9 3a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2m3 0a1 1 0 1 0 0-2 1 1 0 0 0 0 2"/></svg>
                    <?php echo wp_date($dateFormat, $dateStart->getTimestamp()); ?> — <?php echo wp_date($dateFormat, $dateEnd->getTimestamp()); ?>
                </div>

                <div id="ka-datepicker-dropdown" class="rounded bg-white shadow" style="display: none; position: absolute; width:320px; z-index: 9992;">
                    <div class="mb-3 bg-dark text-white p-3 rounded-top fw-bold d-flex justify-content-between">
                        <?php // only output pagination for date ranges between reasonable dates... to prevent ever-crawling bots from going wild ?>
                        <?php if ($dateStart >  $total_start_date) { ?>
                        <a class="js-quicknav-prev text-decoration-none text-white me-2" href="<?php echo esc_attr(add_query_arg(['start_date' => $prevDates[0]->format('Y-m-d'), 'end_date' => $prevDates[1]->format('Y-m-d')], $dashboard_url)); ?>">◂</a>
                        <?php } else { ?>
                            <a class="text-decoration-none text-white me-2">◂</a>
                        <?php } ?>
                        <span><?php echo wp_date($dateFormat, $dateStart->getTimestamp()); ?> — <?php echo wp_date($dateFormat, $dateEnd->getTimestamp()); ?></span>
                        <?php if ($dateEnd < $total_end_date) { ?>
                        <a class="js-quicknav-next text-decoration-none text-white ms-2" href="<?php echo esc_attr(add_query_arg(['start_date' => $nextDates[0]->format('Y-m-d'), 'end_date' => $nextDates[1]->format('Y-m-d')], $dashboard_url)); ?>">▸</a>
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
                                <option value="custom" <?php echo $range === 'custom' ? 'selected' : ''; ?> disabled><?php esc_html_e('Custom', 'koko-analytics'); ?></option>
                                <?php foreach ($this->get_date_presets() as $key => $label) :
                                    ?><option value="<?php echo $key; ?>"<?php echo ( $key === $range ) ? ' selected' : ''; ?>><?php echo esc_html($label); ?></option><?php
                                endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for='ka-date-start' class="ka-label"><?php esc_html_e('Start date', 'koko-analytics'); ?></label>
                            <input name="start_date" id='ka-date-start' type="date" size="10" min="2000-01-01" max="2100-01-01"
                                   value="<?php echo $dateStart->format('Y-m-d'); ?>" class="ka-input">
                        </div>
                        <div class="mb-3">
                            <label for='ka-date-end' class="ka-label"><?php esc_html_e('End date', 'koko-analytics'); ?></label>
                            <input name="end_date" id='ka-date-end' type="date" size="10" min="2000-01-01" max="2100-01-01"
                                   value="<?php echo $dateEnd->format('Y-m-d'); ?>" class="ka-input">
                        </div>
                        <div>
                            <button type="submit" class="btn btn-primary"><?php esc_html_e('Submit', 'koko-analytics'); ?></button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="ka-filter" <?php echo $page === 0 ? 'style="display: none;"' : ''; ?>>
                <?php esc_html_e('Page', 'koko-analytics'); ?> =
                <a class="" href="<?php echo esc_attr(home_url($page)); ?>"><?php echo esc_html($page); ?></a>
                <a class="text-decoration-none text-reset ms-2" aria-label="<?php esc_attr_e('Clear page filter', 'koko-analytics'); ?>" title="<?php esc_attr_e('Clear page filter', 'koko-analytics'); ?>" href="<?php echo esc_attr(remove_query_arg('p')); ?>">✕</a>
            </div>

            <?php do_action('koko_analytics_after_datepicker', $dateStart, $dateEnd); ?>
        </div>

        <?php require __DIR__ . '/nav.php'; ?>
    </div>

    <?php /* totals component */ ?>
    <div class="mb-3 ka-box bg-dark p-4">
        <table class="d-block">
            <tbody class="d-flex gap-5">
            <?php
            /* Total visitors */
            $diff = $totals->visitors - $totals_previous->visitors;
            $change = $totals_previous->visitors == 0 ? 0 : ($totals->visitors / $totals_previous->visitors) - 1;
            ?>
            <tr>
                <th class="d-block text-start mb-1" scope="row"><?php esc_html_e('Total visitors', 'koko-analytics'); ?></th>
                <td class="d-block fs-1 lh-1 mb-1">
                    <span class="" title="<?php echo esc_attr($totals->visitors); ?>"><?php echo number_format_i18n($totals->visitors); ?></span>
                    <span class="fs-3 align-top <?= ($diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-white')) ?>">
                        <?php echo Fmt::percent($change); ?>
                    </span>
                </td>
                <td class="text-muted">
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
                </td>
            </tr>
            <?php
            /* Total pageviews */
            $diff = $totals->pageviews - $totals_previous->pageviews;
            $change = $totals_previous->pageviews == 0 ? 0 : ($totals->pageviews / $totals_previous->pageviews) - 1;
            ?>
            <tr>
                <th class="d-block text-start mb-1" scope="row"><?php esc_html_e('Total pageviews', 'koko-analytics'); ?></th>
                <td class="d-block fs-1 lh-1 mb-1">
                    <span class="" title="<?php echo esc_attr($totals->pageviews); ?>"><?php echo number_format_i18n($totals->pageviews); ?></span>
                    <span class="fs-3 align-top <?= ($diff > 0 ? 'text-success' : ($diff < 0 ? 'text-danger' : 'text-white')) ?>">
                        <?php echo Fmt::percent($change); ?>
                    </span>
                </td>
                <td class="text-muted">
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
                </td>
            </tr>
            <tr id="ka-realtime">
                <th class="d-block text-start mb-1" scope="row"><?php esc_html_e('Realtime pageviews', 'koko-analytics'); ?></th>
                <td class="d-block fs-1 lh-1 mb-1"><?php echo number_format_i18n($realtime); ?></td>
                <td class="text-muted">
                    <?php esc_html_e('pageviews in the last hour', 'koko-analytics'); ?>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php /* end totals component */ ?>

    <?php /* CHART COMPONENT */ ?>
    <?php if (count($chart_data) > 1) { ?>
    <div class="ka-box mb-3 p-3">
        <?php new Chart_View($chart_data, $dateStart, $dateEnd); ?>
    </div>
    <?php } ?>

    <div class="ka-row ka-row-cols-1 ka-row-cols-xl-2 g-3 mb-3 <?php echo $page !== 0 ? 'page-filter-active' : ''; ?>">
        <?php /* TOP PAGES */ ?>
        <div id="top-pages" class="ka-col">
            <div class="ka-box">
                <table class="ka-table">
                    <thead>
                        <tr>
                            <th class="" style="width: 3ch;" scope="col">#</th>
                            <th class="" scope="col"><?php esc_html_e('Pages', 'koko-analytics'); ?></th>
                            <th title="<?php echo esc_attr__('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics'); ?>" class="text-end  d-none d-lg-table-cell w-fit" scope="row"><?php esc_html_e('Visitors', 'koko-analytics'); ?></th>
                            <th title="<?php echo esc_attr__('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics'); ?>" class="text-end ka-pageviews w-fit text-truncate" scope="col"><?php esc_html_e('Pageviews', 'koko-analytics'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $i => $p) { ?>
                            <?php $pct = $totals->pageviews > 0 && $page === 0  ? round(($p->pageviews / $totals->pageviews) * 100, 0) : 0; ?>
                            <tr <?php echo $page == $p->path ? 'class="page-filter-active"' : ''; ?> style="background: linear-gradient(to right,rgba(104, 159, 210, 0.05) <?=$pct?>%, transparent <?=$pct?>%);">
                                <td class="text-muted"><?php echo  $posts_offset + $i + 1; ?></td>
                                <td class="text-truncate"><a href="<?php echo esc_attr(add_query_arg(['p' => $p->path])); ?>" style="z-index:1;"><?php echo esc_html($p->label); ?></a></td>
                                <td class="text-end d-none d-lg-table-cell"><?php echo number_format_i18n(max(1, $p->visitors)); ?></td>
                                <td class="text-end"><?php echo number_format_i18n($p->pageviews); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if (empty($posts)) { ?>
                            <tr>
                                <td colspan="4">
                                    <?php esc_html_e('There is nothing here. Yet!', 'koko-analytics'); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>

                <?php if ($posts_offset >= $posts_limit || $posts_offset + $posts_limit < $posts_count) { ?>
               <div class='ka-pagination'>
                    <?php if ($posts_offset >= $posts_limit) { ?>
                    <a class='ka-pagination--prev' href="<?php echo esc_attr(add_query_arg(['posts' => [ 'offset' => $posts_offset - $posts_limit, 'limit' => $posts_limit ]])); ?>"><?php esc_html_e('Previous', 'koko-analytics'); ?></a>
                    <?php } ?>
                    <?php if ($posts_offset + $posts_limit < $posts_count) { ?>
                    <a class='ka-pagination--next' href="<?php echo esc_attr(add_query_arg(['posts' => [ 'offset' => $posts_offset + $posts_limit, 'limit' => $posts_limit ]])); ?>"><?php esc_html_e('Next', 'koko-analytics'); ?></a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div>

        <?php /* TOP REFERRERS */ ?>
        <div id="top-referrers" class="ka-col">
            <div class="ka-box">
                <table class="ka-table">
                    <thead>
                        <tr>
                            <th scope="col" style="width: 3ch;">#</th>
                            <th scope="col"><?php esc_html_e('Referrers', 'koko-analytics'); ?></th>
                            <th scope="col" title="<?php echo esc_attr__('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics'); ?>" class="text-end d-none d-lg-table-cell w-fit" style=""><?php esc_html_e('Visitors', 'koko-analytics'); ?></th>
                            <th scope="col" title="<?php echo esc_attr__('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics'); ?>" class="text-end text-truncate w-fit ka-pageviews"><?php esc_html_e('Pageviews', 'koko-analytics'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($referrers as $i => $r) { ?>
                            <tr>
                                <td class="text-muted"><?php echo $referrers_offset + $i + 1; ?></td>
                                <td class="text-truncate"><?php echo Fmt::referrer_url_label(esc_html($r->url)); ?></td>
                                <td class="text-end d-none d-lg-table-cell"><?php echo number_format_i18n(max(1, $r->visitors)); ?></td>
                                <td class="text-end"><?php echo number_format_i18n($r->pageviews); ?></td>
                            </tr>
                        <?php } ?>
                        <?php if (empty($referrers)) { ?>
                            <tr>
                                <td colspan="4">
                                    <?php esc_html_e('There is nothing here. Yet!', 'koko-analytics'); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
                <?php if ($referrers_offset >= $referrers_limit || $referrers_offset + $referrers_limit < $referrers_count) { ?>
               <div class='ka-pagination'>
                    <?php if ($referrers_offset >= $referrers_limit) { ?>
                    <a class='ka-pagination--prev' href="<?php echo esc_attr(add_query_arg(['referrers' => [ 'offset' => $referrers_offset - $referrers_limit, 'limit' => $referrers_limit ]])); ?>"><?php esc_html_e('Previous', 'koko-analytics'); ?></a>
                    <?php } ?>
                    <?php if ($referrers_offset + $referrers_limit < $referrers_count) { ?>
                    <a class='ka-pagination--next' href="<?php echo esc_attr(add_query_arg(['referrers' => [ 'offset' => $referrers_offset + $referrers_limit, 'limit' => $referrers_limit ]])); ?>"><?php esc_html_e('Next', 'koko-analytics'); ?></a>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
        </div><?php // end div.col ?>

        <?php do_action_deprecated('koko_analytics_show_dashboard_components', [], '1.4', 'koko_analytics_after_dashboard_components'); ?>
        <?php do_action('koko_analytics_after_dashboard_components', $dateStart, $dateEnd); ?>
    </div><?php // end div.ka-row ?>

    <?php if (!defined('KOKO_ANALYTICS_PRO_VERSION')) : ?>
    <div class="p-3 rounded"  style="background: #fff3cd;">
        <h2 class="mt-0 mb-2"><?php esc_html_e('Upgrade to Koko Analytics Pro', 'koko-analytics'); ?></h2>
        <p class="mt-0 mb-2">
            <?= esc_html('You are currently using the free version of Koko Analytics.', 'koko-analytics'); ?>
            <?= esc_html('With Koko Analytics Pro you can unlock powerful benefits like geo-location, event tracking and periodic email reports.', 'koko-analytics'); ?>
        </p>
        <p class="mt-0 mb-0">
        <a class="btn btn-sm btn-primary" href="https://www.kokoanalytics.com/pricing/" target="_blank"><?php esc_html_e('Upgrade Now', 'koko-analytics'); ?> <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-up-circle-fill align-middle ms-2" viewBox="0 0 16 16"><path d="M16 8A8 8 0 1 0 0 8a8 8 0 0 0 16 0m-7.5 3.5a.5.5 0 0 1-1 0V5.707L5.354 7.854a.5.5 0 1 1-.708-.708l3-3a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 5.707z"/>
</svg></a></p>
    </div>
    <?php endif; ?>
</div>

<script>
// save scroll position when navigating away
function storeScrollPosition() {
    sessionStorage.setItem("scrollX", window.pageXOffset);
    sessionStorage.setItem("scrollY", window.pageYOffset);
}
document.addEventListener('click', storeScrollPosition);
window.addEventListener('beforeunload', storeScrollPosition);

// restore scroll position on page load
var scrollX = parseInt(sessionStorage.getItem("scrollX") ?? 0);
var scrollY = parseInt(sessionStorage.getItem("scrollY") ?? 0);
if (scrollX != 0 || scrollY != 0) {
  window.scroll(scrollX, scrollY);
}
</script>

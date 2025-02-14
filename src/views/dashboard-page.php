<?php

use KokoAnalytics\Chart_View;
use KokoAnalytics\Fmt;

use function KokoAnalytics\get_page_title;

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

require __DIR__ . '/../template-functions.php';
?>
<div class="wrap">
    <?php $this->maybe_show_pro_notice(); ?>
    <?php $this->maybe_show_adblocker_notice(); ?>

    <div class="ka-dashboard-nav">
        <div class="ka-dashboard-nav--left">
            <div class="ka-datepicker">
                <div class='ka-datepicker--label' tabindex="0" aria-expanded="false" aria-controls="ka-datepicker-dropdown" onclick="var el = document.getElementById('ka-datepicker-dropdown'); el.style.display = el.offsetParent === null ? 'block' : 'none'; this.ariaExpanded =  el.offsetParent === null ? 'false' : 'true';">
                    <?php echo wp_date($dateFormat, $dateStart->getTimestamp()); ?> — <?php echo wp_date($dateFormat, $dateEnd->getTimestamp()); ?>
                </div>

                <div id="ka-datepicker-dropdown" class="ka-datepicker--dropdown" style="display: none;">
                    <div class="ka-datepicker--quicknav">
                        <?php // only output pagination for date ranges between reasonable dates... to prevent ever-crawling bots from going wild ?>
                        <?php if ($dateStart > new \DateTimeImmutable('2000-01-01')) { ?>
                        <a class="ka-datepicker--quicknav-prev" href="<?php echo esc_attr(add_query_arg(['start_date' => $prevDates[0]->format('Y-m-d'), 'end_date' => $prevDates[1]->format('Y-m-d')], $dashboard_url)); ?>"><?php esc_html_e('Previous date range', 'koko-analytics'); ?></a>
                        <?php } ?>
                        <span class="ka-datepicker--quicknav-heading"><?php echo wp_date($dateFormat, $dateStart->getTimestamp()); ?> — <?php echo wp_date($dateFormat, $dateEnd->getTimestamp()); ?></span>
                        <?php if ($dateEnd < new \DateTimeImmutable('2100-01-01')) { ?>
                        <a class="ka-datepicker--quicknav-next" href="<?php echo esc_attr(add_query_arg(['start_date' => $nextDates[0]->format('Y-m-d'), 'end_date' => $nextDates[1]->format('Y-m-d')], $dashboard_url)); ?>"><?php esc_html_e('Next date range', 'koko-analytics'); ?></a>
                        <?php } ?>
                    </div>
                    <form method="get">
                        <?php foreach (['page', 'p', 'koko-analytics-dashboard'] as $key) {
                            if (isset($_GET[$key])) {
                                echo '<input type="hidden" name="', $key, '" value="', esc_attr($_GET[$key]), '">';
                            }
                        } ?>

                        <div class="ka-datepicker--dropdown-content">
                            <label for="ka-date-presets"><?php esc_html_e('Date range', 'koko-analytics'); ?></label>
                            <select id="ka-date-presets" name="view">
                                <option value="custom" <?php echo $range === 'custom' ? 'selected' : ''; ?> disabled><?php esc_html_e('Custom', 'koko-analytics'); ?></option>
                                <?php foreach ($this->get_date_presets() as $key => $label) {
                                    ?>
                                    <option value="<?php echo $key; ?>"
                                        <?php echo ( $key === $range ) ? ' selected' : ''; ?>><?php echo esc_html($label); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <div style="display: flex; margin-top: 12px;">
                                <div>
                                    <label for='ka-date-start'><?php esc_html_e('Start date', 'koko-analytics'); ?></label>
                                    <input name="start_date" id='ka-date-start' type="date" size="10" placeholder="YYYY-MM-DD" min="2000-01-01" max="2100-01-01"
                                           value="<?php echo $dateStart->format('Y-m-d'); ?>">
                                    <span>&nbsp;&mdash;&nbsp;</span>
                                </div>
                                <div>
                                    <label for='ka-date-end'><?php esc_html_e('End date', 'koko-analytics'); ?></label>
                                    <input name="end_date" id='ka-date-end' type="date" size="10" placeholder="YYYY-MM-DD" min="2000-01-01" max="2100-01-01"
                                           value="<?php echo $dateEnd->format('Y-m-d'); ?>">
                                </div>
                            </div>
                            <div style="margin-top: 12px;">
                                <button type="submit" class="button button-secondary"><?php esc_html_e('Submit', 'koko-analytics'); ?></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="ka-page-filter" <?php echo $page === 0 ? 'style="display: none;"' : ''; ?>>
                <?php esc_html_e('Page', 'koko-analytics'); ?> =
                <a href="<?php echo esc_attr(get_the_permalink($page)); ?>"><?php echo esc_html(get_page_title($page)); ?></a>
                <a class="ka-page-filter--close" aria-label="<?php esc_attr_e('Clear page filter', 'koko-analytics'); ?>" title="<?php esc_attr_e('Clear page filter', 'koko-analytics'); ?>" href="<?php echo esc_attr(remove_query_arg('p')); ?>">✕</a>
            </div>

            <?php do_action('koko_analytics_after_datepicker', $dateStart, $dateEnd); ?>
        </div>

        <?php require __DIR__ . '/nav.php'; ?>
    </div>
    <table id="ka-totals" class='ka-totals m'>
        <tbody>

        <?php
        /* Total visitors */
        $diff = $totals->visitors - $totals_previous->visitors;
        $change = $totals_previous->visitors == 0 ? 0 : ($totals->visitors / $totals_previous->visitors) - 1;
        ?>
        <tr class="<?php echo $diff > 0 ? 'ka-up' : ''; ?> <?php echo $diff < 0 ? 'ka-down' : ''; ?>">
            <th><?php esc_html_e('Total visitors', 'koko-analytics'); ?></th>
            <td class='ka-totals--amount'>
                <span title="<?php echo esc_attr($totals->visitors); ?>"><?php echo number_format_i18n($totals->visitors); ?></span>
                <span class="ka-totals--change">
                    <?php echo Fmt::percent($change); ?>
                </span>
            </td>
            <td class='ka-totals--subtext'>
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
        <tr class="<?php echo $diff > 0 ? 'ka-up' : ''; ?> <?php echo $diff < 0 ? 'ka-down' : ''; ?>">
            <th><?php esc_html_e('Total pageviews', 'koko-analytics'); ?></th>
            <td class='ka-totals--amount'>
                <span title="<?php echo esc_attr($totals->pageviews); ?>"><?php echo number_format_i18n($totals->pageviews); ?></span>
                <span class="ka-totals--change">
                    <?php echo Fmt::percent($change); ?>
                </span>
            </td>
            <td class='ka-totals--subtext'>
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
            <th><?php esc_html_e('Realtime pageviews', 'koko-analytics'); ?></th>
            <td class='ka-totals--amount'><?php echo number_format_i18n($realtime); ?></td>
            <td class='ka-totals--subtext'>
                <?php esc_html_e('pageviews in the last hour', 'koko-analytics'); ?>
            </td>
        </tr>
        </tbody>
    </table>

    <?php /* CHART COMPONENT */ ?>
    <?php if (count($chart_data) > 1) { ?>
    <div class="ka-box ka-margin-s" style="padding: 24px;">
        <?php new Chart_View($chart_data, $dateStart, $dateEnd); ?>
    </div>
    <?php } ?>

    <div class="ka-dashboard-components <?php echo $page !== 0 ? 'page-filter-active' : ''; ?>" >

        <?php /* TOP PAGES */ ?>
        <div id="top-pages" class="ka-box">
            <table class="ka-table ka-top-posts">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php esc_html_e('Pages', 'koko-analytics'); ?></th>
                        <th title="<?php echo esc_attr__('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics'); ?>" class="ka-th-visitors"><?php esc_html_e('Visitors', 'koko-analytics'); ?></th>
                        <th title="<?php echo esc_attr__('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics'); ?>" class="ka-th-pageviews"><?php esc_html_e('Pageviews', 'koko-analytics'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $i => $p) { ?>
                        <tr <?php echo $page == $p->id ? 'class="page-filter-active"' : ''; ?>>
                            <td><?php echo  $posts_offset + $i + 1; ?></td>
                            <td><a href="<?php echo esc_attr(add_query_arg(['p' => $p->id])); ?>"><?php echo esc_html($p->post_title); ?></a></td>
                            <td><?php echo number_format_i18n(max(1, $p->visitors)); ?></td>
                            <td><?php echo number_format_i18n($p->pageviews); ?></td>
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

        <?php /* TOP REFERRERS */ ?>
        <div id="top-referrers" class="ka-box">
            <table class="ka-table ka-top-referrers">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><?php esc_html_e('Referrers', 'koko-analytics'); ?></th>
                        <th title="<?php echo esc_attr__('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics'); ?>" class="ka-th-visitors"><?php esc_html_e('Visitors', 'koko-analytics'); ?></th>
                        <th title="<?php echo esc_attr__('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics'); ?>" class="ka-th-pageviews"><?php esc_html_e('Pageviews', 'koko-analytics'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referrers as $i => $r) { ?>
                        <tr>
                            <td><?php echo $referrers_offset + $i + 1; ?></td>
                            <td><a href="<?php echo esc_attr(Fmt::referrer_url_href($r->url)); ?>"><?php echo Fmt::referrer_url_label(esc_html($r->url)); ?></a></td>
                            <td><?php echo number_format_i18n(max(1, $r->visitors)); ?></td>
                            <td><?php echo number_format_i18n($r->pageviews); ?></td>
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
        <?php do_action_deprecated('koko_analytics_show_dashboard_components', [], '1.4', 'koko_analytics_after_dashboard_components'); ?>
        <?php do_action('koko_analytics_after_dashboard_components', $dateStart, $dateEnd); ?>
    </div>
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
var scrollX = parseInt(sessionStorage.getItem("scrollX"));
var scrollY = parseInt(sessionStorage.getItem("scrollY"));
if (scrollX != 0 || scrollY != 0) {
  window.scroll(scrollX, scrollY);
}
</script>

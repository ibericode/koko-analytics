<?php defined('ABSPATH') or exit;
$tab = 'dashboard';

/**
 * @var \KokoAnalytics\Dashboard $this
 * @var \DateTimeInterface $dateStart
 * @var \DateTimeInterface $dateEnd
 * @var object $totals
 * @var int $realtime
 * @var string $dateFormat
 * @var string $preset
 * @var \KokoAnalytics\Dates $dates
 * @var \KokoAnalytics\Stats $stats
 */

use function KokoAnalytics\fmt_large_number;
?>
<script src="<?php echo plugins_url('assets/dist/js/dashboard.js', KOKO_ANALYTICS_PLUGIN_FILE); ?>?v=<?php echo KOKO_ANALYTICS_VERSION; ?>" defer></script>
<?php do_action('koko_analytics_dashboard_print_assets'); ?>
<link rel="stylesheet" href="<?php echo plugins_url('assets/dist/css/dashboard.css', KOKO_ANALYTICS_PLUGIN_FILE); ?>?v=<?php echo KOKO_ANALYTICS_VERSION; ?>">
<div class="wrap">
    <?php $this->maybe_show_adblocker_notice(); ?>

    <div style="display: flex; gap: 12px; ">
        <div class="ka-datepicker">
            <div class='ka-datepicker--label' tabindex="0" aria-expanded="false" aria-controls="ka-datepicker-dropdown" onclick="var el = document.getElementById('ka-datepicker-dropdown'); el.style.display = el.offsetParent === null ? 'block' : 'none'; this.ariaExpanded =  el.offsetParent === null ? 'false' : 'true';">
                <?php echo $dateStart->format($dateFormat); ?> — <?php echo $dateEnd->format($dateFormat); ?>
            </div>

            <div id="ka-datepicker-dropdown" class="ka-datepicker--dropdown" style="display: none;">
                <div class="ka-datepicker--quicknav">
                    <a class="ka-datepicker--quicknav-prev" href="<?php echo esc_attr(add_query_arg(['start_date' => $prevDates[0]->format('Y-m-d'), 'end_date' => $prevDates[1]->format('Y-m-d')])); ?>"><?php esc_html_e('Previous date range', 'koko-analytics'); ?></a>
                    <span class="ka-datepicker--quicknav-heading"><?php echo $dateStart->format($dateFormat); ?> — <?php echo $dateEnd->format($dateFormat); ?></span>
                    <a class="ka-datepicker--quicknav-next" href="<?php echo esc_attr(add_query_arg(['start_date' => $nextDates[0]->format('Y-m-d'), 'end_date' => $nextDates[1]->format('Y-m-d')])); ?>"><?php esc_html_e('Next date range', 'koko-analytics'); ?></a>
                </div>
                <form method="get" action="">
                    <?php if (!empty($_GET['page'])) { ?>
                        <input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']); ?>">
                    <?php } ?>
                    <?php if (!empty($_GET['koko-analytics-dashboard'])) { ?>
                    <input type="hidden" name="koko-analytics-dashboard" value="<?php echo esc_attr($_GET['koko-analytics-dashboard']); ?>">
                    <?php } ?>


                    <div class="ka-datepicker--dropdown-content">
                        <label for="ka-date-presets"><?php echo esc_html__('Date range', 'koko-analytics'); ?></label>
                        <select id="ka-date-presets" name="view">
                            <option value="custom" <?php echo $range === 'custom' ? 'selected' : ''; ?> disabled><?php echo esc_html__('Custom', 'koko-analytics'); ?></option>
                            <?php foreach ($this->get_date_presets() as $key => $label) {
                                ?>
                                <option value="<?php echo $key; ?>"
                                    <?php echo ( $key === $range ) ? ' selected' : ''; ?>><?php echo esc_html($label); ?>
                                </option>
                            <?php } ?>
                        </select>
                        <div style="display: flex; margin-top: 12px;">
                            <div>
                                <label for='ka-date-start'><?php echo esc_html__('Start date', 'koko-analytics'); ?></label>
                                <input name="start_date" id='ka-date-start' type="date" size="10" placeholder="YYYY-MM-DD" min="2000-01-01" max="2100-01-01"
                                       value="<?php echo $dateStart->format('Y-m-d'); ?>">
                                <span>&nbsp;&mdash;&nbsp;</span>
                            </div>
                            <div>
                                <label for='ka-date-end'><?php echo esc_html__('End date', 'koko-analytics'); ?></label>
                                <input name="end_date" id='ka-date-end' type="date" size="10" placeholder="YYYY-MM-DD" min="2000-01-01" max="2100-01-01"
                                       value="<?php echo $dateEnd->format('Y-m-d'); ?>">
                            </div>
                        </div>
                        <div style="margin-top: 12px;">
                            <button type="submit" class="button button-secondary"><?php esc_html_e('Submit', 'koko-analytics'); ?></button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="ka-page-filter" <?php if ($page !== 0) { echo 'style="display: block;"'; } ?>>
            <span><?php esc_html_e('Page', 'koko-analytics'); ?> = </span>
            <span style="font-weight: bold;">
                <a href="<?php echo esc_attr(get_the_permalink($page)); ?>"><?php echo esc_html(get_the_title($page)); ?></a>
            </span>
            <a class="ka-page-filter--close" aria-label="<?php esc_attr_e('Clear page filter', 'koko-analytics'); ?>" title="<?php esc_attr_e('Clear filter', 'koko-analytics'); ?>" href="<?php echo esc_attr(remove_query_arg('p')); ?>">✕</a>
        </div>

        <?php require __DIR__ . '/nav.php'; ?>
    </div>

    <div id="ka-totals" class='ka-totals m'>
        <div class="ka-fade <?php echo $totals->visitors_change > 0 ? 'ka-up' : ''; ?> <?php echo $totals->visitors_change < 0 ? 'ka-down' : ''; ?>">
            <div class='ka-totals--heading'><?php echo esc_html__('Total visitors', 'koko-analytics'); ?></div>
            <div class='ka-totals--amount'>
                <span title="<?php echo esc_attr($totals->visitors); ?>"><?php echo fmt_large_number($totals->visitors); ?></span>
                <span class="ka-totals--change">
                    <?php echo $totals->visitors_change_rel !== null ? sprintf('%+.0f%%', $totals->visitors_change_rel * 100) : ''; ?>
                </span>
            </div>
            <div class='ka-totals--subtext'>
                <span><?php echo fmt_large_number(abs($totals->visitors_change)); ?></span>
                <span class="ka-totals--subtext-up"><?php echo esc_html__('more than previous period', 'koko-analytics'); ?></span>
                <span class="ka-totals--subtext-down"><?php echo esc_html__('less than previous period', 'koko-analytics'); ?></span>
            </div>
        </div>
        <div class="ka-fade <?php echo $totals->pageviews_change > 0 ? 'ka-up' : ''; ?> <?php echo $totals->pageviews_change < 0 ? 'ka-down' : ''; ?>">
            <div class='ka-totals--heading'><?php echo esc_html__('Total pageviews', 'koko-analytics'); ?></div>
            <div class='ka-totals--amount'>
                <span title="<?php echo esc_attr($totals->pageviews); ?>"><?php echo fmt_large_number($totals->pageviews); ?></span>
                <span class="ka-totals--change">
                    <?php echo $totals->pageviews_change_rel !== null ? sprintf('%+.0f%%', $totals->pageviews_change_rel * 100) : ''; ?>
                </span>
            </div>
            <div class='ka-totals--subtext'>
                <span><?php echo fmt_large_number(abs($totals->pageviews_change)); ?></span>
                <span class="ka-totals--subtext-up"><?php echo esc_html__('more than previous period', 'koko-analytics'); ?></span>
                <span class="ka-totals--subtext-down"><?php echo esc_html__('less than previous period', 'koko-analytics'); ?></span>
            </div>
        </div>
        <div id="ka-realtime" class='ka-fade'>
            <div class='ka-totals--heading'><?php echo esc_html__('Realtime pageviews', 'koko-analytics'); ?></div>
            <div class='ka-totals--amount'><?php echo fmt_large_number($realtime); ?></div>
            <div class='ka-totals--subtext'>
                <?php echo esc_html__('pageviews in the last hour', 'koko-analytics'); ?>
            </div>
        </div>
    </div>

    <div class="ka-box ka-margin-s ka-chart"><div id="ka-chart"></div></div>

    <div class="ka-dashboard-components <?php if ($page !== 0) { echo 'page-filter-active'; } ?>" >

        <div class="ka-box">
            <table class="ka-table ka-fade ka-top-posts">
                <thead>
                    <tr>
                        <th class="ka-topx--rank" width="12">#</th>
                        <th><?php esc_html_e('Pages', 'koko-analytics'); ?></th>
                        <th class='amount' title="<?php echo esc_attr__('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics'); ?>"><?php esc_html_e('Visitors', 'koko-analytics'); ?></th>
                        <th class='amount' title="<?php echo esc_attr__('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics'); ?>"><?php esc_html_e('Pageviews', 'koko-analytics'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($posts as $i => $p) { ?>
                        <tr>
                            <td class="rank"><?php echo  $posts_offset + $i + 1; ?></td>
                            <td><a href="<?php echo esc_attr(add_query_arg(['p' => $p->id])); ?>"><?php echo $p->post_title; ?></a></td>
                            <td class='amount'><?php echo $p->visitors; ?></td>
                            <td class='amount'><?php echo $p->pageviews; ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (empty($posts)) { ?>
                        <tr>
                            <td colspan="4" class="ka-topx--placeholder">
                                <?php esc_html_e('There is nothing here. Yet!', 'koko-analytics'); ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>

            <?php if ($posts_offset >= $posts_limit || $posts_offset + $posts_limit < $posts_count) { ?>
           <div class='ka-pagination'>
                <?php if ($posts_offset >= $posts_limit) { ?>
                <a class='ka-pagination--prev' href="<?php echo esc_attr(add_query_arg(['posts[offset]' => $posts_offset - $posts_limit])); ?>"><?php echo esc_html__('Previous', 'koko-analytics'); ?></a>
                <?php } ?>
                <?php if ($posts_offset + $posts_limit < $posts_count) { ?>
                <a class='ka-pagination--next' href="<?php echo esc_attr(add_query_arg(['posts[offset]' => $posts_offset + $posts_limit])); ?>"><?php echo esc_html__('Next', 'koko-analytics'); ?></a>
                <?php } ?>
            </div>
            <?php } ?>
        </div>

        <div class="ka-box">
            <table class="ka-table ka-fade ka-top-referrers">
                <thead>
                    <tr>
                        <th class="ka-topx--rank" width="12">#</th>
                        <th><?php esc_html_e('Referrers', 'koko-analytics'); ?></th>
                        <th class='amount' title="<?php echo esc_attr__('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics'); ?>"><?php esc_html_e('Visitors', 'koko-analytics'); ?></th>
                        <th class='amount' title="<?php echo esc_attr__('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics'); ?>"><?php esc_html_e('Pageviews', 'koko-analytics'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($referrers as $i => $r) { ?>
                        <tr>
                            <td class="rank"><?php echo $referrers_offset + $i + 1; ?></td>
                            <td><a href="<?php echo esc_attr($r->url); ?>"><?php echo esc_html($r->url); ?></a></td>
                            <td class='amount'><?php echo $r->visitors; ?></td>
                            <td class='amount'><?php echo $r->pageviews; ?></td>
                        </tr>
                    <?php } ?>
                    <?php if (empty($referrers)) { ?>
                        <tr>
                            <td colspan="4" class="ka-topx--placeholder">
                                <?php esc_html_e('There is nothing here. Yet!', 'koko-analytics'); ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php if ($referrers_offset >= $referrers_limit || $referrers_offset + $referrers_limit < $referrers_count) { ?>
           <div class='ka-pagination'>
                <?php if ($referrers_offset >= $referrers_limit) { ?>
                <a class='ka-pagination--prev' href="<?php echo esc_attr(add_query_arg(['referrers[offset]' => $referrers_offset - $referrers_limit])); ?>"><?php echo esc_html__('Previous', 'koko-analytics'); ?></a>
                <?php } ?>
                <?php if ($referrers_offset + $referrers_limit < $referrers_count) { ?>
                <a class='ka-pagination--next' href="<?php echo esc_attr(add_query_arg(['referrers[offset]' => $referrers_offset + $referrers_limit])); ?>"><?php echo esc_html__('Next', 'koko-analytics'); ?></a>
                <?php } ?>
            </div>
            <?php } ?>
        </div>
        <?php do_action('koko_analytics_show_dashboard_components'); ?>
    </div>

    <div class="ka-margin-s" style="text-align: right">
        <p><?php echo $this->get_usage_tip(); ?></p>
    </div>
</div>

<script>
var koko_analytics = <?php echo json_encode($this->get_script_data($dateStart, $dateEnd)); ?>;
</script>

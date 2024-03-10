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
            <div class='ka-datepicker--label' aria-expanded="false" aria-controls="ka-datepicker-dropdown">
                <?php echo $dateStart->format($dateFormat); ?> — <?php echo $dateEnd->format($dateFormat); ?>
            </div>
            <div id="ka-datepicker-dropdown" class="ka-datepicker--dropdown" style="display: none;">
                <div class="ka-datepicker--quicknav">
                    <span class="ka-datepicker--quicknav-prev" title=<?php echo esc_html__('Previous', 'koko-analytics'); ?>></span>
                    <span class="ka-datepicker--quicknav-heading"><?php echo $dateStart->format($dateFormat); ?> — <?php echo $dateEnd->format($dateFormat); ?></span>
                    <span class="ka-datepicker--quicknav-next" title=<?php echo esc_html__('Next', 'koko-analytics'); ?>></span>
                </div>
                <div class="ka-datepicker--dropdown-content">
                    <label for="ka-date-presets"><?php echo esc_html__('Date range', 'koko-analytics'); ?></label>
                    <select id="ka-date-presets">
                        <option value="custom" <?php echo $preset === 'custom' ? 'selected' : ''; ?> disabled><?php echo esc_html__('Custom', 'koko-analytics'); ?></option>
                        <?php foreach ($this->get_date_presets() as $key => $label) {
                            $range = $dates->get_range($key); ?>
                            <option value="<?php echo $key; ?>"
                                    data-start-date="<?php echo $range[0]->format('Y-m-d'); ?>"
                                    data-end-date="<?php echo $range[1]->format('Y-m-d'); ?>"
                                    <?php echo ( $key === $preset ) ? ' selected' : ''; ?>><?php echo esc_html($label); ?></option>
                        <?php } ?>
                    </select>
                    <div style="display: flex; margin-top: 12px;">
                        <div>
                            <label for='ka-date-start'><?php echo esc_html__('Start date', 'koko-analytics'); ?></label>
                            <input id='ka-date-start' type="date" size="10" placeholder="YYYY-MM-DD" min="2000-01-01" max="2100-01-01"
                                   value="<?php echo $dateStart->format('Y-m-d'); ?>">
                            <span>&nbsp;&mdash;&nbsp;</span>
                        </div>
                        <div>
                            <label for='ka-date-end'><?php echo esc_html__('End date', 'koko-analytics'); ?></label>
                            <input id='ka-date-end' type="date" size="10" placeholder="YYYY-MM-DD" min="2000-01-01" max="2100-01-01"
                                   value="<?php echo $dateEnd->format('Y-m-d'); ?>">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="ka-page-filter">
            <span><?php esc_html_e('Page', 'koko-analytics'); ?> = </span>
            <span style="font-weight: bold;"></span>
            <span class="ka-page-filter--close" aria-label="clear filter" title="<?php esc_attr_e('Clear filter', 'koko-analytics'); ?>">✕</span>
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

    <div class="ka-dashboard-components">
        <div class='ka-topx ka-box ka-fade top-posts'>
            <div class='ka-topx--head ka-topx--row'>
                <div class='ka-topx--rank'>#</div>
                <div><?php echo esc_html__('Pages', 'koko-analytics'); ?></div>
                <div class='ka-topx--amount' title="<?php echo esc_html__('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics'); ?>"><?php echo esc_html__('Visitors', 'koko-analytics'); ?></div>
                    <div class='ka-topx--amount' title="<?php echo esc_html__('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics'); ?>"><?php echo esc_html__('Pageviews', 'koko-analytics'); ?></div>
            </div>
            <div id="ka-top-posts" class='ka-topx--body'></div>
            <div class="ka-topx--placeholder"><?php esc_html_e('There is nothing here. Yet!', 'koko-analytics'); ?></div>
            <div class='ka-pagination'>
                <span class='ka-pagination--prev disabled'><?php echo esc_html__('Previous', 'koko-analytics'); ?></span>
                <span class='ka-pagination--next'><?php echo esc_html__('Next', 'koko-analytics'); ?></span>
            </div>
        </div>

        <div class='ka-topx ka-box ka-fade top-referrers'>
            <div class='ka-topx--head ka-topx--row'>
                <div class='ka-topx--rank'>#</div>
                <div><?php echo esc_html__('Referrers', 'koko-analytics'); ?></div>
                <div class='ka-topx--amount'><?php echo esc_html__('Visitors', 'koko-analytics'); ?></div>
                <div class='ka-topx--amount'><?php echo esc_html__('Pageviews', 'koko-analytics'); ?></div>
            </div>
            <div id="ka-top-referrers" class='ka-topx--body'></div>
            <div class="ka-topx--placeholder"><?php echo esc_html__('There is nothing here. Yet!', 'koko-analytics'); ?></div>
            <div class='ka-pagination'>
                <span class='ka-pagination--prev disabled'><?php echo esc_html__('Previous', 'koko-analytics'); ?></span>
                <span class='ka-pagination--next'><?php echo esc_html__('Next', 'koko-analytics'); ?></span>
            </div>
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

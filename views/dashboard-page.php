<?php defined('ABSPATH') or exit;
$tab = 'dashboard';

/**
 * @var \KokoAnalytics\Admin $this
 * @var bool $is_buffer_dir_writable
 * @var string $buffer_dirname
 * @var \DateTimeInterface $dateStart
 * @var \DateTimeInterface $dateEnd
 * @var object $totals
 * @var int $realtime
 * @var string $dateFormat
 * @var string $preset
 * @var \KokoAnalytics\Dates $dates
 */

use function KokoAnalytics\fmt_large_number;
?>
<div class="wrap" id="koko-analytics-admin">
    <?php
    if (false === $this->is_cron_event_working()) {
        echo '<div class="notice notice-warning inline koko-analytics-cron-warning is-dismissible"><p>';
        echo esc_html__('There seems to be an issue with your site\'s WP Cron configuration that prevents Koko Analytics from automatically processing your statistics.', 'koko-analytics');
        echo ' ';
        echo esc_html__('If you\'re not sure what this is about, please ask your webhost to look into this.', 'koko-analytics');
        echo '</p></div>';
    }

    if (false === $is_buffer_dir_writable) {
        echo '<div class="notice notice-warning inline is-dismissible"><p>';
        echo wp_kses(sprintf(__('Koko Analytics is unable to write to the <code>%s</code> directory. Please update the file permissions so that your web server can write to it.', 'koko-analytics'), $buffer_dirname), array( 'code' => array() ));
        echo '</p></div>';
    }

    ?>
    <div class="notice notice-warning is-dismissible" id="koko-analytics-adblock-notice" style="display: none;">
        <p>
            <?php _e('You appear to be using an ad-blocker that has Koko Analytics on its blocklist. Please whitelist this domain in your ad-blocker setting if your dashboard does not seem to be working correctly.', 'koko-analytics'); ?>
        </p>
    </div>
    <script src="<?php echo plugins_url('/assets/dist/js/koko-analytics-script-test.js', KOKO_ANALYTICS_PLUGIN_FILE); ?>"
            defer onerror="document.getElementById('koko-analytics-adblock-notice').style.display = '';"></script>

    <noscript>
        <p><?php echo esc_html__('Please enable JavaScript for this page to work.', 'koko-analytics'); ?></p>
    </noscript>

    <?php require __DIR__ . '/nav.php'; ?>

    <div class="ka-datepicker">
        <div class='ka-datepicker--label'>
            <?php echo $dateStart->format($dateFormat); ?> — <?php echo $dateEnd->format($dateFormat); ?>
        </div>
        <div class="ka-datepicker--dropdown" style="display: none;">
            <div class="ka-datepicker--quicknav">
                <span class="ka-datepicker--quicknav-prev" title=<?php echo esc_html__('Previous', 'koko-analytics'); ?>></span>
                <span class="ka-datepicker--quicknav-heading"><?php echo $dateStart->format($dateFormat); ?> — <?php echo $dateEnd->format($dateFormat); ?></span>
                <span class="ka-datepicker--quicknav-next" title=<?php echo esc_html__('Next', 'koko-analytics'); ?>></span>
            </div>
            <div class="ka-datepicker--dropdown-content">
                <label for="ka-date-presets"><?php echo __('Date range', 'koko-analytics'); ?></label>
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
                        <label for='ka-date-start'><?php echo __('Start date', 'koko-analytics'); ?></label>
                        <input id='ka-date-start' type="date" size="10" placeholder="YYYY-MM-DD"
                               value="<?php echo $dateStart->format('Y-m-d'); ?>">
                        <span>&nbsp;&mdash;&nbsp;</span>
                    </div>
                    <div>
                        <label for='ka-date-end'><?php echo __('End date', 'koko-analytics'); ?></label>
                        <input id='ka-date-end' type="date" size="10" placeholder="YYYY-MM-DD"
                               value="<?php echo $dateEnd->format('Y-m-d'); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="ka-totals" class='ka-totals m'>
        <div class='ka-fade'>
            <div class='ka-totals--heading'><?php echo esc_html__('Total visitors', 'koko-analytics'); ?></div>
            <div class='ka-totals--amount'>
                <span><?php echo fmt_large_number($totals->visitors); ?></span>
                <span class="ka-totals--change <?php echo (int) ($totals->visitors_change_rel * 100) > 0 ? 'up' : 'down'; ?>">
                    <?php echo sprintf('%+.0f%%', $totals->visitors_change_rel * 100); ?>
                </span>
            </div>
            <div class='ka-totals--subtext'>
                <?php echo fmt_large_number(abs($totals->visitors_change)); ?>
                <?php echo $totals->visitors_change > 0 ? esc_html__('more than previous period', 'koko-analytics') : esc_html__('less than previous period', 'koko-analytics'); ?>
            </div>
        </div>
        <div class='ka-fade'>
            <div class='ka-totals--heading'><?php echo esc_html__('Total pageviews', 'koko-analytics'); ?></div>
            <div class='ka-totals--amount'>
                <span><?php echo fmt_large_number($totals->pageviews); ?></span>
                <span class="ka-totals--change <?php echo (int) ($totals->pageviews_change_rel * 100) > 0 ? 'up' : 'down'; ?>">
                    <?php echo sprintf('%+.0f%%', $totals->pageviews_change_rel * 100); ?>
                </span>
            </div>
            <div class='ka-totals--subtext'>
                <?php echo fmt_large_number(abs($totals->pageviews_change)); ?>
                <?php echo $totals->pageviews_change > 0 ? esc_html__('more than previous period', 'koko-analytics') : esc_html__('less than previous period', 'koko-analytics'); ?>
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

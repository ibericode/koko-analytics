<?php defined('ABSPATH') or exit;
$tab = 'dashboard';

/**
 * @var \KokoAnalytics\Admin $this
 * @var bool $is_cron_event_working
 * @var bool $is_buffer_dir_writable
 * @var string $buffer_dirname
 */

use function KokoAnalytics\get_realtime_pageview_count;
use function KokoAnalytics\fmt_large_number;
?>
<div class="wrap" id="koko-analytics-admin">

    <?php
    if (false === $is_cron_event_working) {
        echo '<div class="notice notice-warning inline koko-analytics-cron-warning"><p>';
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

    $settings   = \KokoAnalytics\get_settings();
    $dates = new \KokoAnalytics\Dates();
    $dateRange = $dates->get_range($settings['default_view']);
    $dateStart  = isset($_GET['start_date']) ? new \DateTimeImmutable($_GET['start_date']) : $dateRange[0];
    $dateEnd    = isset($_GET['end_date']) ? new \DateTimeImmutable($_GET['end_date']) : $dateRange[1];
    $dateFormat = get_option('date_format');
    $preset     = ! isset($_GET['start_date']) && ! isset($_GET['end_date']) ? $settings['default_view'] : 'custom';
    $random_usage_tip = $this->get_usage_tip();
    $totals = (new \KokoAnalytics\Stats())->get_totals($dateStart->format('Y-m-d'), $dateEnd->format('Y-m-d'));
    $realtime = get_realtime_pageview_count('-1 hour');
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
                <span class="ka-datepicker--quicknav-prev" title=<?php echo __('Previous', 'koko-analytics'); ?>></span>
                <span class="ka-datepicker--quicknav-heading"><?php echo $dateStart->format($dateFormat); ?> — <?php echo $dateEnd->format($dateFormat); ?></span>
                <span class="ka-datepicker--quicknav-next" title=<?php echo __('Next', 'koko-analytics'); ?>></span>
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
                               value="<?php echo $dateStart->format('Y-m-d'); ?>"/>
                        <span>&nbsp;&mdash;&nbsp;</span>
                    </div>
                    <div>
                        <label for='ka-date-end'><?php echo __('End date', 'koko-analytics'); ?></label>
                        <input id='ka-date-end' type="date" size="10" placeholder="YYYY-MM-DD"
                               value="<?php echo $dateEnd->format('Y-m-d'); ?>"/>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="ka-totals" class='ka-totals m'>
        <div class='ka-fade'>
            <div class='ka-totals--heading'><?php echo __('Total visitors', 'koko-analytics'); ?></div>
            <div class='ka-totals--amount'>
                <span><?php echo fmt_large_number($totals->visitors); ?></span>
                <span class="ka-totals--change <?php echo (int) ($totals->visitors_change_rel * 100) > 0 ? 'up' : 'down'; ?>">
                    <?php echo sprintf('%+.0f%%', $totals->visitors_change_rel * 100); ?>
                </span>
            </div>
            <div class='ka-totals--subtext'>
                <?php echo fmt_large_number(abs($totals->visitors_change)); ?>
                <?php echo $totals->visitors_change > 0 ? __('more than previous period', 'koko-analytics') : __('less than previous period', 'koko-analytics'); ?>
            </div>
        </div>
        <div class='ka-fade'>
            <div class='ka-totals--heading'><?php echo __('Total pageviews', 'koko-analytics'); ?></div>
            <div class='ka-totals--amount'>
                <span><?php echo fmt_large_number($totals->pageviews); ?></span>
                <span class="ka-totals--change <?php echo (int) ($totals->pageviews_change_rel * 100) > 0 ? 'up' : 'down'; ?>">
                    <?php echo sprintf('%+.0f%%', $totals->pageviews_change_rel * 100); ?>
                </span>
            </div>
            <div class='ka-totals--subtext'>
                <?php echo fmt_large_number(abs($totals->pageviews_change)); ?>
                <?php echo $totals->pageviews_change > 0 ? __('more than previous period', 'koko-analytics') : __('less than previous period', 'koko-analytics'); ?>
            </div>
        </div>
        <div id="ka-realtime" class='ka-fade'>
            <div class='ka-totals--heading'><?php echo __('Realtime pageviews', 'koko-analytics'); ?></div>
            <div class='ka-totals--amount'><?php echo esc_html(fmt_large_number($realtime)); ?></div>
            <div class='ka-totals--subtext'>
                <?php echo __('pageviews in the last hour', 'koko-analytics'); ?>
            </div>
        </div>
    </div>

    <div class="ka-box ka-margin-s ka-chart"><div id="ka-chart"></div></div>

    <div class="ka-dashboard-components">
        <div class='ka-topx ka-box ka-fade top-posts'>
            <div class='ka-topx--head ka-topx--row'>
                <div class='ka-topx--rank'>#</div>
                <div><?php echo __('Pages', 'koko-analytics'); ?></div>
                <div class='ka-topx--amount' title="<?php echo __('A visitor represents the number of sessions during which a page was viewed one or more times.', 'koko-analytics'); ?>"><?php echo __('Visitors', 'koko-analytics'); ?></div>
                    <div class='ka-topx--amount' title="<?php echo __('A pageview is defined as a view of a page on your site. If a user clicks reload after reaching the page, this is counted as an additional pageview. If a visitor navigates to a different page and then returns to the original page, a second pageview is recorded as well.', 'koko-analytics'); ?>"><?php echo __('Pageviews', 'koko-analytics'); ?></div>
            </div>
            <div id="ka-top-posts" class='ka-topx--body'></div>
            <div class="ka-topx--placeholder"><?php esc_html_e('There is nothing here. Yet!', 'koko-analytics'); ?></div>
            <div class='ka-pagination'>
                <span class='ka-pagination--prev disabled'><?php echo __('Previous', 'koko-analytics'); ?></span>
                <span class='ka-pagination--next'><?php echo __('Next', 'koko-analytics'); ?></span>
            </div>
        </div>

        <div class='ka-topx ka-box ka-fade top-referrers'>
            <div class='ka-topx--head ka-topx--row'>
                <div class='ka-topx--rank'>#</div>
                <div><?php echo __('Referrers', 'koko-analytics'); ?></div>
                <div class='ka-topx--amount'><?php echo __('Visitors', 'koko-analytics'); ?></div>
                <div class='ka-topx--amount'><?php echo __('Pageviews', 'koko-analytics'); ?></div>
            </div>
            <div id="ka-top-referrers" class='ka-topx--body'></div>
            <div class="ka-topx--placeholder"><?php esc_html_e('There is nothing here. Yet!', 'koko-analytics'); ?></div>
            <div class='ka-pagination'>
                <span class='ka-pagination--prev disabled'><?php echo __('Previous', 'koko-analytics'); ?></span>
                <span class='ka-pagination--next'><?php echo __('Next', 'koko-analytics'); ?></span>
            </div>
        </div>

        <?php do_action('koko_analytics_show_dashboard_components'); ?>
    </div>

    <div class="ka-margin-s" style="text-align: right">
        <p><?php echo $random_usage_tip; ?></p>
    </div>

</div>

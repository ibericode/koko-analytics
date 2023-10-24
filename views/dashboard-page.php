<?php defined('ABSPATH') or exit;
$tab = 'dashboard';

/**
 * @var \KokoAnalytics\Admin $this
 * @var bool $is_cron_event_working
 * @var bool $is_buffer_dir_writable
 * @var string $buffer_dirname
 */
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
    $dateStart  = isset($_GET['start_date']) ? new \DateTimeImmutable($_GET['start_date']) : new \DateTimeImmutable('-28 days');
    $dateEnd    = isset($_GET['end_date']) ? new \DateTimeImmutable($_GET['end_date']) : new \DateTimeImmutable('now');
    $dateFormat = get_option('date_format');
    $preset     = ! isset($_GET['start_date']) && ! isset($_GET['end_date']) ? $settings['default_view'] : '';
    $random_usage_tip = $this->get_usage_tip();
    ?>

    <div class="notice notice-warning is-dismissible" id="koko-analytics-adblock-notice" style="display: none;">
        <p>
            <?php _e('You appear to be using an ad-blocker that has Koko Analytics on its blocklist. Please whitelist this domain in your ad-blocker setting if your dashboard does not seem to be working correctly.', 'koko-analytics'); ?>
        </p>
    </div>
    <script src="<?php echo plugins_url('/assets/dist/js/koko-analytics-script-test.js', KOKO_ANALYTICS_PLUGIN_FILE); ?>"
            defer onerror="document.getElementById('koko-analytics-adblock-notice').style.display = '';"></script>

    <noscript>
        <?php echo esc_html__('Please enable JavaScript for this page to work.', 'koko-analytics'); ?>
    </noscript>

    <?php require __DIR__ . '/nav.php'; ?>

    <div class="ka-datepicker">
        <div class='ka-datepicker--label'>
            <?php echo $dateStart->format($dateFormat); ?> — <?php echo $dateEnd->format($dateFormat); ?>
        </div>
        <div class="ka-datepicker--dropdown" style="display: none;">
            <div class="ka-datepicker--quicknav">
                <span class="ka-datepicker--quicknav-prev dashicons dashicons-arrow-left"
                      title=<?php echo __('Previous', 'koko-analytics'); ?>></span>
                <span class="ka-datepicker--quicknav-heading"><?php echo $dateStart->format($dateFormat); ?> — <?php echo $dateEnd->format($dateFormat); ?></span>
                <span class="ka-datepicker--quicknav-next dashicons dashicons-arrow-right"
                      title=<?php echo __('Next', 'koko-analytics'); ?>></span>
            </div>
            <div class="ka-datepicker--dropdown-content">
                <label for="ka-date-presets"
                       style="display: block;"><?php echo __('Date range', 'koko-analytics'); ?></label>
                <select id="ka-date-presets">
                    <?php foreach ($this->get_date_presets() as $key => $label) { ?>
                        <option value="<?php echo $key; ?>" <?php echo ( $key === $preset ) ? 'selected' : ''; ?>><?php echo $label; ?></option>
                    <?php } ?>
                </select>
                <div style="display: flex; margin-top: 12px;">
                    <div>
                        <label for='ka-date-start'
                               style="display: block;"><?php echo __('Start date', 'koko-analytics'); ?></label>
                        <input id='ka-date-start' type="date" size="10" placeholder="YYYY-MM-DD"
                               value="<?php echo $dateStart->format('Y-m-d'); ?>"/>
                        <span>&nbsp;&mdash;&nbsp;</span>
                    </div>
                    <div>
                        <label for='ka-date-end'
                               style="display: block;"><?php echo __('End date', 'koko-analytics'); ?></label>
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
                <span></span>
                <span class="ka-totals--change"></span>
            </div>
            <div class='ka-totals--subtext'>&nbsp;</div>
        </div>
        <div class='ka-fade'>
            <div class='ka-totals--heading'><?php echo __('Total pageviews', 'koko-analytics'); ?></div>
            <div class='ka-totals--amount'>
                <span></span>
                <span class="ka-totals--change"></span>
            </div>
            <div class='ka-totals--subtext'>&nbsp;</div>
        </div>
        <div id="ka-realtime" class='ka-fade'>
            <div class='ka-totals--heading'><?php echo __('Realtime pageviews', 'koko-analytics'); ?></div>
            <div class='ka-totals--amount'></div>
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
            <div class="ka-topx--placeholder">There is nothing here. Yet!</div>
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
            <div class="ka-topx--placeholder">There is nothing here. Yet!</div>
            <div class='ka-pagination'>
                <span class='ka-pagination--prev disabled'><?php echo __('Previous', 'koko-analytics'); ?></span>
                <span class='ka-pagination--next'><?php echo __('Next', 'koko-analytics'); ?></span>
            </div>
        </div>

        <?php do_action('koko_analytics_show_dashboard_components'); ?>
    </div>

    <div class="ka-margin-s">
        <p class="description ka-right"><?php echo $random_usage_tip; ?></p>
    </div>

</div>

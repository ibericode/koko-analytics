<?php defined('ABSPATH') or exit;
/**
 * @var \KokoAnalytics\Admin $this
 * @var array $settings
 * @var string $database_size
 * @var array $custom_endpoint
 */
$tab          = 'settings';
$user_roles   = $this->get_available_roles();
$date_presets = array(
    'custom' => __('Custom', 'koko-analytics'),
    'today' => __('Today', 'koko-analytics'),
    'yesterday' => __('Yesterday', 'koko-analytics'),
    'this_week' => __('This week', 'koko-analytics'),
    'last_week' => __('Last week', 'koko-analytics'),
    'last_28_days' => __('Last 28 days', 'koko-analytics'),
    'this_month' => __('This month', 'koko-analytics'),
    'last_month' => __('Last month', 'koko-analytics'),
    'this_quarter' => __('This quarter', 'koko-analytics'),
    'last_quarter' => __('Last quarter', 'koko-analytics'),
    'this_year' => __('This year', 'koko-analytics'),
    'last_year' => __('Last year', 'koko-analytics'),
);

?>
<div class="wrap" id="koko-analytics-admin">
    <?php require __DIR__ . '/nav.php'; ?>

    <div class="ka-admin-container">
        <h1><?php esc_html_e('Settings', 'koko-analytics'); ?></h1>

        <?php if (isset($_GET['settings-updated'])) { ?>
            <div class="notice notice-success is-dismissible">
                <p><strong><?php echo __('Settings saved.'); ?></strong></p>
            </div>
        <?php } ?>

        <form method="POST" action="<?php echo add_query_arg(array( 'koko_analytics_action' => 'save_settings' )); ?>">
            <?php wp_nonce_field('koko_analytics_save_settings'); ?>
            <div class="ka-margin-m">
                <label for="ka-exclude-user-roles" class="ka-settings--label"><?php esc_html_e('Exclude pageviews from these user roles', 'koko-analytics'); ?></label>
                <select id="ka-exclude-user-roles" multiple="" name="koko_analytics_settings[exclude_user_roles][]" style="min-height: <?php echo count($user_roles) * 30; ?>px; min-width: 240px;">
                    <?php
                    foreach ($user_roles as $key => $value) {
                        echo sprintf('<option value="%s" %s>%s</option>', esc_attr($key), selected(in_array($key, $settings['exclude_user_roles']), true, false), esc_html($value));
                    }
                    ?>
                </select>
                <p class="description">
                    <?php esc_html_e('Visits and pageviews from users with any of the selected roles will be ignored.', 'koko-analytics'); ?>
                    <?php echo ' '; ?>
                    <?php esc_html_e('Use CTRL to select multiple options.', 'koko-analytics'); ?>
                </p>
            </div>
            <div class="ka-margin-m">
                <fieldset>
                    <legend class="ka-settings--label"><?php esc_html_e('Use cookie to determine unique visitors and pageviews?', 'koko-analytics'); ?></legend>
                    <label class="ka-setings--cb-label"><input type="radio" name="koko_analytics_settings[use_cookie]" value="1" <?php checked($settings['use_cookie'], 1); ?>><?php esc_html_e('Yes'); ?></label>
                    <label class="ka-setings--cb-label"><input type="radio" name="koko_analytics_settings[use_cookie]" value="0" <?php checked($settings['use_cookie'], 0); ?>> <?php esc_html_e('No'); ?></label>
                </fieldset>
                <p class="description">
                    <?php esc_html_e('Set to "no" if you do not want to use a cookie. Without the use of a cookie, Koko Analytics can not reliably detect returning visitors.', 'koko-analytics'); ?>
                </p>
            </div>
            <div class="ka-margin-m">
                <label for="ka-default-date-period" class="ka-settings--label"><?php esc_html_e('Default date period', 'koko-analytics'); ?></label>
                <select id="ka-default-date-period" name="koko_analytics_settings[default_view]">
                    <?php
                    foreach ($date_presets as $key => $value) {
                        echo sprintf('<option value="%s" %s>%s</option>', esc_attr($key), selected($key === $settings['default_view'], true, false), esc_html($value));
                    }
                    ?>
                </select>
                <p class="description"><?php esc_html_e('The default date period to show when opening the analytics dashboard.', 'koko-analytics'); ?></p>
            </div>
            <div class="ka-margin-m">
                <label for="ka-prune-after-input" class="ka-settings--label"><?php esc_html_e('Automatically delete data older than how many months?', 'koko-analytics'); ?></label>
                <input id="ka-prune-after-input" name="koko_analytics_settings[prune_data_after_months]" type="number" step="1" min="0" max="600" value="<?php echo esc_attr($settings['prune_data_after_months']); ?>"> <?php esc_html_e('months', 'koko-analytics'); ?>
                <p class="description"><?php esc_html_e('Statistics older than the number of months configured here will automatically be deleted. Set to 0 to disable.', 'koko-analytics'); ?></p>
            </div>
            <div class="ka-margin-m">
                <?php submit_button(null, 'primary', 'submit', false); ?>
            </div>
        </form>

        <?php do_action('koko_analytics_show_settings_sections'); ?>

        <?php if (false === is_multisite()) { ?>
        <div class="ka-margin-l">
            <h2><?php esc_html_e('Performance', 'koko-analytics'); ?></h2>
            <?php if ($custom_endpoint['enabled']) { ?>
                <p>✓ <?php esc_html_e('The plugin is currently using an optimized tracking endpoint. Great!', 'koko-analytics'); ?></p>
            <?php } else { ?>
                <p>❌ <?php echo sprintf(esc_html__('The plugin is currently not using an optimized tracking endpoint. To address, create a file named %1s in your WordPress root directory with the following file contents:', 'koko-analytics'), $custom_endpoint['filename']); ?></p>
                <textarea readonly="readonly" class="widefat" rows="18" onfocus="this.select();" spellcheck="false"><?php echo esc_html($custom_endpoint['file_contents']); ?></textarea>
                <p><?php esc_html_e('Please note that this is entirely optional and only recommended for high-traffic websites.', 'koko-analytics'); ?></p>
            <?php } ?>
        </div>
        <?php } ?>

        <div class="ka-margin-l">
            <h2><?php esc_html_e('Data', 'koko-analytics'); ?></h2>
            <p><?php esc_html_e('Database size:', 'koko-analytics'); ?> <?php echo esc_html($database_size); ?> MB</p>
            <div class="ka-margin-m">
                <p><?php esc_html_e('Use the button below to erase all of your current analytics data. You may have to clear your browser\'s cache afterwards for the effect to be evident.', 'koko-analytics'); ?></p>
                <form method="POST" action="" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to reset all of your statistics? This can not be undone.', 'koko-analytics'); ?>')">
                    <?php wp_nonce_field('koko_analytics_reset_statistics'); ?>
                    <input type="hidden" name="koko_analytics_action" value="reset_statistics" />
                    <input type="submit" value="<?php esc_attr_e('Reset Statistics', 'koko-analytics'); ?>" class="button button-secondary" />
                </form>
            </div>
        </div>
    </div>
</div>

<?php defined('ABSPATH') or exit;
/**
 * @var \KokoAnalytics\Admin $this
 * @var array $settings
 * @var string $database_size
 * @var array $using_custom_endpoint
 * @var \KokoAnalytics\Endpoint_Installer $endpoint_installer
 * @var array $user_roles
 * @var array $date_presets
 */
$tab          = 'settings';
$public_dashboard_url = add_query_arg(['koko-analytics-dashboard' => 1], home_url());
?>
<link rel="stylesheet" href="<?php echo esc_attr(plugins_url('assets/dist/css/dashboard.css', KOKO_ANALYTICS_PLUGIN_FILE)); ?>?v=<?php echo KOKO_ANALYTICS_VERSION; ?>">

<div class="wrap" id="koko-analytics-admin">

    <div class="ka-dashboard-nav">
    <?php require __DIR__ . '/nav.php'; ?>
    </div>

    <div class="ka-admin-container">
        <h1 class="ka-logo"><?php echo esc_html__('Koko Analytics Settings', 'koko-analytics'); ?></h1>

        <?php if (isset($_GET['settings-updated'])) { ?>
            <div class="notice notice-success is-dismissible">
                <p><strong><?php echo esc_html__('Settings saved.'); ?></strong></p>
            </div>
        <?php } ?>

        <?php if (isset($_GET['endpoint-installed'])) { ?>
            <div class="notice notice-<?php echo $_GET['endpoint-installed'] ? 'success' : 'warning'; ?> is-dismissible">
                <p><?php echo $_GET['endpoint-installed'] ? esc_html__('Successfully installed optimized endpoint.') : esc_html__('Unable to install optimized endpoint. Please create the file manually and then try again.', 'koko-analytics'); ?></p>
            </div>
        <?php } ?>

        <form method="POST" action="<?php echo esc_attr(add_query_arg(['koko_analytics_action' => 'save_settings'])); ?>">
            <?php wp_nonce_field('koko_analytics_save_settings'); ?>
            <div class="ka-margin-m">
                <label for="ka-exclude-user-roles" class="ka-settings--label"><?php esc_html_e('Exclude pageviews from these user roles', 'koko-analytics'); ?></label>
                <select id="ka-exclude-user-roles" multiple="" name="koko_analytics_settings[exclude_user_roles][]" style="min-height: <?php echo count($user_roles) * 30; ?>px; min-width: 240px;">
                    <?php
                    foreach ($user_roles as $key => $value) {
                        $key = esc_attr($key);
                        $value = esc_html($value);
                        $selected = (in_array($key, $settings['exclude_user_roles']) ? 'selected' : '');

                        echo "<option value=\"{$key}\" {$selected}>{$value}</option>";
                    }
                    ?>
                </select>
                <p class="description">
                    <?php esc_html_e('Visits and pageviews from any of the selected user roles will be ignored.', 'koko-analytics'); ?>
                    <?php esc_html_e('Use CTRL to select multiple options.', 'koko-analytics'); ?>
                </p>
            </div>

            <div class="ka-margin-m">
                <label for="ka-exclude-ip-addresses" class="ka-settings--label"><?php esc_html_e('Exclude pageviews from these IP addresses', 'koko-analytics'); ?></label>
                <?php
                echo '<textarea id="ka-exclude-ip-addresses" name="koko_analytics_settings[exclude_ip_addresses]" class="widefat" rows="6">';
                echo esc_textarea(join(PHP_EOL, $settings['exclude_ip_addresses']));
                echo '</textarea>';
                ?>
                <p class="description">
                    <?php esc_html_e('Visits and pageviews from any of these IP addresses will be ignored.', 'koko-analytics'); ?>
                    <?php echo ' '; ?>
                    <?php esc_html_e('Enter each IP address on its own line.', 'koko-analytics'); ?>
                    <?php echo ' '; ?>
                    <?php printf(esc_html__('Your current IP address is %s.', 'koko-analytics'), '<code>' . $_SERVER['REMOTE_ADDR'] . '</code>'); ?>
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
                <fieldset>
                    <legend class="ka-settings--label"><?php esc_html_e('Should your dashboard be publicly accessible?', 'koko-analytics'); ?></legend>
                    <label class="ka-setings--cb-label"><input type="radio" name="koko_analytics_settings[is_dashboard_public]" value="1" <?php checked($settings['is_dashboard_public'], 1); ?>><?php esc_html_e('Yes'); ?></label>
                    <label class="ka-setings--cb-label"><input type="radio" name="koko_analytics_settings[is_dashboard_public]" value="0" <?php checked($settings['is_dashboard_public'], 0); ?>> <?php esc_html_e('No'); ?></label>
                </fieldset>
                <p class="description">
                    <?php echo wp_kses(sprintf(__('Set to "yes" if you want your dashboard to be publicly accessible. With this setting enabled, you can <a href="%s">find your public dashboard here</a>.', 'koko-analytics'), esc_attr($public_dashboard_url)), [ 'a' => [ 'href' => [] ] ]); ?>
                </p>
            </div>
            <div class="ka-margin-m">
                <label for="ka-default-date-period" class="ka-settings--label"><?php esc_html_e('Default date period', 'koko-analytics'); ?></label>
                <select id="ka-default-date-period" name="koko_analytics_settings[default_view]">
                    <?php
                    foreach ($date_presets as $key => $label) {
                        $selected = ($key === $settings['default_view'] ? 'selected' : '');
                        echo "<option value=\"{$key}\" {$selected}>{$label}</option>";
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

            <?php if (! defined('KOKO_ANALYTICS_PRO_VERSION')) { ?>
            <div class="ka-margin-m">
                <fieldset>
                    <legend class="ka-settings--label">Track all outbound link clicks?</legend>
                    <label class="ka-setings--cb-label"><input type="radio" disabled><?php esc_html_e('Yes'); ?></label>
                    <label class="ka-setings--cb-label"><input type="radio" disabled checked> <?php esc_html_e('No'); ?></label>
                </fieldset>
                <p class="description">
                    Select "yes" if you want Koko Analytics to count all clicks on links to external websites. <br>This feature is only available in <a href="https://www.kokoanalytics.com/pricing/">Koko Analytics Pro</a>.
               </p>
            </div>

            <div class="ka-margin-m">
                <fieldset>
                    <legend class="ka-settings--label">Track form submissions?</legend>
                    <label class="ka-setings--cb-label"><input type="radio" disabled><?php esc_html_e('Yes'); ?></label>
                    <label class="ka-setings--cb-label"><input type="radio" disabled checked> <?php esc_html_e('No'); ?></label>
                </fieldset>
                <p class="description">
                    Select "yes" if you want Koko Analytics to count all form submissions. <br>This feature is only available in <a href="https://www.kokoanalytics.com/pricing/">Koko Analytics Pro</a>.
               </p>
            </div>

            <div class="ka-margin-m">
                <fieldset>
                    <legend class="ka-settings--label">Send periodic email reports?</legend>
                    <ul>
                    <li><label class="ka-setings--cb-label"><input type="checkbox" disabled>Daily</label></li>
                    <li><label class="ka-setings--cb-label"><input type="checkbox" disabled>Weekly</label></li>
                    <li><label class="ka-setings--cb-label"><input type="checkbox" disabled>Monthly</label></li>
                    </ul>
                </fieldset>
                <p class="description">
                    Select the timeframes for which you want to receive an email report summarising your most important statistics. <br>This feature is only available in <a href="https://www.kokoanalytics.com/pricing/">Koko Analytics Pro</a>.
               </p>
            </div>
            <?php } // end if not Koko Analytics Pro ?>

            <div class="ka-margin-m">
                <?php submit_button(null, 'primary', 'submit', false); ?>
            </div>

            <?php do_action('koko_analytics_extra_settings_rows', $settings); ?>
        </form>

        <?php do_action('koko_analytics_show_settings_sections'); ?>

        <?php if ($endpoint_installer->is_eligibile()) { ?>
            <div class="ka-margin-l">
                <h2><?php esc_html_e('Performance', 'koko-analytics'); ?></h2>
                <?php if ($using_custom_endpoint) { ?>
                    <p><?php esc_html_e('The plugin is currently using an optimized tracking endpoint. Great!', 'koko-analytics'); ?></p>
                <?php } else { ?>
                    <p><?php esc_html_e('The plugin is currently not using an optimized tracking endpoint.', 'koko-analytics'); ?></p>
                    <form method="POST" action="">
                        <?php wp_nonce_field('koko_analytics_install_optimized_endpoint'); ?>
                        <input type="hidden" name="koko_analytics_action" value="install_optimized_endpoint">
                        <input type="submit" value="<?php esc_attr_e('Create optimized endpoint file', 'koko-analytics'); ?>" class="button button-secondary">
                    </form>
                    <p><?php printf(__('To use one, create the file %1s with the following file contents: ', 'koko-analytics'), '<code>' . $endpoint_installer->get_file_name() . '</code>'); ?></p>
                    <textarea readonly="readonly" class="widefat" rows="18" onfocus="this.select();" spellcheck="false"><?php echo esc_html($endpoint_installer->get_file_contents()); ?></textarea>
                    <p><?php esc_html_e('Please note that this is entirely optional and only recommended for high-traffic websites.', 'koko-analytics'); ?></p>
                <?php } ?>
            </div>
        <?php } ?>

        <div class="ka-margin-l">
            <h2><?php esc_html_e('Data', 'koko-analytics'); ?></h2>
            <p><?php esc_html_e('Database size:', 'koko-analytics'); ?> <?php echo esc_html($database_size); ?> MB</p>
            <p>
                <?php $seconds_since_last_aggregation = (time() - (int) get_option('koko_analytics_last_aggregation_at', 0)); ?>
                <?php esc_html_e('Last aggregation:', 'koko-analytics'); ?>
                <span <?php echo $seconds_since_last_aggregation > 300 ? 'style="color: red;"' : ''; ?>>
                    <?php echo esc_html(sprintf(__('%d seconds ago', 'koko-analytics'), $seconds_since_last_aggregation)); ?>
                </span>
            </p>
            <div class="ka-margin-m">
                <p><?php esc_html_e('Use the button below to erase all of your current analytics data. You may have to clear your browser\'s cache afterwards for the effect to be evident.', 'koko-analytics'); ?></p>
                <form method="POST" action="" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to reset all of your statistics? This can not be undone.', 'koko-analytics'); ?>')">
                    <?php wp_nonce_field('koko_analytics_reset_statistics'); ?>
                    <input type="hidden" name="koko_analytics_action" value="reset_statistics" />
                    <input type="submit" value="<?php esc_attr_e('Reset Statistics', 'koko-analytics'); ?>" class="button button-secondary" />
                </form>
            </div>
        </div>

        <div class="ka-margin-l">
            <h2><?php esc_html_e('Help', 'koko-analytics'); ?></h2>
            <p><?php echo wp_kses(sprintf(__('Have a look at our <a href="%1s">knowledge base</a> for help with configuring and using Koko Analytics.', 'koko-analytics'), 'https://www.kokoanalytics.com/kb/'), [ 'a' => [ 'href' => [] ] ]); ?></p>
        </div>

         <?php if (! defined('KOKO_ANALYTICS_PRO_VERSION')) { ?>
        <div class="ka-margin-l ka-pro-cta">
            <h2>Upgrade to Koko Analytics Pro</h2>
            <p>You are currently using the free version of Koko Analytics. There is a paid add-on called <a href="https://www.kokoanalytics.com/pricing/">Koko Analytics Pro</a> which adds several powerful features:</p>
            <ul class="ul-square">
                <li>Track outbound link clicks</li>
                <li>Track form submissions</li>
                <li><a href="https://www.kokoanalytics.com/kb/tracking-events/">Custom event tracking</a></li>
                <li><a href="https://www.kokoanalytics.com/2024/08/21/setting-up-email-reports-with-koko-analytics-pro/">Periodic email report of your most important statistics</a></li>
                <li>Export dashboard view to CSV</li>
            </ul>
            <p><a class="button" href="https://www.kokoanalytics.com/pricing/">View pricing</a></p>
            <p>By purchasing Koko Analytics Pro you get immediate access to these features while simultaneously supporting further development and maintenance of this free plugin.</p>
        </div>
         <?php } ?>
    </div>
</div>

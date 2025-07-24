<?php

use KokoAnalytics\Endpoint_Installer;

 defined('ABSPATH') or exit;
/**
 * @var \KokoAnalytics\Admin $this
 * @var array $settings
 * @var int $database_size
 * @var array $using_custom_endpoint
 * @var \KokoAnalytics\Endpoint_Installer $endpoint_installer
 * @var array $user_roles
 * @var array $date_presets
 */
$tab          = 'settings';
$public_dashboard_url = add_query_arg(['koko-analytics-dashboard' => 1], home_url());
?>
<div class="wrap" id="koko-analytics-admin">
    <div class="ka-dashboard-nav">
    <?php require __DIR__ . '/nav.php'; ?>
    </div>

    <div class="ka-admin-wrap">
        <div class="ka-admin-main">
            <h1 class="ka-logo"><?php esc_html_e('Koko Analytics Settings', 'koko-analytics'); ?></h1>

            <?php if (isset($_GET['settings-updated'])) { ?>
                <div class="notice notice-success is-dismissible">
                    <p><strong><?php esc_html_e('Settings saved.', 'koko-analytics'); ?></strong></p>
                </div>
            <?php } ?>

            <?php if (isset($_GET['endpoint-installed'])) { ?>
                <?php if ((int) $_GET['endpoint-installed']) { ?>
                    <div class="notice notice-success is-dismissible">
                        <p><?php esc_html_e('Successfully installed optimized endpoint.', 'koko-analytics'); ?></p>
                    </div>
                <?php } else { ?>
                <div class="notice notice-warning is-dismissible">
                    <p>
                        <?php esc_html_e('Unable to install optimized endpoint: ', 'koko-analytics'); ?>
                        <?php echo esc_html($_GET['endpoint-installed']); ?>
                    </p>
                </div>
                <?php } ?>
            <?php } // end if endpoint-installed ?>

            <form method="POST" action="<?php echo esc_attr(add_query_arg(['koko_analytics_action' => 'save_settings'])); ?>">
                <?php wp_nonce_field('koko_analytics_save_settings'); ?>
                <?php wp_referer_field(); ?>

                 <div class="ka-margin-m">
                    <fieldset>
                        <legend class="ka-settings--label"><?php esc_html_e('Which method should the plugin use to detect returning visitors and unique pageviews?', 'koko-analytics'); ?></legend>

                        <ul>
                            <li>
                                <label class="ka-setings--cb-label">
                                    <input type="radio" name="koko_analytics_settings[tracking_method]" value="cookie" <?php checked($settings['tracking_method'], 'cookie'); ?>>
                                    <strong><?php esc_html_e('Cookie', 'koko-analytics'); ?>: </strong> <?php esc_html_e('most accurate and privacy-friendly, but may require a cookie policy and/or consent.', 'koko-analytics'); ?>
                                </label>

                            </li>
                            <li>
                                <label class="ka-setings--cb-label">
                                <input type="radio" name="koko_analytics_settings[tracking_method]" value="fingerprint" <?php checked($settings['tracking_method'], 'fingerprint'); ?>>
                                <strong><?php esc_html_e('Cookieless', 'koko-analytics'); ?>: </strong> <?php esc_html_e('slightly less accurate and private, but does not require a cookie policy or consent.', 'koko-analytics'); ?>
                            </label>
                            </li>
                            <li><label class="ka-setings--cb-label">
                                <input type="radio" name="koko_analytics_settings[tracking_method]" value="none" <?php checked($settings['tracking_method'], 'none'); ?>>
                                <strong><?php esc_html_e('None', 'koko-analytics'); ?>: </strong> <?php esc_html_e('if you don\'t care about unique pageviews.', 'koko-analytics'); ?>
                            </label>
                            </li>
                        </ul>

                        <p class="description"><?php echo sprintf(wp_kses(__('For some more information about how each of these methods work, read this article on <a href="%1$s">cookie vs. cookieless tracking</a>.', 'koko-analytics'), ['a' => ['href' => true]]), 'https://www.kokoanalytics.com/kb/cookie-vs-cookieless-tracking-methods'); ?></p>
                    </fieldset>
                </div>

                <div class="ka-margin-m">
                    <label for="ka-exclude-user-roles" class="ka-settings--label"><?php esc_html_e('Exclude pageviews from these user roles', 'koko-analytics'); ?></label>
                    <select id="ka-exclude-user-roles" multiple="" name="koko_analytics_settings[exclude_user_roles][]" style="min-height: <?php echo count($user_roles) * 24; ?>px; min-width: 240px;">
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
                    echo sprintf('<textarea id="ka-exclude-ip-addresses" name="koko_analytics_settings[exclude_ip_addresses]" class="widefat" rows="%d">', max(4, count($settings['exclude_ip_addresses'])));
                    echo esc_textarea(join(PHP_EOL, $settings['exclude_ip_addresses']));
                    echo '</textarea>';
                    ?>
                    <p class="description">
                        <?php esc_html_e('Visits and pageviews from any of these IP addresses will be ignored.', 'koko-analytics'); ?>
                        <?php echo ' '; ?>
                        <?php esc_html_e('Enter each IP address on its own line.', 'koko-analytics'); ?>
                        <?php echo ' '; ?>
                        <?php printf(esc_html__('Your current IP address is %s.', 'koko-analytics'), '<code>' . esc_html(\KokoAnalytics\get_client_ip()) . '</code>'); ?>
                    </p>
                </div>

                <div class="ka-margin-m">
                    <fieldset>
                        <legend class="ka-settings--label"><?php esc_html_e('Should your dashboard be publicly accessible?', 'koko-analytics'); ?></legend>
                        <label class="ka-setings--cb-label"><input type="radio" name="koko_analytics_settings[is_dashboard_public]" value="1" <?php checked($settings['is_dashboard_public'], 1); ?>><?php esc_html_e('Yes', 'koko-analytics'); ?></label>
                        <label class="ka-setings--cb-label"><input type="radio" name="koko_analytics_settings[is_dashboard_public]" value="0" <?php checked($settings['is_dashboard_public'], 0); ?>> <?php esc_html_e('No', 'koko-analytics'); ?></label>
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

                <?php do_action('koko_analytics_extra_settings_rows_before_submit', $settings); ?>

                <div class="ka-margin-m">
                    <?php submit_button('', 'primary', 'submit', false); ?>
                </div>

                <?php do_action('koko_analytics_extra_settings_rows', $settings); ?>
            </form>

            <?php do_action('koko_analytics_show_settings_sections'); ?>

            <?php if (Endpoint_Installer::is_eligibile()) { ?>
                <div class="ka-margin-l">
                    <h2><?php esc_html_e('Performance', 'koko-analytics'); ?></h2>
                    <?php if ($using_custom_endpoint) { ?>
                        <p><span style="color: green;">✓</span> <?php esc_html_e('The plugin is currently using an optimized tracking endpoint. Great!', 'koko-analytics'); ?></p>
                    <?php } else { ?>
                        <p><?php esc_html_e('The plugin is currently not using an optimized tracking endpoint.', 'koko-analytics'); ?></p>
                        <form method="POST" action="">
                            <?php wp_nonce_field('koko_analytics_install_optimized_endpoint'); ?>
                            <input type="hidden" name="koko_analytics_action" value="install_optimized_endpoint">
                            <input type="submit" value="<?php esc_attr_e('Create optimized endpoint file', 'koko-analytics'); ?>" class="button button-secondary">
                        </form>
                        <p><?php printf(esc_html__('To use one, create the file %s with the following file contents: ', 'koko-analytics'), '<code>' . Endpoint_Installer::get_file_name() . '</code>'); ?></p>
                        <textarea readonly="readonly" class="widefat" rows="18" onfocus="this.select();" spellcheck="false"><?php echo esc_html(Endpoint_Installer::get_file_contents()); ?></textarea>
                        <p><?php esc_html_e('Please note that this is entirely optional and only recommended for high-traffic websites.', 'koko-analytics'); ?></p>
                    <?php } ?>
                </div>
            <?php } ?>

            <div class="ka-margin-l">
                <h2><?php esc_html_e('Data', 'koko-analytics'); ?></h2>
                <p><?php esc_html_e('Database size:', 'koko-analytics'); ?> <?php echo esc_html(size_format($database_size)); ?><br />
                    <?php $seconds_since_last_aggregation = (time() - (int) get_option('koko_analytics_last_aggregation_at', 0)); ?>
                    <?php esc_html_e('Last aggregation:', 'koko-analytics'); ?>
                    <span <?php echo $seconds_since_last_aggregation > 300 ? 'style="color: red;"' : ''; ?>>
                        <?php printf(esc_html__('%d seconds ago', 'koko-analytics'), $seconds_since_last_aggregation); ?>
                    </span>
                </p>
                <div class="ka-margin-m">
                    <h3 id="import-data"><?php esc_html_e('Import data', 'koko-analytics'); ?></h3>

                    <?php if (isset($_GET['import-error'])) { ?>
                        <?php if ($_GET['import-error'] == UPLOAD_ERR_INI_SIZE) { ?>
                            <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Sorry, your import file is too large. Please import it into your database in some other way.', 'koko-analytics'); ?></p></div>
                        <?php } elseif ($_GET['import-error'] == UPLOAD_ERR_NO_FILE) { ?>
                            <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Import file can not be empty', 'koko-analytics'); ?></p></div>
                        <?php } else { ?>
                            <div class="notice notice-error is-dismissible"><p><?php esc_html_e('Something went wrong trying to process your import file.', 'koko-analytics'); ?></p></div>
                        <?php } ?>
                    <?php } ?>

                    <?php if (isset($_GET['import-success'])) { ?>
                        <div class="notice notice-success is-dismissible"><p><?php esc_html_e('Database was successfully imported from the given file.', 'koko-analytics'); ?></p></div>
                    <?php } ?>

                    <p><?php esc_html_e('You can import a dataset from an earlier export into Koko Analytics using the form below.', 'koko-analytics'); ?></p>

                    <form method="POST" action="" enctype="multipart/form-data" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to import the given dataset? This will replace your current data.', 'koko-analytics'); ?>')">
                        <?php wp_nonce_field('koko_analytics_import_data'); ?>
                        <input type="hidden" name="koko_analytics_action" value="import_data" />
                        <div>
                            <input type="file" name="import-file" id="import-file" accept=".sql,application/sql,text/sql,text/plain" required>
                        </div>
                        <div class="ka-margin-s">
                            <input type="submit" value="<?php esc_attr_e('Import', 'koko-analytics'); ?>" class="button button-secondary" />
                        </div>
                    </form>
                </div>
                <div class="ka-margin-m">
                    <h3><?php esc_html_e('Export data', 'koko-analytics'); ?></h3>
                    <p><?php esc_html_e('Export your current dataset to SQL using the form below.', 'koko-analytics'); ?></p>
                    <form method="POST" action="">
                        <?php wp_nonce_field('koko_analytics_export_data'); ?>
                        <input type="hidden" name="koko_analytics_action" value="export_data" />
                        <input type="submit" value="<?php esc_attr_e('Export', 'koko-analytics'); ?>" class="button button-secondary" />
                    </form>
                </div>
                <div class="ka-margin-m">
                    <h3><?php esc_html_e('Reset data', 'koko-analytics'); ?></h3>
                    <p><?php esc_html_e('Use the button below to erase all of your current analytics data.', 'koko-analytics'); ?></p>
                    <form method="POST" action="" onsubmit="return confirm('<?php esc_attr_e('Are you sure you want to reset all of your statistics? This can not be undone.', 'koko-analytics'); ?>')">
                        <?php wp_nonce_field('koko_analytics_reset_statistics'); ?>
                        <input type="hidden" name="koko_analytics_action" value="reset_statistics" />
                        <input type="submit" value="<?php esc_attr_e('Reset Statistics', 'koko-analytics'); ?>" class="button button-secondary" />
                    </form>
                </div>
            </div><?php // end data section div ?>
        </div><?php // end container ?>

        <div class="ka-admin-sidebar">

            <?php if (! defined('KOKO_ANALYTICS_PRO_VERSION')) { ?>
            <div>
                <div class="ka-pro-cta ka-margin-m">
                    <h2><?php esc_html_e('Upgrade to Koko Analytics Pro', 'koko-analytics'); ?></h2>
                    <p><?php esc_html_e('You are currently using the free version of Koko Analytics. There is a premium version of this plugin which adds several powerful features.', 'koko-analytics'); ?></p>
                    <h4><?php esc_html_e('Premium features', ' koko-analytics'); ?></h4>
                    <ul class="ul-square">
                        <li><a href="https://www.kokoanalytics.com/features/geo-location/"><?php esc_html_e('Geo-location', 'koko-analytics'); ?></a></li>
                        <li><a href="https://www.kokoanalytics.com/features/custom-event-tracking/"><?php esc_html_e('Event Tracking', 'koko-analytics'); ?></a></li>
                        <li><a href="https://www.kokoanalytics.com/features/email-reports/"><?php esc_html_e('Periodic Email Reports', 'koko-analytics'); ?></a></li>
                        <li><a href="https://www.kokoanalytics.com/features/pageviews-column/"><?php esc_html_e('Pageviews Column', 'koko-analytics'); ?></a></li>
                        <li><a href="https://www.kokoanalytics.com/features/csv-export/"><?php esc_html_e('CSV Export', 'koko-analytics'); ?></a></li>
                        <li><a href="https://www.kokoanalytics.com/features/admin-bar/"><?php esc_html_e('Admin Bar', 'koko-analytics'); ?></a></li>
                    </ul>
                    <p><?php esc_html_e('You can unlock all these benefits for a small yearly fee.', 'koko-analytics'); ?>
                    <a class="" href="https://www.kokoanalytics.com/pricing/" target="_blank"><?php esc_html_e('Take a look at Koko Analytics Pro now.', 'koko-analytics'); ?></a></p>

                </div>
            </div>
            <?php } // end if defined KOKO_ANALYTICS_PRO ?>

            <div>
                <h2><?php esc_html_e('Coming from another statistics plugin?', 'koko-analytics'); ?></h2>
                <p><?php esc_html_e('Use the import tool to import your historical data from other plugins into Koko Analytics', 'koko-analytics'); ?>

                <ul class="ul-square">
                    <li><a href="<?php echo esc_attr(add_query_arg(['tab' => 'jetpack_importer'])); ?>"><?php esc_html_e('Import from Jetpack Stats', 'koko-analytics'); ?></a></li>
                    <li><a href="<?php echo esc_attr(add_query_arg(['tab' => 'burst_importer'])); ?>"><?php esc_html_e('Import from Burst Statistics', 'koko-analytics'); ?></a></li>
                </ul>
            </div>

            <div>
                <h2><?php esc_html_e('Help', 'koko-analytics'); ?></h2>
                <ul class="ul-square">
                    <li><?php printf(esc_html__('Have a look at our %1$sknowledge base%2$s for help with configuring and using Koko Analytics.', 'koko-analytics'), '<a href="https://www.kokoanalytics.com/kb/" target="_blank">', '</a>'); ?></li>
                    <li><?php printf(esc_html__('Go through our %1$srepository of sample code snippets%2$s for inspiration on modifying the default Koko Analytics behavior.', 'koko-analytics'), '<a href="https://github.com/ibericode/koko-analytics/tree/main/code-snippets" target="_blank">', '</a>'); ?></li>
                    <li><?php printf(esc_html__('Vote on %1$snew features you would like to have in Koko Analytics%2$s.', 'koko-analytics'), '<a href="https://github.com/ibericode/koko-analytics/discussions?discussions_q=is%3Aopen+sort%3Atop" target="_blank">', '</a>'); ?></li>
                </ul>
            </div>

            <?php
            // Fetch 5 most recent posts from www.kokoanalytics.com
            $posts = get_transient('koko_analytics_remote_posts');
            if (!$posts) {
                $response = wp_remote_get('https://www.kokoanalytics.com/wp-json/wp/v2/posts?per_page=5');
                if ($response && wp_remote_retrieve_response_code($response) == 200) {
                    $body = wp_remote_retrieve_body($response);

                    // in case response is 200 but can't be decoded as JSON, use an empty array instead
                    $posts = json_decode($body) ?? [];
                } else {
                    // store empty array to prevent doing an HTTP request on every page load
                    // we'll try again in 8 hours
                    $posts = [];
                }
                set_transient('koko_analytics_remote_posts', $posts, HOUR_IN_SECONDS * 8);
            }

            if (count($posts) > 0) { ?>
            <div>
                <h2><?php esc_html_e('Koko Analytics news', 'koko-analytics'); ?></h2>
                <ul class="ul-square" style="margin-left: 1em; margin-bottom: 1em;">
                    <?php foreach ($posts as $p) { ?>
                        <li><a href="<?php echo esc_attr($p->link); ?>"><?php echo esc_html($p->title->rendered); ?></a></li>
                    <?php } ?>
                </ul>
            </div>
            <?php } ?>
        </div>
    </div><?php // end flex wrap ?>
</div>

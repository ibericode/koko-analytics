<h2 class="mt-0 mb-3"><?= esc_html__('Tracking settings', 'koko-analytics'); ?></h2>
<form method="POST" action="">
    <?php wp_nonce_field('koko_analytics_save_settings'); ?>
    <?php wp_referer_field(); ?>
    <input type="hidden" name="koko_analytics_action" value="save_settings">

    <div class="mb-4">
        <fieldset class="mb-2">
            <legend class="ka-label"><?php esc_html_e('Which method should the plugin use to detect returning visitors and unique pageviews?', 'koko-analytics'); ?></legend>
            <ul class="list-unstyled mt-0 mb-2">
                <li>
                    <label class="">
                        <input type="radio" name="koko_analytics_settings[tracking_method]" value="cookie" <?php checked($settings['tracking_method'], 'cookie'); ?>>
                        <strong><?php esc_html_e('Cookie', 'koko-analytics'); ?>: </strong> <?php esc_html_e('most accurate and privacy-friendly, but may require a cookie policy and/or consent.', 'koko-analytics'); ?>
                    </label>

                </li>
                <li>
                    <label class="">
                        <input type="radio" name="koko_analytics_settings[tracking_method]" value="fingerprint" <?php checked($settings['tracking_method'], 'fingerprint'); ?>>
                        <strong><?php esc_html_e('Cookieless', 'koko-analytics'); ?>: </strong> <?php esc_html_e('slightly less accurate and private, but does not require a cookie policy or consent.', 'koko-analytics'); ?>
                    </label>
                </li>
                <li><label class="">
                        <input type="radio" name="koko_analytics_settings[tracking_method]" value="none" <?php checked($settings['tracking_method'], 'none'); ?>>
                        <strong><?php esc_html_e('None', 'koko-analytics'); ?>: </strong> <?php esc_html_e('if you don\'t care about unique pageviews.', 'koko-analytics'); ?>
                    </label>
                </li>
            </ul>

            <p class="description"><?php echo sprintf(wp_kses(__('For some more information about how each of these methods work, read this article on <a href="%1$s">cookie vs. cookieless tracking</a>.', 'koko-analytics'), ['a' => ['href' => true]]), 'https://www.kokoanalytics.com/kb/cookie-vs-cookieless-tracking-methods'); ?></p>
        </fieldset>
    </div>
    <div class="mb-4">
        <label for="ka-exclude-user-roles" class="ka-label"><?php esc_html_e('Exclude pageviews from these user roles', 'koko-analytics'); ?></label>
        <select id="ka-exclude-user-roles" multiple="" class="ka-select mb-2" name="koko_analytics_settings[exclude_user_roles][]" style="min-height: <?php echo count($user_roles) * 24; ?>px; min-width: 240px;">
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

    <div class="mb-4">
        <label for="ka-exclude-ip-addresses" class="ka-label"><?php esc_html_e('Exclude pageviews from these IP addresses', 'koko-analytics'); ?></label>
        <?php
        echo sprintf('<textarea id="ka-exclude-ip-addresses" name="koko_analytics_settings[exclude_ip_addresses]" class="ka-input mb-2" rows="%d">', max(4, count($settings['exclude_ip_addresses'])));
        echo esc_textarea(join(PHP_EOL, $settings['exclude_ip_addresses']));
        echo '</textarea>';
        ?>
        <p class="description">
            <?php esc_html_e('Visits and pageviews from any of these IP addresses will be ignored.', 'koko-analytics'); ?>
            <?php echo ' '; ?>
            <?php esc_html_e('Enter each IP address on its own line.', 'koko-analytics'); ?>
            <?php echo ' '; ?>
            <?php if (\KokoAnalytics\get_client_ip()) : ?>
                <?php printf(esc_html__('Your current IP address is %s.', 'koko-analytics'), '<code>' . esc_html(\KokoAnalytics\get_client_ip()) . '</code>'); ?>
            <?php endif; ?>
        </p>
    </div>

    <?php do_action('koko_analytics_output_after_tracking_settings', $settings); ?>

    <div>
        <input type="submit" class="btn btn-primary" value="<?= esc_attr__('Save Changes') ?>">
    </div>
</form>


<?php if (!defined('KOKO_ANALYTICS_PRO_VERSION')) : ?>
    <p class="text-muted mt-5"><?= sprintf(__('Collect country, browser, operating system and device statistics with %s.', 'koko-analytics'), '<a href="https://www.kokoanalytics.com/pricing/">Koko Analytics Pro</a>'); ?></p>
<?php endif; ?>

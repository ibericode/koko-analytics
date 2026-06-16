<?php

defined('ABSPATH') || exit;

/**
* @var array $settings
* @var array $user_roles
*/
?>
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

            <p class="description">
                <?php /* translators: %1$s: cookie vs. cookieless tracking documentation URL. */ ?>
                <?php printf(wp_kses(__('For some more information about how each of these methods work, read this article on <a href="%1$s">cookie vs. cookieless tracking</a>.', 'koko-analytics'), ['a' => ['href' => true]]), 'https://www.kokoanalytics.com/docs/tracking/cookie-vs-cookieless-tracking-methods/#utm_source=koko-analytics&amp;utm_medium=link&amp;utm_campaign=free-plugin-settings-tracking-docs'); ?>
            </p>
        </fieldset>
    </div>
    <div class="mb-4">
        <label for="ka-exclude-user-roles" class="ka-label"><?php esc_html_e('Exclude pageviews from these user roles', 'koko-analytics'); ?></label>
        <select id="ka-exclude-user-roles" multiple="" class="ka-select mb-2" name="koko_analytics_settings[exclude_user_roles][]" style="min-height: <?php echo count($user_roles) * 24; ?>px; min-width: 240px;">
            <?php
            foreach ($user_roles as $key => $value) {
                $key      = esc_attr($key);
                $value    = esc_html($value);
                $selected = (in_array($key, $settings['exclude_user_roles']) ? 'selected' : '');

                echo "<option value=\"{$key}\" {$selected}>{$value}</option>"; // phpcs:ignore
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
        printf('<textarea id="ka-exclude-ip-addresses" name="koko_analytics_settings[exclude_ip_addresses]" class="ka-input mb-2" rows="%d">', esc_attr((string) max(4, count($settings['exclude_ip_addresses']))));
        echo esc_textarea(join(PHP_EOL, $settings['exclude_ip_addresses']));
        echo '</textarea>';
        ?>
        <p class="description">
            <?php esc_html_e('Visits and pageviews from any of these IP addresses will be ignored.', 'koko-analytics'); ?>
            <?php echo ' '; ?>
            <?php esc_html_e('Enter each IP address on its own line.', 'koko-analytics'); ?>
            <?php echo ' '; ?>
            <?php if (\KokoAnalytics\get_client_ip()) : ?>
                <?php /* translators: %s: current IP address. */ ?>
                <?php printf(esc_html__('Your current IP address is %s.', 'koko-analytics'), '<code>' . esc_html(\KokoAnalytics\get_client_ip()) . '</code>'); ?>
            <?php endif; ?>
        </p>
    </div>

        
    <?php if (! defined('KOKO_ANALYTICS_PRO_VERSION')) : ?>
    <div class="ka-locked-section">
        <div class="ka-locked-section-button">
            <a href="https://www.kokoanalytics.com/pricing/?utm_source=koko-analytics&utm_medium=link&utm_campaign=free-plugin-settings-tracking" class="btn btn-koko btn-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-unlock me-2" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M12 0a4 4 0 0 1 4 4v2.5h-1V4a3 3 0 1 0-6 0v2h.5A2.5 2.5 0 0 1 12 8.5v5A2.5 2.5 0 0 1 9.5 16h-7A2.5 2.5 0 0 1 0 13.5v-5A2.5 2.5 0 0 1 2.5 6H8V4a4 4 0 0 1 4-4M2.5 7A1.5 1.5 0 0 0 1 8.5v5A1.5 1.5 0 0 0 2.5 15h7a1.5 1.5 0 0 0 1.5-1.5v-5A1.5 1.5 0 0 0 9.5 7z"/></svg> 
                Unlock with Pro
            </a>
        </div>
        <div class="ka-locked-section-contents">
            <div class="mb-4">
                <label class="ka-label"><?php esc_html_e('Enable geo-location?', 'koko-analytics'); ?></label>
                <div class="mb-2">
                    <label class="me-1"><input type="radio" disabled> <?php esc_html_e('Yes', 'koko-analytics'); ?></label> &nbsp;
                    <label><input type="radio" checked="checked" disabled> <?php esc_html_e('No', 'koko-analytics'); ?></label>
                </div>
                <p class="description"><?php esc_html_e('Select "yes" if you want Koko Analytics to geo-locate visitors by their IP address.', 'koko-analytics'); ?></p>
            </div>
            <div class="mb-4">
                <label class="ka-label"><?php esc_html_e('Enable device tracking?', 'koko-analytics'); ?></label>
                <div class="mb-2">
                    <label class="me-1"><input type="radio" disabled> <?php esc_html_e('Yes', 'koko-analytics'); ?></label> &nbsp;
                    <label><input type="radio" checked="checked" disabled> <?php esc_html_e('No', 'koko-analytics'); ?></label>
                </div>
                <p class="description"><?php esc_html_e('Select "yes" if you want Koko Analytics to count browsers, operating systems and device types.', 'koko-analytics'); ?></p>
            </div>
            <div class="mb-0">
                <label class="ka-label"><?php esc_html_e('Enable UTM tracking?', 'koko-analytics'); ?></label>
                <div class="mb-2">
                    <label class="me-1"><input type="radio" disabled> <?php esc_html_e('Yes', 'koko-analytics'); ?></label> &nbsp;
                    <label><input type="radio" checked="checked" disabled> <?php esc_html_e('No', 'koko-analytics'); ?></label>
                </div>
                <p class="description"><?php esc_html_e('Select "yes" if you want Koko Analytics to track utm_source, utm_medium and utm_campaign parameters from the URL query string or hash.', 'koko-analytics'); ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php do_action('koko_analytics_output_after_tracking_settings', $settings); ?>

    <div>
        <input type="submit" class="btn btn-primary" value="<?= esc_attr__('Save Changes'); ?>">
    </div>
</form>

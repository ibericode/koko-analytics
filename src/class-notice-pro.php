<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Notice_Pro
{
    public function __construct()
    {
        $this->maybe_show();
    }

    public function get_settings(): array
    {
        $settings = get_settings();
        $defaults = [
            'timestamp_installed' => null,
            'dismissed' => false,
        ];
        $settings['notice_pro'] = array_merge($defaults, $settings['notice_pro'] ?? []);
        return $settings;
    }

    private function get_setting(string $key)
    {
        $settings = $this->get_settings();
        return $settings['notice_pro'][$key];
    }

    private function update_setting(string $key, $value): void
    {
        $settings = $this->get_settings();
        $settings['notice_pro'][$key] = $value;
        update_option('koko_analytics_settings', $settings, true);
    }

    public function maybe_show(): void
    {
        // don't show if user doesn't have capability for managing koko analytics
        // don't show if Koko Analytics Pro is installed
        if (!current_user_can('manage_koko_analytics') || defined('KOKO_ANALYTICS_PRO_VERSION')) {
            return;
        }

        if (isset($_GET['ka-notice-pro-dismiss'])) {
            $this->update_setting('dismissed', true);
            return;
        }

        $date_installed = $this->get_setting('timestamp_installed');

        // if first time loading dashboard, don't show
        if ($date_installed === null) {
            $this->update_setting('timestamp_installed', time());
            return;
        }

        // if installed less than 30 days ago, don't show
        if ($date_installed > time() - (86400 * 30)) {
            return;
        }

        // if previously dismissed, don't show
        if ($this->get_setting('dismissed')) {
            return;
        }

        ?>
        <style>
            .ka-notice {background: #fff8c5; border: 1px solid #d4a72c66; padding: 0 1em; margin: 1em 0; font-size: 14px;}
            .ka-notice summary { padding: 1em 0; cursor: pointer; }
            .ka-notice p, .ka-notice li { font-size: 14px; }
        </style>
        <div class="ka-notice">
            <details>
                <summary><?php printf(esc_html__('%1$sHello!%2$s You have been using Koko Analytics for a while now. We are showing you this one-time notice to ask for a small favor.', 'koko-analytics'), '<strong>', '</strong>'); ?></summary>
                <p><?php esc_html_e('If you enjoy using this free plugin, please consider giving back by:', 'koko-analytics'); ?></p>
                <ul class="ul-square">
                    <li><?php printf(esc_html__('Purchasing %1$sKoko Analytics Pro%2$s, unlocking several benefits like %3$speriodic email reports%2$s, %4$sstats in your admin bar%2$s and %5$scustom event tracking%2$s.', 'koko-analytics'), '<a href="https://www.kokoanalytics.com/pricing/">', '</a>', '<a href="https://www.kokoanalytics.com/features/email-reports/">', '<a href="https://www.kokoanalytics.com/features/admin-bar/">', '<a href="https://www.kokoanalytics.com/features/custom-event-tracking/">', ''); ?></li>
                    <li><?php printf(esc_html__('%1$sLeaving a plugin review on WordPress.org%2$s.', 'koko-analytics'), '<a href="https://wordpress.org/support/view/plugin-reviews/koko-analytics?rate=5#postform">', '</a>'); ?></li>
                    <li><?php esc_html_e('Writing about Koko Analytics on your blog or social media.', 'koko-analytics'); ?></li>
                </ul>
                <p><?php printf(esc_html__('We thank you for your consideration. %s~ Danny, Harish and Arne', 'koko-analytics'), '<br />'); ?></p>
            <p><a href="https://www.kokoanalytics.com/pricing/" class="button button-primary"><?php esc_html_e('Learn more about Koko Analytics Pro', 'koko-analytics'); ?></a> &nbsp; <a href="<?php echo esc_attr(add_query_arg(['ka-notice-pro-dismiss' => 1])); ?>" style="color: #CC0000;"><?php esc_html_e('Never show again', 'koko-analytics'); ?></a></p>
            </details>
        </div>
        <?php
    }
}

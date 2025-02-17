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
                <summary><strong>Hello!</strong> You have been using Koko Analytics for a while now. We are showing you this one-time notice to ask for a small favor.</summary>
                <p>If you enjoy using this free plugin, please consider giving back by:</p>
                <ul class="ul-square">
                    <li>Purchasing <a href="https://www.kokoanalytics.com/pricing/">Koko Analytics Pro</a>, unlocking several benefits like <a href="https://www.kokoanalytics.com/features/email-reports/">periodic email reports</a>, <a href="https://www.kokoanalytics.com/features/admin-bar/">stats in your admin bar</a> and <a href="https://www.kokoanalytics.com/features/custom-event-tracking/">custom event tracking</a>.</li>
                    <li><a href="https://wordpress.org/support/view/plugin-reviews/koko-analytics?rate=5#postform">Leaving a plugin review on WordPress.org.</a></li>
                    <li>Writing about Koko Analytics on your blog or social media.</li>
                </ul>
                <p>We thank you for your consideration. <br />
                   ~ Danny, Harish and Arne</p>
            <p><a href="https://www.kokoanalytics.com/pricing/" class="button button-primary">Learn more about Koko Analytics Pro</a> &nbsp; <a href="<?php echo esc_attr(add_query_arg(['ka-notice-pro-dismiss' => 1])); ?>" style="color: #CC0000;">Never show again</a></p>
            </details>
        </div>
        <?php
    }
}

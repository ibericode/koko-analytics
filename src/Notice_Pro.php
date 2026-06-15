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
        $settings               = get_settings();
        $defaults               = [
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
        $settings                     = $this->get_settings();
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
        <div class="ka-notice rounded mb-3 p-3" style="background: #f5f8ff;">
            <h2 class="mt-0 mb-2"><?php esc_html_e('Enjoying Koko Analytics?', 'koko-analytics'); ?></h2>
            <p class="mt-0 mb-2"><?php esc_html_e('A quick review on WordPress.org helps more people find the plugin and helps us keep maintaining it for the long term.', 'koko-analytics'); ?></p>
            <div>
                <a class="btn btn-sm btn-primary me-2" href="https://wordpress.org/support/view/plugin-reviews/koko-analytics?rate=5#postform"><?php esc_html_e('Review the plugin on WordPress.org', 'koko-analytics'); ?></a>
                <a href="<?php echo esc_url(add_query_arg(['ka-notice-pro-dismiss' => 1])); ?>"><?php esc_html_e('Don\'t show this again', 'koko-analytics'); ?></a>
            </div>
        </div>
        <?php
    }
}

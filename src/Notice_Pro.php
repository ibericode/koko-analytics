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
        <div class="ka-notice rounded mb-3 p-3" style="background: #fff3cd;">
            <details>
                <summary style="cursor: pointer;">
                    <strong><?php esc_html_e('Hello!', 'koko-analytics'); ?></strong>
                    <?php esc_html_e('You have been using Koko Analytics for a while now. We are showing you this one-time notice to ask for a small favor.', 'koko-analytics'); ?>
                </summary>
                <p><?= esc_html__('If you enjoy using this free plugin, consider helping us out by:', 'koko-analytics'); ?></p>
                <ul class="ul-square mb-4">
                    <li><a href="https://www.kokoanalytics.com/pricing/" class=""><?php esc_html_e('Upgrade to Koko Analytics Pro', 'koko-analytics'); ?></a></li>
                    <li><a href="https://wordpress.org/support/view/plugin-reviews/koko-analytics?rate=5#postform"><?=esc_html__('Review the plugin on WordPress.org', 'koko-analytics'); ?></a></li>
                    <li><?= esc_html__('Write about Koko Analytics on your blog or on social media', 'koko-analytics'); ?></li>
                </ul>

                <p class="mb-0"><a href="<?php echo esc_attr(add_query_arg(['ka-notice-pro-dismiss' => 1])); ?>" class="btn btn-danger btn-sm"><?php esc_html_e('Never show again', 'koko-analytics'); ?></a></p>
            </details>
        </div>
        <?php
    }
}

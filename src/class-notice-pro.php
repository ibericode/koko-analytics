<?php

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

    private function update_setting(string $key, $value)
    {
        $settings = $this->get_settings();
        $settings['notice_pro'][$key] = $value;
        update_option('koko_analytics_settings', $settings, true);
    }

    public function maybe_show()
    {
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }
        if (defined('KOKO_ANALYTICS_PRO_VERSION')) {
            return;
        }

        if (isset($_GET['ka-notice-pro-dismiss'])) {
            $this->update_setting('dismissed', true);
            return;
        }

        $date_installed = $this->get_setting('date_installed');
        if ($date_installed === null) {
            $this->update_setting('date_installed', time());
            return;
        }

        if ($date_installed > time() - (86400 * 30)) {
            return;
        }

        $dismissed = $this->get_setting('dismissed');
        if ($dismissed) {
            return;
        }

        // SHOW NOTICE
        ?>
        <style>
            .ka-notice {background: #fff8c5; border: 1px solid #d4a72c66; padding: 0 1em; margin: 1em 0; font-size: 14px;}
            .ka-notice summary { padding: 1em 0; }
            .ka-notice p, .ka-notice li { font-size: 14px; }
        </style>
        <div class="ka-notice">
            <details>
                <summary><strong>Hello!</strong> You have been using Koko Analytics for a while now. We are showing you this one-time notice to ask for a small favor.</summary>
            <p>If you enjoy using this free plugin, please consider giving back by:</p>
            <ul class="ul-square">
                <li>Purchasing a license for <a href="https://www.kokoanalytics.com/pricing/">Koko Analytics Pro</a>, unlocking powerful features like <a href="https://www.kokoanalytics.com/features/email-reports/">periodic email reports</a> and <a href="https://www.kokoanalytics.com/features/custom-event-tracking/">custom event tracking</a>.</li>
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

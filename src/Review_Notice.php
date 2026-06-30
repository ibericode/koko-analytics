<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

/**
 * Standalone, drop-in admin notice that asks the user to review the plugin
 * once it has been installed for a while.
 *
 * All dependencies are passed through the constructor so the class makes no
 * assumptions about the host plugin. The notice's state is stored as keys
 * inside the option named in the constructor (merged with any existing data),
 * while the dismissal is tracked per-user — unless a previous version already
 * dismissed it globally, in which case it stays dismissed for everyone.
 */
class Review_Notice
{
    private string $plugin_name;
    private string $plugin_slug;
    private string $option_name;
    private string $state_key;
    private string $capability;
    private int $show_after_days;

    /**
     * @param string $plugin_name     Human-readable plugin name, e.g. "Koko Analytics".
     * @param string $plugin_slug     WordPress.org slug, e.g. "koko-analytics".
     * @param string $option_name     Option to store (and merge) the notice state into.
     * @param string $state_key       Key within that option to namespace the state under.
     *                                Defaults to "<slug>_notice"; pass the legacy key to
     *                                stay backwards compatible with existing data.
     * @param string $capability      Capability a user must have to see the notice.
     * @param int    $show_after_days Days after install before the notice appears.
     */
    public function __construct(
        string $plugin_name,
        string $plugin_slug,
        string $option_name,
        string $state_key = '',
        string $capability = 'manage_options',
        int $show_after_days = 30
    ) {
        $this->plugin_name     = $plugin_name;
        $this->plugin_slug     = $plugin_slug;
        $this->option_name     = $option_name;
        $this->state_key       = $state_key !== '' ? $state_key : str_replace('-', '_', $plugin_slug) . '_notice';
        $this->capability      = $capability;
        $this->show_after_days = $show_after_days;
    }

    public function maybe_show(): void
    {
        // Don't show if the user can't manage the plugin.
        if (!current_user_can($this->capability)) {
            return;
        }

        $state = $this->get_state();

        // Dismissed globally by a previous version: never show again, for anyone.
        if (!empty($state['dismissed'])) {
            return;
        }

        // Handle a dismissal request (per-user from here on).
        if (isset($_GET[$this->get_dismiss_param()])) {
            $this->dismiss_for_current_user();
            return;
        }

        $installed_at = $state['timestamp_installed'];

        // First time loading the dashboard: start the clock, don't show yet.
        if ($installed_at === null) {
            $this->update_state('timestamp_installed', time());
            return;
        }

        // Installed too recently.
        if ($installed_at > time() - ($this->show_after_days * DAY_IN_SECONDS)) {
            return;
        }

        // Already dismissed by the current user.
        if ($this->is_dismissed_by_current_user()) {
            return;
        }

        $this->render();
    }

    /**
     * Returns the notice state, merged with its defaults, from the option.
     *
     * @return array{timestamp_installed: ?int, dismissed: bool}
     */
    private function get_state(): array
    {
        $option   = (array) get_option($this->option_name, []);
        $defaults = [
            'timestamp_installed' => null,
            // Legacy global dismissal. Kept read-only for backwards compatibility;
            // new dismissals are stored per-user in user meta instead.
            'dismissed' => false,
        ];
        return array_merge($defaults, (array) ($option[$this->state_key] ?? []));
    }

    private function update_state(string $key, mixed $value): void
    {
        $option              = (array) get_option($this->option_name, []);
        $state               = (array) ($option[$this->state_key] ?? []);
        $state[$key]         = $value;
        $option[$this->state_key] = $state;
        update_option($this->option_name, $option, true);
    }

    private function get_dismiss_param(): string
    {
        return $this->plugin_slug . '-notice-dismiss';
    }

    private function get_user_meta_key(): string
    {
        // Leading underscore marks this as protected (hidden) user meta.
        return '_' . str_replace('-', '_', $this->plugin_slug) . '_notice_dismissed';
    }

    private function is_dismissed_by_current_user(): bool
    {
        return (bool) get_user_meta(get_current_user_id(), $this->get_user_meta_key(), true);
    }

    private function dismiss_for_current_user(): void
    {
        update_user_meta(get_current_user_id(), $this->get_user_meta_key(), 1);
    }

    private function render(): void
    {
        $review_url = sprintf('https://wordpress.org/support/view/plugin-reviews/%s?rate=5#postform', $this->plugin_slug);
        ?>
        <div class="notice notice-info" style="margin: 0 0 1rem 0; padding: 1.5rem;">
            <h2 style="margin: 0 0 1rem 0;"><?php echo esc_html(sprintf(
                /* translators: %s is the plugin name. */
                __('Enjoying %s?', 'koko-analytics'),
                $this->plugin_name
            )); ?></h2>
            <p style="margin: 0 0 1rem 0;"><?php esc_html_e('A quick review on WordPress.org helps more people find the plugin and helps us keep maintaining it for the long term.', 'koko-analytics'); ?></p>
            <p style="margin: 0 0 0 0;">
                <a class="button button-primary" href="<?php echo esc_url($review_url); ?>"><?php esc_html_e('Review the plugin on WordPress.org', 'koko-analytics'); ?></a>
                <a class="button button-secondary" href="<?php echo esc_url(add_query_arg([$this->get_dismiss_param() => 1])); ?>"><?php esc_html_e('Don\'t show this again', 'koko-analytics'); ?></a>
            </p>
        </div>
        <?php
    }
}

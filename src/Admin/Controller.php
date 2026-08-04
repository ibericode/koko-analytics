<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

use KokoAnalytics\Dashboard_Widget;

use function KokoAnalytics\lazy;

if (!defined('ABSPATH')) {
    exit;
}

class Controller
{
    public function hook(): void
    {
        add_action('wp_loaded', [$this, 'action_wp_loaded'], 10, 0);
        add_action('wp_dashboard_setup', [$this, 'action_wp_dashboard_setup'], 10, 0);
        add_action('admin_notices', [$this, 'action_admin_notices'], 10, 0);
        add_action('admin_menu', [$this, 'action_admin_menu'], 10, 0);
        add_action('admin_enqueue_scripts', [$this, 'action_admin_enqueue_scripts'], 10, 1);

        // Hooks for plugins overview page
        global $pagenow;
        if ($pagenow === 'plugins.php') {
            add_filter('plugin_action_links', [$this, 'filter_plugin_action_links'], 10, 2);
            add_filter('plugin_row_meta', [$this, 'filter_plugin_row_meta'], 10, 2);
        }
    }

    public function action_wp_loaded(): void
    {
        (new Actions())->run();
    }

    public function action_admin_menu(): void
    {
        add_submenu_page('index.php', 'Koko Analytics', esc_html__('Analytics', 'koko-analytics'), 'view_koko_analytics', 'koko-analytics', lazy(Pages::class, 'show_dashboard_page'));
        add_submenu_page('options-general.php', 'Koko Analytics', 'Koko Analytics', 'manage_koko_analytics', 'koko-analytics-settings', lazy(Pages::class, 'show_settings_page'));
    }

    public function action_wp_dashboard_setup(): void
    {
        (new Dashboard_Widget())->register();
    }

    /**
     * Add the dashboard and settings links to the Plugins overview
     *
     * @param array $links
     * @param string $file
     * @return array
     */
    public function filter_plugin_action_links($links, $file)
    {
        if ($file !== plugin_basename(KOKO_ANALYTICS_PLUGIN_FILE)) {
            return $links;
        }

        $own_links = [];

        // the dashboard lives under Dashboard > Analytics, which is easy to miss, so link to it from here too
        if (current_user_can('view_koko_analytics')) {
            $href        = esc_url(admin_url('index.php?page=koko-analytics'));
            $label       = esc_html__('Dashboard', 'koko-analytics');
            $own_links[] = "<a href=\"{$href}\">{$label}</a>";
        }

        $href        = esc_url(admin_url('options-general.php?page=koko-analytics-settings'));
        $label       = esc_html__('Settings', 'koko-analytics');
        $own_links[] = "<a href=\"{$href}\">{$label}</a>";

        return array_merge($own_links, $links);
    }

    /**
     * Adds meta links to the plugin in the WP Admin > Plugins screen
     *
     * @param array $links
     * @param string $file
     * @return array
     */
    public function filter_plugin_row_meta($links, $file)
    {
        if ($file !== plugin_basename(KOKO_ANALYTICS_PLUGIN_FILE)) {
            return $links;
        }

        // add links to documentation
        $links[] = '<a href="https://www.kokoanalytics.com/docs/#utm_source=koko-analytics&amp;utm_medium=link&amp;utm_campaign=free-plugin-plugins-page-docs">' . esc_html__('Documentation', 'koko-analytics') . '</a>';

        // add link to Pro version, unless already running it
        if (! \defined('KOKO_ANALYTICS_PRO_VERSION')) {
            $links[] = '<a href="https://www.kokoanalytics.com/pricing/#utm_source=koko-analytics&amp;utm_medium=link&amp;utm_campaign=free-plugin-plugins-page-upgrade" style="font-weight: bold;">' . esc_html__('Upgrade to Koko Analytics Pro', 'koko-analytics') . '</a>';
        }

        return $links;
    }

    /**
     * @param string $hook_suffix
     */
    public function action_admin_enqueue_scripts($hook_suffix): void
    {
        if ($hook_suffix !== 'dashboard_page_koko-analytics' && $hook_suffix !== 'settings_page_koko-analytics-settings') {
            return;
        }

        wp_enqueue_style('koko-analytics-dashboard', plugins_url('assets/css/dashboard.css', KOKO_ANALYTICS_PLUGIN_FILE), [], KOKO_ANALYTICS_VERSION);
        wp_enqueue_script('koko-analytics-dashboard', plugins_url('assets/js/dashboard.js', KOKO_ANALYTICS_PLUGIN_FILE), [], KOKO_ANALYTICS_VERSION, ['strategy' => 'defer']);
    }

    public function action_admin_notices(): void
    {
        // only show to users with required capability
        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        $this->maybe_show_activation_notice();
        $this->maybe_show_migration_notice();
    }

    /**
     * Points the user to the dashboard once, right after activating the plugin.
     *
     * The dashboard is registered as a submenu of Dashboard, so it is easy to
     * overlook when you don't already know where to look for it.
     */
    private function maybe_show_activation_notice(): void
    {
        if (get_transient('koko_analytics_show_activation_notice') === false) {
            return;
        }

        // no point in explaining where the dashboard is while the user is looking at it
        $screen = get_current_screen();
        if ($screen !== null && $screen->id === 'dashboard_page_koko-analytics') {
            delete_transient('koko_analytics_show_activation_notice');
            return;
        }

        // WordPress prints its own "Plugin activated." notice further down this very page, so
        // replace that one rather than stacking a second, near-identical notice on top of it.
        if ($this->should_replace_plugin_activated_notice()) {
            add_action('all_admin_notices', [$this, 'action_all_admin_notices'], 100, 0);
            return;
        }

        // show this only once, on the first admin page loaded after activation
        delete_transient('koko_analytics_show_activation_notice');
        echo wp_kses_post($this->get_activation_notice_markup());
    }

    /**
     * Whether WordPress is about to print its own "Plugin activated." notice on this request.
     *
     * Only WordPress 6.4 and up render these notices through a filterable function, so on
     * older versions we simply add our own notice instead of replacing theirs.
     */
    private function should_replace_plugin_activated_notice(): bool
    {
        global $pagenow;

        if ($pagenow !== 'plugins.php' || !function_exists('wp_admin_notice')) {
            return false;
        }

        // WordPress prints only the first message that matches, in this order. "activate-multi" is
        // deliberately left alone, since that message covers the other activated plugins too.
        return isset($_GET['activate']) && !isset($_GET['error']) && !isset($_GET['deleted']);
    }

    /**
     * Registers the filter that swaps out the "Plugin activated." notice.
     *
     * This runs on the last of the admin notice hooks so that any notice printed by WordPress
     * itself or by another plugin has already been rendered by the time the filter is added.
     */
    public function action_all_admin_notices(): void
    {
        add_filter('wp_admin_notice_markup', [$this, 'filter_wp_admin_notice_markup'], 10, 3);
    }

    /**
     * @param string $markup
     * @param string $message
     * @param array $args
     * @return string
     */
    public function filter_wp_admin_notice_markup($markup, $message, $args)
    {
        // the notice we are after is the one WordPress renders with id="message"
        if (!is_array($args) || ($args['id'] ?? '') !== 'message') {
            return $markup;
        }

        // only ever replace a single notice
        remove_filter('wp_admin_notice_markup', [$this, 'filter_wp_admin_notice_markup'], 10);
        delete_transient('koko_analytics_show_activation_notice');
        return $this->get_activation_notice_markup();
    }

    private function get_activation_notice_markup(): string
    {
        $dashboard_url = admin_url('index.php?page=koko-analytics');
        $menu_path     = '<strong>' . esc_html__('Dashboard', 'koko-analytics') . ' &rarr; ' . esc_html__('Analytics', 'koko-analytics') . '</strong>';

        ob_start();
        ?>
        <div class="notice notice-success is-dismissible">
            <p><strong><?php esc_html_e('Koko Analytics is now collecting statistics for your site.', 'koko-analytics'); ?></strong></p>
            <p>
                <?php
                /* translators: %s: the admin menu path to the dashboard, e.g. "Dashboard → Analytics". */
                echo wp_kses(sprintf(__('You can view your statistics at any time through %s in the WordPress admin menu.', 'koko-analytics'), $menu_path), ['strong' => []]);
                ?>
            </p>
            <p><a class="button button-primary" href="<?php echo esc_url($dashboard_url); ?>"><?php esc_html_e('View your analytics dashboard', 'koko-analytics'); ?></a></p>
        </div>
        <?php
        return (string) ob_get_clean();
    }

    private function maybe_show_migration_notice(): void
    {
        /** @var \wpdb $wpdb */
        global $wpdb;

        // table may not exist yet (database migrations only run when viewing dashboard / aggregating stats)
        if ($wpdb->get_var("SHOW TABLES LIKE '{$wpdb->prefix}koko_analytics_post_stats'") === null) {
            return;
        }

        // Test for unmigrated post id records
        $results = $wpdb->get_var("SELECT COUNT(DISTINCT(post_id)) FROM {$wpdb->prefix}koko_analytics_post_stats WHERE post_id IS NOT NULL AND path_id IS NULL");
        if (!$results) {
            return;
        }
        ?>
        <div class="notice notice-warning">
            <p>
                <?php esc_html_e('Koko Analytics needs to migrate your page stats to a new storage format.', 'koko-analytics'); ?>
                <?php esc_html_e('Click the button below to proceed with the database migration, this can take some time if you have a large site.', 'koko-analytics'); ?>
            </p>
            <form action="" method="post">
                <?php wp_nonce_field('koko_analytics_migrate_post_stats_to_v2'); ?>
                <input type="hidden" name="koko_analytics_action" value="migrate_post_stats_to_v2">
                <p><button type="submit" class="button button-primary"><?php esc_html_e('Migrate', 'koko-analytics'); ?></button></p>
            </form>
            <p class="help description text-muted"><?php esc_html_e('We recommend making a back-up of your Koko Analytics database tables before running the migration.', 'koko-analytics'); ?></p>
            <p class="help description text-muted"><?php esc_html_e('You can also run the migration using WP CLI: ', 'koko-analytics'); ?> <code>wp koko-analytics migrate_post_stats_to_v2</code></p>
        </div>
        <?php
    }
}

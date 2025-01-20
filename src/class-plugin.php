<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Plugin
{
    public function __construct()
    {
        register_activation_hook(KOKO_ANALYTICS_PLUGIN_FILE, [$this, 'on_activation']);
        add_action('init', [$this, 'maybe_run_actions'], 20, 0);
    }

    public function on_activation(): void
    {
        // add capabilities to administrator role (if it exists)
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('view_koko_analytics');
            $role->add_cap('manage_koko_analytics');
        }

        // (maybe) create optimized endpoint file
        $endpoint_installer = new Endpoint_Installer();
        if ($endpoint_installer->is_eligibile()) {
            $endpoint_installer->install();
        }
    }

    public function maybe_run_actions(): void
    {
        $actions = [];

        if (isset($_GET['koko_analytics_action'])) {
            $actions[] = trim($_GET['koko_analytics_action']);
        }

        if (isset($_POST['koko_analytics_action'])) {
            $actions[] = trim($_POST['koko_analytics_action']);
        }

        if (empty($actions)) {
            return;
        }

        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        // fire all supplied action hooks
        foreach ($actions as $action) {
            do_action("koko_analytics_{$action}");
        }

        wp_safe_redirect(remove_query_arg('koko_analytics_action'));
        exit;
    }
}

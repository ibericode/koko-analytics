<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Plugin
{
    /**
     * @var Aggregator
     */
    private $aggregator;

    /**
     * @param Aggregator $aggregator
     */
    public function __construct(Aggregator $aggregator)
    {
        $this->aggregator = $aggregator;

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

        // schedule action for aggregating stats
        $this->aggregator->setup_scheduled_event();

        // (maybe) create optimized endpoint file
        $endpoint_installer = new Endpoint_Installer();
        if ($endpoint_installer->is_eligibile()) {
            $endpoint_installer->install();
        }
    }

    public function maybe_run_actions(): void
    {
        if (isset($_GET['koko_analytics_action'])) {
            $action = $_GET['koko_analytics_action'];
        } elseif (isset($_POST['koko_analytics_action'])) {
            $action = $_POST['koko_analytics_action'];
        } else {
            return;
        }

        if (!current_user_can('manage_koko_analytics')) {
            return;
        }

        do_action('koko_analytics_' . $action);
        wp_safe_redirect(remove_query_arg('koko_analytics_action'));
        exit;
    }
}

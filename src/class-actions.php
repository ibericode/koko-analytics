<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Actions
{
    public static function run(): void
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

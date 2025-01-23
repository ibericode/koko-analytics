<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Plugin
{
    public static function setup_capabilities(): void
    {
        // add capabilities to administrator role (if it exists)
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('view_koko_analytics');
            $role->add_cap('manage_koko_analytics');
        }
    }

    public static function install_optimized_endpoint(): void
    {
        // (maybe) create optimized endpoint file
        $endpoint_installer = new Endpoint_Installer();
        if ($endpoint_installer->is_eligibile()) {
            $endpoint_installer->install();
        }
    }

    public static function remove_optimized_endpoint(): void
    {
        // delete custom endpoint file
        if (file_exists(ABSPATH . '/koko-analytics-collect.php')) {
            unlink(ABSPATH . '/koko-analytics-collect.php');
        }
    }
}

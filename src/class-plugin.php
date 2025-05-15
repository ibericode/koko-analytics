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
        if (file_exists(rtrim(ABSPATH, '/') . '/koko-analytics-collect.php')) {
            unlink(rtrim(ABSPATH, '/') . '/koko-analytics-collect.php');
        }
    }

    public static function create_and_protect_uploads_dir(): void
    {
        $filename = get_buffer_filename();
        $directory = \dirname($filename);
        if (! \is_dir($directory)) {
            \mkdir($directory, 0755, true);
        }

        // create empty index.html to prevent directory listing
        file_put_contents("$directory/index.html", '');

        // create .htaccess in case we're using apache
        $lines = [
            '<IfModule !authz_core_module>',
            'Order deny,allow',
            'Deny from all',
            '</IfModule>',
            '<IfModule authz_core_module>',
            'Require all denied',
            '</IfModule>',
            '',
        ];
        file_put_contents("$directory/.htaccess", join(PHP_EOL, $lines));
    }
}

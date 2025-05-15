<?php

use KokoAnalytics\Endpoint_Installer;
use KokoAnalytics\Plugin;

defined('ABSPATH') or exit;

// create and protect uploads directory for buffer files
if (class_exists(Plugin::class) && method_exists(Plugin::class, 'create_and_protect_uploads_dir')) {
    Plugin::create_and_protect_uploads_dir();
}

// re-install optimized endpoint using latest content
if (is_file(ABSPATH . '/koko-analytics-collect.php')) {
    @unlink(ABSPATH . '/koko-analytics-collect.php');

    // (Maybe) create optimized endpoint file
    if (class_exists(Endpoint_Installer::class)) {
        $endpoint_installer = new Endpoint_Installer();
        if ($endpoint_installer->is_eligibile()) {
            $endpoint_installer->install();
        }
    }
}

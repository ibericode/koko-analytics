<?php

use KokoAnalytics\Endpoint_Installer;
use KokoAnalytics\Plugin;

defined('ABSPATH') or exit;

// create and protect uploads directory for buffer files
if (class_exists(Plugin::class) && method_exists(Plugin::class, 'create_and_protect_uploads_dir')) {
    Plugin::create_and_protect_uploads_dir();
}

<?php

defined('ABSPATH') or exit;

// re-install AND verify optimized endpoint file
if (class_exists(KokoAnalytics\Endpoint_Installer::class)) {
    KokoAnalytics\Endpoint_Installer::install();
}

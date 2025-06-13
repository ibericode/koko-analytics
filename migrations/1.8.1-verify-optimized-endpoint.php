<?php

defined('ABSPATH') or exit;

// re-install optimized endpoint file
if (class_exists(KokoAnalytics\Endpoint_Installer::class)) {
    KokoAnalytics\Endpoint_Installer::test();
}

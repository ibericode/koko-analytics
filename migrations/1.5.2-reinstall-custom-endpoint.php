<?php

// Unlink the custom endpoint file to ensure we get the latest logic for determining whether to use this
if (file_exists(ABSPATH . '/koko-analytics-collect.php')) {
    unlink(ABSPATH . '/koko-analytics-collect.php');
}

// Update option that says to use custom endpoint, this will be recalculated the next time logic for custom endpoint runs
update_option('koko_analytics_use_custom_endpoint', false);

// (Maybe) create optimized endpoint file
$endpoint_installer = new \KokoAnalytics\Endpoint_Installer();
if ($endpoint_installer->is_eligibile()) {
    $endpoint_installer->install();
}

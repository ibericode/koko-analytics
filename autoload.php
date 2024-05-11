<?php

require __DIR__ . '/src/functions.php';
require __DIR__ . '/src/global-functions.php';

spl_autoload_register(function($class) {
    static $classmap = [
        'KokoAnalytics\\Admin' => __DIR__ . '/src/class-admin.php',
        'KokoAnalytics\\Aggregator' => __DIR__ . '/src/class-aggregator.php',
        'KokoAnalytics\\Command' => __DIR__ . '/src/class-command.php',
        'KokoAnalytics\\Dashboard' => __DIR__ . '/src/class-dashboard.php',
        'KokoAnalytics\\Dashboard_Widget' => __DIR__ . '/src/class-dashboard-widget.php',
        'KokoAnalytics\\Dates' => __DIR__ . '/src/class-dates.php',
        'KokoAnalytics\\Endpoint_Installer' => __DIR__ . '/src/class-endpoint-installer.php',
        'KokoAnalytics\\Migrations' => __DIR__ . '/src/class-migrations.php',
        'KokoAnalytics\\Pageview_Aggregator' => __DIR__ . '/src/class-pageview-aggregator.php',
        'KokoAnalytics\\Plugin' => __DIR__ . '/src/class-plugin.php',
        'KokoAnalytics\\Pruner' => __DIR__ . '/src/class-pruner.php',
        'KokoAnalytics\\Rest' => __DIR__ . '/src/class-rest.php',
        'KokoAnalytics\\Script_Loader' => __DIR__ . '/src/class-script-loader.php',
        'KokoAnalytics\\ShortCode_Site_Counter' => __DIR__ . '/src/class-shortcode-site-counter.php',
        'KokoAnalytics\\Shortcode_Most_Viewed_Posts' => __DIR__ . '/src/class-shortcode-most-viewed-posts.php',
        'KokoAnalytics\\Stats' => __DIR__ . '/src/class-stats.php',
        'KokoAnalytics\\Widget_Most_Viewed_Posts' => __DIR__ . '/src/class-widget-most-viewed-posts.php',
    ];

    if (isset($classmap[$class])) {
        require $classmap[$class];
    }
});

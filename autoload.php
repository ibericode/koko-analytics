<?php

require __DIR__ . '/src/functions.php';
require __DIR__ . '/src/global-functions.php';
require __DIR__ . '/src/collect-functions.php';

spl_autoload_register(function ($class) {
    static $classmap = [
        'KokoAnalytics\\Admin' => '/src/class-admin.php',
        'KokoAnalytics\\Aggregator' => '/src/class-aggregator.php',
        'KokoAnalytics\\Command' => '/src/class-command.php',
        'KokoAnalytics\\Chart_View' => '/src/class-chart-view.php',
        'KokoAnalytics\\Dashboard' => '/src/class-dashboard.php',
        'KokoAnalytics\\Dashboard_Widget' => '/src/class-dashboard-widget.php',
        'KokoAnalytics\\Data_Exporter' => '/src/class-data-exporter.php',
        'KokoAnalytics\\Data_Importer' => '/src/class-data-importer.php',
        'KokoAnalytics\\Endpoint_Installer' => '/src/class-endpoint-installer.php',
        'KokoAnalytics\\Jetpack_Importer' => '/src/class-jetpack-importer.php',
        'KokoAnalytics\\Migrations' => '/src/class-migrations.php',
        'KokoAnalytics\\Notice_Pro' => '/src/class-notice-pro.php',
        'KokoAnalytics\\Pageview_Aggregator' => '/src/class-pageview-aggregator.php',
        'KokoAnalytics\\Plugin' => '/src/class-plugin.php',
        'KokoAnalytics\\Pruner' => '/src/class-pruner.php',
        'KokoAnalytics\\QueryLoopBlock' => '/src/class-query-loop-block.php',
        'KokoAnalytics\\Rest' => '/src/class-rest.php',
        'KokoAnalytics\\Script_Loader' => '/src/class-script-loader.php',
        'KokoAnalytics\\ShortCode_Site_Counter' => '/src/class-shortcode-site-counter.php',
        'KokoAnalytics\\Shortcode_Most_Viewed_Posts' => '/src/class-shortcode-most-viewed-posts.php',
        'KokoAnalytics\\Stats' => '/src/class-stats.php',
        'KokoAnalytics\\Widget_Most_Viewed_Posts' => '/src/class-widget-most-viewed-posts.php',
    ];

    if (isset($classmap[$class])) {
        require __DIR__ . $classmap[$class];
    }
});

<?php

require __DIR__ . '/src/functions.php';
require __DIR__ . '/src/global-functions.php';
require __DIR__ . '/src/collect-functions.php';

spl_autoload_register(function ($class) {
    static $classmap = [
        'KokoAnalytics\\Actions' => '/src/class-actions.php',
        'KokoAnalytics\\Admin' => '/src/class-admin.php',
        'KokoAnalytics\\Admin_Actions' => '/src/class-admin-actions.php',
        'KokoAnalytics\\Admin_Page' => '/src/class-admin-page.php',
        'KokoAnalytics\\Admin_Bar' => '/src/class-admin-bar.php',
        'KokoAnalytics\\Aggregator' => '/src/class-aggregator.php',
        'KokoAnalytics\\Burst_Importer' => '/src/class-burst-importer.php',
        'KokoAnalytics\\Command' => '/src/class-command.php',
        'KokoAnalytics\\Chart_View' => '/src/class-chart-view.php',
        'KokoAnalytics\\Dashboard_Widget' => '/src/class-dashboard-widget.php',
        'KokoAnalytics\\Dashboard' => '/src/class-dashboard.php',
        'KokoAnalytics\\Data_Exporter' => '/src/class-data-exporter.php',
        'KokoAnalytics\\Data_Importer' => '/src/class-data-importer.php',
        'KokoAnalytics\\Endpoint_Installer' => '/src/class-endpoint-installer.php',
        'KokoAnalytics\\Fmt' => '/src/class-fmt.php',
        'KokoAnalytics\\Jetpack_Importer' => '/src/class-jetpack-importer.php',
        'KokoAnalytics\\Migrations' => '/src/class-migrations.php',
        'KokoAnalytics\\Notice_Pro' => '/src/class-notice-pro.php',
        'KokoAnalytics\\Pageview_Aggregator' => '/src/class-pageview-aggregator.php',
        'KokoAnalytics\\Plugin' => '/src/class-plugin.php',
        'KokoAnalytics\\Pruner' => '/src/class-pruner.php',
        'KokoAnalytics\\Rest' => '/src/class-rest.php',
        'KokoAnalytics\\Script_Loader' => '/src/class-script-loader.php',
        'KokoAnalytics\\Stats' => '/src/class-stats.php',
        'KokoAnalytics\\Shortcode_Most_Viewed_Posts' => '/src/class-shortcode-most-viewed-posts.php',
        'KokoAnalytics\\Shortcode_Site_Counter' => '/src/class-shortcode-site-counter.php',
        'KokoAnalytics\\Stats' => '/src/class-stats.php',
        'KokoAnalytics\\Query_Loop_Block' => '/src/class-query-loop-block.php',
        'KokoAnalytics\\Widget_Most_Viewed_Posts' => '/src/class-widget-most-viewed-posts.php',
    ];

    if (isset($classmap[$class])) {
        require __DIR__ . $classmap[$class];
    }
});

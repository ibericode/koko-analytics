<?php

require dirname(__DIR__) . '/mocks.php';

// make sure we're not running through all migrations
update_option('koko_analytics_version', '1.6.2');

$memory = memory_get_usage();
$time_start = microtime(true);

require dirname(__DIR__, 2) . '/koko-analytics.php';

$time = round((microtime(true) - $time_start) * 1000, 2);
$memory_used = (memory_get_usage() - $memory) >> 10;

echo "Memory: $memory_used KB\n";
echo "Time: $time ms\n";

<?php

require __DIR__ . '/src/Resources/functions/functions.php';
require __DIR__ . '/src/Resources/functions/global.php';
require __DIR__ . '/src/Resources/functions/collect.php';
require __DIR__ . '/src/Resources/backwards-compat.php';

spl_autoload_register(function ($class) {
    // only act on classnames starting with our namespace
    if (!str_starts_with($class, 'KokoAnalytics\\') || str_starts_with($class, 'KokoAnalytics\\Pro\\')) {
        return;
    }

    // turn FQCN into filename according to PSR-4 standard
    $file = strtr(substr($class, 14), '\\', '/');
    require __DIR__ . "/src/{$file}.php";
});

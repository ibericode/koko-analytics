<?php

if (!file_exists(__DIR__ . '/../../../../wp-config.php')) {
    echo -1;
    return;
}

// load wp-config.php without loading WordPress... This is hacky, but it works.
set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
    throw new ErrorException($errstr, 1111, $errno, $errfile, $errline);
}, E_NOTICE);

// this constant is redefined in wp-settings.php, so will throw a notice when that file is loaded
define('WPINC', true);

try {
    require_once __DIR__ . '/../../../../wp-config.php';
} catch(ErrorException $e) {
    // catch notice for already defined constant and ignore it
    if ($e->getCode() !== 1111) {
        throw $e;
    }
}

// the floor is ours!
restore_error_handler();


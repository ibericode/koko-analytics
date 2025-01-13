<?php

// phpcs:disable PSR1.Files.SideEffects

require __DIR__ . '/functions.php';

$n = 100000;

$time = bench(function () use ($n) {
    $array = [];
    $value = "%s";
    for ($i = 0; $i < $n; $i++) {
        $array[] = $value;
    }
});
printf("array append took %.4f seconds" . PHP_EOL, $time);

$time = bench(function () use ($n) {
    $array = array_fill(0, $n, '%s');
});
printf("array_fill took %.4f seconds" . PHP_EOL, $time);

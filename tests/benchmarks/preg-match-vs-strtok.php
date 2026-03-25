<?php

require __DIR__ . '/functions.php';

$n = 1000;
$files = [
    '001-do-this.php',
    '002-do-that.php',
    '003-do-something-else.php',
    '004-do-something-else.php',
    '004-more.php',
];

$time = bench(function () use ($files) {
    foreach ($files as $file) {
        $parts = explode('-', $file);
        $version = (int) $parts[0];
    }
}, $n);
printf("explode \t%.2f ms\t(%.3f per it)\n", $time, $time / $n);

$time = bench(function () use ($files) {
    foreach ($files as $file) {
        $version = (int) strtok($file, '-');
    }
}, $n);
printf("strtok   \t%.2f ms\t(%.3f per it)\n", $time, $time / $n);

$time = bench(function () use ($files) {
    foreach ($files as $file) {
        preg_match('/^(\d+)-/', $file, $matches);
        $version = (int) $matches[1];
    }
}, $n);
printf("preg_match \t%.2f ms\t(%.3f per it)\n", $time, $time / $n);

$time = bench(function () use ($files) {
    foreach ($files as $file) {
        $version = (int) $file;
    }
}, $n);
printf("int cast \t%.2f ms\t(%.3f per it)\n", $time, $time / $n);
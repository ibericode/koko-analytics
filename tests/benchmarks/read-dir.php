<?php

require __DIR__ . '/functions.php';

$time = bench(function () {
    $results = scandir("/home/danny/Downloads");
});
printf("scandir:\t %.2f ns (%.2f per it)\n", $time, $time / 1000);

$time = bench(function () {
    $results = [];
    foreach (new DirectoryIterator("/home/danny/Downloads") as $fileInfo) {
        $results[] = $fileInfo;
    }
    return $results;
});
printf("DirectoryIterator:\t %.2f ns (%.2f per it)\n", $time, $time / 1000);

$time = bench(function () {
    $results = glob("/home/danny/Downloads/*", GLOB_NOSORT);
});
printf("glob:\t %.2f ns (%.2f per it)\n", $time, $time / 1000);


dd(scandir("/home/danny/Downloads"));

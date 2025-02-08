<?php

require __DIR__ . '/functions.php';

$data = ['p', time(), 1234, 0, 1, 'https://www.kokoanalytics.com'];
$fh = tmpfile();

// prepare naive csv file
ftruncate($fh, 0);
fputs($fh, join(',', $data) . PHP_EOL);
fseek($fh, 0);
$time = bench(function () use ($fh) {
    $data = explode(',', trim(fgets($fh)));
}, 10000);
printf("explode:\t %6.2f\n", $time);

// prepare csv file
ftruncate($fh, 0);
fputcsv($fh, $data);
fseek($fh, 0);
$time = bench(function () use ($fh) {
    $data = fgetcsv($fh, 2048);
}, 10000);
printf("fgetcsv:\t %6.2f\n", $time);

// prepare serialized data file
ftruncate($fh, 0);
fputs($fh, serialize($data) . PHP_EOL);
fseek($fh, 0);
$time = bench(function () use ($fh) {
    $data = unserialize(trim(fgets($fh)));
}, 10000);
printf("unserialize:\t %6.2f\n", $time);

// prepare json_encode file
ftruncate($fh, 0);
fputs($fh, json_encode($data) . PHP_EOL);
fseek($fh, 0);
$time = bench(function () use ($fh) {
    $data = json_decode(trim(fgets($fh)));
}, 10000);
printf("json_decode:\t %6.2f\n", $time);

fclose($fh);

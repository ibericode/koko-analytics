<?php

require __DIR__ . '/functions.php';

$data = ['p', time(), 1234, 0, 1, 'https://www.kokoanalytics.com'];
$fh = tmpfile();

// prepare naive csv file
ftruncate($fh, 0);
fputs($fh, join(',', $data) . PHP_EOL);
fseek($fh, 0);
$time = bench(function () use ($fh, $data) {
    $read = explode(',', trim(fgets($fh)));
    assert($read == $data);
    fseek($fh, 0);
});
printf("explode:\t %6.2f\n", $time);

// prepare csv file
ftruncate($fh, 0);
fputcsv($fh, $data);
fseek($fh, 0);
$time = bench(function () use ($fh, $data) {
    $read = fgetcsv($fh, 2048);
    fseek($fh, 0);
    assert($read == $data);
});
printf("fgetcsv:\t %6.2f\n", $time);

// prepare serialized data file
ftruncate($fh, 0);
fputs($fh, serialize($data) . PHP_EOL);
fseek($fh, 0);
$time = bench(function () use ($fh, $data) {
    $read = unserialize(trim(fgets($fh)), ['allowed_classes' => false]);
    assert($read == $data);
    fseek($fh, 0);
});
printf("unserialize:\t %6.2f\n", $time);

// prepare json_encode file
ftruncate($fh, 0);
fputs($fh, json_encode($data) . PHP_EOL);
fseek($fh, 0);
$time = bench(function () use ($fh, $data) {
    $read = json_decode(trim(fgets($fh)));
    assert($read == $data);
    fseek($fh, 0);
});
printf("json_decode:\t %6.2f\n", $time);

fclose($fh);

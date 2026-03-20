<?php

require __DIR__ . '/functions.php';

$n = 1000;

foreach ([1, 10, 100, 1000] as $count) {
    echo "# count: $count\n";
    $values = array_fill(0, $count, bin2hex(random_bytes(32)));

    $time = bench(function () use ($values) {
        $placeholders = rtrim(str_repeat('(%s),', count($values)), ',');
    }, $n);
    printf("str_repeat \t%.2f ms\t(%.2f per it)\n", $time, $time / $n);


    $time = bench(function () use ($values) {
        $placeholders = join(',', array_fill(0, count($values), '(%s)'));
    }, $n);
    printf("join       \t%.2f ms\t(%.2f per it)\n", $time, $time / $n);
}

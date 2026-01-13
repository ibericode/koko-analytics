<?php

require __DIR__ . '/functions.php';

$n = 100000;

foreach ([5, 100, 500] as $count) {
    echo "# count: $count\n";
    $values = array_fill(0, $count, 'abcdef');

    $time = bench(function () use ($values) {
        $placeholders = rtrim(str_repeat('(%s),', count($values)), ',');
    }, $n);
    printf("str_repeat \t%.2f ns\t(%.2f per it)\n", $time, $time / $n);


    $time = bench(function () use ($values) {
        $placeholders = join(',', array_fill(0, count($values), '(%s)'));
    }, $n);
    printf("join       \t%.2f ns\t(%.2f per it)\n", $time, $time / $n);
}

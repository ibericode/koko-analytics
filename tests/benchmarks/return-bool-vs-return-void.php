<?php

require __DIR__ . '/functions.php';

global $arr;
$arr = [];

function a()
{
    // do something
    // return nothing
    global $arr;
    $arr["k"] = "v";
}

function b()
{
    // do something
    // always return true
    global $arr;
    $arr["k"] = "v";
    return true;
}

$iterations = 1_000_000;

// instance creation + call
$time = bench(function () {
    a();
}, $iterations);
printf("return void \t\t%.2f ns\t(%.2f per it)\n", $time, $time / $iterations);


// static method
$time = bench(function () {
    b();
}, $iterations);
printf("return true\t\t%.2f ns\t(%.2f per it)\n", $time, $time / $iterations);

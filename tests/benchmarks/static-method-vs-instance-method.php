<?php

require __DIR__ . '/functions.php';

class A
{
    public function method(): void
    {
    }
}

class B
{
    public static function method(): void
    {
    }
}

$iterations = 100000;

// instance creation + call
$time = bench(function () {
    $instance = new A;
    $instance->method();
}, $iterations);
printf("new instance method \t%.2f ns\t(%.2f per it)\n", $time, $time / $iterations);

// existing instance
$instance = new A;
$time = bench(function () use ($instance) {
    $instance->method();
}, $iterations);
printf("instance method \t%.2f ns\t(%.2f per it)\n", $time, $time / $iterations);

// static method
$time = bench(function () {
    B::method();
}, $iterations);
printf("static method\t\t%.2f ns\t(%.2f per it)\n", $time, $time / $iterations);

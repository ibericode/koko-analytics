<?php

// phpcs:disable PSR1.Files.SideEffects
@set_time_limit(0);

function bench(Closure $fn, $iterations = 3)
{
    $time_start = microtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $fn();
    }
    $time_end = microtime(true);
    return ($time_end - $time_start) / $iterations;
}

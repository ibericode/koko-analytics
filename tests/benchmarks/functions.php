<?php

// phpcs:disable PSR1.Files.SideEffects
@set_time_limit(0);

function bench(Closure $fn, $iterations = 1000)
{
    $time_start = microtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $fn();
    }
    $time_end = microtime(true);
    return round(($time_end - $time_start) * 1000 * 1000, 2);
}

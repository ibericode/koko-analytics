<?php

// phpcs:disable PSR1.Files.SideEffects

function analyze(array $values): array
{
    $sum = 0.0;
    $min = PHP_FLOAT_MAX;
    $max = PHP_FLOAT_MIN;
    foreach ($values as $v) {
        $sum += $v;
        $min =  min($min, $v);
        $max = max($max, $v);
    }
    $mean = $sum / count($values);
    return [round($min, 3), round($mean, 3), round($max, 3)];
}

function bench(int $n = 100, bool $opcache = true): void
{
    // run benchmark
    $memories = [];
    $times = [];
    $url = $opcache ? 'http://localhost:8080/plugin.php' : 'http://localhost:8080/plugin.php?disable-opcache=1';

    // make a single request to test whether HTTP server is up
    // but also to warm the opcache
    $body = file_get_contents($url);
    if ($body === false) {
        throw new Exception("Error making HTTP request. Is the HTTP server running?");
    }

    // run for 5 seconds
    $time_until = time() + 5;
    while (time() < $time_until) {
        $body = file_get_contents($url);
        $data = json_decode($body);
        $memories[] = $data->memory;
        $times[] = $data->time;
    }

    [$time_min, $time_mean, $time_max] = analyze($times);

    echo $opcache ? "with opcache:     " : "without opcache:  ";
    echo "min: $time_min\tmean: $time_mean\tmax: $time_max\n";
}

$root = __DIR__;
$ph = popen("php -S localhost:8080 -q -t {$root} &2>/dev/null", "r");
if (!$ph) {
    echo "Error starting HTTP server\n";
    exit(1);
}
sleep(2);

bench(100, true);
bench(100, false);

pclose($ph);

<?php

function bench(Closure $fn, $iterations = 3) {
	$time_start = microtime(true);
	for ($i = 0; $i < $iterations; $i++) {
		$fn();
	}
	$time_end = microtime(true);
	return ($time_end - $time_start) / $iterations;
}

@set_time_limit(0);

$n = 100000;

$time = bench(function() use($n) {
	$array = [];
	$value = "%s";
	for ($i = 0; $i < $n; $i++) {
		$array[] = $value;
	}
});
printf("array append took %.4f seconds" . PHP_EOL, $time);

$time = bench(function() use($n) {
	$array = array_fill(0, $n, '%s');
});
printf("array_fill took %.4f seconds" . PHP_EOL, $time);

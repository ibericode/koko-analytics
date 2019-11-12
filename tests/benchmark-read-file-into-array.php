<?php

function bench($desc, Closure $fn, Closure $setup, $iterations = 3) {
	$total_time = 0;

	for ($i = 0; $i < $iterations; $i++) {
		$setup();
		$time_start = microtime(true);
		$fn();
		$time_end = microtime(true);
		$total_time += $time_end - $time_start;
	}

	$average = $total_time / $iterations;
	printf('%s took %.3f seconds' . PHP_EOL, $desc, $average);
	return $average;
}

@set_time_limit(0);
$file = '/tmp/bench-file.txt';

// create file with 100K lines of data
$setup = function() use($file) {
	file_put_contents($file, str_repeat('0,1,2,3' . PHP_EOL, 100000));
};

bench('Read using file()', function() use($file) {
	$data = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	file_put_contents($file, '');
}, $setup);


bench('Read using fgets()', function() use($file) {
	$data = [];

	$handle = fopen($file, 'r+');
	while (($line = fgets($handle)) !== false) {
		$data[] = $line;
	}
	ftruncate($handle, 0);

}, $setup);

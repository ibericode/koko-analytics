<?php

require __DIR__ . '/functions.php';

$n = 100000;

$time = bench(function() use($n) {
	$str = "";
	for ($i = 0; $i < $n; $i++) {
		$str .= '%s,';
	}
	$str = rtrim($str, ',');
});
printf("str concat in loop took %.4f seconds" . PHP_EOL, $time);

$time = bench(function() use($n) {
	$array = array_fill(0, $n, '%s');
	$str = join(',', $array);
});
printf("array_fill + join took %.4f seconds" . PHP_EOL, $time);

$time = bench(function() use($n) {
	$str = str_repeat('%s,', $n);
	$str = rtrim($str, ',');
});
printf("str_repeat + rtrim took %.4f seconds" . PHP_EOL, $time);

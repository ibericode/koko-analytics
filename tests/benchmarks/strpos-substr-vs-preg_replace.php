<?php

require __DIR__ . '/functions.php';
$n = 1000000;
$str_without = 'https://www.kokoanalytics.com/?foo=bar';
$str_with = 'https://www.kokoanalytics.com/?foo=bar#bar=foo&foo=bar';

$time = bench(function() use($n, $str_with) {
	for ($i = 0; $i < $n; $i++) {
		$pos = strpos($str_with, '#');
		if ($pos !== false) {
			$new_str = substr($str_with, 0, $pos);
		}
	}
});
printf("strpos on string with # took %.4f seconds" . PHP_EOL, $time);

$time = bench(function() use($n, $str_with) {
	for ($i = 0; $i < $n; $i++) {
		$new_str = preg_replace( '/#.*$/', '', $str_with );
	}
});
printf("preg_replace on string with # took %.4f seconds" . PHP_EOL, $time);


$time = bench(function() use($n, $str_without) {
	for ($i = 0; $i < $n; $i++) {
		$pos = strpos($str_without, '#');
		if ($pos !== false) {
			$new_str = substr($str_without, 0, $pos);
		}
	}
});
printf("strpos on string without # took %.4f seconds" . PHP_EOL, $time);

$time = bench(function() use($n, $str_without) {
	for ($i = 0; $i < $n; $i++) {
		$new_str = preg_replace( '/#.*$/', '', $str_without );
	}
});
printf("preg_replace on string without # took %.4f seconds" . PHP_EOL, $time);

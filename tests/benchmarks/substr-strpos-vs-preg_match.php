<?php

require __DIR__ . '/functions.php';
$n = 1000000;
$url = 'https://a.com/one/';

$time = bench(function() use($n, $url) {
	for ($i = 0; $i < $n; $i++) {
		// 8 characters for protocol
		// 1 or more characters for domain name
		// = 9 char offset
		$pos = strpos( $url, '/', 9);
		$path = $pos !== false ? substr( $url, $pos ) : '/';
	}
});
printf("strpos on string with # took %.4f seconds" . PHP_EOL, $time);

$time = bench(function() use($n, $url) {
	for ($i = 0; $i < $n; $i++) {
		$result = preg_match('/.\w+(\/.*)$/', $url, $matches);
		$path = $result ? $matches[1] : '/';
	}
});
printf("preg_match on string with # took %.4f seconds" . PHP_EOL, $time);

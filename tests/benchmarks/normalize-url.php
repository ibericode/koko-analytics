<?php

use KokoAnalytics\Aggregator;

require __DIR__ . '/functions.php';
require __DIR__ . '/../../src/class-aggregator.php';

$a = new Aggregator();
$time = bench(function() use($a) {
	for ($i = 0; $i < 10000; $i++) {
		$a->normalize_url( 'https://m.facebook.com/profile/johndoe' );
	}
}, 100);
printf("Took %.4f ms", $time*1000 );

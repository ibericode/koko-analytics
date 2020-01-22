<?php

require __DIR__ . '/functions.php';
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

<?php

require __DIR__ . '/functions.php';
$n = 1_000_000;
$url = 'https://a.com/one/';

$time = bench(function() use($n, $url) {
    for ($i = 0; $i < $n; $i++) {
        $new_url = (string) preg_replace('/^https?:\/\/(www\.)?(.+?)\/?$/', '$2', $url);
        // assert($new_url === 'a.com/one');
    }
});
printf("preg_replace with match groups took %.4f seconds" . PHP_EOL, $time);

$time = bench(function() use($n, $url) {
    for ($i = 0; $i < $n; $i++) {

        $new_url = $url;
        if (strpos($new_url, 'http://') === 0) {
            $new_url = substr($new_url, 0, 7);
        } else if (strpos($new_url, 'https://') === 0) {
            $new_url = substr($new_url, 0, 8);
        }

        if (strpos($new_url, 'www.') === 0) {
            $new_url = substr($new_url, 0, 4);
        }
        $new_url = rtrim($new_url, '/');
        // assert($new_url === 'a.com/one/');
    }
});
printf("strpos + substr took %.4f seconds" . PHP_EOL, $time);

// preg_replace alternative
$time = bench(function() use($n, $url) {
    for ($i = 0; $i < $n; $i++) {
        $new_url = (string) preg_replace('/^https?:\/\/(?:www\.)?/', '', $url);
        $new_url = rtrim($new_url, '/');
    }
});
printf("preg_replace + rtrim took %.4f seconds" . PHP_EOL, $time);

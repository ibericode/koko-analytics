<?php

// phpcs:disable PSR1.Files.SideEffects

require __DIR__ . '/../../../../wp-load.php';
require __DIR__ . '/functions.php';

global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}koko_analytics_bench_referrer_urls");
$wpdb->query("CREATE TABLE {$wpdb->prefix}koko_analytics_bench_referrer_urls (
   id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
   url VARCHAR(255) NOT NULL,
   UNIQUE INDEX (url)
) ENGINE=INNODB CHARACTER SET={$wpdb->charset} COLLATE={$wpdb->collate}");

$existing_urls = [];
$new_urls = [];
$unrelated_urls = [];
$n = 1000;

for ($i = 0; $i < $n; $i++) {
    $unrelated_urls[] = 'https://unrelated-url-' . $i . '.com/';
    $existing_urls[] = 'https://existing-url-' . $i . '.com/';
    $new_urls[] = 'https://new-url-' . $i . '.com/';
}
$all_urls = array_merge($existing_urls, $new_urls);


$values = array_merge($existing_urls, $unrelated_urls);
$placeholders = array_fill(0, count($values), '(%s)');
$placeholders = join(',', $placeholders);
$seed_sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_bench_referrer_urls(url) VALUES {$placeholders}", $values);
$wpdb->query($seed_sql);


// Bench 1.1: Insert all URL's regardless of duplicates
$total_time = 0;
$time = bench(function () use ($wpdb, $all_urls) {
    $placeholders = array_fill(0, count($all_urls), '(%s)');
    $placeholders = join(',', $placeholders);
    $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_bench_referrer_urls(url) VALUES {$placeholders} ON DUPLICATE KEY UPDATE url = url", $all_urls));
}, 1);
$total_time += $time;
printf("Inserting rows while ignoring duplicates took: %.4f seconds" . PHP_EOL, $time);

// Bench 1.2: Select all URL's so we have ID's for each of them
$time = bench(function () use ($wpdb, $all_urls) {
    $placeholders = array_fill(0, count($all_urls), '%s');
    $placeholders = join(',', $placeholders);
    $urls = $wpdb->get_results($wpdb->prepare("SELECT id, url FROM {$wpdb->prefix}koko_analytics_bench_referrer_urls WHERE url IN({$placeholders})", $all_urls));
}, 3);
$total_time += $time;
printf("Selecting all rows took %.4f seconds" . PHP_EOL, $time);
printf("Total time spent: %.4f seconds" . PHP_EOL, $total_time);

// Between benches: Empty table
echo PHP_EOL;
$wpdb->query("TRUNCATE {$wpdb->prefix}koko_analytics_bench_referrer_urls");
$wpdb->query($seed_sql);

// Bench 2.1: Select all URL's already in table so we can ignore duplicates
$total_time = 0;
$time = bench(function () use ($wpdb, $all_urls) {
    $placeholders = array_fill(0, count($all_urls), '%s');
    $placeholders = join(',', $placeholders);
    $results = $wpdb->get_results($wpdb->prepare("SELECT id, url FROM {$wpdb->prefix}koko_analytics_bench_referrer_urls WHERE url IN({$placeholders})", $all_urls));

    $existing_urls = [];
    foreach ($results as $r) {
        $existing_urls[$r->url] = 1;
    }
    $new_urls = [];
    foreach ($all_urls as $url) {
        if (!isset($existing_urls[$url])) {
            $new_urls[] = $url;
        }
    }
}, 3);
$total_time += $time;
printf("Selecting only existing rows took %.4f seconds" . PHP_EOL, $time);

// Bench 2.2: Insert new URL's
$time = bench(function () use ($wpdb, $new_urls) {
    $values = $new_urls;
    $placeholders = array_fill(0, count($new_urls), '(%s)');
    $placeholders = join(',', $placeholders);
    $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}koko_analytics_bench_referrer_urls(url) VALUES {$placeholders}", $values));
}, 1);
$total_time += $time;
printf("Inserting only new rows took %.4f seconds" . PHP_EOL, $time);

printf("Total time spent: %.4f seconds" . PHP_EOL, $total_time);

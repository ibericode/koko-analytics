<?php

// phpcs:disable PSR1.Files.SideEffects

require __DIR__ . '/../../../../wp-load.php';

function bench(Closure $fn, $iterations = 3)
{
    $time_start = microtime(true);
    for ($i = 0; $i < $iterations; $i++) {
        $fn();
    }
    $time_end = microtime(true);
    return ($time_end - $time_start) / $iterations;
}

@set_time_limit(0);

global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bench_combined_stats");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bench_site_stats");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bench_post_stats");
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}bench_referrer_stats");

$days = 365 * 5;
$pages = 200;
$referrers = 50;

// Benchmark combined
printf("Benchmarking single (combined) table: " . PHP_EOL);
$wpdb->query("CREATE TABLE {$wpdb->prefix}bench_combined_stats (
	   type ENUM('post', 'referrer') NOT NULL DEFAULT 'post',
	   id BIGINT(20) UNSIGNED NOT NULL,
	   date DATE NOT NULL,
	   visitors INTEGER UNSIGNED NOT NULL,
	   pageviews INTEGER UNSIGNED NOT NULL,
	   PRIMARY KEY (id, type, date)
	) ENGINE=INNODB CHARACTER SET={$wpdb->charset} COLLATE={$wpdb->collate}");

// inserting
$time = bench(function () use ($wpdb, $days, $pages, $referrers) {
    for ($d = 0; $d < $days; $d++) {
        $date = date("Y-m-d", strtotime(sprintf('%d days ago', $days + $d)));
        $placeholders = [];
        $values = [];

        for ($i = 0; $i < $pages; $i++) {
            $placeholders[] = '(%s, %d, %s, %d, %d)';
            array_push($values, 'post', $i, $date, 10, 20);
        }

        for ($i = 0; $i < $referrers; $i++) {
            $placeholders[] = '(%s, %d, %s, %d, %d)';
            array_push($values, 'referrer', $i, $date, 10, 20);
        }

        $placeholders = join(',', $placeholders);
        $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}bench_combined_stats(type, id, date, visitors, pageviews) VALUES {$placeholders}", $values);
        $wpdb->query($sql);
    }
}, 1);
printf("Inserting rows took: %.4f seconds" . PHP_EOL, $time);

// selecting
$time = bench(function () use ($wpdb) {
    $date_start = date("Y-m-d");
    $date_end = date("Y-m-d", strtotime('-30 days ago'));
    $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}bench_combined_stats WHERE id = 0 AND type = 'post' AND date >= %s AND date <= %s", [$date_start, $date_end]));
});
printf("Selecting site stats for this month took: %.4f seconds" . PHP_EOL, $time);

// selecting
$time = bench(function () use ($wpdb) {
    $date_start = date("Y-m-d");
    $date_end = date("Y-m-d", strtotime('-30 days ago'));
    $wpdb->get_results($wpdb->prepare("SELECT id, SUM(visitors) AS visitors, SUM(pageviews) as pageviews FROM {$wpdb->prefix}bench_combined_stats WHERE type = 'post' AND id > 0 AND date >= %s AND date <= %s GROUP BY id ORDER BY pageviews DESC", [$date_start, $date_end]));
});
printf("Selecting page stats for this month took: %.4f seconds" . PHP_EOL, $time);

// selecting
$time = bench(function () use ($wpdb) {
    $date_start = date("Y-m-d");
    $date_end = date("Y-m-d", strtotime('-30 days ago'));
    $wpdb->get_results($wpdb->prepare("SELECT id, SUM(visitors) AS visitors, SUM(pageviews) as pageviews FROM {$wpdb->prefix}bench_combined_stats WHERE type = 'referral' AND date >= %s AND date <= %s GROUP BY id ORDER BY pageviews DESC", [$date_start, $date_end]));
});
printf("Selecting referral stats for this month took: %.4f seconds" . PHP_EOL, $time);

print PHP_EOL;
printf("Benchmarking multiple (separate) tables: " . PHP_EOL);

// Benchmark multiple (separate) tables
$wpdb->query("CREATE TABLE {$wpdb->prefix}bench_site_stats (
	   date DATE NOT NULL,
	   visitors INTEGER UNSIGNED NOT NULL,
	   pageviews INTEGER UNSIGNED NOT NULL,
	   PRIMARY KEY (date)
	) ENGINE=INNODB CHARACTER SET={$wpdb->charset} COLLATE={$wpdb->collate}");

$wpdb->query("CREATE TABLE {$wpdb->prefix}bench_post_stats (
 	   id BIGINT(20) UNSIGNED NOT NULL,
	   date DATE NOT NULL,
	   visitors INTEGER UNSIGNED NOT NULL,
	   pageviews INTEGER UNSIGNED NOT NULL,
	   PRIMARY KEY (date, id)
	) ENGINE=INNODB CHARACTER SET={$wpdb->charset} COLLATE={$wpdb->collate}");

$wpdb->query("CREATE TABLE {$wpdb->prefix}bench_referrer_stats(
 	   id BIGINT(20) UNSIGNED NOT NULL,
	   date DATE NOT NULL,
	   visitors INTEGER UNSIGNED NOT NULL,
	   pageviews INTEGER UNSIGNED NOT NULL,
	  PRIMARY KEY (date, id)
	) ENGINE=INNODB CHARACTER SET={$wpdb->charset} COLLATE={$wpdb->collate}");

// inserting
$time = bench(function () use ($wpdb, $days, $pages, $referrers) {
    for ($d = 0; $d < $days; $d++) {
        $date = date("Y-m-d", strtotime(sprintf('%d days ago', $days + $d)));

        // insert site stats
        $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}bench_site_stats(date, visitors, pageviews) VALUES(%s, %d, %d)", [$date, 10, 20]));

        // insert pageviews for 100 posts
        $placeholders = [];
        $values = [];
        for ($i = 1; $i < $pages; $i++) {
            $placeholders[] = '(%d, %s, %d, %d)';
            array_push($values, $i, $date, 10, 20);
        }

        $placeholders = join(',', $placeholders);
        $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}bench_post_stats(id, date, visitors, pageviews) VALUES {$placeholders}", $values);
        $wpdb->query($sql);

        // insert some referrals
        $placeholders = [];
        $values = [];
        for ($i = 0; $i < $referrers; $i++) {
            $placeholders[] = '(%d, %s, %d, %d)';
            array_push($values, $i, $date, 10, 20);
        }
        $placeholders = join(',', $placeholders);
        $sql = $wpdb->prepare("INSERT INTO {$wpdb->prefix}bench_referrer_stats(id, date, visitors, pageviews) VALUES {$placeholders}", $values);
        $wpdb->query($sql);
    }
}, 1);
printf("Inserting rows took: %.4f seconds" . PHP_EOL, $time);

// selecting
$time = bench(function () use ($wpdb) {
    $date_start = date("Y-m-d");
    $date_end = date("Y-m-d", strtotime('-30 days ago'));
    $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->prefix}bench_site_stats WHERE date >= %s AND date <= %s", [$date_start, $date_end]));
});
printf("Selecting site stats for this month took: %.4f seconds" . PHP_EOL, $time);

// selecting
$time = bench(function () use ($wpdb) {
    $date_start = date("Y-m-d");
    $date_end = date("Y-m-d", strtotime('-30 days ago'));
    $wpdb->get_results($wpdb->prepare("SELECT id, SUM(visitors) AS visitors, SUM(pageviews) as pageviews FROM {$wpdb->prefix}bench_post_stats WHERE date >= %s AND date <= %s GROUP BY id ORDER BY pageviews DESC", [$date_start, $date_end]));
});
printf("Selecting page stats for this month took: %.4f seconds" . PHP_EOL, $time);

// selecting
$time = bench(function () use ($wpdb) {
    $date_start = date("Y-m-d");
    $date_end = date("Y-m-d", strtotime('-30 days ago'));
    $wpdb->get_results($wpdb->prepare("SELECT id, SUM(visitors) AS visitors, SUM(pageviews) as pageviews FROM {$wpdb->prefix}bench_referrer_stats WHERE date >= %s AND date <= %s GROUP BY id ORDER BY pageviews DESC", [$date_start, $date_end]));
});
printf("Selecting referral stats for this month took: %.4f seconds" . PHP_EOL, $time);

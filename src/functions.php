<?php

namespace AAA;

function maybe_collect_request() {
    // Short-circuit a bunch of AJAX stuff
    if ($_SERVER['REQUEST_URI'] !== '/aaa-collect.php' && (stripos($_SERVER['REQUEST_URI'], '/admin-ajax.php') === false || ! isset($_GET['action']) || $_GET['action'] !== 'aaa_collect')) {
        return;
    }

    $now = date('Y-m-d H:i:s');

    $visitor_hash = $_GET['vh'];
    $pageview_hash = $_GET['ph'];
    $post_id = intval($_GET['p']);

    send_origin_headers();
    send_nosniff_header();
    nocache_headers();

    collect_in_database($visitor_hash, $pageview_hash, $post_id, $now);
	//collect_in_file($visitor_hash, $pageview_hash, $post_id, $now);
	exit;
}

function collect_in_database($visitor_hash, $pageview_hash, $post_id, $now)
{
	global $wpdb;
	$visitor_by_hash = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aaa_todays_visitors WHERE hash =  %s", [$visitor_hash]));
	$is_new_visitor = $visitor_by_hash < 1;
	if ($is_new_visitor) {
		$is_unique_pageview = true;
		$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}aaa_todays_visitors(hash, seen) VALUES(%s, %s)", [$visitor_hash, $now]));
	} else {
		$is_unique_pageview = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->prefix}aaa_todays_pageviews WHERE hash =  %s LIMIT 1", [$pageview_hash])) < 1;
		if ($is_unique_pageview) {
			$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}aaa_todays_pageviews(hash) VALUES(%s)", [$pageview_hash]));
		}
	}

	hash_add('hits', join(',', [$post_id, $is_new_visitor, $is_new_visitor, $now]));
	//$wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}aaa_pageviews(post_id, is_unique, is_new_visitor, timestamp) VALUES(%s, %s, %s, %s)", [$post_id, $is_unique_pageview, $is_new_visitor, $now]));
}

function collect_in_file($visitor_hash, $pageview_hash, $post_id, $now)
{
	$is_new_visitor = !hash_exists('visitors', $visitor_hash);

	if ($is_new_visitor) {
		$is_new_pageview = true;
		hash_add('visitors', $visitor_hash);
	} else {
		$is_new_pageview = !hash_exists('pageviews', $pageview_hash);
	}

	if ($is_new_pageview) {
		hash_add('pageviews', $pageview_hash);
	}

	hash_add('hits', join(',', [$post_id, $is_new_visitor, $is_new_visitor, $now]));
}

function hash_exists($type, $hash)
{
	$uploads = wp_get_upload_dir();
	$filename = $uploads['basedir'] . '/' . $type . '.php';
	$hashes = file($filename, FILE_IGNORE_NEW_LINES);
	return in_array($hash, $hashes);
}

function hash_add($type, $hash)
{
	$uploads = wp_get_upload_dir();
	$filename = $uploads['basedir'] . '/' . $type . '.php';

	$content = '';
	if (!file_exists($filename)) {
		$content = '<?php exit; ?>' . PHP_EOL;
	}

	$content .= $hash . PHP_EOL;
	return file_put_contents($filename, $content, FILE_APPEND|LOCK_EX);
}

function random_str($length = 64, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
	if ($length < 1) {
		throw new \RangeException("Length must be a positive integer");
	}
	$pieces = [];
	$max = mb_strlen($keyspace, '8bit') - 1;
	for ($i = 0; $i < $length; ++$i) {
		$pieces []= $keyspace[random_int(0, $max)];
	}
	return implode('', $pieces);
}

function benchmark_approaches()
{

// FILE:
	set_time_limit(0);
	$n = 25000;
	$start = microtime(true);
	for ($i = 0; $i < $n; $i++) {
		$visitor_hash = random_str(32);
		$pageview_hash = random_str(32);
		collect_in_file($visitor_hash, $pageview_hash, 1, date('Y-m-d'));
	}
	$end = microtime(true);
	printf("File-based approach took %.3f seconds". PHP_EOL, $end - $start);

// DATABASE:
	$start = microtime(true);
	for ($i = 0; $i < $n; $i++) {
		$visitor_hash = random_str(32);
		$pageview_hash = random_str(32);
		collect_in_database($visitor_hash, $pageview_hash, 1, date('Y-m-d'));
	}
	$end = microtime(true);
	printf("Database-based approach took %.3f seconds" . PHP_EOL, $end - $start);
	die();

}

//benchmark_approaches();

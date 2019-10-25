<?php

namespace AAA;

function maybe_collect_request() {
    // Short-circuit a bunch of AJAX stuff
    if (stripos($_SERVER['REQUEST_URI'], '/admin-ajax.php') === false || ! isset($_GET['action']) || $_GET['action'] !== 'aaa_collect') {
        return;
    }

    $now = date('Y-m-d H:i:s');

    global $wpdb;
    $visitor_hash = $_GET['vh'];
    $pageview_hash = $_GET['ph'];
    $post_id = intval($_GET['p']);

    send_origin_headers();
    send_nosniff_header();
    nocache_headers();

    $visitor_by_hash = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aaa_todays_visitors WHERE hash =  %s LIMIT 1", [$visitor_hash]));
    $is_new_visitor = $visitor_by_hash === null;
    if ($visitor_by_hash) {
        // rate-limit this visitor, already seen this second
        if ($visitor_by_hash->seen === $now ) {
            exit;
        }

        // update "seen" for this returning visitor
        $wpdb->query($wpdb->prepare("UPDATE {$wpdb->prefix}aaa_todays_visitors SET seen = %s WHERE hash = %s", [$now, $visitor_hash]));
    } else {
        // new visitor
        $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}aaa_todays_visitors(hash, seen) VALUES(%s, %s)", [$visitor_hash, $now]));
    }

    $is_unique_pageview = !$wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}aaa_todays_pageviews WHERE hash =  %s LIMIT 1", [$pageview_hash]));
    if ($is_unique_pageview) {
        $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}aaa_todays_pageviews(hash) VALUES(%s)", [$pageview_hash]));
    }

    $wpdb->query($wpdb->prepare("INSERT INTO {$wpdb->prefix}aaa_pageviews(post_id, is_unique, is_new_visitor, timestamp) VALUES(%s, %s, %s, %s)", [$post_id, $is_unique_pageview, $is_new_visitor, $now]));
    exit;
}
<?php

namespace KokoAnalytics;

function maybe_collect_request() {
    // since we call this function (early) on every AJAX request, detect our specific request here
    // this allows us to short-circuit a bunch of unrelated AJAX stuff and gain a lot of performance
    if (! isset($_GET['action']) || $_GET['action'] !== 'koko_analytics_collect' || ! defined('DOING_AJAX') || !DOING_AJAX) {
        return;
    }

    $unique_visitor = (int) $_GET['nv'];
    $unique_pageview = (int) $_GET['up'];
    $post_id = (int) $_GET['p'];
    $referrer = isset($_GET['r']) ? trim($_GET['r']) : '';

    collect_in_file($post_id, $unique_visitor, $unique_pageview, $referrer);

    // set OK headers & prevent caching
    status_header(200);
    header('Content-Type: image/gif');
    header('X-Content-Type-Options: nosniff');
    header('Expires: Wed, 11 Jan 1984 05:00:00 GMT');
    header('Cache-Control: no-cache, must-revalidate, max-age=0');
    header_remove('Last-Modified');
    send_origin_headers();

    // indicate that we are not tracking user specifically, see https://www.w3.org/TR/tracking-dnt/
    header('Tk: N');

    // 1px transparent GIF, needs to be an actual image to make sure browser fires the onload event
    echo base64_decode('R0lGODlhAQABAIAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
    exit;
}

function collect_in_file($post_id, $is_new_visitor, $is_unique_pageview, $referrer = '')
{
    $line = join(',', array($post_id, $is_new_visitor, $is_unique_pageview, $referrer));
    $uploads = wp_get_upload_dir();
    $filename = $uploads['basedir'] . '/pageviews.php';
    $content = '';

    // if file does not yet exist, add PHP header to prevent direct file access
    if (!file_exists($filename)) {
        $content = '<?php exit; ?>' . PHP_EOL;
    }

    // append data to file
    $content .= $line . PHP_EOL;
    return file_put_contents($filename, $content, FILE_APPEND);
}

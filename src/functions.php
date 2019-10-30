<?php

namespace AP;

function maybe_collect_request() {
    // Short-circuit a bunch of AJAX stuff
    if (stripos($_SERVER['REQUEST_URI'], '/ap-collect.php') === false
		&& (stripos($_SERVER['REQUEST_URI'], '/admin-ajax.php') === false || ! isset($_GET['action']) || $_GET['action'] !== 'ap_collect')) {
        return;
    }

	$now = date('Y-m-d H:i:s');
    $unique_visitor = (int) $_GET['nv'];
    $unique_pageview = (int) $_GET['up'];
    $post_id = (int) $_GET['p'];

    send_origin_headers();
    send_nosniff_header();
    nocache_headers();

	collect_in_file($post_id, $now, $unique_visitor, $unique_pageview);
	status_header(200);
	echo base64_decode("R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7");
	exit;
}

function collect_in_file($post_id, $now, $is_new_visitor, $is_unique_pageview)
{
	$line = join(',', array($now, $post_id, $is_new_visitor, $is_unique_pageview));
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

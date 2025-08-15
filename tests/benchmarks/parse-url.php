<?php

require __DIR__ . '/functions.php';

function normalize_with_parse_str(string $url): string
{

    // remove # from URL
    $pos = strpos($url, '#');
    if ($pos !== false) {
        $url = substr($url, 0, $pos);
    }

    // TODO: Benchmark this against explode on '&' then '=' then string concat

    // if URL contains query string, parse it and only keep certain parameters
    $pos = strpos($url, '?');
    if ($pos !== false) {
        $query_str = substr($url, $pos + 1);

        $params = [];
        parse_str($query_str, $params);

        $new_query_str  = http_build_query(array_intersect_key($params, [ 'page_id' => 1, 'p' => 1, 'tag' => 1, 'cat' => 1, 'product' => 1, 'attachment_id' => 1]));
        $new_url        = substr($url, 0, $pos + 1) . $new_query_str;

        // trim trailing question mark & replace url with new sanitized url
        $url = rtrim($new_url, '?');
    }

    return $url;
}

function normalize_explode(string $url): string
{
    // remove # from URL
    if (($pos = strpos($url, '#')) !== false) {
        $url = substr($url, 0, $pos);
    }

    // if URL contains query string, parse it and only keep certain parameters
    if (($pos = strpos($url, '?')) !== false) {
        $query_string = substr($url, $pos+1);
        $url = substr($url, 0, $pos+1);
        $allowed_params = [ 'page_id', 'p', 'tag', 'cat', 'product', 'attachment_id'];

        foreach (explode('&', $query_string) as $a) {
            $parts = explode('=', $a);
            $left = $parts[0];
            $right = $parts[1] ?? '';

            if (in_array($left, $allowed_params)) {
                $url .= $left;

                if ($right) {
                    $url .= '=';
                    $url .= $right;
                    $url .= '&';
                }
            }
        }

        $url = rtrim($url, '?&');
    }

    return $url;
}


$n = 10000;

$time = bench(function () {
    $normalized = normalize_with_parse_str("/about/?utm_source=source&utm_medium=medium&p=100&utm_campaign=campaign&");
    bench_assert("/about/?p=100", $normalized);
}, $n);

printf("normalize_with_parse_str:\t %.2f ns (%.2f per it)\n", $time, $time / $n);

$time = bench(function () {
    $normalized = normalize_explode("/about/?utm_source=source&utm_medium=medium&p=100&utm_campaign=campaign&");
    bench_assert("/about/?p=100", $normalized);
}, $n);
printf("normalize_explode:\t %.2f ns (%.2f per it)\n", $time, $time / $n);

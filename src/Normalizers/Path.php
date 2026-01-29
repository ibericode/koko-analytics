<?php

namespace KokoAnalytics\Normalizers;

class Path
{
    public static function get_allowed_query_vars(): array
    {
        return apply_filters('koko_analytics_allowed_query_vars', ['page_id', 'p', 'tag', 'cat', 'product', 'attachment_id']);
    }

    public static function normalize(string $value): string
    {
        // remove # from URL
        if (($pos = strpos($value, '#')) !== false) {
            $value = substr($value, 0, $pos);
        }

        // if URL contains query string, parse it and only keep certain parameters
        if (($pos = strpos($value, '?')) !== false) {
            $query_str = substr($value, $pos + 1);
            $value = substr($value, 0, $pos + 1);

            $params = [];
            parse_str($query_str, $params);
            $value .= http_build_query(array_intersect_key($params, array_flip(self::get_allowed_query_vars())));

            // trim trailing question mark & replace url with new sanitized url
            $value = rtrim($value, '?');
        }

        // in case wordpress is served from a subdirectory, use the path relative to the wordpress root page
        $home_path = parse_url(home_url(''), PHP_URL_PATH);
        if ($home_path && $home_path !== '/' && str_starts_with($value, $home_path)) {
            $value = substr($value, strlen($home_path));
        }

        // if value ends with /amp/, remove suffix (but leave trailing slash)
        if (str_ends_with($value, '/amp/')) {
            $value = substr($value, 0, strlen($value) - 4);
        }

        return $value;
    }
}

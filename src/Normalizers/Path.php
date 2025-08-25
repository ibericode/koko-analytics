<?php

namespace KokoAnalytics\Normalizers;

class Path
{
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
            $value .= http_build_query(array_intersect_key($params, [ 'page_id' => 1, 'p' => 1, 'tag' => 1, 'cat' => 1, 'product' => 1, 'attachment_id' => 1]));

            // trim trailing question mark & replace url with new sanitized url
            $value = rtrim($value, '?');
        }

        return $value;
    }
}

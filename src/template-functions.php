<?php

namespace KokoAnalytics;

function percent_format_i18n($pct): string
{
    _deprecated_function(__FUNCTION__, '1.6.6', 'KokoAnalytics\Fmt::percent');
    return Fmt::percent($pct);
}

function get_referrer_url_href(string $url): string
{
    _deprecated_function(__FUNCTION__, '1.6.6', 'KokoAnalytics\Fmt::referrer_url_href');
    return Fmt::referrer_url_href($url);
}

function get_referrer_url_label(string $url): string
{
    _deprecated_function(__FUNCTION__, '1.6.6', 'KokoAnalytics\Fmt::referrer_url_label');
    return Fmt::referrer_url_label($url);
}

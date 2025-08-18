<?php

namespace KokoAnalytics;

class Fmt
{
    public static function percent($pct): string
    {
        if ($pct == 0) {
            return '';
        }

        $prefix = $pct > 0 ? '+' : '';
        $formatted = \number_format_i18n($pct * 100, 0);
        return $prefix . $formatted . '%';
    }

    public static function referrer_url_href(string $url): string
    {
        if (\strpos($url, '://t.co/') !== false) {
            return 'https://twitter.com/search?q=' . \urlencode($url);
        } elseif (\strpos($url, 'android-app://') === 0) {
            return \str_replace('android-app://', 'https://play.google.com/store/apps/details?id=', $url);
        }

        return apply_filters('koko_analytics_referrer_url_href', $url);
    }

    public static function referrer_url_label(string $url): string
    {
        // if link starts with android-app://, turn that prefix into something more human readable
        if (\strpos($url, 'android-app://') === 0) {
            return \str_replace('android-app://', 'Android app: ', $url);
        }

        // strip protocol and www. prefix
        $url = (string) \preg_replace('/^https?:\/\/(?:www\.)?/', '', $url);

        // trim trailing slash
        $url = \rtrim($url, '/');

        return apply_filters('koko_analytics_referrer_url_label', $url);
    }
}

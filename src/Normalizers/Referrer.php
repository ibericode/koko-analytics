<?php

namespace KokoAnalytics\Normalizers;

class Referrer
{
    public static function normalize(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        // for backwards compatibility with users using filters hooked on `koko_analytics_url_aggregations`
        // we run the full URL through the filter before limiting it to just the host and maybe path
        static $aggregations = [
            '/^android-app:\/\/com\.(www\.)?google\.android\.googlequicksearchbox.*/' => 'https://www.google.com',
            '/^android-app:\/\/com\.www\.google\.android\.gm$/' => 'https://www.google.com',
            '/^https?:\/\/(?:www\.)?(google|bing|ecosia)\.([a-z]{2,4}(?:\.[a-z]{2,4})?)(?:\/search|\/url)?/' => 'https://www.$1.$2',
            '/^android-app:\/\/com\.facebook\.(.+)/' => 'https://facebook.com',
            '/^https?:\/\/(?:[a-z-]{1,32}\.)?l?facebook\.com(?:\/l\.php)?/' => 'https://facebook.com',
            '/^https?:\/\/(?:[a-z-]{1,32}\.)?l?instagram\.com(?:\/l\.php)?/' => 'https://www.instagram.com',
            '/^https?:\/\/(?:www\.)?linkedin\.com\/feed.*/' => 'https://www.linkedin.com',
            '/^https?:\/\/(?:www\.)?pinterest\.com/' => 'https://pinterest.com',
            '/^https?:\/\/(?:www|m)\.baidu\.com.*/' => 'https://www.baidu.com',
            '/^https?:\/\/yandex\.ru\/clck.*/' => 'https://yandex.ru',
            '/^https?:\/\/yandex\.ru\/search/' => 'https://yandex.ru',
            '/^https?:\/\/(?:[a-z-]{1,32}\.)?search\.yahoo\.com\/(?:search)?[^?]*(.*)/' => 'https://search.yahoo.com/search$1',
            '/^https?:\/\/(out|new|old|www|m)\.reddit\.com(.*)/' => 'https://reddit.com$2',
            '/^https?:\/\/(?:[a-z0-9]{1,8}\.)+sendib(?:m|t)[0-9]\.com.*/' => 'https://www.brevo.com',
        ];

        $aggregations = apply_filters('koko_analytics_url_aggregations', $aggregations);
        $normalized_value = (string) preg_replace(array_keys($aggregations), array_values($aggregations), $value, 1);
        if (preg_last_error() !== PREG_NO_ERROR) {
            error_log("Koko Analytics: preg_replace error in Referrer::normalize('$value'): " . preg_last_error_msg());
            return $value;
        }
        $value = $normalized_value;

        // limit resulting value to just host
        $url_parts = parse_url($value);
        $result = $url_parts['host'];

        // strip www. prefix
        if (str_starts_with($result, 'www.')) {
            $result = substr($result, 4);
        }

        // add path if domain is whitelisted
        $whitelisted_domains = ['wordpress.org', 'kokoanalytics.com', 'github.com', 'reddit.com', 'indiehackers.com'];
        $whitelisted_domains = apply_filters('koko_analytics_whitelisted_referrer_domains', $whitelisted_domains);
        if (in_array($result, $whitelisted_domains) && !empty($url_parts['path']) && $url_parts['path'] !== '/') {
            $result .= $url_parts['path'];
        }

        return $result;
    }
}

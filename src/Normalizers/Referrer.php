<?php

namespace KokoAnalytics\Normalizers;

class Referrer
{
    public static function normalize(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        // if URL has no protocol, assume HTTP
        // we change this to HTTPS for sites that are known to support it
        if (strpos($value, '://') === false) {
            $value = 'http://' . $value;
        }

        // discard everything after 255 chars
        $value = substr($value, 0, 255);

        // normalize path component
        $value = Path::normalize($value);

        // trim trailing slash if URL has no path component
        $path = parse_url($value, PHP_URL_PATH);
        if ($path === '' || $path === '/') {
            $value = rtrim($value, '/');
        }

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

        return $normalized_value;
    }
}

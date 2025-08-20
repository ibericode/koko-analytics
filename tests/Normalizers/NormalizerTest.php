<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Normalizers\Normalizer;
use PHPUnit\Framework\TestCase;

class NormalizerTest extends TestCase
{
    public function test_path(): void
    {
        $tests = [
            // empty string should remain untouched
            '' => '',
            '/' => '/',
            '/about/' => '/about/',
            '/koko/is/great/' => '/koko/is/great/',
            '/?p=100' => '/?p=100',
            '/?utm_source=source&utm_medium=medium&utm_campaign=campaign' => '/',
            '/?utm_source=source&utm_medium=medium&p=200&utm_campaign=campaign' => '/?p=200',
            '/?attachment_id=123' => '/?attachment_id=123',
        ];

        foreach ($tests as $input => $output) {
            $this->assertEquals($output, Normalizer::path($input));
        }
    }

    public function test_referrer(): void
    {
        $tests = [
            '' => '',
            'https://www.kokoanalytics.com' => 'kokoanalytics.com',
            'https://dentalclinicwhatever.com' => 'dentalclinicwhatever.com',
            'https://wordpress.org/plugins/koko-analytics/' => 'wordpress.org/plugins/koko-analytics/',
            'https://pinterest.com/pin/foobar' => 'pinterest.com',
            'https://www.pinterest.com' => 'pinterest.com',
            'https://www.pinterest.com/pin/foobar' => 'pinterest.com',
            'https://www.google.com' => 'google.com',
            'https://www.google.nl/url' => 'google.nl',
            'https://www.google.nl/search' => 'google.nl',
            'http://google.nl/search' => 'google.nl',
            'https://www.google.co.uk/search' => 'google.co.uk',
            'https://www.google.com/search' => 'google.com',
            'android-app://com.google.android.googlequicksearchbox' => 'google.com',
            'android-app://com.google.android.googlequicksearchbox/https/www.google.com' => 'google.com',
            'android-app://com.www.google.android.googlequicksearchbox' => 'google.com',
            'android-app://com.www.google.android.googlequicksearchbox/https/www.google.com' => 'google.com',
            'android-app://com.www.google.android.gm' => 'google.com',
            'https://bing.com' => 'bing.com',
            'https://www.bing.com' => 'bing.com',
            'https://www.bing.com/search' => 'bing.com',
            'https://www.bing.com/url' => 'bing.com',
            'android-app://com.facebook.katana' => 'facebook.com',
            'android-app://m.facebook.com' => 'facebook.com',
            'https://m.facebook.com' => 'facebook.com',
            'https://m.facebook.com/profile/whatever' => 'facebook.com',
            'https://l.facebook.com' => 'facebook.com',
            'https://l.facebook.com/l.php' => 'facebook.com',
            'https://lfacebook.com' => 'facebook.com', // Don't know what's up with this domain
            'https://de-de.facebook.com' => 'facebook.com',
            'https://www.facebook.com' => 'facebook.com',
            'https://l.instagram.com' => 'instagram.com',
            'https://instagram.com' => 'instagram.com',
            'https://www.ecosia.org/search' => 'ecosia.org',
            'https://www.linkedin.com/feed' => 'linkedin.com',
            'https://www.linkedin.com/feed/' => 'linkedin.com',
            'https://www.linkedin.com/feed/update/urn:li:activity:6620280880285921280' => 'linkedin.com',
            'https://www.baidu.com/link' => 'baidu.com',
            'https://m.baidu.com/from=844b/bd_page_type=1/ssid=98c26c6f6e676d65697869620b/uid=0/pu=usm%402%2Csz%40320_1001%2Cta%40iphone_2_9.0_24_79.0/baiduid=B24A174BB75A8A37CEA414106EC583CB/w=0_10_/t=iphone/l=1/tc' => 'baidu.com',
            'https://yandex.ru/clck/jsredir' => 'yandex.ru',
            'https://yandex.ru/search' => 'yandex.ru',
            'https://search.yahoo.com/search;_ylt=AwrJ62D7vO9hhigARMpXNyoA;_ylu=Y29sbwNiZjEEcG9zAzEEdnRpZAMEc2VjA3BhZ2luYXRpb24-?p=danny+van+kooten' => 'search.yahoo.com',
            'https://r.search.yahoo.com/search;_ylt=AwrJ62D7vO9hhigARMpXNyoA;_ylu=Y29sbwNiZjEEcG9zAzEEdnRpZAMEc2VjA3BhZ2luYXRpb24-?p=danny+van+kooten' => 'search.yahoo.com',
            'https://r.search.yahoo.com/_ylt=AwrJ3s8QPIlhnGgADxAYAopQ;_ylu=c2VjA3NyBHNsawNpbWcEb2lkA2U2ZTY3ZmExZDUzNDAwYmU5MjAzYTYxN2U1ZTI5YTQ2BGdwb3MDMTcEaXQDYmluZw--/RV=2/RE=1636412560/RO=11/RU=http%3a%2f%2fvankootenarchitectuur.nl%2fverbouwing-kerkzaal-taborkerk%2f' => 'search.yahoo.com',
            'https://out.reddit.com' => 'reddit.com',
            'https://new.reddit.com' => 'reddit.com',
            'https://old.reddit.com' => 'reddit.com',
            'https://www.reddit.com' => 'reddit.com',
            'https://m.reddit.com' => 'reddit.com',
            'https://6gg78.r.ah.d.sendibm4.com/mk/cl/f/sugrxasd218e287' => 'brevo.com',
            'https://6gg78.r.ah.d.sendibt1.com/mk/cl/f/sugrxasd218e287' => 'brevo.com',

            'https://wordpress.org' => 'wordpress.org',
            'https://wordpress.org/' => 'wordpress.org',
            'https://wordpress.org/?utm_source=duckduckgo' => 'wordpress.org',
            'https://wordpress.org/?page_id=500&utm_source=duckduckgo' => 'wordpress.org',
            'https://wordpress.org/?utm_source=duckduckgo&p=500&cat=cars&product=toyota-yaris' => 'wordpress.org',
            'https://wordpress.org/?foo=bar&p=500&utm_source=duckduckgo#utm_medium=link' => 'wordpress.org',
            'https://wordpress.org/#foo=bar&bar=foo' => 'wordpress.org',
        ];

        foreach ($tests as $input => $output) {
            $this->assertEquals($output, Normalizer::referrer($input), $input);
        }
    }
}

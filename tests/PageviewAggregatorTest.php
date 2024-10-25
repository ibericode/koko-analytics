<?php
declare(strict_types=1);

use KokoAnalytics\Aggregator;
use PHPUnit\Framework\TestCase;

final class PageviewAggregatorTest extends TestCase
{
    public function test_clean_url() : void
    {
        $a = new \KokoAnalytics\Pageview_Aggregator();

        $tests = [
            'https://wordpress.org' => 'https://wordpress.org',
            'https://wordpress.org/' => 'https://wordpress.org',
            'https://wordpress.org/?utm_source=duckduckgo' => 'https://wordpress.org',
            'https://wordpress.org/?page_id=500&utm_source=duckduckgo' => 'https://wordpress.org/?page_id=500',
            'https://wordpress.org/?utm_source=duckduckgo&p=500&cat=cars&product=toyota-yaris' => 'https://wordpress.org/?p=500&cat=cars&product=toyota-yaris',
            'https://wordpress.org/?foo=bar&p=500&utm_source=duckduckgo#utm_medium=link' => 'https://wordpress.org/?p=500',
            'https://wordpress.org/#foo=bar&bar=foo' => 'https://wordpress.org',
            'https://search.yahoo.com/search;_ylt=AwrJ62D7vO9hhigARMpXNyoA;_ylu=Y29sbwNiZjEEcG9zAzEEdnRpZAMEc2VjA3BhZ2luYXRpb24-?p=danny+van+kooten&fr=sfp&fr2=p%3As%2Cv%3Asfp%2Cm%3Asb-top&b=8&pz=7&bct=0&xargs=0' => 'https://search.yahoo.com/search;_ylt=AwrJ62D7vO9hhigARMpXNyoA;_ylu=Y29sbwNiZjEEcG9zAzEEdnRpZAMEc2VjA3BhZ2luYXRpb24-?p=danny+van+kooten',
        ];

        foreach ($tests as $input => $output) {
            $this->assertEquals($output, $a->clean_url($input));
        }
    }

    public function test_normalize_url() : void
    {
        $a = new \KokoAnalytics\Pageview_Aggregator();
        $tests = [
            '' => '',
            'https://www.kokoanalytics.com' => 'https://www.kokoanalytics.com',
            'https://wordpress.org/plugins/koko-analytics/' => 'https://wordpress.org/plugins/koko-analytics/',
            'https://pinterest.com/pin/foobar' => 'https://pinterest.com/pin/foobar',
            'https://www.pinterest.com' => 'https://pinterest.com',
            'https://www.pinterest.com/pin/foobar' => 'https://pinterest.com/pin/foobar',
            'www.pinterest.com/pin/foobar' => 'https://pinterest.com/pin/foobar',
            'pinterest.com/pin/foobar' => 'https://pinterest.com/pin/foobar',
            'www.google.com' => 'https://www.google.com',
            'https://www.google.com' => 'https://www.google.com',
            'https://www.google.nl/url' => 'https://www.google.nl',
            'https://www.google.nl/search' => 'https://www.google.nl',
            'http://google.nl/search' => 'https://www.google.nl',
            'https://www.google.co.uk/search' => 'https://www.google.co.uk',
            'https://www.google.com/search' => 'https://www.google.com',
            'android-app://com.google.android.googlequicksearchbox' => 'https://www.google.com',
            'android-app://com.google.android.googlequicksearchbox/https/www.google.com' => 'https://www.google.com',
            'android-app://com.www.google.android.googlequicksearchbox' => 'https://www.google.com',
            'android-app://com.www.google.android.googlequicksearchbox/https/www.google.com' => 'https://www.google.com',
            'android-app://com.www.google.android.gm' => 'https://www.google.com',
            'https://bing.com' => 'https://www.bing.com',
            'https://www.bing.com' => 'https://www.bing.com',
            'https://www.bing.com/search' => 'https://www.bing.com',
            'https://www.bing.com/url' => 'https://www.bing.com',
            'android-app://com.facebook.katana' => 'https://facebook.com',
            'https://m.facebook.com' => 'https://facebook.com',
            'https://m.facebook.com/profile/whatever' => 'https://facebook.com/profile/whatever',
            'https://l.facebook.com' => 'https://facebook.com',
            'https://l.facebook.com/l.php' => 'https://facebook.com',
            'https://lfacebook.com' => 'https://facebook.com', // Don't know what's up with this domain
            'https://de-de.facebook.com' => 'https://facebook.com',
            'https://www.facebook.com' => 'https://facebook.com',
            'facebook.com' => 'https://facebook.com',
            'www.instagram.com' => 'https://www.instagram.com',
            'https://l.instagram.com' => 'https://www.instagram.com',
            'https://instagram.com' => 'https://www.instagram.com',
            'https://www.ecosia.org/search' => 'https://www.ecosia.org',
            'https://www.linkedin.com/feed' => 'https://www.linkedin.com',
            'https://www.linkedin.com/feed/' => 'https://www.linkedin.com',
            'https://www.linkedin.com/feed/update/urn:li:activity:6620280880285921280' => 'https://www.linkedin.com',
            'https://www.baidu.com/link' => 'https://www.baidu.com',
            'https://m.baidu.com/from=844b/bd_page_type=1/ssid=98c26c6f6e676d65697869620b/uid=0/pu=usm%402%2Csz%40320_1001%2Cta%40iphone_2_9.0_24_79.0/baiduid=B24A174BB75A8A37CEA414106EC583CB/w=0_10_/t=iphone/l=1/tc' => 'https://www.baidu.com',
            'https://yandex.ru/clck/jsredir' => 'https://yandex.ru',
            'https://yandex.ru/search' => 'https://yandex.ru',
            'https://search.yahoo.com/search;_ylt=AwrJ62D7vO9hhigARMpXNyoA;_ylu=Y29sbwNiZjEEcG9zAzEEdnRpZAMEc2VjA3BhZ2luYXRpb24-?p=danny+van+kooten' => 'https://search.yahoo.com/search?p=danny+van+kooten',
            'https://r.search.yahoo.com/search;_ylt=AwrJ62D7vO9hhigARMpXNyoA;_ylu=Y29sbwNiZjEEcG9zAzEEdnRpZAMEc2VjA3BhZ2luYXRpb24-?p=danny+van+kooten' => 'https://search.yahoo.com/search?p=danny+van+kooten',
            'https://r.search.yahoo.com/_ylt=AwrJ3s8QPIlhnGgADxAYAopQ;_ylu=c2VjA3NyBHNsawNpbWcEb2lkA2U2ZTY3ZmExZDUzNDAwYmU5MjAzYTYxN2U1ZTI5YTQ2BGdwb3MDMTcEaXQDYmluZw--/RV=2/RE=1636412560/RO=11/RU=http%3a%2f%2fvankootenarchitectuur.nl%2fverbouwing-kerkzaal-taborkerk%2f' => 'https://search.yahoo.com/search',
            'https://out.reddit.com/r/foobar' => 'https://reddit.com/r/foobar',
            'https://new.reddit.com/r/foobar' => 'https://reddit.com/r/foobar',
            'https://old.reddit.com/r/foobar' => 'https://reddit.com/r/foobar',
            'https://old.reddit.com/r/foobar' => 'https://reddit.com/r/foobar',
            'https://6gg78.r.ah.d.sendibm4.com/mk/cl/f/sugrxasd218e287' => 'https://www.brevo.com',
            'https://6gg78.r.ah.d.sendibt1.com/mk/cl/f/sugrxasd218e287' => 'https://www.brevo.com',
        ];

        foreach ($tests as $input => $output) {
            $this->assertEquals($output, $a->normalize_url($input));
        }
    }

    public function test_is_valid_url(): void {
        $a = new \KokoAnalytics\Pageview_Aggregator();

        foreach ([
            'https://www.kokoanalytics.com',
            'android-app://com.google.android.googlequicksearchbox',
        ] as $url) {
            $this->assertTrue($a->is_valid_url($url));
        }

         foreach ([
            '',
            'Hello world',
            '<script>alert(1)</script>',
        ] as $url) {
            $this->assertFalse($a->is_valid_url($url));
        }
    }
}

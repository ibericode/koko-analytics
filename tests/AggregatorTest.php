<?php
declare(strict_types=1);

use KokoAnalytics\Aggregator;
use PHPUnit\Framework\TestCase;

final class AggregatorTest extends TestCase
{
    public function test_clean_url() : void
    {
        $a = new Aggregator();

        $tests = [
          'https://wordpress.org/' => 'https://wordpress.org',
		  'https://wordpress.org/?utm_source=duckduckgo' => 'https://wordpress.org',
          'https://wordpress.org/?page_id=500&utm_source=duckduckgo' => 'https://wordpress.org/?page_id=500',
          'https://wordpress.org/?foo=bar&p=500&utm_source=duckduckgo#utm_medium=link' => 'https://wordpress.org/?p=500',
        ];

        foreach ($tests as $input => $output) {
            $this->assertEquals($output, $a->clean_url($input));
        }
    }

    public function test_normalize_url() : void
	{
		$a = new Aggregator();
		$tests = [
			'https://wordpress.org/plugins/koko-analytics/' => 'https://wordpress.org/plugins/koko-analytics/',
			'https://www.google.com/search' => 'https://www.google.com',
			'https://www.google.co.uk/search' => 'https://www.google.co.uk',
			'https://www.google.nl/url' => 'https://www.google.nl',
			'https://m.facebook.com' => 'https://facebook.com',
			'https://m.facebook.com/profile/whatever' => 'https://facebook.com/profile/whatever',
			'https://pinterest.com/pin/foobar' => 'https://pinterest.com/pin/foobar',
			'https://www.linkedin.com/feed' => 'https://www.linkedin.com',
			'https://www.linkedin.com/feed/update/urn:li:activity:6620280880285921280' => 'https://www.linkedin.com',
			'https://www.baidu.com/link' => 'https://www.baidu.com',
			'https://m.baidu.com/from=844b/bd_page_type=1/ssid=98c26c6f6e676d65697869620b/uid=0/pu=usm%402%2Csz%40320_1001%2Cta%40iphone_2_9.0_24_79.0/baiduid=B24A174BB75A8A37CEA414106EC583CB/w=0_10_/t=iphone/l=1/tc' => 'https://www.baidu.com',
			'https://yandex.ru/clck/jsredir' => 'https://yandex.ru',
		];

		foreach ($tests as $input => $output) {
		    $this->assertEquals($output, $a->normalize_url($input));
		}
	}
}

<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use function KokoAnalytics\extract_pageview_data;
use function KokoAnalytics\extract_event_data;
use function KokoAnalytics\number_fmt;
use function KokoAnalytics\get_client_ip;

final class FunctionsTest extends TestCase
{
    public function testFmtLargeNumber(): void
    {
        $this->assertEquals('0', number_fmt(0));
        $this->assertEquals('1', number_fmt(1));
        $this->assertEquals('100', number_fmt(100));
        $this->assertEquals('123', number_fmt(123));
        $this->assertEquals('1000', number_fmt(1000));
        $this->assertEquals('1234', number_fmt(1234));
        $this->assertEquals('1700', number_fmt(1700));
        $this->assertEquals('10K', number_fmt(10000));
        $this->assertEquals('12.3K', number_fmt(12340));
        $this->assertEquals('17K', number_fmt(17000));
        $this->assertEquals('100K', number_fmt(100000));
        $this->assertEquals('123K', number_fmt(123000));
        $this->assertEquals('170K', number_fmt(170000));
        $this->assertEquals('171K', number_fmt(170500));
        $this->assertEquals('1,000K', number_fmt(1000000));
        $this->assertEquals('1,765K', number_fmt(1765499));
        $this->assertEquals('1,766K', number_fmt(1765500));
    }

    public function testExtractPageviewData(): void
    {
       // incomplete params
       $this->assertEquals(extract_pageview_data([]), []);
       $this->assertEquals(extract_pageview_data(['p' => '1']), []);
       $this->assertEquals(extract_pageview_data(['nv' => '2']), []);
       $this->assertEquals(extract_pageview_data(['up' => '3']), []);
       $this->assertEquals(extract_pageview_data(['p' => '1', 'nv' => '2']), []);
       $this->assertEquals(extract_pageview_data(['p' => '1', 'up' => '3']), []);
       $this->assertEquals(extract_pageview_data(['nv' => '2', 'up' => '3']), []);

       // complete but invalid
       $this->assertEquals(extract_pageview_data(['p' => '', 'nv' => '', 'up' => '']), []);
       $this->assertEquals(extract_pageview_data(['p' => 'x', 'nv' => '2', 'up' => '3']), []);
       $this->assertEquals(extract_pageview_data(['p' => '1', 'nv' => 'x', 'up' => '3']), []);
       $this->assertEquals(extract_pageview_data(['p' => '1', 'nv' => '2', 'up' => 'x']), []);
       $this->assertEquals(extract_pageview_data(['p' => '1', 'nv' => '2', 'up' => '3', 'r' => 'not an url']), []);

       // complete and valid
      $this->assertEquals(extract_pageview_data(['p' => '1', 'nv' => '2', 'up' => '3']), ['p', 1, 2, 3, '']);
      $this->assertEquals(extract_pageview_data(['p' => '1', 'nv' => '2', 'up' => '3', 'r' => '']), ['p', 1, 2, 3, '']);
      $this->assertEquals(extract_pageview_data(['p' => '1', 'nv' => '2', 'up' => '3', 'r' => 'https://www.kokoanalytics.com']), ['p', 1, 2, 3, 'https://www.kokoanalytics.com']);
    }

    public function testExtractEventData(): void
    {
       // incomplete
       $this->assertEquals(extract_event_data([]), []);
       $this->assertEquals(extract_event_data(['e' => 'Event']), []);
       $this->assertEquals(extract_event_data(['p' => 'Param']), []);
       $this->assertEquals(extract_event_data(['u' => '1']), []);
       $this->assertEquals(extract_event_data(['v' => '1']), []);
       $this->assertEquals(extract_event_data(['e' => 'Event', 'v' => '1']), []);
       $this->assertEquals(extract_event_data(['e' => 'Event', 'u' => '1']), []);
       $this->assertEquals(extract_event_data(['p' => 'Param', 'v' => '1']), []);
       $this->assertEquals(extract_event_data(['p' => 'Param', 'u' => '1']), []);
       $this->assertEquals(extract_event_data(['e' => 'Event', 'p' => 'Param', 'u' => '1']), []);
       $this->assertEquals(extract_event_data(['e' => 'Event', 'p' => 'Param', 'v' => '1']), []);

       // complete but invalid
       $this->assertEquals(extract_event_data(['e' => 'Event', 'p' => 'Param', 'u' => '1', 'v' => 'nan']), []);
       $this->assertEquals(extract_event_data(['e' => 'Event', 'p' => 'Param', 'u' => 'nan', 'v' => '100']), []);
       $this->assertEquals(extract_event_data(['e' => 'Event', 'p' => 'Param', 'u' => 'nan', 'v' => 'nan']), []);
       $this->assertEquals(extract_event_data(['e' => '', 'p' => 'Param', 'u' => '1', 'v' => '100']), []);

       // complete and valid
       $this->assertEquals(extract_event_data(['e' => 'Event', 'p' => 'Param', 'u' => '1', 'v' => '100']), ['e', 'Event', 'Param', 1, 100]);
    }

    public function testGetClientIp(): void
    {
        $this->assertEquals(get_client_ip(), '');

        $_SERVER['REMOTE_ADDR'] = '1.1.1.1';
        $this->assertEquals(get_client_ip(), '1.1.1.1');

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '2.2.2.2';
        $this->assertEquals(get_client_ip(), '2.2.2.2');

        $_SERVER['HTTP_X_FORWARDED_FOR'] = '3.3.3.3, 2.2.2.2';
        $this->assertEquals(get_client_ip(), '3.3.3.3');

        $_SERVER['HTTP_X_FORWARDED_FOR'] = 'not-an-ip';
        $this->assertEquals(get_client_ip(), '1.1.1.1');
    }
}

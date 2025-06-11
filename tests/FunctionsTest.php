<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use PHPUnit\Framework\TestCase;

use function KokoAnalytics\extract_pageview_data;
use function KokoAnalytics\extract_event_data;
use function KokoAnalytics\get_client_ip;

final class FunctionsTest extends TestCase
{
    public function testExtractPageviewData(): void
    {
       // incomplete params
        $this->assertEquals(extract_pageview_data([]), []);
        $this->assertEquals(extract_pageview_data(['r' => 'http://www.kokoanalytics.com']), []);

       // complete but invalid
        $this->assertEquals(extract_pageview_data(['p' => '']), []);
        $this->assertEquals(extract_pageview_data(['p' => '1', 'r' => 'not an url']), []);

        // complete and valid
        foreach (
            [
            [['p' => '1'], ['p', null, 1, 1, 1, '']],

            ] as [$input, $expected]
        ) {
            $actual = extract_pageview_data($input);
            $this->assertEquals($expected[0], $actual[0]);  // type indicator
            $this->assertIsInt($actual[1]); // timestamp
            $this->assertEquals($expected[2], $actual[2]);  // post id
            $this->assertEquals($expected[3], $actual[3]);
            $this->assertEquals($expected[4], $actual[4]);
            $this->assertEquals($expected[5], $actual[5]);
        }
    }

    public function testExtractEventData(): void
    {
        // incomplete
        $this->assertEquals(extract_event_data([]), []);
        $this->assertEquals(extract_event_data(['e' => 'Event']), []);
        $this->assertEquals(extract_event_data(['p' => 'Param']), []);
        $this->assertEquals(extract_event_data(['v' => '1']), []);
        $this->assertEquals(extract_event_data(['e' => 'Event', 'v' => '1']), []);
        $this->assertEquals(extract_event_data(['p' => 'Param', 'v' => '1']), []);

        // complete but invalid
        $this->assertEquals(extract_event_data(['e' => 'Event', 'p' => 'Param', 'v' => 'nan']), []);
        $this->assertEquals(extract_event_data(['e' => '', 'p' => 'Param', 'v' => '100']), []);

        // complete and valid
        $actual = extract_event_data(['e' => 'Event', 'p' => 'Param', 'v' => '100']);
        $expected = ['e', 'Event', 'Param', 1, 100];
        $this->assertEquals($expected[0], $actual[0]);
        $this->assertEquals($expected[1], $actual[1]);
        $this->assertEquals($expected[2], $actual[2]);
        $this->assertEquals($expected[3], $actual[3]);
        $this->assertEquals($expected[4], $actual[4]);
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

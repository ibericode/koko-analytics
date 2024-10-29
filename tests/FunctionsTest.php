<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use function KokoAnalytics\extract_pageview_data;
use function KokoAnalytics\fmt_large_number;

final class FunctionsTest extends TestCase
{
    public function testFmtLargeNumber(): void
    {
        $this->assertEquals('170', fmt_large_number(170));
        $this->assertEquals('1000', fmt_large_number(1000));
        $this->assertEquals('1700', fmt_large_number(1700));
        $this->assertEquals('10K', fmt_large_number(10000));
        $this->assertEquals('17K', fmt_large_number(17000));
        $this->assertEquals('100K', fmt_large_number(100000));
        $this->assertEquals('170K', fmt_large_number(170000));
        $this->assertEquals('176K', fmt_large_number(175500));
        $this->assertEquals('17.6K', fmt_large_number(17550));
        $this->assertEquals('1755', fmt_large_number(1755));
        $this->assertEquals('175', fmt_large_number(175));
    }

    public function testExtractData(): void {
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
}

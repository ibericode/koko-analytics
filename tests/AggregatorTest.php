<?php
declare(strict_types=1);

use KokoAnalytics\Aggregator;
use PHPUnit\Framework\TestCase;

final class AggregatorTest extends TestCase
{
    public function testSanitizeUrl() : void
    {
        $a = new Aggregator();

        $tests = [
          'https://wordpress.org/' => 'https://wordpress.org/',
          'https://wordpress.org/?page_id=500&utm_source=duckduckgo' => 'https://wordpress.org/?page_id=500',
          'https://wordpress.org/?foo=bar&p=500&utm_source=duckduckgo#utm_medium=link' => 'https://wordpress.org/?p=500',
        ];

        foreach ($tests as $input => $output) {
            $this->assertEquals($output, $a->sanitize_url($input));
        }
    }
}

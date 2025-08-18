<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Aggregator;
use PHPUnit\Framework\TestCase;

final class PageviewAggregatorTest extends TestCase
{
    public function test_is_valid_url(): void
    {
        $a = new \KokoAnalytics\Pageview_Aggregator();

        foreach ([
            'https://www.kokoanalytics.com',
            'android-app://com.google.android.googlequicksearchbox',
            ] as $url
        ) {
            $this->assertTrue($a->is_valid_url($url));
        }

        foreach ([
             '',
             'Hello world',
             '<script>alert(1)</script>',
             ] as $url
        ) {
            $this->assertFalse($a->is_valid_url($url));
        }
    }
}

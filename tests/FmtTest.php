<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Fmt;
use PHPUnit\Framework\TestCase;

class FmtTest extends TestCase
{
    public function testPercentFormatI18n(): void
    {
        $fmt = new Fmt();
        $this->assertEquals($fmt->percent(0), '');
        $this->assertEquals($fmt->percent(0.00), '');
        $this->assertEquals($fmt->percent(1.00), '+100%');
        $this->assertEquals($fmt->percent(-1.00), '-100%');
        $this->assertEquals($fmt->percent(0.55), '+55%');
        $this->assertEquals($fmt->percent(-0.55), '-55%');
    }

    public function testGetReferrerUrlLabel(): void
    {
        $fmt = new Fmt();

        self::assertEquals('', $fmt->referrer_url_label(''));
        self::assertEquals('kokoanalytics.com', $fmt->referrer_url_label('https://www.kokoanalytics.com/'));
        self::assertEquals('kokoanalytics.com/about', $fmt->referrer_url_label('https://www.kokoanalytics.com/about'));
    }
}

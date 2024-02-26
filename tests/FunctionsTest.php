<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use function KokoAnalytics\fmt_large_number;

final class FunctionsTest extends TestCase
{
    public function testCanInstantiate(): void
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
}

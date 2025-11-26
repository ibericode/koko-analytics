<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Aggregator;
use KokoAnalytics\Blocklist;
use PHPUnit\Framework\TestCase;

final class BlocklistTest extends TestCase
{
    public function test(): void
    {
        $b = new Blocklist();
        self::assertIsBool($b->contains(''));
        $b->update(true);
        self::assertTrue($b->contains('1xslot.site'));
        self::assertFalse($b->contains('kokoanalytics.com'));
    }
}

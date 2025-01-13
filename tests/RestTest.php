<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Rest;
use PHPUnit\Framework\TestCase;

final class RestTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $i = new Rest();
        self::assertTrue($i instanceof Rest);
    }

    public function test_validate_date_param()
    {
        $rest = new Rest();
        self::assertTrue($rest->validate_date_param('2000-01-01', null, null));
        self::assertFalse($rest->validate_date_param('foobar', null, null));
    }
}

<?php
declare(strict_types=1);

use KokoAnalytics\Rest;
use PHPUnit\Framework\TestCase;

final class RestTest extends TestCase
{
    public function testCanInstantiate() : void
    {
        $i = new Rest();
        self::assertTrue($i instanceof Rest);
    }
}

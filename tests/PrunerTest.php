<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Pruner;
use PHPUnit\Framework\TestCase;

final class PrunerTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $i = new Pruner();
        self::assertTrue($i instanceof Pruner);
    }
}

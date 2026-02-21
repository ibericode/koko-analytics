<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Aggregator;
use KokoAnalytics\Plugin;
use PHPUnit\Framework\TestCase;

final class PluginTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $i = new Plugin();
        self::assertTrue($i instanceof Plugin);
    }
}

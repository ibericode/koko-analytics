<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Script_Loader;
use PHPUnit\Framework\TestCase;

final class ScriptLoaderTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $i = new Script_Loader();
        self::assertTrue($i instanceof Script_Loader);
    }
}

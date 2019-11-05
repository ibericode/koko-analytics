<?php
declare(strict_types=1);

use KokoAnalytics\ScriptLoader;
use PHPUnit\Framework\TestCase;

final class ScriptLoaderTest extends TestCase
{
    public function testCanInstantiate() : void
    {
        $i = new ScriptLoader();
        self::assertTrue($i instanceof ScriptLoader);
    }
}

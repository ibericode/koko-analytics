<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Script_Loader;
use PHPUnit\Framework\TestCase;
use stdClass;

final class ScriptLoaderTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $i = new Script_Loader();
        self::assertTrue($i instanceof Script_Loader);
    }

    public function test_get_canonical_path(): void
    {
        $GLOBALS['wp'] = new stdClass;
        $GLOBALS['wp']->public_query_vars = ['p', 'page_id', 'tag', 'cat'];

        $_SERVER['REQUEST_URI'] = '/';
        self::assertEquals(Script_Loader::get_canonical_path(), '/');

        $_SERVER['REQUEST_URI'] = '/about/?utm_medium=Email';
        self::assertEquals(Script_Loader::get_canonical_path(), '/about/');

        $_SERVER['REQUEST_URI'] = '/about/?utm_medium=Email&foo=bar';
        self::assertEquals(Script_Loader::get_canonical_path(), '/about/');
    }
}

<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests\Admin;

use KokoAnalytics\Admin\Controller;
use PHPUnit\Framework\TestCase;

final class AdminTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $i = new Controller();
        self::assertTrue($i instanceof Controller);
    }
}

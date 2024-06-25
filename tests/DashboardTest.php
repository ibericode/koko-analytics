<?php
declare(strict_types=1);

use KokoAnalytics\Dashboard;
use PHPUnit\Framework\TestCase;

final class DashboardTest extends TestCase
{
    public function testCanInstantiate() : void
    {
        $i = new Dashboard();
        self::assertTrue($i instanceof Dashboard);
    }
}

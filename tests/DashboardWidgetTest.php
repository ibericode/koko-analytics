<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Dashboard_Widget;
use PHPUnit\Framework\TestCase;

final class DashboardWidgetTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $i = new Dashboard_Widget();
        self::assertTrue($i instanceof Dashboard_Widget);
    }
}

<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Dashboard;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

final class DashboardTest extends TestCase
{
    public function testCanInstantiate(): void
    {
        $i = new Dashboard();
        self::assertTrue($i instanceof Dashboard);
    }

    public function testGetFirstDayOfCurrentWeekWithWeekStartOnSunday(): void
    {
        $i = new Dashboard();

        self::assertEquals(new \DateTimeImmutable('2025-01-05'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-05'), 0));
        self::assertEquals(new \DateTimeImmutable('2025-01-05'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-06'), 0));
        self::assertEquals(new \DateTimeImmutable('2025-01-05'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-07'), 0));
        self::assertEquals(new \DateTimeImmutable('2025-01-05'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-08'), 0));
        self::assertEquals(new \DateTimeImmutable('2025-01-05'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-09'), 0));
        self::assertEquals(new \DateTimeImmutable('2025-01-05'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-10'), 0));
        self::assertEquals(new \DateTimeImmutable('2025-01-05'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-11'), 0));
        self::assertEquals(new \DateTimeImmutable('2025-01-12'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-12'), 0));
    }

    public function testGetFirstDayOfCurrentWeekWithWeekStartOnMonday(): void
    {
        $i = new Dashboard();
        self::assertEquals(new \DateTimeImmutable('2024-12-30'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-05'), 1));
        self::assertEquals(new \DateTimeImmutable('2025-01-06'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-06'), 1));
        self::assertEquals(new \DateTimeImmutable('2025-01-06'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-07'), 1));
        self::assertEquals(new \DateTimeImmutable('2025-01-06'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-08'), 1));
        self::assertEquals(new \DateTimeImmutable('2025-01-06'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-09'), 1));
        self::assertEquals(new \DateTimeImmutable('2025-01-06'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-10'), 1));
        self::assertEquals(new \DateTimeImmutable('2025-01-06'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-11'), 1));
        self::assertEquals(new \DateTimeImmutable('2025-01-06'), $i->get_first_day_of_current_week(new DateTimeImmutable('2025-01-12'), 1));
    }

    public function testGetDatesForRangeThisWeekWithWeekStartOnSunday(): void
    {
        $i = new Dashboard();
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-11 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-05'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-11 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-06'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-11 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-07'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-11 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-08'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-11 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-09'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-11 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-10'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-11 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-11'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-12'), new DateTimeImmutable('2025-01-18 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-12'), 'this_week', 0));
    }

    public function testGetDatesForRangeThisWeekWithWeekStartOnMonday(): void
    {
        $i = new Dashboard();
        self::assertEquals([new DateTimeImmutable('2024-12-30'), new DateTimeImmutable('2025-01-05 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-05'), 'this_week', 1));
        self::assertEquals([new DateTimeImmutable('2025-01-06'), new DateTimeImmutable('2025-01-12 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-06'), 'this_week', 1));
    }

    public function testGetDatesForRangeLastWeekWithWeekStartOnSunday(): void
    {
        $i = new Dashboard();
        self::assertEquals([new DateTimeImmutable('2024-12-29'), new DateTimeImmutable('2025-01-04 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-05'), 'last_week', 0));
        self::assertEquals([new DateTimeImmutable('2024-12-29'), new DateTimeImmutable('2025-01-04 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-06'), 'last_week', 0));
    }

    public function testGetDatesForRangeLastWeekWithWeekStartOnMonday(): void
    {
        $i = new Dashboard();
        self::assertEquals([new DateTimeImmutable('2024-12-23'), new DateTimeImmutable('2024-12-29 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-05'), 'last_week', 1));
        self::assertEquals([new DateTimeImmutable('2024-12-30'), new DateTimeImmutable('2025-01-05 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-06'), 'last_week', 1));
    }
}

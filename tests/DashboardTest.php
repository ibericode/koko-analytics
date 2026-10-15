<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Dashboard;
use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

final class DashboardTest extends TestCase
{
    public function test_public_pagination_uses_buttons_without_crawlable_urls(): void
    {
        ob_start();
        Dashboard::pagination('posts', 20, 20, 100, [
            'filters' => [
                'type' => 'popular & trending+',
            ],
        ]);
        $html = (string) ob_get_clean();

        self::assertSame(2, substr_count($html, '<button'));
        self::assertStringContainsString('data-pagination-controls hidden', $html);
        self::assertStringContainsString('data-pagination-key="posts"', $html);
        self::assertStringContainsString('posts%5Bfilters%5D%5Btype%5D=popular+%26+trending%2B', $html);
        self::assertStringContainsString('posts%5Boffset%5D=40', $html);
        self::assertStringNotContainsString('href=', $html);
    }

    public function test_admin_pagination_uses_links(): void
    {
        ob_start();
        Dashboard::pagination('posts', 0, 20, 100, [], true);
        $html = (string) ob_get_clean();

        self::assertStringContainsString('<a ', $html);
        self::assertStringContainsString('href=', $html);
        self::assertStringNotContainsString('<button', $html);
        self::assertStringNotContainsString('data-pagination-controls', $html);
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
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-05 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-05'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-06 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-06'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-07 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-07'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-08 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-08'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-09 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-09'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-10 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-10'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-05'), new DateTimeImmutable('2025-01-11 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-11'), 'this_week', 0));
        self::assertEquals([new DateTimeImmutable('2025-01-12'), new DateTimeImmutable('2025-01-12 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-12'), 'this_week', 0));
    }

    public function testGetDatesForRangeThisWeekWithWeekStartOnMonday(): void
    {
        $i = new Dashboard();
        self::assertEquals([new DateTimeImmutable('2024-12-30'), new DateTimeImmutable('2025-01-05 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-05'), 'this_week', 1));
        self::assertEquals([new DateTimeImmutable('2025-01-06'), new DateTimeImmutable('2025-01-06 23:59:59')], $i->get_dates_for_range(new DateTimeImmutable('2025-01-06'), 'this_week', 1));
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

    public function testGetDatesForRollingRangesIncludesToday(): void
    {
        $i   = new Dashboard();
        $now = new DateTimeImmutable('2026-10-15 12:00:00');

        $ranges = [
            'last_7_days' => ['2026-10-09', '2026-10-15 23:59:59'],
            'last_14_days' => ['2026-10-02', '2026-10-15 23:59:59'],
            'last_28_days' => ['2026-09-18', '2026-10-15 23:59:59'],
            'last_91_days' => ['2026-07-17', '2026-10-15 23:59:59'],
        ];

        foreach ($ranges as $key => [$start, $end]) {
            self::assertEquals([new DateTimeImmutable($start), new DateTimeImmutable($end)], $i->get_dates_for_range($now, $key));
        }
    }

    public function testGetDatesForCurrentCalendarPeriodsEndsToday(): void
    {
        $i   = new Dashboard();
        $now = new DateTimeImmutable('2026-10-15 12:00:00');

        self::assertEquals([new DateTimeImmutable('2026-10-12'), new DateTimeImmutable('2026-10-15 23:59:59')], $i->get_dates_for_range($now, 'this_week', 1));
        self::assertEquals([new DateTimeImmutable('2026-10-01'), new DateTimeImmutable('2026-10-15 23:59:59')], $i->get_dates_for_range($now, 'this_month'));
        self::assertEquals([new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-10-15 23:59:59')], $i->get_dates_for_range($now, 'this_year'));
        self::assertEquals([new DateTimeImmutable('2025-11-01'), new DateTimeImmutable('2026-10-15 23:59:59')], $i->get_dates_for_range($now, 'last_12_months'));
    }

    public function testGetDatesForCurrentCalendarPeriodsCanReturnCompletePeriods(): void
    {
        $i   = new Dashboard();
        $now = new DateTimeImmutable('2026-10-15 12:00:00');

        self::assertEquals([new DateTimeImmutable('2026-10-12'), new DateTimeImmutable('2026-10-18 23:59:59')], $i->get_dates_for_range($now, 'this_week', 1, true));
        self::assertEquals([new DateTimeImmutable('2026-10-01'), new DateTimeImmutable('2026-10-31 23:59:59')], $i->get_dates_for_range($now, 'this_month', 0, true));
        self::assertEquals([new DateTimeImmutable('2026-01-01'), new DateTimeImmutable('2026-12-31 23:59:59')], $i->get_dates_for_range($now, 'this_year', 0, true));
        self::assertEquals([new DateTimeImmutable('2025-11-01'), new DateTimeImmutable('2026-10-31 23:59:59')], $i->get_dates_for_range($now, 'last_12_months', 0, true));
    }

    public function testGetNextPeriod(): void
    {
        $i = new Dashboard();

        // simple
        self::assertEquals($i->get_next_period(new DateTimeImmutable('2025-01-01'), new DateTimeImmutable('2025-01-02'), 1), [new DateTimeImmutable('2025-01-03'), new DateTimeImmutable('2025-01-04'), new DateTimeImmutable('2025-01-04')]);
        self::assertEquals($i->get_next_period(new DateTimeImmutable('2025-01-01'), new DateTimeImmutable('2025-01-02'), -1), [new DateTimeImmutable('2024-12-30'), new DateTimeImmutable('2024-12-31'), new DateTimeImmutable('2024-12-31')]);

        // large diff
        self::assertEquals($i->get_next_period(new DateTimeImmutable('2023-07-01'), new DateTimeImmutable('2025-06-11'), -1), [new DateTimeImmutable('2021-07-19'), new DateTimeImmutable('2023-06-30'), new DateTimeImmutable('2023-06-30')]);

        // full month range crossing a year boundary
        $previous = $i->get_next_period(new DateTimeImmutable('2025-11-01'), new DateTimeImmutable('2026-10-31 23:59:59'), -1);
        self::assertEquals(new DateTimeImmutable('2024-11-01'), $previous[0]);
        self::assertEquals(new DateTimeImmutable('2025-10-31 23:59:59'), $previous[1]);
    }

    public function testGetNextPeriodKeepsRollingRangesTheSameLength(): void
    {
        $i   = new Dashboard();
        $now = new DateTimeImmutable('2026-07-01 12:00:00');

        $previous = $i->get_next_period(new DateTimeImmutable('2026-02-01'), new DateTimeImmutable('2026-02-28 23:59:59'), -1, false, $now);
        self::assertEquals(new DateTimeImmutable('2026-01-04'), $previous[0]);
        self::assertEquals(new DateTimeImmutable('2026-01-31 23:59:59'), $previous[1]);

        $previous = $i->get_next_period(new DateTimeImmutable('2026-04-01'), new DateTimeImmutable('2026-06-30 23:59:59'), -1, false, $now);
        self::assertEquals(new DateTimeImmutable('2025-12-31'), $previous[0]);
        self::assertEquals(new DateTimeImmutable('2026-03-31 23:59:59'), $previous[1]);
    }

    public function testGetNextPeriodDoesNotComparePastThePeriodEnd(): void
    {
        $i   = new Dashboard();
        $now = new DateTimeImmutable('2026-03-31 12:00:00');

        $previous = $i->get_next_period(new DateTimeImmutable('2026-03-01'), new DateTimeImmutable('2026-03-31 23:59:59'), -1, true, $now);
        self::assertEquals(new DateTimeImmutable('2026-02-01'), $previous[0]);
        self::assertEquals(new DateTimeImmutable('2026-02-28 23:59:59'), $previous[1]);
        self::assertEquals($previous[1], $previous[2]);
    }
}

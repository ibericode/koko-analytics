<?php

declare(strict_types=1);

namespace KokoAnalytics\Tests;

use KokoAnalytics\Stats;
use PHPUnit\Framework\TestCase;

final class StatsTest extends TestCase
{
    private Stats $stats;

    protected function setUp(): void
    {
        $this->stats = new Stats();
    }

    public function testGenerateDateRangeByDay(): void
    {
        $result = $this->stats->generate_date_range('2025-01-01', '2025-01-05', 'day');
        self::assertEquals(['2025-01-01', '2025-01-02', '2025-01-03', '2025-01-04', '2025-01-05'], $result);
    }

    public function testGenerateDateRangeSingleDay(): void
    {
        $result = $this->stats->generate_date_range('2025-03-15', '2025-03-15', 'day');
        self::assertEquals(['2025-03-15'], $result);
    }

    public function testGenerateDateRangeByWeekStartingSunday(): void
    {
        // 2025-01-08 is a Wednesday; aligned back to the previous Sunday (2025-01-05)
        update_option('start_of_week', 0);
        $result = $this->stats->generate_date_range('2025-01-08', '2025-01-22', 'week');
        self::assertEquals(['2025-01-05', '2025-01-12', '2025-01-19'], $result);
    }

    public function testGenerateDateRangeByWeekStartingMonday(): void
    {
        // 2025-01-08 is a Wednesday; aligned back to the previous Monday (2025-01-06)
        update_option('start_of_week', 1);
        $result = $this->stats->generate_date_range('2025-01-08', '2025-01-22', 'week');
        self::assertEquals(['2025-01-06', '2025-01-13', '2025-01-20'], $result);
    }

    public function testGenerateDateRangeByWeekStartAlreadyAligned(): void
    {
        // 2025-01-06 is a Monday; no alignment needed when week starts on Monday
        update_option('start_of_week', 1);
        $result = $this->stats->generate_date_range('2025-01-06', '2025-01-19', 'week');
        self::assertEquals(['2025-01-06', '2025-01-13'], $result);
    }

    public function testGenerateDateRangeByMonth(): void
    {
        // Start mid-month; aligns to first day of month
        $result = $this->stats->generate_date_range('2025-01-15', '2025-03-10', 'month');
        self::assertEquals(['2025-01-01', '2025-02-01', '2025-03-01'], $result);
    }

    public function testGenerateDateRangeByMonthAlreadyAligned(): void
    {
        $result = $this->stats->generate_date_range('2025-01-01', '2025-02-01', 'month');
        self::assertEquals(['2025-01-01', '2025-02-01'], $result);
    }

    public function testGenerateDateRangeByYear(): void
    {
        // Start mid-year; aligns to Jan 1st of that year
        $result = $this->stats->generate_date_range('2024-06-15', '2025-03-10', 'year');
        self::assertEquals(['2024-01-01', '2025-01-01'], $result);
    }

    public function testGenerateDateRangeByYearAlreadyAligned(): void
    {
        $result = $this->stats->generate_date_range('2024-01-01', '2024-12-31', 'year');
        self::assertEquals(['2024-01-01'], $result);
    }
}

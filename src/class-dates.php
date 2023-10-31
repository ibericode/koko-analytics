<?php

namespace KokoAnalytics;

class Dates
{
    public function get_range(string $key): array
    {
        switch ($key) {
            case 'today':
                return [
                    new \DateTimeImmutable('today midnight'),
                    new \DateTimeImmutable('tomorrow midnight, -1 second')
                ];
            case 'yesterday':
                return [
                    new \DateTimeImmutable('yesterday midnight'),
                    new \DateTimeImmutable('today midnight, -1 second')
                ];
            case 'this_week':
                $offset = get_option('start_of_week', 0);
                return [
                    (new \DateTimeImmutable('last sunday, midnight'))->modify("+$offset days"),
                    (new \DateTimeImmutable('next sunday, midnight, -1 second'))->modify("+$offset days")
                ];
            case 'last_week':
                $offset = get_option('start_of_week', 0);
                return [
                    (new \DateTimeImmutable('last sunday, midnight, -7 days'))->modify("+$offset days"),
                    (new \DateTimeImmutable('last sunday, midnight, -1 second'))->modify("+$offset days"),
                ];
            case 'last_14_days':
                return [
                    new \DateTimeImmutable('-14 days'),
                    new \DateTimeImmutable('tomorrow midnight, -1 second')
                ];
            case 'last_28_days':
                return [
                    new \DateTimeImmutable('-28 days'),
                    new \DateTimeImmutable('tomorrow midnight, -1 second')
                ];
            case 'this_month':
                return [
                    new \DateTimeImmutable('first day of this month'),
                    new \DateTimeImmutable('last day of this month')
                ];
            case 'last_month':
                return [
                    new \DateTimeImmutable('first day of last month, midnight'),
                    new \DateTimeImmutable('last day of last month')
                ];
            case 'this_year':
                $now = new \DateTimeImmutable('now');
                return [
                    $now->setDate($now->format('Y'), 1, 1),
                    $now->setDate($now->format('Y'), 12, 31),
                ];
            case 'last_year':
                $now = new \DateTimeImmutable('now');
                return [
                    $now->setDate($now->format('Y') - 1, 1, 1),
                    $now->setDate($now->format('Y') - 1, 12, 31),
                ];
            default:
                throw new \InvalidArgumentException("Invalid date preset: $key");
        }
    }
}

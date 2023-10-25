<?php

namespace KokoAnalytics;

class Dates
{
    public function get_range(string $key): array
    {
        switch ($key) {
            case 'today':
                return [
                    new \DateTime('today midnight'),
                    new \DateTime('tomorrow midnight, -1 second')
                ];
            case 'yesterday':
                return [
                    new \DateTime('yesterday midnight'),
                    new \DateTime('today midnight, -1 second')
                ];
            case 'this_week':
                return [
                    new \DateTime('last monday, midnight'),
                    new \DateTime('next monday, midnight, -1 second')
                ];
            case 'last_week':
                return [
                    new \DateTime('last monday, midnight, -7 days'),
                    new \DateTime('last monday, midnight, -1 second')
                ];
            case 'last_28_days':
                return [
                    new \DateTime('-28 days'),
                    new \DateTime('tomorrow midnight, -1 second')
                ];
            case 'this_month':
                return [
                    new \DateTime('first day of this month'),
                    new \DateTime('last day of this month')
                ];
            case 'last_month':
                return [
                    new \DateTime('first day of last month, midnight'),
                    new \DateTime('last day of last month')
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

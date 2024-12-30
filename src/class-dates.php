<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

class Dates
{
    public function get_range(string $key): array
    {
        $now = create_local_datetime('now');

        switch ($key) {
            case 'today':
                return [
                    $now->modify('today midnight'),
                    $now->modify('tomorrow midnight, -1 second')
                ];
            case 'yesterday':
                return [
                    $now->modify('yesterday midnight'),
                    $now->modify('today midnight, -1 second')
                ];
            case 'this_week':
                $offset = (int) get_option('start_of_week', 0);
                $last_sunday = (int) $now->format('w') === 0 ? $now : $now->modify('last sunday');
                return [
                    ($last_sunday->modify('midnight'))->modify("+$offset days"),
                    ($now->modify('next sunday, midnight, -1 second'))->modify("+$offset days")
                ];
            case 'last_week':
                $offset = (int) get_option('start_of_week', 0);
                $last_sunday = (int) $now->format('w') === 0 ? $now : $now->modify('last sunday');
                return [
                    ($last_sunday->modify('midnight, -7 days'))->modify("+$offset days"),
                    ($last_sunday->modify('midnight, -1 second'))->modify("+$offset days"),
                ];
            case 'last_14_days':
                return [
                    $now->modify('-14 days'),
                    $now->modify('tomorrow midnight, -1 second')
                ];
            default:
            case 'last_28_days':
                return [
                    $now->modify('-28 days'),
                    $now->modify('tomorrow midnight, -1 second')
                ];
            case 'this_month':
                return [
                    $now->modify('first day of this month'),
                    $now->modify('last day of this month')
                ];
            case 'last_month':
                return [
                    $now->modify('first day of last month, midnight'),
                    $now->modify('last day of last month')
                ];
            case 'this_year':
                return [
                    $now->setDate($now->format('Y'), 1, 1),
                    $now->setDate($now->format('Y'), 12, 31),
                ];
            case 'last_year':
                return [
                    $now->setDate($now->format('Y') - 1, 1, 1),
                    $now->setDate($now->format('Y') - 1, 12, 31),
                ];
        }
    }
}

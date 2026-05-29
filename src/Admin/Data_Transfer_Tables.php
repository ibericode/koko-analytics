<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics\Admin;

class Data_Transfer_Tables
{
    public const BATCH_SIZE = 500;

    /**
     * @return array<string, array{columns: string[], placeholders: string[]}>
     */
    public static function get(): array
    {
        return apply_filters('koko_analytics_data_transfer_tables', [
            'koko_analytics_site_stats' => [
                'columns' => ['date', 'visitors', 'pageviews'],
                'placeholders' => ['%s', '%d', '%d'],
            ],
            'koko_analytics_paths' => [
                'columns' => ['id', 'path'],
                'placeholders' => ['%d', '%s'],
            ],
            'koko_analytics_post_stats' => [
                'columns' => ['date', 'path_id', 'post_id', 'visitors', 'pageviews'],
                'placeholders' => ['%s', '%d', '%d', '%d', '%d'],
            ],
            'koko_analytics_referrer_labels' => [
                'columns' => ['id', 'value'],
                'placeholders' => ['%d', '%s'],
            ],
            'koko_analytics_referrer_stats' => [
                'columns' => ['date', 'id', 'unique_hits', 'hits'],
                'placeholders' => ['%s', '%d', '%d', '%d'],
            ],
        ]);
    }
}

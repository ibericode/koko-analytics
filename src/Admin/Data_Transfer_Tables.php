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
        $tables = apply_filters('koko_analytics_data_transfer_tables', [
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

        if (! is_array($tables)) {
            return [];
        }

        return array_filter($tables, [self::class, 'is_valid_table_spec'], ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param mixed $spec
     * @param mixed $table
     */
    private static function is_valid_table_spec($spec, $table): bool
    {
        if (! is_string($table) || ! preg_match('/^koko_analytics_[a-z0-9_]+$/', $table)) {
            return false;
        }

        if (! is_array($spec) || ! isset($spec['columns'], $spec['placeholders']) || ! is_array($spec['columns']) || ! is_array($spec['placeholders'])) {
            return false;
        }

        if (count($spec['columns']) === 0 || count($spec['columns']) !== count($spec['placeholders'])) {
            return false;
        }

        foreach ($spec['columns'] as $column) {
            if (! is_string($column) || ! preg_match('/^[a-z0-9_]+$/', $column)) {
                return false;
            }
        }

        foreach ($spec['placeholders'] as $placeholder) {
            if (! in_array($placeholder, ['%s', '%d', '%f'], true)) {
                return false;
            }
        }

        return true;
    }
}

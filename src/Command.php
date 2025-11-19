<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

use KokoAnalytics\Admin\Actions;
use WP_CLI;

class Command
{
    /**
     * Aggregates stats from the pageview buffer file into permanent storage
     *
     * ## EXAMPLES
     *
     *     wp koko-analytics aggregate
     *
     * @param array $args
     * @param array $assoc_args
     */
    public function aggregate($args, $assoc_args)
    {
        WP_CLI::line('Aggregating data...');
        Aggregator::run();
        WP_CLI::success('Done! Data aggregated.');
    }

    public function prune($args, $assoc_args)
    {
        WP_CLI::line('Pruning data...');
        Pruner::run();
        WP_CLI::success('Done! Data pruned.');
    }

    public function migrate_post_stats_to_v2($args, $assoc_args)
    {
        WP_CLI::line('Migrating post stats...');
        Actions::migrate_post_stats_to_v2();
        WP_CLI::success('Done! Post stats migrated.');
    }

    public function migrate_referrer_stats_to_v2($args, $assoc_args)
    {
        WP_CLI::line('Migrating referrer stats...');
        Actions::migrate_referrer_stats_to_v2();
        WP_CLI::success('Done! Referrer stats migrated.');
    }
}

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
        WP_CLI::success('Data aggregated');
    }

    /**
     * Removes data older than the treshold specified on the settings page
     */
    public function prune($args, $assoc_args)
    {
        WP_CLI::line('Pruning data...');
        Pruner::run();
        WP_CLI::success('Data pruned');
    }

    /**
     * Updates the built-in referrer blocklist
     *
     * @subcommand update-referrer-blocklist
     */
    public function update_referrer_blocklist($args, $assoc_args)
    {
        (new Blocklist())->update(true);
        WP_CLI::success('Blocklist updated');
    }

    /**
     * Migrates the post_stats database qtable to the new v2 structure.
     */
    public function migrate_post_stats_to_v2($args, $assoc_args)
    {
        WP_CLI::line('Migrating post stats...');
        Actions::migrate_post_stats_to_v2();
        WP_CLI::success('Post stats migrated');
    }

    /**
     * Migrates referrer stats to the new v2 format.
     */
    public function migrate_referrer_stats_to_v2($args, $assoc_args)
    {
        WP_CLI::line('Migrating referrer stats...');
        Actions::migrate_referrer_stats_to_v2();
        WP_CLI::success('Referrer stats migrated');
    }
}

<?php

/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */

namespace KokoAnalytics;

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
        $aggregator = new Aggregator();
        $aggregator->aggregate();
        WP_CLI::success('Stats aggregated.');
    }
}

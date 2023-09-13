<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Danny van Kooten
 */
namespace KokoAnalytics;

use WP_CLI;

class Command {

	/**
	 * Aggregates stats from the pageview buffer file into permanent storage
	 *
	 * ## EXAMPLES
	 *
	 *     wp koko-analytics aggregate
	 */
	function aggregate( $args, $assoc_args ) {
		$force = $assoc_args['force'] ?? false;
		$aggregator = new Aggregator();
		$aggregator->aggregate($force);
		WP_CLI::success( 'Stats aggregated.' );
	}
}

<?php
/**
 * @package koko-analytics
 * @license GPL-3.0+
 * @author Anil Kulkarni
 * Adds support for a shortcode to display the number of times a page or a site has been viewed
 * For example, to show the last 30 day stats in the footer
 * <?php
 *   echo do_shortcode('[koko_analytics_site_counter days=30]');
 * ?>
 *
 * Options:
 * days: How many previous days to count. Defaults to -1 which means show views for all time
 * use_pageviews: If you want to counte pageviews instead of visitors, set this to True
 * always_show_global_stats: By default, the value of the counter will depend on the page.
 *     If you're viewing a single page, it shows the stats only for that page, else the whole site.
 *     Setting always_show_global_stats to True means the stats will always be for the whole site
 */

namespace KokoAnalytics;

class ShortCode_Site_Counter {
	const SHORTCODE = 'koko_analytics_site_counter';

	public function init() {
		add_shortcode( self::SHORTCODE, array( $this, 'content' ) );
	}

	public function content( $args ) {
		$default_args = array(
		'days' => -1,
		'use_pageviews' => false,
		'always_show_global_stats' => false,
		);
		$args = shortcode_atts( $default_args, $args, self::SHORTCODE );
		$days = $args['days'];
		# Since the column value is directly in the sql query, hard code it
		# to prevent SQL injection attacks.
		$column = $args['use_pageviews'] === true ? 'pageviews' : 'visitors';

		if ( ! is_single() || $args['always_show_global_stats'] === true ) {
			$count = $this->get_site_views( $days, $column );
		} else {
			$count = $this->get_single_page_views( $days, $column );
		}

		$html = sprintf( PHP_EOL . ' <span class="koko-analytics-post-count">%s</span>', $count );
		return $html;
	}

	private function get_start_date( $days ) {
		$timezone = get_option( 'timezone_string', 'UTC' );
			$datetime = new \DateTime( 'now', new \DateTimeZone( $timezone ) );
			$datetime->modify( sprintf( '-%d days', $days ) );
			$start_date = $datetime->format( 'Y-m-d' );
			return $start_date;
	}

	private function get_site_views( $days, $column ) {
		global $wpdb;
		if ( $days == -1 ) {
				$sql = "SELECT SUM({$column}) FROM {$wpdb->prefix}koko_analytics_site_stats";
		} else {
				$start_date = $this->get_start_date( $days );
				$sql = $wpdb->prepare( "SELECT SUM({$column}) FROM {$wpdb->prefix}koko_analytics_site_stats s WHERE s.date >= %s", array( $start_date ) );
		}
		return (int) ( $wpdb->get_var( $sql ) ?? 0 );
	}

	private function get_single_page_views( $days, $column ) {
		global $wpdb;
		if ( $days == -1 ) {
				$sql = $wpdb->prepare( "SELECT SUM({$column}) FROM {$wpdb->prefix}koko_analytics_post_stats s WHERE s.id = %d", array( get_the_ID() ) );
		} else {
				$start_date = $this->get_start_date( $days );
				$sql = $wpdb->prepare( "SELECT SUM({$column}) FROM {$wpdb->prefix}koko_analytics_post_stats s WHERE s.id = %d AND s.date >= %s", array( get_the_ID(), $start_date ) );
		}
		return (int) ( $wpdb->get_var( $sql ) ?? 0 );

	}
}
